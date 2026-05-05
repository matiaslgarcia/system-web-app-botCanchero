<?php

define('SKIP_AUTH', true);
require __DIR__ . '/../app/int.php';

function fail($msg) {
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(1);
}

$field = query("SELECT id FROM soccer_field ORDER BY id ASC LIMIT 1", 'ARRAY');
if (!$field) fail('No hay canchas');
$fieldId = (int) $field['id'];

$schedules = query("SELECT id FROM schedules ORDER BY id ASC LIMIT 2", 'ALL');
if (!$schedules || count($schedules) < 2) fail('No hay suficientes horarios');
$hourA = (int) $schedules[0]->id;
$hourB = (int) $schedules[1]->id;

$date = date('Y-m-d', strtotime('+2 day'));
$day = dayName($date);
$seed = time();

$phoneA = '54915' . substr((string) $seed, -8);
$phoneB = '54916' . substr((string) $seed, -8);

query("INSERT INTO customers (full_name, phone, email) VALUES (?, ?, ?)", '', ['Smoke Reagenda A', $phoneA, '']);
query("INSERT INTO customers (full_name, phone, email) VALUES (?, ?, ?)", '', ['Smoke Reagenda B', $phoneB, '']);

$custA = query("SELECT id FROM customers WHERE phone = ? ORDER BY id DESC LIMIT 1", 'ARRAY', [$phoneA]);
$custB = query("SELECT id FROM customers WHERE phone = ? ORDER BY id DESC LIMIT 1", 'ARRAY', [$phoneB]);
if (!$custA || !$custB) fail('No se pudieron crear clientes de prueba');

query(
    "INSERT INTO booking (id_customer, id_field, day_booking, time_booking, date_booking, user, status)
     VALUES (?, ?, ?, ?, ?, 1, 1)",
    '',
    [(int) $custA['id'], $fieldId, $day, $hourA, $date]
);
query(
    "INSERT INTO booking (id_customer, id_field, day_booking, time_booking, date_booking, user, status)
     VALUES (?, ?, ?, ?, ?, 1, 1)",
    '',
    [(int) $custB['id'], $fieldId, $day, $hourB, $date]
);

$bA = query("SELECT id FROM booking WHERE id_customer = ? ORDER BY id DESC LIMIT 1", 'ARRAY', [(int) $custA['id']]);
$bB = query("SELECT id FROM booking WHERE id_customer = ? ORDER BY id DESC LIMIT 1", 'ARRAY', [(int) $custB['id']]);
if (!$bA || !$bB) fail('No se pudieron crear reservas de prueba');

$bookingA = (int) $bA['id'];
$bookingB = (int) $bB['id'];

try {
    $ref = new ReflectionMethod('Booking', 'hasSlotConflict');
    $ref->setAccessible(true);
    $conflict = (bool) $ref->invoke(null, $fieldId, $date, $hourB, $bookingA);

    // Cleanup best effort.
    query("UPDATE booking SET status = 2 WHERE id IN (?, ?)", '', [$bookingA, $bookingB]);

    echo json_encode([
        'ok' => true,
        'field_id' => $fieldId,
        'date' => $date,
        'booking_a' => $bookingA,
        'booking_b' => $bookingB,
        'target_hour' => $hourB,
        'conflict_detected' => $conflict
    ], JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $e) {
    query("UPDATE booking SET status = 2 WHERE id IN (?, ?)", '', [$bookingA, $bookingB]);
    fail('Error en reflection hasSlotConflict: ' . $e->getMessage());
}
