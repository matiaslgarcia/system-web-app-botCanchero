<?php
/**
 * Jugada 14 — normaliza customers.phone al formato canónico (+549XXXXXXXXXX)
 * y fusiona las fichas duplicadas que hoy existen por culpa de formatos
 * distintos del mismo número (con/sin "+", con/sin "54", con/sin "9", etc.).
 *
 * Uso:
 *   php scripts/merge_duplicate_customers.php --dry-run   (default, no escribe nada)
 *   php scripts/merge_duplicate_customers.php --apply     (aplica los cambios en una transacción)
 *
 * Qué hace, en orden:
 *   1. Agrupa todos los customers por teléfono normalizado (Customers::normalizePhone).
 *   2. Para cada grupo con más de una ficha, elige como "canónica" la que tiene
 *      más reservas (booking + recurring_booking combinados); empate -> id más bajo (la más vieja).
 *   3. Re-apunta a la ficha canónica todas las referencias de las duplicadas:
 *      booking.id_customer, recurring_booking.customer_id, booking_waitlist.customer_id,
 *      customer_consent.customer_id, customer_note.customer_id, customer_tag_map.customer_id,
 *      customer_privacy_scope.customer_id.
 *   4. Antes de re-apuntar customer_tag_map / customer_privacy_scope (que tienen UNIQUE
 *      compuesto), borra las filas duplicadas que ya existan en la canónica para esa
 *      combinación, así el UPDATE no revienta por clave duplicada.
 *   5. Actualiza el teléfono de la ficha canónica al formato normalizado y borra las
 *      fichas duplicadas ya vacías.
 *   6. Además, normaliza el teléfono de TODOS los customers restantes (incluso los que
 *      no tenían duplicado), para que a partir de ahora todo quede en un solo formato.
 *
 * Todo corre dentro de una transacción: si algo falla, se hace rollback completo.
 * Con --dry-run (default) solo imprime el plan, no toca la base.
 */

define('SKIP_AUTH', true);
require __DIR__ . '/../app/int.php';

$apply = in_array('--apply', $argv, true);

function out($msg) {
    echo $msg . PHP_EOL;
}

// query() atrapa PDOException internamente y, para UPDATE/DELETE, devuelve
// false tanto si la query falló como si simplemente no hay fila que
// fetch() — no se puede distinguir un error real mirando su retorno. Para
// las escrituras de esta migración usamos el statement de PDO directo, que
// con ATTR_ERRMODE_EXCEPTION sí relanza una excepción real ante un error de
// SQL, y esa excepción es la que dispara el rollback de la transacción.
function mustQuery($sql, $params = []) {
    $stmt = conexion($sql);
    $stmt->execute($params);
    return $stmt;
}

out($apply ? '=== MODO: APLICAR CAMBIOS ===' : '=== MODO: DRY-RUN (no se escribe nada; usá --apply para ejecutar) ===');

$pdo = Db::pdo();

// 1) Traer todos los customers y agruparlos por teléfono normalizado.
$customers = query("SELECT id, full_name, phone, email FROM customers ORDER BY id ASC", 'ALL');
$groups = [];
foreach ($customers as $c) {
    $norm = Customers::normalizePhone($c->phone);
    if ($norm === '') continue; // teléfono vacío/inválido: no se toca.
    $groups[$norm][] = $c;
}

$duplicateGroups = array_filter($groups, fn($g) => count($g) > 1);
out(sprintf('Customers totales: %d | Teléfonos únicos: %d | Grupos duplicados: %d', count($customers), count($groups), count($duplicateGroups)));

if (empty($duplicateGroups)) {
    out('No hay duplicados para fusionar.');
}

// Tablas que referencian customers, con su columna de FK.
$FK_TABLES = [
    ['table' => 'booking',                 'col' => 'id_customer'],
    ['table' => 'recurring_booking',       'col' => 'customer_id'],
    ['table' => 'booking_waitlist',        'col' => 'customer_id'],
    ['table' => 'customer_consent',        'col' => 'customer_id'],
    ['table' => 'customer_note',           'col' => 'customer_id'],
    ['table' => 'customer_privacy_scope',  'col' => 'customer_id'],
    ['table' => 'customer_tag_map',        'col' => 'customer_id'],
];

function tableExistsForMerge($pdo, $table) {
    static $cache = [];
    if (!isset($cache[$table])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
        $stmt->execute([$table]);
        $cache[$table] = (bool) $stmt->fetchColumn();
    }
    return $cache[$table];
}

function bookingCount($customerId) {
    $b = query("SELECT COUNT(*) AS c FROM booking WHERE id_customer = ?", '', [$customerId]);
    $rb = 0;
    if (tableExistsForMerge(Db::pdo(), 'recurring_booking')) {
        $rbRow = query("SELECT COUNT(*) AS c FROM recurring_booking WHERE customer_id = ?", '', [$customerId]);
        $rb = (int) ($rbRow->c ?? 0);
    }
    return (int) ($b->c ?? 0) + $rb;
}

$pdo->beginTransaction();
$mergedCount = 0;
$movedRows = 0;

try {
    foreach ($duplicateGroups as $normPhone => $group) {
        // Elegir canónico: más reservas, empate -> id más bajo.
        usort($group, function ($a, $b) {
            $ca = bookingCount($a->id);
            $cb = bookingCount($b->id);
            if ($ca !== $cb) return $cb <=> $ca;
            return $a->id <=> $b->id;
        });
        $canonical = $group[0];
        $duplicates = array_slice($group, 1);

        out(sprintf(
            '--- %s: canónico #%d (%s, %d reservas) <- fusiona %s',
            $normPhone,
            $canonical->id,
            $canonical->full_name,
            bookingCount($canonical->id),
            implode(', ', array_map(fn($d) => '#' . $d->id . ' (' . $d->full_name . ', tel guardado "' . $d->phone . '")', $duplicates))
        ));

        foreach ($duplicates as $dup) {
            foreach ($FK_TABLES as $fk) {
                if (!tableExistsForMerge($pdo, $fk['table'])) continue;

                // customer_tag_map y customer_privacy_scope tienen UNIQUE compuesto:
                // si el canónico ya tiene esa misma fila (mismo tag / mismo establishment),
                // el UPDATE de la duplicada rompería la clave única. Las borramos primero.
                if ($fk['table'] === 'customer_tag_map') {
                    $sql = "DELETE d FROM customer_tag_map d
                            INNER JOIN customer_tag_map c ON c.tag_id = d.tag_id AND c.customer_id = ?
                            WHERE d.customer_id = ?";
                    if ($apply) mustQuery($sql, [$canonical->id, $dup->id]);
                    else out("  [dry-run] limpiaría tags duplicados de #{$dup->id} contra #{$canonical->id}");
                }
                if ($fk['table'] === 'customer_privacy_scope') {
                    $sql = "DELETE d FROM customer_privacy_scope d
                            INNER JOIN customer_privacy_scope c ON c.establishment_id = d.establishment_id AND c.customer_id = ?
                            WHERE d.customer_id = ?";
                    if ($apply) mustQuery($sql, [$canonical->id, $dup->id]);
                    else out("  [dry-run] limpiaría privacy_scope duplicado de #{$dup->id} contra #{$canonical->id}");
                }

                $countSql = "SELECT COUNT(*) AS c FROM {$fk['table']} WHERE {$fk['col']} = ?";
                $countRow = query($countSql, '', [$dup->id]);
                $count = (int) ($countRow->c ?? 0);
                if ($count > 0) {
                    $movedRows += $count;
                    out("  {$fk['table']}.{$fk['col']}: {$count} fila(s) de #{$dup->id} -> #{$canonical->id}");
                    if ($apply) {
                        mustQuery("UPDATE {$fk['table']} SET {$fk['col']} = ? WHERE {$fk['col']} = ?", [$canonical->id, $dup->id]);
                    }
                }
            }

            if ($apply) {
                mustQuery("DELETE FROM customers WHERE id = ?", [$dup->id]);
            } else {
                out("  [dry-run] borraría customers #{$dup->id}");
            }
        }

        // Dejar el teléfono canónico en formato normalizado.
        if ($apply) {
            mustQuery("UPDATE customers SET phone = ? WHERE id = ?", [$normPhone, $canonical->id]);
        }
        $mergedCount += count($duplicates);
    }

    // Normalizar también los que no tenían duplicado, para que todo quede parejo.
    $rewritten = 0;
    foreach ($groups as $normPhone => $group) {
        if (count($group) > 1) continue; // ya se dejó normalizado arriba (si hubo merge)
        $c = $group[0];
        if ($c->phone !== $normPhone) {
            $rewritten++;
            if ($apply) {
                mustQuery("UPDATE customers SET phone = ? WHERE id = ?", [$normPhone, $c->id]);
            }
        }
    }
    out(sprintf('Teléfonos re-formateados (sin duplicado, solo formato): %d', $rewritten));

    if ($apply) {
        $pdo->commit();
        out(sprintf('APLICADO. Fichas fusionadas: %d | Filas re-apuntadas: %d', $mergedCount, $movedRows));
        if (function_exists('audit')) {
            audit('customers_merged_duplicates', 'customers', null, ['merged' => $mergedCount, 'moved_rows' => $movedRows]);
        }
    } else {
        $pdo->rollBack();
        out(sprintf('DRY-RUN completo (nada escrito). Fichas que se fusionarían: %d | Filas que se re-apuntarían: %d', $mergedCount, $movedRows));
        out('Corré con --apply para ejecutar de verdad.');
    }
} catch (Throwable $e) {
    $pdo->rollBack();
    out('ERROR, se hizo rollback completo: ' . $e->getMessage());
    exit(1);
}
