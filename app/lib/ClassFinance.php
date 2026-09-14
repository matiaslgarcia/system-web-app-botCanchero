<?php

class Finance {
    private static $tableExistsCache = [];
    private static $columnExistsCache = [];

    private static function tableExists($tableName) {
        $tableName = trim((string) $tableName);
        if ($tableName === '') return false;
        if (array_key_exists($tableName, self::$tableExistsCache)) {
            return self::$tableExistsCache[$tableName];
        }
        $row = query(
            "SELECT 1
               FROM information_schema.tables
              WHERE table_schema = DATABASE()
                AND table_name = ?
              LIMIT 1",
            'ARRAY',
            [$tableName]
        );
        self::$tableExistsCache[$tableName] = !empty($row);
        return self::$tableExistsCache[$tableName];
    }

    private static function columnExists($tableName, $columnName) {
        $tableName = trim((string) $tableName);
        $columnName = trim((string) $columnName);
        if ($tableName === '' || $columnName === '') return false;
        $cacheKey = $tableName . '.' . $columnName;
        if (array_key_exists($cacheKey, self::$columnExistsCache)) {
            return self::$columnExistsCache[$cacheKey];
        }
        $row = query(
            "SELECT 1
               FROM information_schema.columns
              WHERE table_schema = DATABASE()
                AND table_name = ?
                AND column_name = ?
              LIMIT 1",
            'ARRAY',
            [$tableName, $columnName]
        );
        self::$columnExistsCache[$cacheKey] = !empty($row);
        return self::$columnExistsCache[$cacheKey];
    }

    public static function isInfrastructureReady() {
        return self::tableExists('expense')
            && self::tableExists('cash_session')
            && self::tableExists('cash_movement');
    }

    public static function currentEstablishmentId() {
        return (int) FeatureGate::currentEstablishmentId();
    }

    private static function currentUserId() {
        return (int) ($_SESSION['canchero'] ?? 0);
    }

    private static function normalizeDate($value, $default = null) {
        $value = trim((string) $value);
        if ($value === '') {
            return $default ?: date('Y-m-d');
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            return setDate($value);
        }
        $ts = strtotime($value);
        return $ts ? date('Y-m-d', $ts) : ($default ?: date('Y-m-d'));
    }

    private static function normalizeDateTime($value, $fallbackDate = null) {
        $value = trim((string) $value);
        if ($value === '') {
            $date = self::normalizeDate($fallbackDate ?: date('Y-m-d'));
            return $date . ' ' . date('H:i:s');
        }
        $ts = strtotime(str_replace('T', ' ', $value));
        if ($ts === false) {
            $date = self::normalizeDate($fallbackDate ?: date('Y-m-d'));
            return $date . ' ' . date('H:i:s');
        }
        return date('Y-m-d H:i:s', $ts);
    }

    public static function normalizeMonth($value, $default = null) {
        $value = trim((string) $value);
        if ($value === '') {
            return $default ?: date('Y-m');
        }
        if (preg_match('/^\d{4}-\d{2}$/', $value)) {
            return $value;
        }
        $ts = strtotime($value . '-01');
        return $ts ? date('Y-m', $ts) : ($default ?: date('Y-m'));
    }

    public static function monthRange($month = null) {
        $month = self::normalizeMonth($month);
        $fromDate = $month . '-01';
        return [
            'month' => $month,
            'from_date' => $fromDate,
            'to_date' => date('Y-m-t', strtotime($fromDate)),
        ];
    }

    public static function previousMonth($month = null) {
        $month = self::normalizeMonth($month);
        return date('Y-m', strtotime($month . '-01 -1 month'));
    }

    private static function isFeatureEnabled($establishmentId) {
        return FeatureGate::isEnabled((int) $establishmentId, 'mod_finance', true);
    }

    private static function isAnalyticsEnabled($establishmentId) {
        return FeatureGate::isEnabled((int) $establishmentId, 'mod_analytics', true);
    }

    private static function canAccessEstablishment($establishmentId) {
        $establishmentId = (int) $establishmentId;
        if ($establishmentId <= 0) return false;
        if (Users::isSuperAdmin()) return true;
        return $establishmentId === (int) self::currentEstablishmentId();
    }

    private static function ensureCanManage($establishmentId) {
        $establishmentId = (int) $establishmentId;
        if (!self::canAccessEstablishment($establishmentId)) {
            JSON(['error' => 'No tenés permiso para operar este establecimiento'], 403, true);
        }
        if (!Users::isSuperAdmin() && !self::isFeatureEnabled($establishmentId)) {
            JSON(['error' => 'El módulo de caja y egresos no está habilitado para este establecimiento'], 403, true);
        }
    }

    private static function normalizeSelectedEstablishmentId($selectedEstablishmentId = 0) {
        if (Users::isSuperAdmin()) {
            $selectedEstablishmentId = (int) $selectedEstablishmentId;
            if ($selectedEstablishmentId > 0) return $selectedEstablishmentId;
            $establishments = FeatureGate::listEstablishmentsOverview();
            if (!empty($establishments)) {
                return (int) ($establishments[0]->id ?? 0);
            }
            return 0;
        }
        return (int) self::currentEstablishmentId();
    }

    private static function getEstablishmentRow($establishmentId) {
        $establishmentId = (int) $establishmentId;
        if ($establishmentId <= 0) return null;
        return query(
            "SELECT id, name, active
               FROM establishment
              WHERE id = ?
              LIMIT 1",
            'ARRAY',
            [$establishmentId]
        ) ?: null;
    }

    private static function getFieldRow($fieldId) {
        $fieldId = (int) $fieldId;
        if ($fieldId <= 0) return null;
        return query(
            "SELECT id, full_name, establishment_id
               FROM soccer_field
              WHERE id = ?
              LIMIT 1",
            'ARRAY',
            [$fieldId]
        ) ?: null;
    }

    private static function getFieldsByEstablishment($establishmentId) {
        $establishmentId = (int) $establishmentId;
        if ($establishmentId <= 0) return [];
        return query(
            "SELECT id, full_name AS name
               FROM soccer_field
              WHERE establishment_id = ?
                AND status = 1
              ORDER BY full_name ASC",
            'ALL',
            [$establishmentId]
        ) ?: [];
    }

    public static function getExpenseCategories() {
        return [
            'supplies' => 'Insumos',
            'maintenance' => 'Mantenimiento',
            'services' => 'Servicios',
            'rent' => 'Alquiler',
            'salary_advance' => 'Adelanto sueldo',
            'tax' => 'Impuestos',
            'refund' => 'Reembolso',
            'cleaning' => 'Limpieza',
            'other' => 'Otro',
        ];
    }

    public static function getPaymentMethods() {
        return [
            'efectivo' => 'Efectivo',
            'transferencia' => 'Transferencia',
            'mercado_pago' => 'Mercado Pago',
            'debito' => 'Tarjeta débito',
            'credito' => 'Tarjeta crédito',
            'mixto' => 'Mixto',
            'online' => 'Online',
            'otro' => 'Otro',
        ];
    }

    public static function getMovementTypeLabels() {
        return [
            'opening' => 'Apertura',
            'booking_cash' => 'Cobro reserva',
            'extra_income_cash' => 'Ingreso extra',
            'expense_cash' => 'Egreso',
            'adjustment' => 'Ajuste',
        ];
    }

    public static function formatDateTimeLabel($value, $includeDate = true) {
        $value = trim((string) $value);
        if ($value === '') return '-';
        $ts = strtotime($value);
        if ($ts === false) return $value;
        return $includeDate ? date('d/m/Y H:i', $ts) : date('H:i', $ts);
    }

    private static function normalizePaymentMethodKey($method) {
        $method = trim(strtolower((string) $method));
        if ($method === '') return 'otro';

        $map = [
            'efectivo' => 'efectivo',
            'cash' => 'efectivo',
            'transferencia' => 'transferencia',
            'transfer' => 'transferencia',
            'mercado pago' => 'mercado_pago',
            'mercadopago' => 'mercado_pago',
            'mercado_pago' => 'mercado_pago',
            'mp' => 'mercado_pago',
            'debito' => 'debito',
            'tarjeta debito' => 'debito',
            'tarjeta débito' => 'debito',
            'credito' => 'credito',
            'crédito' => 'credito',
            'tarjeta credito' => 'credito',
            'tarjeta crédito' => 'credito',
            'mixto' => 'mixto',
            'online' => 'online',
            'otro' => 'otro',
        ];
        return $map[$method] ?? preg_replace('/[^a-z0-9_]+/', '_', $method);
    }

    public static function getPaymentMethodLabel($method) {
        $key = self::normalizePaymentMethodKey($method);
        $methods = self::getPaymentMethods();
        return $methods[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    private static function appendPaymentMethodSummary(&$summary, $method, $direction, $amount, $count = 1) {
        $key = self::normalizePaymentMethodKey($method);
        if (!isset($summary[$key])) {
            $summary[$key] = [
                'method_key' => $key,
                'label' => self::getPaymentMethodLabel($key),
                'in_total' => 0.0,
                'out_total' => 0.0,
                'net_total' => 0.0,
                'count' => 0,
            ];
        }
        $amount = round(abs((float) $amount), 2);
        if ($amount <= 0) return;
        if ($direction === 'out') {
            $summary[$key]['out_total'] += $amount;
            $summary[$key]['net_total'] -= $amount;
        } else {
            $summary[$key]['in_total'] += $amount;
            $summary[$key]['net_total'] += $amount;
        }
        $summary[$key]['count'] += (int) $count;
    }

    private static function getPaymentMethodSummary($establishmentId, $date, $fieldId = 0) {
        $summary = [];
        $establishmentId = (int) $establishmentId;
        $fieldId = (int) $fieldId;
        if ($establishmentId <= 0) return [];

        $fieldFilter = $fieldId > 0 ? $fieldId : null;

        $bookings = Invoices::getIngresosByFilters($date, $fieldFilter, $establishmentId);
        foreach ($bookings as $booking) {
            $signedTotal = (float) ($booking['signed_total'] ?? 0);
            if ($signedTotal === 0.0) continue;
            self::appendPaymentMethodSummary(
                $summary,
                (string) ($booking['paymet_method'] ?? 'online'),
                $signedTotal < 0 ? 'out' : 'in',
                abs($signedTotal)
            );
        }

        $extraIncomes = Invoices::getExtraIngresosByFilters($date, $fieldFilter, $establishmentId);
        foreach ($extraIncomes as $extra) {
            self::appendPaymentMethodSummary(
                $summary,
                (string) ($extra['method_payment'] ?? 'otro'),
                'in',
                (float) ($extra['amount'] ?? 0)
            );
        }

        $expenses = self::listExpenses($establishmentId, [
            'date' => $date,
            'field_id' => $fieldId,
        ]);
        foreach ($expenses as $expense) {
            self::appendPaymentMethodSummary(
                $summary,
                (string) ($expense->payment_method ?? 'otro'),
                'out',
                (float) ($expense->amount ?? 0)
            );
        }

        if (self::isInfrastructureReady()) {
            $adjustmentWhere = ['status = 1', 'establishment_id = :est', 'movement_date = :movement_date', "movement_type = 'adjustment'"];
            $adjustmentParams = [
                ':est' => $establishmentId,
                ':movement_date' => self::normalizeDate($date),
            ];
            if ($fieldId > 0) {
                $adjustmentWhere[] = 'field_id = :field';
                $adjustmentParams[':field'] = $fieldId;
            }
            $adjustments = query(
                "SELECT payment_method, direction, amount
                   FROM cash_movement
                  WHERE " . implode(' AND ', $adjustmentWhere),
                'ALL',
                $adjustmentParams
            ) ?: [];
            foreach ($adjustments as $adjustment) {
                self::appendPaymentMethodSummary(
                    $summary,
                    (string) ($adjustment->payment_method ?? 'efectivo'),
                    (string) ($adjustment->direction ?? 'in'),
                    (float) ($adjustment->amount ?? 0)
                );
            }
        }

        uasort($summary, function ($a, $b) {
            return strcmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
        });
        return array_values($summary);
    }

    private static function normalizeFilters($selectedEstablishmentId = 0, $filters = []) {
        if (is_object($filters)) $filters = (array) $filters;
        return [
            'establishment_id' => self::normalizeSelectedEstablishmentId($selectedEstablishmentId),
            'date' => self::normalizeDate($filters['date'] ?? date('Y-m-d')),
            'field_id' => (int) ($filters['field_id'] ?? 0),
            'category' => trim((string) ($filters['category'] ?? '')),
            'movement_type' => trim((string) ($filters['movement_type'] ?? '')),
        ];
    }

    private static function buildRangeWhere($dateColumn, $establishmentId = 0, $fieldId = 0, $fromDate = '', $toDate = '', $establishmentColumn = '', $fieldColumn = '') {
        $where = [];
        $params = [];
        if ($fromDate !== '' && $toDate !== '') {
            $where[] = $dateColumn . ' BETWEEN :from_date AND :to_date';
            $params[':from_date'] = self::normalizeDate($fromDate);
            $params[':to_date'] = self::normalizeDate($toDate);
        }
        if ((int) $establishmentId > 0 && $establishmentColumn !== '') {
            $where[] = $establishmentColumn . ' = :est';
            $params[':est'] = (int) $establishmentId;
        }
        if ((int) $fieldId > 0 && $fieldColumn !== '') {
            $where[] = $fieldColumn . ' = :field';
            $params[':field'] = (int) $fieldId;
        }
        return [$where, $params];
    }

    private static function mapRowsByDate($rows, $valueKey = 'total') {
        $result = [];
        foreach (($rows ?: []) as $row) {
            $dateKey = (string) ($row->date_key ?? '');
            if ($dateKey === '') continue;
            $result[$dateKey] = round((float) ($row->{$valueKey} ?? 0), 2);
        }
        return $result;
    }

    private static function buildComparisonMetric($currentValue, $previousValue) {
        $currentValue = round((float) $currentValue, 2);
        $previousValue = round((float) $previousValue, 2);
        $delta = round($currentValue - $previousValue, 2);
        if (abs($previousValue) < 0.00001) {
            $deltaPercent = $currentValue === 0.0 ? 0.0 : null;
        } else {
            $deltaPercent = round(($delta / abs($previousValue)) * 100, 2);
        }
        return [
            'current' => $currentValue,
            'previous' => $previousValue,
            'delta' => $delta,
            'delta_percent' => $deltaPercent,
        ];
    }

    private static function buildSignal($label, $value, $goodThreshold, $warningThreshold, $inverse = false, $suffix = '', $context = '') {
        $value = round((float) $value, 2);
        $status = 'danger';
        if ($inverse) {
            if ($value <= $goodThreshold) {
                $status = 'success';
            } elseif ($value <= $warningThreshold) {
                $status = 'warning';
            }
        } else {
            if ($value >= $goodThreshold) {
                $status = 'success';
            } elseif ($value >= $warningThreshold) {
                $status = 'warning';
            }
        }

        $statusLabel = [
            'success' => 'Saludable',
            'warning' => 'Atencion',
            'danger' => 'Critico',
        ][$status] ?? 'Sin estado';

        return [
            'label' => $label,
            'value' => $value,
            'formatted_value' => number_format($value, 2) . $suffix,
            'status' => $status,
            'status_label' => $statusLabel,
            'context' => trim((string) $context),
        ];
    }

    private static function buildAnalyticsSignals($overview, $operational, $commercial) {
        $riskRate = (float) ($operational['booking']['cancellation_rate'] ?? 0) + (float) ($operational['booking']['no_show_like_rate'] ?? 0);
        return [
            'profitability' => self::buildSignal(
                'Rentabilidad operativa',
                (float) ($overview['operating_net_total'] ?? 0),
                0.01,
                -0.01,
                false,
                '',
                'Resultado del mes despues de egresos'
            ),
            'occupancy' => self::buildSignal(
                'Ocupacion',
                (float) ($overview['occupancy']['summary']['occupancy_rate'] ?? 0),
                70,
                45,
                false,
                '%',
                'Uso de la capacidad configurada'
            ),
            'experience' => self::buildSignal(
                'Riesgo de servicio',
                $riskRate,
                8,
                18,
                true,
                '%',
                'Cancelaciones + no-show probable'
            ),
            'waitlist' => self::buildSignal(
                'Motor waitlist',
                (float) ($operational['waitlist']['conversion_rate'] ?? 0),
                35,
                15,
                false,
                '%',
                'Capacidad de convertir demanda en reserva'
            ),
            'ticket' => self::buildSignal(
                'Ticket promedio',
                (float) ($commercial['avg_ticket_total'] ?? 0),
                25000,
                12000,
                false,
                '',
                'Valor bruto promedio por reserva cobrada'
            ),
        ];
    }

    private static function buildAnalyticsInsights($overview, $previousOverview, $operational, $previousOperational, $commercial, $previousCommercial) {
        $insights = [];
        $operatingNet = (float) ($overview['operating_net_total'] ?? 0);
        $operatingPrev = (float) ($previousOverview['operating_net_total'] ?? 0);
        $occupancyRate = (float) ($overview['occupancy']['summary']['occupancy_rate'] ?? 0);
        $waitlistRate = (float) ($operational['waitlist']['conversion_rate'] ?? 0);
        $cancelRate = (float) ($operational['booking']['cancellation_rate'] ?? 0);
        $noShowRate = (float) ($operational['booking']['no_show_like_rate'] ?? 0);
        $refundRate = (float) ($commercial['refund_rate'] ?? 0);
        $avgTicket = (float) ($commercial['avg_ticket_total'] ?? 0);
        $avgTicketPrev = (float) ($previousCommercial['avg_ticket_total'] ?? 0);
        $fieldRank = $commercial['field_revenue_rank'] ?? [];
        $bucketRank = $overview['occupancy']['by_bucket'] ?? [];
        $paymentMix = $commercial['payment_mix'] ?? [];

        if ($operatingNet > $operatingPrev && $operatingNet > 0) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Rentabilidad en mejora',
                'message' => 'El resultado operativo mejora frente al mes anterior y se mantiene positivo.',
            ];
        } elseif ($operatingNet < 0) {
            $insights[] = [
                'type' => 'danger',
                'title' => 'Resultado operativo negativo',
                'message' => 'Los egresos superan la generacion operativa del periodo y conviene revisar costos o precios.',
            ];
        }

        if ($occupancyRate < 45) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Capacidad subutilizada',
                'message' => 'La ocupacion mensual esta por debajo del umbral recomendado; hay margen para promos o mejoras de demanda.',
            ];
        } elseif ($occupancyRate >= 80) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Alta utilizacion',
                'message' => 'La ocupacion mensual es fuerte; puede ser buen momento para revisar pricing o ampliar oferta en horas pico.',
            ];
        }

        if (($cancelRate + $noShowRate) >= 20) {
            $insights[] = [
                'type' => 'danger',
                'title' => 'Friccion operativa elevada',
                'message' => 'Cancelaciones y ausencias probables estan altas; conviene reforzar confirmaciones y politicas de anticipo.',
            ];
        }

        if ($waitlistRate > 0 && $waitlistRate < 20) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Waitlist con baja conversion',
                'message' => 'La lista de espera genera demanda, pero convierte poco; hay oportunidad en tiempos de respuesta y automatizacion.',
            ];
        } elseif ($waitlistRate >= 40) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Waitlist efectiva',
                'message' => 'La lista de espera esta recuperando buena parte de la demanda perdida del calendario.',
            ];
        }

        if ($refundRate >= 10) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Reembolsos a seguir',
                'message' => 'La tasa de reembolso del periodo es alta y puede estar erosionando el ingreso neto.',
            ];
        }

        if ($avgTicket > $avgTicketPrev && $avgTicket > 0) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Ticket promedio en alza',
                'message' => 'El valor promedio por reserva cobrada sube respecto del mes anterior.',
            ];
        }

        if (!empty($fieldRank)) {
            $topField = $fieldRank[0];
            $insights[] = [
                'type' => 'info',
                'title' => 'Cancha lider del mes',
                'message' => trim((string) ($topField['field_name'] ?? 'Cancha')) . ' concentra la mejor facturacion comercial del periodo.',
            ];
        }

        if (!empty($bucketRank)) {
            $bestBucket = $bucketRank[0];
            $worstBucket = $bucketRank[count($bucketRank) - 1];
            $insights[] = [
                'type' => 'info',
                'title' => 'Franja fuerte vs franja floja',
                'message' => trim((string) ($bestBucket['bucket'] ?? '')) . ' es la franja con mejor ocupacion, mientras que ' . trim((string) ($worstBucket['bucket'] ?? '')) . ' necesita mas traccion comercial.',
            ];
        }

        if (!empty($paymentMix)) {
            $leadMethod = $paymentMix[0];
            if ((float) ($leadMethod['share_rate'] ?? 0) >= 60) {
                $insights[] = [
                    'type' => 'info',
                    'title' => 'Dependencia de metodo de pago',
                    'message' => 'El metodo ' . trim((string) ($leadMethod['label'] ?? 'principal')) . ' domina el ingreso del mes; conviene monitorear concentracion y costos asociados.',
                ];
            }
        }

        if (count($insights) === 0) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Panel estable',
                'message' => 'No se detectan alertas fuertes con los umbrales actuales; conviene seguir comparando tendencia y ocupacion.',
            ];
        }

        return array_slice($insights, 0, 8);
    }

    private static function getMonthOccurrencesByDayOfWeek($month) {
        $range = self::monthRange($month);
        $counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0];
        $cursor = strtotime($range['from_date']);
        $limit = strtotime($range['to_date']);
        while ($cursor <= $limit) {
            $counts[(int) date('N', $cursor)]++;
            $cursor = strtotime('+1 day', $cursor);
        }
        return $counts;
    }

    private static function getTimeBucketLabel($hour) {
        $hour = trim((string) $hour);
        $hourNum = (int) substr($hour, 0, 2);
        if ($hourNum >= 0 && $hourNum < 6) return 'Madrugada';
        if ($hourNum < 12) return 'Manana';
        if ($hourNum < 18) return 'Tarde';
        return 'Noche';
    }

    private static function getOccupancyAnalytics($month, $establishmentId = 0, $fieldId = 0) {
        $month = self::normalizeMonth($month);
        $range = self::monthRange($month);
        $dowOccurrences = self::getMonthOccurrencesByDayOfWeek($month);
        $fieldWhere = ['1=1'];
        $fieldParams = [];

        if ((int) $establishmentId > 0) {
            $fieldWhere[] = 'f.establishment_id = :est';
            $fieldParams[':est'] = (int) $establishmentId;
        }
        if ((int) $fieldId > 0) {
            $fieldWhere[] = 'f.id = :field';
            $fieldParams[':field'] = (int) $fieldId;
        }

        $capacityRows = query(
            "SELECT
                f.id AS field_id,
                f.full_name AS field_name,
                COALESCE(NULLIF(f.threshold, 0), 1) AS threshold,
                sf.id_day,
                s.hour,
                s.hour12
             FROM schedules_field sf
             INNER JOIN soccer_field f ON f.id = sf.id_field
             INNER JOIN schedules s ON s.id = sf.id_schedule
             WHERE " . implode(' AND ', $fieldWhere) . "
             ORDER BY f.full_name ASC, s.hour ASC",
            'ALL',
            $fieldParams
        ) ?: [];

        $fieldSummary = [];
        $bucketSummary = [];
        $slotCapacityMap = [];
        $totalCapacityUnits = 0.0;

        foreach ($capacityRows as $row) {
            $fieldKey = (int) ($row->field_id ?? 0);
            if ($fieldKey <= 0) continue;
            $bucket = self::getTimeBucketLabel((string) ($row->hour ?? ''));
            $hourKey = trim((string) ($row->hour ?? ''));
            $capacityUnits = max(1, (int) ($row->threshold ?? 1)) * (int) ($dowOccurrences[(int) ($row->id_day ?? 0)] ?? 0);
            $slotKey = $fieldKey . '|' . $hourKey;

            if (!isset($fieldSummary[$fieldKey])) {
                $fieldSummary[$fieldKey] = [
                    'field_id' => $fieldKey,
                    'field_name' => (string) ($row->field_name ?? 'Cancha'),
                    'capacity_units' => 0.0,
                    'occupied_units' => 0.0,
                    'occupancy_rate' => 0.0,
                ];
            }
            $fieldSummary[$fieldKey]['capacity_units'] += $capacityUnits;

            if (!isset($bucketSummary[$bucket])) {
                $bucketSummary[$bucket] = [
                    'bucket' => $bucket,
                    'capacity_units' => 0.0,
                    'occupied_units' => 0.0,
                    'occupancy_rate' => 0.0,
                ];
            }
            $bucketSummary[$bucket]['capacity_units'] += $capacityUnits;

            if (!isset($slotCapacityMap[$slotKey])) {
                $slotCapacityMap[$slotKey] = [
                    'field_id' => $fieldKey,
                    'field_name' => (string) ($row->field_name ?? 'Cancha'),
                    'hour' => $hourKey,
                    'hour12' => (string) ($row->hour12 ?? $hourKey),
                    'bucket' => $bucket,
                    'capacity_units' => 0.0,
                    'occupied_units' => 0.0,
                    'occupancy_rate' => 0.0,
                ];
            }
            $slotCapacityMap[$slotKey]['capacity_units'] += $capacityUnits;
            $totalCapacityUnits += $capacityUnits;
        }

        [$bookingWhere, $bookingParams] = self::buildRangeWhere(
            'b.date_booking',
            $establishmentId,
            $fieldId,
            $range['from_date'],
            $range['to_date'],
            'f.establishment_id',
            'b.id_field'
        );
        $bookingWhere[] = 'b.status NOT IN (2)';
        $bookingWhere[] = "(b.status <> 7 OR b.expires_at IS NULL OR b.expires_at > NOW())";

        $occupiedRows = query(
            "SELECT
                b.id_field AS field_id,
                f.full_name AS field_name,
                h.hour,
                h.hour12,
                COUNT(*) AS occupied_units
             FROM booking b
             INNER JOIN soccer_field f ON f.id = b.id_field
             INNER JOIN schedules h ON h.id = b.time_booking
             WHERE " . implode(' AND ', $bookingWhere) . "
             GROUP BY b.id_field, h.hour, h.hour12",
            'ALL',
            $bookingParams
        ) ?: [];

        $totalOccupiedUnits = 0.0;
        foreach ($occupiedRows as $row) {
            $fieldKey = (int) ($row->field_id ?? 0);
            $hourKey = trim((string) ($row->hour ?? ''));
            $bucket = self::getTimeBucketLabel($hourKey);
            $occupiedUnits = (float) ($row->occupied_units ?? 0);
            $slotKey = $fieldKey . '|' . $hourKey;

            if (!isset($fieldSummary[$fieldKey])) {
                $fieldSummary[$fieldKey] = [
                    'field_id' => $fieldKey,
                    'field_name' => (string) ($row->field_name ?? 'Cancha'),
                    'capacity_units' => 0.0,
                    'occupied_units' => 0.0,
                    'occupancy_rate' => 0.0,
                ];
            }
            $fieldSummary[$fieldKey]['occupied_units'] += $occupiedUnits;

            if (!isset($bucketSummary[$bucket])) {
                $bucketSummary[$bucket] = [
                    'bucket' => $bucket,
                    'capacity_units' => 0.0,
                    'occupied_units' => 0.0,
                    'occupancy_rate' => 0.0,
                ];
            }
            $bucketSummary[$bucket]['occupied_units'] += $occupiedUnits;

            if (!isset($slotCapacityMap[$slotKey])) {
                $slotCapacityMap[$slotKey] = [
                    'field_id' => $fieldKey,
                    'field_name' => (string) ($row->field_name ?? 'Cancha'),
                    'hour' => $hourKey,
                    'hour12' => (string) ($row->hour12 ?? $hourKey),
                    'bucket' => $bucket,
                    'capacity_units' => 0.0,
                    'occupied_units' => 0.0,
                    'occupancy_rate' => 0.0,
                ];
            }
            $slotCapacityMap[$slotKey]['occupied_units'] += $occupiedUnits;
            $totalOccupiedUnits += $occupiedUnits;
        }

        foreach ($fieldSummary as &$row) {
            $row['occupancy_rate'] = $row['capacity_units'] > 0 ? round(($row['occupied_units'] / $row['capacity_units']) * 100, 2) : 0.0;
        }
        unset($row);
        foreach ($bucketSummary as &$row) {
            $row['occupancy_rate'] = $row['capacity_units'] > 0 ? round(($row['occupied_units'] / $row['capacity_units']) * 100, 2) : 0.0;
        }
        unset($row);
        foreach ($slotCapacityMap as &$row) {
            $row['occupancy_rate'] = $row['capacity_units'] > 0 ? round(($row['occupied_units'] / $row['capacity_units']) * 100, 2) : 0.0;
        }
        unset($row);

        uasort($fieldSummary, function ($a, $b) {
            return ($b['occupancy_rate'] <=> $a['occupancy_rate']) ?: strcmp((string) ($a['field_name'] ?? ''), (string) ($b['field_name'] ?? ''));
        });
        $bucketOrder = ['Manana' => 1, 'Tarde' => 2, 'Noche' => 3, 'Madrugada' => 4];
        uasort($bucketSummary, function ($a, $b) use ($bucketOrder) {
            $oa = $bucketOrder[(string) ($a['bucket'] ?? '')] ?? 99;
            $ob = $bucketOrder[(string) ($b['bucket'] ?? '')] ?? 99;
            return $oa <=> $ob;
        });
        uasort($slotCapacityMap, function ($a, $b) {
            return ($b['occupancy_rate'] <=> $a['occupancy_rate'])
                ?: strcmp((string) ($a['field_name'] ?? ''), (string) ($b['field_name'] ?? ''))
                ?: strcmp((string) ($a['hour'] ?? ''), (string) ($b['hour'] ?? ''));
        });

        return [
            'summary' => [
                'capacity_units' => round($totalCapacityUnits, 2),
                'occupied_units' => round($totalOccupiedUnits, 2),
                'occupancy_rate' => $totalCapacityUnits > 0 ? round(($totalOccupiedUnits / $totalCapacityUnits) * 100, 2) : 0.0,
            ],
            'by_field' => array_values($fieldSummary),
            'by_bucket' => array_values($bucketSummary),
            'top_slots' => array_slice(array_values($slotCapacityMap), 0, 10),
        ];
    }

    private static function buildMonthlyOverview($month, $establishmentId = 0, $fieldId = 0) {
        $range = self::monthRange($month);
        $bookingByDay = self::getBookingIncomeSeriesByRange($range['from_date'], $range['to_date'], $establishmentId, $fieldId);
        $extraByDay = self::getExtraIncomeSeriesByRange($range['from_date'], $range['to_date'], $establishmentId, $fieldId);
        $expenseByDay = self::getExpenseSeriesByRange($range['from_date'], $range['to_date'], $establishmentId, $fieldId);
        $cashByDay = self::getCashMovementSeriesByRange($range['from_date'], $range['to_date'], $establishmentId, $fieldId);
        $occupancy = self::getOccupancyAnalytics($month, $establishmentId, $fieldId);

        $bookingIncomeTotal = round(array_sum($bookingByDay), 2);
        $extraIncomeTotal = round(array_sum($extraByDay), 2);
        $expenseTotal = round(array_sum($expenseByDay), 2);
        $cashInTotal = 0.0;
        $cashOutTotal = 0.0;
        $cashNetTotal = 0.0;
        $activeDays = 0;

        $cursor = strtotime($range['from_date']);
        $limit = strtotime($range['to_date']);
        while ($cursor <= $limit) {
            $dateKey = date('Y-m-d', $cursor);
            $bookingTotal = (float) ($bookingByDay[$dateKey] ?? 0);
            $extraTotal = (float) ($extraByDay[$dateKey] ?? 0);
            $expenseDay = (float) ($expenseByDay[$dateKey] ?? 0);
            $cashNetDay = (float) ($cashByDay[$dateKey]['cash_net_total'] ?? 0);
            if ($bookingTotal !== 0.0 || $extraTotal !== 0.0 || $expenseDay !== 0.0 || $cashNetDay !== 0.0) {
                $activeDays++;
            }
            $cashInTotal += (float) ($cashByDay[$dateKey]['cash_in_total'] ?? 0);
            $cashOutTotal += (float) ($cashByDay[$dateKey]['cash_out_total'] ?? 0);
            $cashNetTotal += $cashNetDay;
            $cursor = strtotime('+1 day', $cursor);
        }

        $operatingNetTotal = round($bookingIncomeTotal + $extraIncomeTotal - $expenseTotal, 2);

        return [
            'month' => $range['month'],
            'from_date' => $range['from_date'],
            'to_date' => $range['to_date'],
            'booking_income_total' => $bookingIncomeTotal,
            'extra_income_total' => $extraIncomeTotal,
            'expense_total' => $expenseTotal,
            'operating_net_total' => $operatingNetTotal,
            'cash_in_total' => round($cashInTotal, 2),
            'cash_out_total' => round($cashOutTotal, 2),
            'cash_net_total' => round($cashNetTotal, 2),
            'active_days' => $activeDays,
            'avg_daily_net_total' => $activeDays > 0 ? round($operatingNetTotal / $activeDays, 2) : 0.0,
            'occupancy' => $occupancy,
        ];
    }

    private static function getOperationalBookingMetrics($fromDate, $toDate, $establishmentId = 0, $fieldId = 0) {
        [$where, $params] = self::buildRangeWhere(
            'b.date_booking',
            $establishmentId,
            $fieldId,
            $fromDate,
            $toDate,
            'sf.establishment_id',
            'b.id_field'
        );

        $summary = query(
            "SELECT
                COUNT(*) AS total_bookings,
                SUM(CASE WHEN b.status = 2 THEN 1 ELSE 0 END) AS cancelled_count,
                SUM(CASE WHEN b.status = 3 THEN 1 ELSE 0 END) AS completed_count,
                SUM(CASE WHEN b.status = 6 THEN 1 ELSE 0 END) AS rescheduled_count,
                SUM(CASE WHEN b.status = 7 THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN b.status = 7 AND b.date_booking < CURDATE() THEN 1 ELSE 0 END) AS no_show_like_count
             FROM booking b
             INNER JOIN soccer_field sf ON sf.id = b.id_field
             WHERE " . implode(' AND ', $where),
            'ARRAY',
            $params
        ) ?: [];

        $totalBookings = (int) ($summary['total_bookings'] ?? 0);
        $cancelledCount = (int) ($summary['cancelled_count'] ?? 0);
        $completedCount = (int) ($summary['completed_count'] ?? 0);
        $rescheduledCount = (int) ($summary['rescheduled_count'] ?? 0);
        $pendingCount = (int) ($summary['pending_count'] ?? 0);
        $noShowLikeCount = (int) ($summary['no_show_like_count'] ?? 0);

        $riskRows = query(
            "SELECT
                sf.id AS field_id,
                sf.full_name AS field_name,
                COUNT(*) AS total_bookings,
                SUM(CASE WHEN b.status = 2 THEN 1 ELSE 0 END) AS cancelled_count,
                SUM(CASE WHEN b.status = 7 AND b.date_booking < CURDATE() THEN 1 ELSE 0 END) AS no_show_like_count,
                SUM(CASE WHEN b.status = 6 THEN 1 ELSE 0 END) AS rescheduled_count
             FROM booking b
             INNER JOIN soccer_field sf ON sf.id = b.id_field
             WHERE " . implode(' AND ', $where) . "
             GROUP BY sf.id, sf.full_name",
            'ALL',
            $params
        ) ?: [];

        $byField = [];
        foreach ($riskRows as $row) {
            $total = (int) ($row->total_bookings ?? 0);
            $cancelled = (int) ($row->cancelled_count ?? 0);
            $noShowLike = (int) ($row->no_show_like_count ?? 0);
            $riskRate = $total > 0 ? round((($cancelled + $noShowLike) / $total) * 100, 2) : 0.0;
            $byField[] = [
                'field_id' => (int) ($row->field_id ?? 0),
                'field_name' => (string) ($row->field_name ?? 'Cancha'),
                'total_bookings' => $total,
                'cancelled_count' => $cancelled,
                'no_show_like_count' => $noShowLike,
                'rescheduled_count' => (int) ($row->rescheduled_count ?? 0),
                'risk_rate' => $riskRate,
            ];
        }
        usort($byField, function ($a, $b) {
            return ($b['risk_rate'] <=> $a['risk_rate'])
                ?: ($b['total_bookings'] <=> $a['total_bookings'])
                ?: strcmp((string) ($a['field_name'] ?? ''), (string) ($b['field_name'] ?? ''));
        });

        return [
            'total_bookings' => $totalBookings,
            'cancelled_count' => $cancelledCount,
            'completed_count' => $completedCount,
            'rescheduled_count' => $rescheduledCount,
            'pending_count' => $pendingCount,
            'no_show_like_count' => $noShowLikeCount,
            'cancellation_rate' => $totalBookings > 0 ? round(($cancelledCount / $totalBookings) * 100, 2) : 0.0,
            'no_show_like_rate' => $totalBookings > 0 ? round(($noShowLikeCount / $totalBookings) * 100, 2) : 0.0,
            'by_field' => array_slice($byField, 0, 10),
        ];
    }

    private static function getWaitlistOperationalMetrics($fromDate, $toDate, $establishmentId = 0, $fieldId = 0) {
        if (!class_exists('Waitlist') || !Waitlist::isInfrastructureReady()) {
            return [
                'ready' => false,
                'total_entries' => 0,
                'converted_count' => 0,
                'expired_count' => 0,
                'cancelled_count' => 0,
                'rejected_count' => 0,
                'offered_count' => 0,
                'offer_total' => 0,
                'accepted_offer_count' => 0,
                'expired_offer_count' => 0,
                'conversion_rate' => 0.0,
                'offer_acceptance_rate' => 0.0,
                'by_channel' => [],
            ];
        }

        [$where, $params] = self::buildRangeWhere(
            'w.date_booking',
            $establishmentId,
            $fieldId,
            $fromDate,
            $toDate,
            'w.establishment_id',
            'w.field_id'
        );

        $summary = query(
            "SELECT
                COUNT(*) AS total_entries,
                SUM(CASE WHEN w.status = 'converted' THEN 1 ELSE 0 END) AS converted_count,
                SUM(CASE WHEN w.status = 'expired' THEN 1 ELSE 0 END) AS expired_count,
                SUM(CASE WHEN w.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_count,
                SUM(CASE WHEN w.status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count,
                SUM(CASE WHEN w.status = 'offered' THEN 1 ELSE 0 END) AS offered_count
             FROM booking_waitlist w
             WHERE " . implode(' AND ', $where),
            'ARRAY',
            $params
        ) ?: [];

        $offerWhere = [];
        $offerParams = [
            ':from_date' => self::normalizeDate($fromDate),
            ':to_date' => self::normalizeDate($toDate),
        ];
        $offerWhere[] = 'DATE(o.offered_at) BETWEEN :from_date AND :to_date';
        if ((int) $establishmentId > 0) {
            $offerWhere[] = 'w.establishment_id = :est';
            $offerParams[':est'] = (int) $establishmentId;
        }
        if ((int) $fieldId > 0) {
            $offerWhere[] = 'w.field_id = :field';
            $offerParams[':field'] = (int) $fieldId;
        }

        $offerSummary = query(
            "SELECT
                COUNT(*) AS offer_total,
                SUM(CASE WHEN o.status = 'accepted' THEN 1 ELSE 0 END) AS accepted_offer_count,
                SUM(CASE WHEN o.status = 'expired' THEN 1 ELSE 0 END) AS expired_offer_count
             FROM booking_waitlist_offer o
             INNER JOIN booking_waitlist w ON w.id = o.waitlist_id
             WHERE " . implode(' AND ', $offerWhere),
            'ARRAY',
            $offerParams
        ) ?: [];

        $channelRows = query(
            "SELECT
                w.requested_by_channel AS channel,
                COUNT(*) AS total_entries,
                SUM(CASE WHEN w.status = 'converted' THEN 1 ELSE 0 END) AS converted_count,
                SUM(CASE WHEN w.status = 'expired' THEN 1 ELSE 0 END) AS expired_count,
                SUM(CASE WHEN w.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_count
             FROM booking_waitlist w
             WHERE " . implode(' AND ', $where) . "
             GROUP BY w.requested_by_channel",
            'ALL',
            $params
        ) ?: [];

        $byChannel = [];
        foreach ($channelRows as $row) {
            $total = (int) ($row->total_entries ?? 0);
            $converted = (int) ($row->converted_count ?? 0);
            $byChannel[] = [
                'channel' => (string) ($row->channel ?? 'sin_canal'),
                'total_entries' => $total,
                'converted_count' => $converted,
                'expired_count' => (int) ($row->expired_count ?? 0),
                'cancelled_count' => (int) ($row->cancelled_count ?? 0),
                'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 2) : 0.0,
            ];
        }
        usort($byChannel, function ($a, $b) {
            return ($b['conversion_rate'] <=> $a['conversion_rate'])
                ?: ($b['total_entries'] <=> $a['total_entries'])
                ?: strcmp((string) ($a['channel'] ?? ''), (string) ($b['channel'] ?? ''));
        });

        $totalEntries = (int) ($summary['total_entries'] ?? 0);
        $convertedCount = (int) ($summary['converted_count'] ?? 0);
        $offerTotal = (int) ($offerSummary['offer_total'] ?? 0);
        $acceptedOfferCount = (int) ($offerSummary['accepted_offer_count'] ?? 0);

        return [
            'ready' => true,
            'total_entries' => $totalEntries,
            'converted_count' => $convertedCount,
            'expired_count' => (int) ($summary['expired_count'] ?? 0),
            'cancelled_count' => (int) ($summary['cancelled_count'] ?? 0),
            'rejected_count' => (int) ($summary['rejected_count'] ?? 0),
            'offered_count' => (int) ($summary['offered_count'] ?? 0),
            'offer_total' => $offerTotal,
            'accepted_offer_count' => $acceptedOfferCount,
            'expired_offer_count' => (int) ($offerSummary['expired_offer_count'] ?? 0),
            'conversion_rate' => $totalEntries > 0 ? round(($convertedCount / $totalEntries) * 100, 2) : 0.0,
            'offer_acceptance_rate' => $offerTotal > 0 ? round(($acceptedOfferCount / $offerTotal) * 100, 2) : 0.0,
            'by_channel' => array_slice($byChannel, 0, 10),
        ];
    }

    private static function getCommercialAnalytics($fromDate, $toDate, $establishmentId = 0, $fieldId = 0, $establishments = []) {
        [$bookingWhere, $bookingParams] = self::buildRangeWhere(
            "DATE(COALESCE(b.paid_in_cash_at, v.last_voucher_date, b.date_booking))",
            $establishmentId,
            $fieldId,
            $fromDate,
            $toDate,
            'sf.establishment_id',
            'b.id_field'
        );
        $bookingWhere[] = "(COALESCE(b.paid_amount, 0) > 0 OR b.payment_status = 'refunded')";

        $bookingSummary = query(
            "SELECT
                COUNT(*) AS paid_booking_count,
                SUM(CASE WHEN b.payment_status = 'refunded' THEN 1 ELSE 0 END) AS refunded_booking_count,
                ROUND(COALESCE(SUM(ABS(COALESCE(b.paid_amount, 0))), 0), 2) AS gross_booking_total,
                ROUND(COALESCE(SUM(
                    CASE
                        WHEN b.payment_status = 'refunded' THEN -ABS(COALESCE(b.paid_amount, 0))
                        ELSE ABS(COALESCE(b.paid_amount, 0))
                    END
                ), 0), 2) AS net_booking_total
             FROM booking b
             INNER JOIN soccer_field sf ON sf.id = b.id_field
             LEFT JOIN (
                SELECT id_booking, MAX(date_create) AS last_voucher_date
                FROM vouchers
                GROUP BY id_booking
             ) v ON v.id_booking = b.id
             WHERE " . implode(' AND ', $bookingWhere),
            'ARRAY',
            $bookingParams
        ) ?: [];

        [$extraWhere, $extraParams] = self::buildRangeWhere(
            'ei.date_income',
            $establishmentId,
            $fieldId,
            $fromDate,
            $toDate,
            'sf.establishment_id',
            'ei.id_field'
        );
        $extraSummary = query(
            "SELECT
                COUNT(*) AS extra_income_count,
                ROUND(COALESCE(SUM(ei.amount), 0), 2) AS extra_income_total
             FROM extra_income ei
             INNER JOIN soccer_field sf ON sf.id = ei.id_field
             WHERE " . implode(' AND ', $extraWhere),
            'ARRAY',
            $extraParams
        ) ?: [];

        $fieldMap = [];
        $bookingByField = query(
            "SELECT
                sf.id AS field_id,
                sf.full_name AS field_name,
                COUNT(*) AS paid_booking_count,
                SUM(CASE WHEN b.payment_status = 'refunded' THEN 1 ELSE 0 END) AS refunded_booking_count,
                ROUND(COALESCE(SUM(ABS(COALESCE(b.paid_amount, 0))), 0), 2) AS gross_booking_total,
                ROUND(COALESCE(SUM(
                    CASE
                        WHEN b.payment_status = 'refunded' THEN -ABS(COALESCE(b.paid_amount, 0))
                        ELSE ABS(COALESCE(b.paid_amount, 0))
                    END
                ), 0), 2) AS booking_income_total
             FROM booking b
             INNER JOIN soccer_field sf ON sf.id = b.id_field
             LEFT JOIN (
                SELECT id_booking, MAX(date_create) AS last_voucher_date
                FROM vouchers
                GROUP BY id_booking
             ) v ON v.id_booking = b.id
             WHERE " . implode(' AND ', $bookingWhere) . "
             GROUP BY sf.id, sf.full_name",
            'ALL',
            $bookingParams
        ) ?: [];
        foreach ($bookingByField as $row) {
            $fieldIdKey = (int) ($row->field_id ?? 0);
            $fieldMap[$fieldIdKey] = [
                'field_id' => $fieldIdKey,
                'field_name' => (string) ($row->field_name ?? 'Cancha'),
                'paid_booking_count' => (int) ($row->paid_booking_count ?? 0),
                'refunded_booking_count' => (int) ($row->refunded_booking_count ?? 0),
                'gross_booking_total' => (float) ($row->gross_booking_total ?? 0),
                'booking_income_total' => (float) ($row->booking_income_total ?? 0),
                'extra_income_total' => 0.0,
                'extra_income_count' => 0,
                'total_commercial_total' => 0.0,
                'avg_ticket_total' => 0.0,
            ];
        }

        $extraByField = query(
            "SELECT
                sf.id AS field_id,
                sf.full_name AS field_name,
                COUNT(*) AS extra_income_count,
                ROUND(COALESCE(SUM(ei.amount), 0), 2) AS extra_income_total
             FROM extra_income ei
             INNER JOIN soccer_field sf ON sf.id = ei.id_field
             WHERE " . implode(' AND ', $extraWhere) . "
             GROUP BY sf.id, sf.full_name",
            'ALL',
            $extraParams
        ) ?: [];
        foreach ($extraByField as $row) {
            $fieldIdKey = (int) ($row->field_id ?? 0);
            if (!isset($fieldMap[$fieldIdKey])) {
                $fieldMap[$fieldIdKey] = [
                    'field_id' => $fieldIdKey,
                    'field_name' => (string) ($row->field_name ?? 'Cancha'),
                    'paid_booking_count' => 0,
                    'refunded_booking_count' => 0,
                    'gross_booking_total' => 0.0,
                    'booking_income_total' => 0.0,
                    'extra_income_total' => 0.0,
                    'extra_income_count' => 0,
                    'total_commercial_total' => 0.0,
                    'avg_ticket_total' => 0.0,
                ];
            }
            $fieldMap[$fieldIdKey]['extra_income_total'] = (float) ($row->extra_income_total ?? 0);
            $fieldMap[$fieldIdKey]['extra_income_count'] = (int) ($row->extra_income_count ?? 0);
        }

        foreach ($fieldMap as &$row) {
            $row['total_commercial_total'] = round((float) $row['booking_income_total'] + (float) $row['extra_income_total'], 2);
            $row['avg_ticket_total'] = (int) $row['paid_booking_count'] > 0
                ? round((float) $row['gross_booking_total'] / (int) $row['paid_booking_count'], 2)
                : 0.0;
        }
        unset($row);
        usort($fieldMap, function ($a, $b) {
            return ($b['total_commercial_total'] <=> $a['total_commercial_total'])
                ?: ($b['avg_ticket_total'] <=> $a['avg_ticket_total'])
                ?: strcmp((string) ($a['field_name'] ?? ''), (string) ($b['field_name'] ?? ''));
        });

        $establishmentCommercialRank = [];
        if (Users::isSuperAdmin() && (int) $fieldId === 0) {
            $estMap = [];
            $bookingByEst = query(
                "SELECT
                    sf.establishment_id,
                    COUNT(*) AS paid_booking_count,
                    SUM(CASE WHEN b.payment_status = 'refunded' THEN 1 ELSE 0 END) AS refunded_booking_count,
                    ROUND(COALESCE(SUM(ABS(COALESCE(b.paid_amount, 0))), 0), 2) AS gross_booking_total,
                    ROUND(COALESCE(SUM(
                        CASE
                            WHEN b.payment_status = 'refunded' THEN -ABS(COALESCE(b.paid_amount, 0))
                            ELSE ABS(COALESCE(b.paid_amount, 0))
                        END
                    ), 0), 2) AS booking_income_total
                 FROM booking b
                 INNER JOIN soccer_field sf ON sf.id = b.id_field
                 LEFT JOIN (
                    SELECT id_booking, MAX(date_create) AS last_voucher_date
                    FROM vouchers
                    GROUP BY id_booking
                 ) v ON v.id_booking = b.id
                 WHERE " . implode(' AND ', $bookingWhere) . "
                 GROUP BY sf.establishment_id",
                'ALL',
                $bookingParams
            ) ?: [];
            foreach ($bookingByEst as $row) {
                $estId = (int) ($row->establishment_id ?? 0);
                $estMap[$estId] = [
                    'establishment_id' => $estId,
                    'name' => 'Establecimiento',
                    'plan_name' => 'Sin plan',
                    'paid_booking_count' => (int) ($row->paid_booking_count ?? 0),
                    'refunded_booking_count' => (int) ($row->refunded_booking_count ?? 0),
                    'gross_booking_total' => (float) ($row->gross_booking_total ?? 0),
                    'booking_income_total' => (float) ($row->booking_income_total ?? 0),
                    'extra_income_total' => 0.0,
                    'total_commercial_total' => 0.0,
                    'avg_ticket_total' => 0.0,
                ];
            }
            $extraByEst = query(
                "SELECT
                    sf.establishment_id,
                    ROUND(COALESCE(SUM(ei.amount), 0), 2) AS extra_income_total
                 FROM extra_income ei
                 INNER JOIN soccer_field sf ON sf.id = ei.id_field
                 WHERE " . implode(' AND ', $extraWhere) . "
                 GROUP BY sf.establishment_id",
                'ALL',
                $extraParams
            ) ?: [];
            foreach ($extraByEst as $row) {
                $estId = (int) ($row->establishment_id ?? 0);
                if (!isset($estMap[$estId])) {
                    $estMap[$estId] = [
                        'establishment_id' => $estId,
                        'name' => 'Establecimiento',
                        'plan_name' => 'Sin plan',
                        'paid_booking_count' => 0,
                        'refunded_booking_count' => 0,
                        'gross_booking_total' => 0.0,
                        'booking_income_total' => 0.0,
                        'extra_income_total' => 0.0,
                        'total_commercial_total' => 0.0,
                        'avg_ticket_total' => 0.0,
                    ];
                }
                $estMap[$estId]['extra_income_total'] = (float) ($row->extra_income_total ?? 0);
            }

            $estInfoMap = [];
            foreach ($establishments as $establishment) {
                $estInfoMap[(int) ($establishment->id ?? 0)] = [
                    'name' => (string) ($establishment->name ?? 'Establecimiento'),
                    'plan_name' => (string) ($establishment->plan_name ?? 'Sin plan'),
                ];
            }
            foreach ($estMap as &$row) {
                $info = $estInfoMap[(int) ($row['establishment_id'] ?? 0)] ?? [];
                $row['name'] = (string) ($info['name'] ?? $row['name']);
                $row['plan_name'] = (string) ($info['plan_name'] ?? $row['plan_name']);
                $row['total_commercial_total'] = round((float) $row['booking_income_total'] + (float) $row['extra_income_total'], 2);
                $row['avg_ticket_total'] = (int) $row['paid_booking_count'] > 0
                    ? round((float) $row['gross_booking_total'] / (int) $row['paid_booking_count'], 2)
                    : 0.0;
            }
            unset($row);
            $establishmentCommercialRank = array_values($estMap);
            usort($establishmentCommercialRank, function ($a, $b) {
                return ($b['total_commercial_total'] <=> $a['total_commercial_total'])
                    ?: ($b['avg_ticket_total'] <=> $a['avg_ticket_total'])
                    ?: strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
            });
        }

        $paymentSummary = self::getPaymentMethodSummaryByRange($fromDate, $toDate, $establishmentId, $fieldId);
        $paymentInTotal = 0.0;
        foreach ($paymentSummary as $row) {
            $paymentInTotal += (float) ($row['in_total'] ?? 0);
        }
        foreach ($paymentSummary as &$row) {
            $row['share_rate'] = $paymentInTotal > 0 ? round(((float) ($row['in_total'] ?? 0) / $paymentInTotal) * 100, 2) : 0.0;
        }
        unset($row);

        $paidBookingCount = (int) ($bookingSummary['paid_booking_count'] ?? 0);
        $grossBookingTotal = (float) ($bookingSummary['gross_booking_total'] ?? 0);
        $netBookingTotal = (float) ($bookingSummary['net_booking_total'] ?? 0);
        $extraIncomeTotal = (float) ($extraSummary['extra_income_total'] ?? 0);
        $refundedBookingCount = (int) ($bookingSummary['refunded_booking_count'] ?? 0);

        return [
            'paid_booking_count' => $paidBookingCount,
            'refunded_booking_count' => $refundedBookingCount,
            'extra_income_count' => (int) ($extraSummary['extra_income_count'] ?? 0),
            'gross_booking_total' => $grossBookingTotal,
            'net_booking_total' => $netBookingTotal,
            'extra_income_total' => $extraIncomeTotal,
            'total_commercial_total' => round($netBookingTotal + $extraIncomeTotal, 2),
            'avg_ticket_total' => $paidBookingCount > 0 ? round($grossBookingTotal / $paidBookingCount, 2) : 0.0,
            'refund_rate' => $paidBookingCount > 0 ? round(($refundedBookingCount / $paidBookingCount) * 100, 2) : 0.0,
            'payment_mix' => $paymentSummary,
            'field_revenue_rank' => array_slice(array_values($fieldMap), 0, 10),
            'establishment_revenue_rank' => array_slice($establishmentCommercialRank, 0, 10),
        ];
    }

    private static function getBookingIncomeSeriesByRange($fromDate, $toDate, $establishmentId = 0, $fieldId = 0) {
        [$where, $params] = self::buildRangeWhere(
            "DATE(COALESCE(b.paid_in_cash_at, v.last_voucher_date, b.date_booking))",
            $establishmentId,
            $fieldId,
            $fromDate,
            $toDate,
            'sf_filter.establishment_id',
            'b.id_field'
        );
        $where[] = "(COALESCE(b.paid_amount, 0) > 0 OR b.payment_status = 'refunded')";

        $rows = query(
            "SELECT DATE(COALESCE(b.paid_in_cash_at, v.last_voucher_date, b.date_booking)) AS date_key,
                    ROUND(COALESCE(SUM(
                        CASE
                            WHEN b.payment_status = 'refunded' THEN -ABS(COALESCE(b.paid_amount, 0))
                            ELSE COALESCE(b.paid_amount, 0)
                        END
                    ), 0), 2) AS total
               FROM booking b
               INNER JOIN soccer_field sf_filter ON sf_filter.id = b.id_field
               LEFT JOIN (
                    SELECT id_booking, MAX(date_create) AS last_voucher_date
                    FROM vouchers
                    GROUP BY id_booking
               ) v ON v.id_booking = b.id
              WHERE " . implode(' AND ', $where) . "
              GROUP BY date_key
              ORDER BY date_key ASC",
            'ALL',
            $params
        ) ?: [];

        return self::mapRowsByDate($rows);
    }

    private static function getExtraIncomeSeriesByRange($fromDate, $toDate, $establishmentId = 0, $fieldId = 0) {
        [$where, $params] = self::buildRangeWhere(
            'ei.date_income',
            $establishmentId,
            $fieldId,
            $fromDate,
            $toDate,
            'sf.establishment_id',
            'ei.id_field'
        );
        $rows = query(
            "SELECT ei.date_income AS date_key,
                    ROUND(COALESCE(SUM(ei.amount), 0), 2) AS total
               FROM extra_income ei
               INNER JOIN soccer_field sf ON sf.id = ei.id_field
              WHERE " . implode(' AND ', $where) . "
              GROUP BY date_key
              ORDER BY date_key ASC",
            'ALL',
            $params
        ) ?: [];
        return self::mapRowsByDate($rows);
    }

    private static function getExpenseSeriesByRange($fromDate, $toDate, $establishmentId = 0, $fieldId = 0) {
        if (!self::isInfrastructureReady()) return [];
        [$where, $params] = self::buildRangeWhere(
            'e.expense_date',
            $establishmentId,
            $fieldId,
            $fromDate,
            $toDate,
            'e.establishment_id',
            'e.field_id'
        );
        $where[] = 'e.status = 1';
        $rows = query(
            "SELECT e.expense_date AS date_key,
                    ROUND(COALESCE(SUM(e.amount), 0), 2) AS total
               FROM expense e
              WHERE " . implode(' AND ', $where) . "
              GROUP BY date_key
              ORDER BY date_key ASC",
            'ALL',
            $params
        ) ?: [];
        return self::mapRowsByDate($rows);
    }

    private static function getCashMovementSeriesByRange($fromDate, $toDate, $establishmentId = 0, $fieldId = 0) {
        if (!self::isInfrastructureReady()) return [];
        [$where, $params] = self::buildRangeWhere(
            'cm.movement_date',
            $establishmentId,
            $fieldId,
            $fromDate,
            $toDate,
            'cm.establishment_id',
            'cm.field_id'
        );
        $where[] = 'cm.status = 1';
        $rows = query(
            "SELECT cm.movement_date AS date_key,
                    ROUND(COALESCE(SUM(CASE WHEN cm.direction = 'in' THEN cm.amount ELSE 0 END), 0), 2) AS in_total,
                    ROUND(COALESCE(SUM(CASE WHEN cm.direction = 'out' THEN cm.amount ELSE 0 END), 0), 2) AS out_total,
                    ROUND(COALESCE(SUM(CASE WHEN cm.direction = 'in' THEN cm.amount ELSE -cm.amount END), 0), 2) AS net_total
               FROM cash_movement cm
              WHERE " . implode(' AND ', $where) . "
              GROUP BY date_key
              ORDER BY date_key ASC",
            'ALL',
            $params
        ) ?: [];

        $result = [];
        foreach ($rows as $row) {
            $dateKey = (string) ($row->date_key ?? '');
            if ($dateKey === '') continue;
            $result[$dateKey] = [
                'cash_in_total' => (float) ($row->in_total ?? 0),
                'cash_out_total' => (float) ($row->out_total ?? 0),
                'cash_net_total' => (float) ($row->net_total ?? 0),
            ];
        }
        return $result;
    }

    private static function getPaymentMethodSummaryByRange($fromDate, $toDate, $establishmentId = 0, $fieldId = 0) {
        // Item 16 (auditoría UX/UI): esto llamaba a Invoices::getIngresosByFilters(),
        // un método que nunca existió en ClassInvoices.php -- fatal error apenas
        // se pedía un solo día. La rama de rango (fromDate !== toDate) ya tenía
        // la consulta completa y correcta al lado; alcanza con no distinguir el
        // caso de un solo día como un camino aparte, es el mismo rango con las
        // dos puntas iguales.
        $summary = [];
        [$where, $params] = self::buildRangeWhere(
            "DATE(COALESCE(b.paid_in_cash_at, v.last_voucher_date, b.date_booking))",
            $establishmentId,
            $fieldId,
            $fromDate,
            $toDate,
            'sf_filter.establishment_id',
            'b.id_field'
        );
        $where[] = "(COALESCE(b.paid_amount, 0) > 0 OR b.payment_status = 'refunded')";
        $bookings = query(
            "SELECT
                CASE
                    WHEN COALESCE(pw.cash_amount, 0) > 0 AND COALESCE(v.method_name, '') <> '' THEN 'mixto'
                    WHEN COALESCE(pw.cash_amount, 0) > 0 THEN 'efectivo'
                    WHEN COALESCE(v.method_name, '') <> '' THEN v.method_name
                    ELSE 'online'
                END AS paymet_method,
                ROUND(
                    CASE
                        WHEN b.payment_status = 'refunded' THEN -ABS(COALESCE(b.paid_amount, 0))
                        ELSE ABS(COALESCE(b.paid_amount, 0))
                    END,
                    2
                ) AS signed_total
             FROM booking b
             INNER JOIN soccer_field sf_filter ON sf_filter.id = b.id_field
             LEFT JOIN (
                SELECT
                    id_booking,
                    MAX(date_create) AS last_voucher_date,
                    SUBSTRING_INDEX(GROUP_CONCAT(method_name ORDER BY date_create DESC), ',', 1) AS method_name
                FROM vouchers
                GROUP BY id_booking
             ) v ON v.id_booking = b.id
             LEFT JOIN (
                SELECT
                    id_booking,
                    SUM(amount_payment) AS cash_amount
                FROM payment_app_web
                GROUP BY id_booking
             ) pw ON pw.id_booking = b.id
             WHERE " . implode(' AND ', $where),
            'ARRAY_ALL',
            $params
        ) ?: [];
        foreach ($bookings as $booking) {
            $signedTotal = (float) ($booking['signed_total'] ?? 0);
            if ($signedTotal === 0.0) continue;
            self::appendPaymentMethodSummary($summary, (string) ($booking['paymet_method'] ?? 'online'), $signedTotal < 0 ? 'out' : 'in', abs($signedTotal));
        }

        [$extraWhere, $extraParams] = self::buildRangeWhere(
            'ei.date_income',
            $establishmentId,
            $fieldId,
            $fromDate,
            $toDate,
            'sf.establishment_id',
            'ei.id_field'
        );
        $extraIncomes = query(
            "SELECT ei.method_payment, ei.amount
               FROM extra_income ei
               INNER JOIN soccer_field sf ON sf.id = ei.id_field
              WHERE " . implode(' AND ', $extraWhere),
            'ARRAY_ALL',
            $extraParams
        ) ?: [];
        foreach ($extraIncomes as $extra) {
            self::appendPaymentMethodSummary($summary, (string) ($extra['method_payment'] ?? 'otro'), 'in', (float) ($extra['amount'] ?? 0));
        }

        if (self::isInfrastructureReady()) {
            [$expenseWhere, $expenseParams] = self::buildRangeWhere(
                'e.expense_date',
                $establishmentId,
                $fieldId,
                $fromDate,
                $toDate,
                'e.establishment_id',
                'e.field_id'
            );
            $expenseWhere[] = 'e.status = 1';
            $expenses = query(
                "SELECT e.payment_method, e.amount
                   FROM expense e
                  WHERE " . implode(' AND ', $expenseWhere),
                'ARRAY_ALL',
                $expenseParams
            ) ?: [];
            foreach ($expenses as $expense) {
                self::appendPaymentMethodSummary($summary, (string) ($expense['payment_method'] ?? 'otro'), 'out', (float) ($expense['amount'] ?? 0));
            }
        }

        uasort($summary, function ($a, $b) {
            return strcmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
        });
        return array_values($summary);
    }

    private static function getSessionByDate($establishmentId, $sessionDate, $onlyOpen = false) {
        if (!self::isInfrastructureReady()) return null;
        $establishmentId = (int) $establishmentId;
        if ($establishmentId <= 0) return null;
        $sessionDate = self::normalizeDate($sessionDate);
        $sql = "SELECT cs.*, uo.full_name AS opened_by_name, uc.full_name AS closed_by_name
                  FROM cash_session cs
                  LEFT JOIN users uo ON uo.id = cs.opened_by_user_id
                  LEFT JOIN users uc ON uc.id = cs.closed_by_user_id
                 WHERE cs.establishment_id = :est
                   AND cs.session_date = :session_date";
        $params = [
            ':est' => $establishmentId,
            ':session_date' => $sessionDate,
        ];
        if ($onlyOpen) {
            $sql .= " AND cs.status = 'open'";
        }
        $sql .= " ORDER BY cs.id DESC LIMIT 1";
        return query($sql, '', $params) ?: null;
    }

    private static function bookingLogsBackfillReady() {
        return self::tableExists('booking_logs')
            && self::columnExists('booking_logs', 'id')
            && self::columnExists('booking_logs', 'id_booking')
            && self::columnExists('booking_logs', 'id_user')
            && self::columnExists('booking_logs', 'action')
            && self::columnExists('booking_logs', 'note')
            && self::columnExists('booking_logs', 'created_at');
    }

    private static function hasActiveMovementSource($sourceType, $sourceId) {
        if (!self::isInfrastructureReady()) return false;
        $sourceType = trim((string) $sourceType);
        $sourceId = (int) $sourceId;
        if ($sourceType === '' || $sourceId <= 0) return false;

        $row = query(
            "SELECT 1
               FROM cash_movement
              WHERE source_type = ?
                AND source_id = ?
                AND status = 1
              LIMIT 1",
            'ARRAY',
            [$sourceType, $sourceId]
        );
        return !empty($row);
    }

    private static function extractCashAmountFromNote($note) {
        $note = trim((string) $note);
        if ($note === '') return 0.0;

        $patterns = [
            '/cash(?:\s+payment)?\s*:\s*\$?\s*([0-9]+(?:[.,][0-9]{1,2})?)/i',
            '/cash\s*\$?\s*([0-9]+(?:[.,][0-9]{1,2})?)/i',
            '/\$\s*([0-9]+(?:[.,][0-9]{1,2})?)/',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $note, $matches)) {
                return round((float) str_replace(',', '.', (string) ($matches[1] ?? 0)), 2);
            }
        }
        return 0.0;
    }

    private static function getMovementTotals($establishmentId, $date, $fieldId = 0) {
        if (!self::isInfrastructureReady()) {
            return ['cash_in_total' => 0.0, 'cash_out_total' => 0.0, 'expected_cash' => 0.0];
        }
        $where = ['status = 1', 'establishment_id = :est', 'movement_date = :movement_date'];
        $params = [
            ':est' => (int) $establishmentId,
            ':movement_date' => self::normalizeDate($date),
        ];
        if ((int) $fieldId > 0) {
            $where[] = 'field_id = :field';
            $params[':field'] = (int) $fieldId;
        }
        $row = query(
            "SELECT
                ROUND(COALESCE(SUM(CASE WHEN direction = 'in' THEN amount ELSE 0 END), 0), 2) AS cash_in_total,
                ROUND(COALESCE(SUM(CASE WHEN direction = 'out' THEN amount ELSE 0 END), 0), 2) AS cash_out_total,
                ROUND(COALESCE(SUM(CASE WHEN direction = 'in' THEN amount ELSE -amount END), 0), 2) AS expected_cash
             FROM cash_movement
             WHERE " . implode(' AND ', $where),
            'ARRAY',
            $params
        ) ?: [];

        return [
            'cash_in_total' => (float) ($row['cash_in_total'] ?? 0),
            'cash_out_total' => (float) ($row['cash_out_total'] ?? 0),
            'expected_cash' => (float) ($row['expected_cash'] ?? 0),
        ];
    }

    private static function getExpenseTotals($establishmentId, $date, $fieldId = 0) {
        if (!self::isInfrastructureReady()) {
            return ['expense_total' => 0.0, 'expense_cash_total' => 0.0, 'expense_count' => 0];
        }
        $where = ['status = 1', 'establishment_id = :est', 'expense_date = :expense_date'];
        $params = [
            ':est' => (int) $establishmentId,
            ':expense_date' => self::normalizeDate($date),
        ];
        if ((int) $fieldId > 0) {
            $where[] = 'field_id = :field';
            $params[':field'] = (int) $fieldId;
        }

        $row = query(
            "SELECT
                ROUND(COALESCE(SUM(amount), 0), 2) AS expense_total,
                ROUND(COALESCE(SUM(CASE WHEN payment_method = 'efectivo' THEN amount ELSE 0 END), 0), 2) AS expense_cash_total,
                COUNT(*) AS expense_count
             FROM expense
             WHERE " . implode(' AND ', $where),
            'ARRAY',
            $params
        ) ?: [];

        return [
            'expense_total' => (float) ($row['expense_total'] ?? 0),
            'expense_cash_total' => (float) ($row['expense_cash_total'] ?? 0),
            'expense_count' => (int) ($row['expense_count'] ?? 0),
        ];
    }

    public static function listMovements($selectedEstablishmentId = 0, $filters = []) {
        if (!self::isInfrastructureReady()) return [];
        $filters = self::normalizeFilters($selectedEstablishmentId, $filters);
        $establishmentId = (int) ($filters['establishment_id'] ?? 0);
        if ($establishmentId <= 0) return [];

        $where = ['cm.status = 1', 'cm.establishment_id = :est', 'cm.movement_date = :movement_date'];
        $params = [
            ':est' => $establishmentId,
            ':movement_date' => $filters['date'],
        ];
        if ((int) $filters['field_id'] > 0) {
            $where[] = 'cm.field_id = :field';
            $params[':field'] = (int) $filters['field_id'];
        }
        if ($filters['movement_type'] !== '') {
            $where[] = 'cm.movement_type = :movement_type';
            $params[':movement_type'] = $filters['movement_type'];
        }

        return query(
            "SELECT cm.*,
                    sf.full_name AS field_name,
                    u.full_name AS created_by_name
               FROM cash_movement cm
               LEFT JOIN soccer_field sf ON sf.id = cm.field_id
               LEFT JOIN users u ON u.id = cm.created_by_user_id
              WHERE " . implode(' AND ', $where) . "
              ORDER BY cm.occurred_at DESC, cm.id DESC",
            'ALL',
            $params
        ) ?: [];
    }

    public static function listExpenses($selectedEstablishmentId = 0, $filters = []) {
        if (!self::isInfrastructureReady()) return [];
        $filters = self::normalizeFilters($selectedEstablishmentId, $filters);
        $establishmentId = (int) ($filters['establishment_id'] ?? 0);
        if ($establishmentId <= 0) return [];

        $where = ['e.status = 1', 'e.establishment_id = :est', 'e.expense_date = :expense_date'];
        $params = [
            ':est' => $establishmentId,
            ':expense_date' => $filters['date'],
        ];
        if ((int) $filters['field_id'] > 0) {
            $where[] = 'e.field_id = :field';
            $params[':field'] = (int) $filters['field_id'];
        }
        if ($filters['category'] !== '') {
            $where[] = 'e.category = :category';
            $params[':category'] = $filters['category'];
        }

        return query(
            "SELECT e.*,
                    sf.full_name AS field_name,
                    u.full_name AS created_by_name
               FROM expense e
               LEFT JOIN soccer_field sf ON sf.id = e.field_id
               LEFT JOIN users u ON u.id = e.created_by_user_id
              WHERE " . implode(' AND ', $where) . "
              ORDER BY e.created_at DESC, e.id DESC",
            'ALL',
            $params
        ) ?: [];
    }

    public static function getDashboardData($selectedEstablishmentId = 0, $filters = []) {
        $filters = self::normalizeFilters($selectedEstablishmentId, $filters);
        $establishmentId = (int) ($filters['establishment_id'] ?? 0);
        $establishments = Users::isSuperAdmin() ? FeatureGate::listEstablishmentsOverview() : [];
        $selectedEstablishment = $establishmentId > 0 ? self::getEstablishmentRow($establishmentId) : null;
        $featureEnabled = $establishmentId > 0 ? self::isFeatureEnabled($establishmentId) : true;
        $fields = self::getFieldsByEstablishment($establishmentId);
        $movements = self::listMovements($establishmentId, $filters);
        $expenses = self::listExpenses($establishmentId, $filters);
        $movementTotals = self::getMovementTotals($establishmentId, $filters['date'], (int) $filters['field_id']);
        $expenseTotals = self::getExpenseTotals($establishmentId, $filters['date'], (int) $filters['field_id']);
        $paymentMethodSummary = self::getPaymentMethodSummary($establishmentId, $filters['date'], (int) $filters['field_id']);

        $bookingIncome = 0.0;
        $extraIncome = 0.0;
        if ($establishmentId > 0) {
            $fieldFilter = (int) $filters['field_id'] > 0 ? (int) $filters['field_id'] : null;
            $bookingIncome = Invoices::getTotalIngresosByFilters($filters['date'], $fieldFilter, $establishmentId);
            $extraIncome = Invoices::getTotalExtraIngresosByFilters($filters['date'], $fieldFilter, $establishmentId);
        }

        $session = $establishmentId > 0 ? self::getSessionByDate($establishmentId, $filters['date']) : null;

        return [
            'ready' => self::isInfrastructureReady(),
            'selected_establishment_id' => $establishmentId,
            'selected_establishment' => $selectedEstablishment,
            'establishments' => $establishments,
            'feature_enabled' => $featureEnabled,
            'filters' => $filters,
            'fields' => $fields,
            'session' => $session,
            'movements' => $movements,
            'expenses' => $expenses,
            'stats' => [
                'cash_in_total' => $movementTotals['cash_in_total'],
                'cash_out_total' => $movementTotals['cash_out_total'],
                'expected_cash' => $movementTotals['expected_cash'],
                'booking_income_total' => $bookingIncome,
                'extra_income_total' => $extraIncome,
                'operating_income_total' => $bookingIncome + $extraIncome,
                'expense_total' => $expenseTotals['expense_total'],
                'expense_cash_total' => $expenseTotals['expense_cash_total'],
                'expense_count' => $expenseTotals['expense_count'],
                'movement_count' => count($movements),
                'payment_method_summary' => $paymentMethodSummary,
            ],
            'can_edit' => $establishmentId > 0 && (Users::isSuperAdmin() || $featureEnabled),
            'payment_methods' => self::getPaymentMethods(),
            'expense_categories' => self::getExpenseCategories(),
            'movement_labels' => self::getMovementTypeLabels(),
        ];
    }

    public static function getMonthlyAnalyticsData($selectedEstablishmentId = 0, $filters = []) {
        if (is_object($filters)) $filters = (array) $filters;

        $monthRange = self::monthRange($filters['month'] ?? date('Y-m'));
        $month = $monthRange['month'];
        $fromDate = $monthRange['from_date'];
        $toDate = $monthRange['to_date'];
        $previousMonth = self::previousMonth($month);
        $establishments = FeatureGate::listEstablishmentsOverview();
        $selectedEstablishmentId = Users::isSuperAdmin()
            ? max(0, (int) $selectedEstablishmentId)
            : (int) self::currentEstablishmentId();
        $selectedEstablishment = $selectedEstablishmentId > 0 ? self::getEstablishmentRow($selectedEstablishmentId) : null;
        $featureEnabled = $selectedEstablishmentId > 0 ? self::isAnalyticsEnabled($selectedEstablishmentId) : true;
        $canView = Users::isSuperAdmin() || ($selectedEstablishmentId > 0 && $featureEnabled);
        $fields = $selectedEstablishmentId > 0 ? self::getFieldsByEstablishment($selectedEstablishmentId) : [];
        $selectedFieldId = (int) ($filters['field_id'] ?? 0);
        if ($selectedFieldId > 0 && $selectedEstablishmentId > 0) {
            $field = self::getFieldRow($selectedFieldId);
            if (!$field || (int) ($field['establishment_id'] ?? 0) !== $selectedEstablishmentId) {
                $selectedFieldId = 0;
            }
        } else {
            $selectedFieldId = 0;
        }

        $currentOverview = self::buildMonthlyOverview($month, $selectedEstablishmentId, $selectedFieldId);
        $previousOverview = self::buildMonthlyOverview($previousMonth, $selectedEstablishmentId, $selectedFieldId);
        $currentOperational = [
            'booking' => self::getOperationalBookingMetrics($fromDate, $toDate, $selectedEstablishmentId, $selectedFieldId),
            'waitlist' => self::getWaitlistOperationalMetrics($fromDate, $toDate, $selectedEstablishmentId, $selectedFieldId),
        ];
        $currentCommercial = self::getCommercialAnalytics($fromDate, $toDate, $selectedEstablishmentId, $selectedFieldId, $establishments);
        $previousRange = self::monthRange($previousMonth);
        $previousOperational = [
            'booking' => self::getOperationalBookingMetrics($previousRange['from_date'], $previousRange['to_date'], $selectedEstablishmentId, $selectedFieldId),
            'waitlist' => self::getWaitlistOperationalMetrics($previousRange['from_date'], $previousRange['to_date'], $selectedEstablishmentId, $selectedFieldId),
        ];
        $previousCommercial = self::getCommercialAnalytics($previousRange['from_date'], $previousRange['to_date'], $selectedEstablishmentId, $selectedFieldId, $establishments);

        $bookingByDay = self::getBookingIncomeSeriesByRange($fromDate, $toDate, $selectedEstablishmentId, $selectedFieldId);
        $extraByDay = self::getExtraIncomeSeriesByRange($fromDate, $toDate, $selectedEstablishmentId, $selectedFieldId);
        $expenseByDay = self::getExpenseSeriesByRange($fromDate, $toDate, $selectedEstablishmentId, $selectedFieldId);
        $cashByDay = self::getCashMovementSeriesByRange($fromDate, $toDate, $selectedEstablishmentId, $selectedFieldId);

        $dailySeries = [];
        $bookingIncomeTotal = 0.0;
        $extraIncomeTotal = 0.0;
        $expenseTotal = 0.0;
        $cashInTotal = 0.0;
        $cashOutTotal = 0.0;
        $cashNetTotal = 0.0;
        $activeDays = 0;

        $cursor = strtotime($fromDate);
        $limit = strtotime($toDate);
        while ($cursor <= $limit) {
            $dateKey = date('Y-m-d', $cursor);
            $bookingTotal = (float) ($bookingByDay[$dateKey] ?? 0);
            $extraTotal = (float) ($extraByDay[$dateKey] ?? 0);
            $expenseDayTotal = (float) ($expenseByDay[$dateKey] ?? 0);
            $cashInDay = (float) ($cashByDay[$dateKey]['cash_in_total'] ?? 0);
            $cashOutDay = (float) ($cashByDay[$dateKey]['cash_out_total'] ?? 0);
            $cashNetDay = (float) ($cashByDay[$dateKey]['cash_net_total'] ?? 0);
            $operatingNet = round($bookingTotal + $extraTotal - $expenseDayTotal, 2);

            if ($bookingTotal !== 0.0 || $extraTotal !== 0.0 || $expenseDayTotal !== 0.0 || $cashNetDay !== 0.0) {
                $activeDays++;
            }

            $dailySeries[] = [
                'date' => $dateKey,
                'date_label' => showDate($dateKey),
                'day_name' => dateToName($dateKey),
                'booking_income_total' => $bookingTotal,
                'extra_income_total' => $extraTotal,
                'expense_total' => $expenseDayTotal,
                'operating_net_total' => $operatingNet,
                'cash_in_total' => $cashInDay,
                'cash_out_total' => $cashOutDay,
                'cash_net_total' => $cashNetDay,
            ];

            $bookingIncomeTotal += $bookingTotal;
            $extraIncomeTotal += $extraTotal;
            $expenseTotal += $expenseDayTotal;
            $cashInTotal += $cashInDay;
            $cashOutTotal += $cashOutDay;
            $cashNetTotal += $cashNetDay;

            $cursor = strtotime('+1 day', $cursor);
        }

        $operatingNetTotal = round($bookingIncomeTotal + $extraIncomeTotal - $expenseTotal, 2);
        $avgDailyNet = $activeDays > 0 ? round($operatingNetTotal / $activeDays, 2) : 0.0;
        $paymentMethodSummary = self::getPaymentMethodSummaryByRange($fromDate, $toDate, $selectedEstablishmentId, $selectedFieldId);
        $signals = self::buildAnalyticsSignals($currentOverview, $currentOperational, $currentCommercial);
        $insights = self::buildAnalyticsInsights($currentOverview, $previousOverview, $currentOperational, $previousOperational, $currentCommercial, $previousCommercial);
        $comparison = [
            'booking_income_total' => self::buildComparisonMetric($currentOverview['booking_income_total'], $previousOverview['booking_income_total']),
            'extra_income_total' => self::buildComparisonMetric($currentOverview['extra_income_total'], $previousOverview['extra_income_total']),
            'expense_total' => self::buildComparisonMetric($currentOverview['expense_total'], $previousOverview['expense_total']),
            'operating_net_total' => self::buildComparisonMetric($currentOverview['operating_net_total'], $previousOverview['operating_net_total']),
            'cash_net_total' => self::buildComparisonMetric($currentOverview['cash_net_total'], $previousOverview['cash_net_total']),
            'occupancy_rate' => self::buildComparisonMetric(
                (float) ($currentOverview['occupancy']['summary']['occupancy_rate'] ?? 0),
                (float) ($previousOverview['occupancy']['summary']['occupancy_rate'] ?? 0)
            ),
            'cancellation_rate' => self::buildComparisonMetric(
                (float) ($currentOperational['booking']['cancellation_rate'] ?? 0),
                (float) ($previousOperational['booking']['cancellation_rate'] ?? 0)
            ),
            'no_show_like_rate' => self::buildComparisonMetric(
                (float) ($currentOperational['booking']['no_show_like_rate'] ?? 0),
                (float) ($previousOperational['booking']['no_show_like_rate'] ?? 0)
            ),
            'waitlist_conversion_rate' => self::buildComparisonMetric(
                (float) ($currentOperational['waitlist']['conversion_rate'] ?? 0),
                (float) ($previousOperational['waitlist']['conversion_rate'] ?? 0)
            ),
            'avg_ticket_total' => self::buildComparisonMetric(
                (float) ($currentCommercial['avg_ticket_total'] ?? 0),
                (float) ($previousCommercial['avg_ticket_total'] ?? 0)
            ),
            'refund_rate' => self::buildComparisonMetric(
                (float) ($currentCommercial['refund_rate'] ?? 0),
                (float) ($previousCommercial['refund_rate'] ?? 0)
            ),
        ];

        $establishmentRanking = [];
        if (Users::isSuperAdmin()) {
            foreach ($establishments as $establishment) {
                $estId = (int) ($establishment->id ?? 0);
                if ($estId <= 0) continue;
                $estBookingByDay = self::getBookingIncomeSeriesByRange($fromDate, $toDate, $estId, 0);
                $estExtraByDay = self::getExtraIncomeSeriesByRange($fromDate, $toDate, $estId, 0);
                $estExpenseByDay = self::getExpenseSeriesByRange($fromDate, $toDate, $estId, 0);
                $estCashByDay = self::getCashMovementSeriesByRange($fromDate, $toDate, $estId, 0);

                $estBookingTotal = round(array_sum($estBookingByDay), 2);
                $estExtraTotal = round(array_sum($estExtraByDay), 2);
                $estExpenseTotal = round(array_sum($estExpenseByDay), 2);
                $estCashInTotal = 0.0;
                $estCashOutTotal = 0.0;
                $estCashNetTotal = 0.0;
                foreach ($estCashByDay as $cashDay) {
                    $estCashInTotal += (float) ($cashDay['cash_in_total'] ?? 0);
                    $estCashOutTotal += (float) ($cashDay['cash_out_total'] ?? 0);
                    $estCashNetTotal += (float) ($cashDay['cash_net_total'] ?? 0);
                }

                $establishmentRanking[] = [
                    'id' => $estId,
                    'name' => (string) ($establishment->name ?? 'Establecimiento'),
                    'plan_name' => (string) ($establishment->plan_name ?? 'Sin plan'),
                    'subscription_status' => (string) ($establishment->subscription_status ?? 'sin estado'),
                    'analytics_enabled' => self::isAnalyticsEnabled($estId),
                    'booking_income_total' => $estBookingTotal,
                    'extra_income_total' => $estExtraTotal,
                    'expense_total' => $estExpenseTotal,
                    'operating_net_total' => round($estBookingTotal + $estExtraTotal - $estExpenseTotal, 2),
                    'cash_in_total' => round($estCashInTotal, 2),
                    'cash_out_total' => round($estCashOutTotal, 2),
                    'cash_net_total' => round($estCashNetTotal, 2),
                ];
            }
            usort($establishmentRanking, function ($a, $b) {
                return ($b['operating_net_total'] <=> $a['operating_net_total']);
            });
        }

        return [
            'ready' => self::isInfrastructureReady(),
            'month' => $month,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'selected_establishment_id' => $selectedEstablishmentId,
            'selected_establishment' => $selectedEstablishment,
            'feature_enabled' => $featureEnabled,
            'can_view' => $canView,
            'is_superadmin' => Users::isSuperAdmin(),
            'establishments' => $establishments,
            'fields' => $fields,
            'filters' => [
                'month' => $month,
                'field_id' => $selectedFieldId,
            ],
            'stats' => [
                'booking_income_total' => round($bookingIncomeTotal, 2),
                'extra_income_total' => round($extraIncomeTotal, 2),
                'expense_total' => round($expenseTotal, 2),
                'operating_net_total' => $operatingNetTotal,
                'cash_in_total' => round($cashInTotal, 2),
                'cash_out_total' => round($cashOutTotal, 2),
                'cash_net_total' => round($cashNetTotal, 2),
                'active_days' => $activeDays,
                'avg_daily_net_total' => $avgDailyNet,
                'payment_method_summary' => $paymentMethodSummary,
                'establishment_count' => count($establishmentRanking),
                'occupancy' => $currentOverview['occupancy'],
                'operational' => $currentOperational,
                'commercial' => $currentCommercial,
                'signals' => $signals,
                'insights' => $insights,
            ],
            'daily_series' => $dailySeries,
            'establishment_ranking' => $establishmentRanking,
            'comparison' => [
                'current_month' => $month,
                'previous_month' => $previousMonth,
                'metrics' => $comparison,
                'current_overview' => $currentOverview,
                'previous_overview' => $previousOverview,
                'current_operational' => $currentOperational,
                'previous_operational' => $previousOperational,
                'current_commercial' => $currentCommercial,
                'previous_commercial' => $previousCommercial,
            ],
        ];
    }

    private static function insertMovement($payload) {
        if (!self::isInfrastructureReady()) return 0;
        $establishmentId = (int) ($payload['establishment_id'] ?? 0);
        if ($establishmentId <= 0) return 0;

        $occurredAt = self::normalizeDateTime($payload['occurred_at'] ?? '', $payload['movement_date'] ?? date('Y-m-d'));
        $movementDate = self::normalizeDate($payload['movement_date'] ?? substr($occurredAt, 0, 10));
        $fieldId = (int) ($payload['field_id'] ?? 0);
        $session = self::getSessionByDate($establishmentId, $movementDate);
        $cashSessionId = (int) ($payload['cash_session_id'] ?? 0);
        if ($cashSessionId <= 0 && $session) {
            $cashSessionId = (int) ($session->id ?? 0);
        }

        query(
            "INSERT INTO cash_movement
                (establishment_id, cash_session_id, field_id, movement_date, movement_type, direction, payment_method,
                 source_type, source_id, description, amount, occurred_at, created_by_user_id, status, created_at, updated_at)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())",
            '',
            [
                $establishmentId,
                $cashSessionId > 0 ? $cashSessionId : null,
                $fieldId > 0 ? $fieldId : null,
                $movementDate,
                trim((string) ($payload['movement_type'] ?? 'adjustment')),
                trim((string) ($payload['direction'] ?? 'in')),
                trim((string) ($payload['payment_method'] ?? 'efectivo')),
                trim((string) ($payload['source_type'] ?? 'manual')) ?: null,
                (int) ($payload['source_id'] ?? 0) ?: null,
                trim((string) ($payload['description'] ?? 'Movimiento de caja')),
                round(abs((float) ($payload['amount'] ?? 0)), 2),
                $occurredAt,
                (int) ($payload['created_by_user_id'] ?? self::currentUserId()) ?: null,
            ]
        );

        $last = query("SELECT LAST_INSERT_ID() AS id", 'ARRAY');
        return (int) ($last['id'] ?? 0);
    }

    public static function disableMovementsBySource($sourceType, $sourceId) {
        if (!self::isInfrastructureReady()) return;
        $sourceType = trim((string) $sourceType);
        $sourceId = (int) $sourceId;
        if ($sourceType === '' || $sourceId <= 0) return;

        query(
            "UPDATE cash_movement
                SET status = 0,
                    updated_at = NOW()
              WHERE source_type = ?
                AND source_id = ?",
            '',
            [$sourceType, $sourceId]
        );
    }

    public static function openCashSession($data) {
        if (!self::isInfrastructureReady()) {
            JSON(['error' => 'La infraestructura financiera no está disponible'], 409, true);
        }
        $establishmentId = (int) ($data->establishment_id ?? 0);
        $sessionDate = self::normalizeDate($data->session_date ?? date('Y-m-d'));
        $openingAmount = round(max(0, (float) ($data->opening_amount ?? 0)), 2);
        $notes = trim((string) ($data->notes ?? ''));
        if ($establishmentId <= 0) {
            JSON(['error' => 'Establecimiento inválido'], 400, true);
        }
        self::ensureCanManage($establishmentId);

        $open = self::getSessionByDate($establishmentId, $sessionDate, true);
        if ($open) {
            JSON(['error' => 'Ya existe una caja abierta para la fecha seleccionada'], 409, true);
        }

        query(
            "INSERT INTO cash_session
                (establishment_id, session_date, status, opening_amount, notes, opened_by_user_id, opened_at, created_at, updated_at)
             VALUES
                (?, ?, 'open', ?, ?, ?, NOW(), NOW(), NOW())",
            '',
            [
                $establishmentId,
                $sessionDate,
                $openingAmount,
                $notes !== '' ? $notes : null,
                self::currentUserId() ?: null,
            ]
        );
        $last = query("SELECT LAST_INSERT_ID() AS id", 'ARRAY');
        $sessionId = (int) ($last['id'] ?? 0);

        if ($openingAmount > 0) {
            self::insertMovement([
                'establishment_id' => $establishmentId,
                'cash_session_id' => $sessionId,
                'movement_date' => $sessionDate,
                'movement_type' => 'opening',
                'direction' => 'in',
                'payment_method' => 'efectivo',
                'source_type' => 'cash_session',
                'source_id' => $sessionId,
                'description' => 'Apertura de caja',
                'amount' => $openingAmount,
                'occurred_at' => $sessionDate . ' ' . date('H:i:s'),
            ]);
        }

        audit('cash_session_open', 'cash_session', $sessionId, [
            'establishment_id' => $establishmentId,
            'session_date' => $sessionDate,
            'opening_amount' => $openingAmount,
        ]);

        JSON([
            'success' => true,
            'msg' => 'Caja abierta correctamente',
            'cash_session_id' => $sessionId,
        ]);
    }

    public static function closeCashSession($data) {
        if (!self::isInfrastructureReady()) {
            JSON(['error' => 'La infraestructura financiera no está disponible'], 409, true);
        }
        $establishmentId = (int) ($data->establishment_id ?? 0);
        $sessionDate = self::normalizeDate($data->session_date ?? date('Y-m-d'));
        $realAmount = round(max(0, (float) ($data->closing_real_amount ?? 0)), 2);
        $notes = trim((string) ($data->notes ?? ''));
        if ($establishmentId <= 0) {
            JSON(['error' => 'Establecimiento inválido'], 400, true);
        }
        self::ensureCanManage($establishmentId);

        $session = self::getSessionByDate($establishmentId, $sessionDate, true);
        if (!$session) {
            JSON(['error' => 'No hay una caja abierta para la fecha seleccionada'], 404, true);
        }

        $totals = self::getMovementTotals($establishmentId, $sessionDate);
        $expected = round((float) $totals['expected_cash'], 2);
        $difference = round($realAmount - $expected, 2);

        query(
            "UPDATE cash_session
                SET status = 'closed',
                    closing_expected_amount = ?,
                    closing_real_amount = ?,
                    difference_amount = ?,
                    notes = CASE
                        WHEN ? IS NULL OR ? = '' THEN notes
                        WHEN notes IS NULL OR notes = '' THEN ?
                        ELSE CONCAT(notes, ' | ', ?)
                    END,
                    closed_by_user_id = ?,
                    closed_at = NOW(),
                    updated_at = NOW()
              WHERE id = ?",
            '',
            [
                $expected,
                $realAmount,
                $difference,
                $notes !== '' ? $notes : null,
                $notes,
                $notes,
                $notes,
                self::currentUserId() ?: null,
                (int) ($session->id ?? 0),
            ]
        );

        audit('cash_session_close', 'cash_session', (int) ($session->id ?? 0), [
            'establishment_id' => $establishmentId,
            'session_date' => $sessionDate,
            'closing_expected_amount' => $expected,
            'closing_real_amount' => $realAmount,
            'difference_amount' => $difference,
        ]);

        JSON([
            'success' => true,
            'msg' => 'Caja cerrada correctamente',
            'difference_amount' => $difference,
        ]);
    }

    public static function createExpense($data) {
        if (!self::isInfrastructureReady()) {
            JSON(['error' => 'La infraestructura financiera no está disponible'], 409, true);
        }
        $establishmentId = (int) ($data->establishment_id ?? 0);
        $fieldId = (int) ($data->field_id ?? 0);
        $expenseDate = self::normalizeDate($data->expense_date ?? date('Y-m-d'));
        $category = trim((string) ($data->category ?? 'other'));
        $description = trim((string) ($data->description ?? ''));
        $amount = round((float) ($data->amount ?? 0), 2);
        $paymentMethod = trim((string) ($data->payment_method ?? 'efectivo'));
        $notes = trim((string) ($data->notes ?? ''));

        if ($establishmentId <= 0 || $description === '' || $amount <= 0) {
            JSON(['error' => 'Completá establecimiento, descripción y monto del egreso'], 400, true);
        }
        self::ensureCanManage($establishmentId);

        $categories = self::getExpenseCategories();
        if (!array_key_exists($category, $categories)) {
            JSON(['error' => 'Categoría de egreso inválida'], 400, true);
        }
        $paymentMethods = self::getPaymentMethods();
        if (!array_key_exists($paymentMethod, $paymentMethods)) {
            JSON(['error' => 'Método de pago inválido'], 400, true);
        }

        if ($fieldId > 0) {
            $field = self::getFieldRow($fieldId);
            if (!$field || (int) ($field['establishment_id'] ?? 0) !== $establishmentId) {
                JSON(['error' => 'La cancha seleccionada no pertenece al establecimiento'], 409, true);
            }
        }

        query(
            "INSERT INTO expense
                (establishment_id, field_id, expense_date, category, description, amount, payment_method, notes, created_by_user_id, status, created_at, updated_at)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())",
            '',
            [
                $establishmentId,
                $fieldId > 0 ? $fieldId : null,
                $expenseDate,
                $category,
                $description,
                $amount,
                $paymentMethod,
                $notes !== '' ? $notes : null,
                self::currentUserId() ?: null,
            ]
        );
        $last = query("SELECT LAST_INSERT_ID() AS id", 'ARRAY');
        $expenseId = (int) ($last['id'] ?? 0);

        if ($paymentMethod === 'efectivo') {
            self::insertMovement([
                'establishment_id' => $establishmentId,
                'field_id' => $fieldId,
                'movement_date' => $expenseDate,
                'movement_type' => 'expense_cash',
                'direction' => 'out',
                'payment_method' => $paymentMethod,
                'source_type' => 'expense',
                'source_id' => $expenseId,
                'description' => $description,
                'amount' => $amount,
                'occurred_at' => $expenseDate . ' ' . date('H:i:s'),
            ]);
        }

        audit('expense_add', 'expense', $expenseId, [
            'establishment_id' => $establishmentId,
            'field_id' => $fieldId,
            'expense_date' => $expenseDate,
            'category' => $category,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
        ]);

        JSON([
            'success' => true,
            'msg' => 'Egreso guardado correctamente',
            'expense_id' => $expenseId,
        ]);
    }

    public static function deleteExpense($data) {
        if (!self::isInfrastructureReady()) {
            JSON(['error' => 'La infraestructura financiera no está disponible'], 409, true);
        }
        $expenseId = (int) ($data->id ?? 0);
        if ($expenseId <= 0) {
            JSON(['error' => 'Egreso inválido'], 400, true);
        }

        $expense = query(
            "SELECT id, establishment_id
               FROM expense
              WHERE id = ?
                AND status = 1
              LIMIT 1",
            'ARRAY',
            [$expenseId]
        );
        if (!$expense) {
            JSON(['error' => 'Egreso no encontrado'], 404, true);
        }

        self::ensureCanManage((int) ($expense['establishment_id'] ?? 0));
        query(
            "UPDATE expense
                SET status = 0,
                    updated_at = NOW()
              WHERE id = ?",
            '',
            [$expenseId]
        );
        self::disableMovementsBySource('expense', $expenseId);

        audit('expense_delete', 'expense', $expenseId);
        JSON(['success' => true, 'msg' => 'Egreso eliminado correctamente']);
    }

    public static function createManualAdjustment($data) {
        if (!self::isInfrastructureReady()) {
            JSON(['error' => 'La infraestructura financiera no está disponible'], 409, true);
        }

        $establishmentId = (int) ($data->establishment_id ?? 0);
        $fieldId = (int) ($data->field_id ?? 0);
        $movementDate = self::normalizeDate($data->movement_date ?? date('Y-m-d'));
        $occurredAt = self::normalizeDateTime($data->occurred_at ?? ($movementDate . ' ' . date('H:i:s')), $movementDate);
        $description = trim((string) ($data->description ?? ''));
        $amount = round((float) ($data->amount ?? 0), 2);
        $direction = trim((string) ($data->direction ?? 'in'));

        if ($establishmentId <= 0 || $description === '' || $amount <= 0) {
            JSON(['error' => 'Completá establecimiento, descripción y monto del ajuste'], 400, true);
        }
        if (!in_array($direction, ['in', 'out'], true)) {
            JSON(['error' => 'Dirección de ajuste inválida'], 400, true);
        }
        self::ensureCanManage($establishmentId);

        if ($fieldId > 0) {
            $field = self::getFieldRow($fieldId);
            if (!$field || (int) ($field['establishment_id'] ?? 0) !== $establishmentId) {
                JSON(['error' => 'La cancha seleccionada no pertenece al establecimiento'], 409, true);
            }
        }

        $movementId = self::insertMovement([
            'establishment_id' => $establishmentId,
            'field_id' => $fieldId,
            'movement_date' => $movementDate,
            'movement_type' => 'adjustment',
            'direction' => $direction,
            'payment_method' => 'efectivo',
            'source_type' => 'manual_adjustment',
            'source_id' => null,
            'description' => $description,
            'amount' => $amount,
            'occurred_at' => $occurredAt,
            'created_by_user_id' => self::currentUserId(),
        ]);

        audit('cash_adjustment_add', 'cash_movement', $movementId, [
            'establishment_id' => $establishmentId,
            'field_id' => $fieldId,
            'movement_date' => $movementDate,
            'direction' => $direction,
            'amount' => $amount,
        ]);

        JSON([
            'success' => true,
            'msg' => 'Ajuste manual registrado correctamente',
            'movement_id' => $movementId,
        ]);
    }

    public static function deleteManualAdjustment($data) {
        if (!self::isInfrastructureReady()) {
            JSON(['error' => 'La infraestructura financiera no está disponible'], 409, true);
        }

        $movementId = (int) ($data->id ?? 0);
        if ($movementId <= 0) {
            JSON(['error' => 'Movimiento inválido'], 400, true);
        }

        $movement = query(
            "SELECT id, establishment_id, movement_type, source_type, status
               FROM cash_movement
              WHERE id = ?
              LIMIT 1",
            'ARRAY',
            [$movementId]
        );
        if (!$movement || (int) ($movement['status'] ?? 0) !== 1) {
            JSON(['error' => 'Movimiento no encontrado'], 404, true);
        }
        if ((string) ($movement['movement_type'] ?? '') !== 'adjustment' || (string) ($movement['source_type'] ?? '') !== 'manual_adjustment') {
            JSON(['error' => 'Solo se pueden eliminar ajustes manuales'], 409, true);
        }

        self::ensureCanManage((int) ($movement['establishment_id'] ?? 0));

        query(
            "UPDATE cash_movement
                SET status = 0,
                    updated_at = NOW()
              WHERE id = ?",
            '',
            [$movementId]
        );

        audit('cash_adjustment_delete', 'cash_movement', $movementId);
        JSON([
            'success' => true,
            'msg' => 'Ajuste manual eliminado correctamente',
        ]);
    }

    public static function registerBookingCashPayment($bookingId, $bookingLogId, $amount, $occurredAt = null, $createdByUserId = null) {
        if (!self::isInfrastructureReady()) return 0;
        $bookingId = (int) $bookingId;
        $bookingLogId = (int) $bookingLogId;
        $amount = round((float) $amount, 2);
        if ($bookingId <= 0 || $bookingLogId <= 0 || $amount <= 0) return 0;

        $booking = query(
            "SELECT b.id, b.id_field, sf.establishment_id
               FROM booking b
               INNER JOIN soccer_field sf ON sf.id = b.id_field
              WHERE b.id = ?
              LIMIT 1",
            'ARRAY',
            [$bookingId]
        );
        if (!$booking) return 0;

        return self::insertMovement([
            'establishment_id' => (int) ($booking['establishment_id'] ?? 0),
            'field_id' => (int) ($booking['id_field'] ?? 0),
            'movement_type' => 'booking_cash',
            'direction' => 'in',
            'payment_method' => 'efectivo',
            'source_type' => 'booking_log',
            'source_id' => $bookingLogId,
            'description' => 'Cobro en efectivo reserva #' . $bookingId,
            'amount' => $amount,
            'occurred_at' => $occurredAt ?: date('Y-m-d H:i:s'),
            'created_by_user_id' => $createdByUserId ?: self::currentUserId(),
        ]);
    }

    public static function registerExtraIncomeCashMovement($extraIncomeId) {
        if (!self::isInfrastructureReady()) return 0;
        $extraIncomeId = (int) $extraIncomeId;
        if ($extraIncomeId <= 0) return 0;

        self::disableMovementsBySource('extra_income', $extraIncomeId);

        $extra = query(
            "SELECT ei.*, sf.establishment_id
               FROM extra_income ei
               INNER JOIN soccer_field sf ON sf.id = ei.id_field
              WHERE ei.id = ?
              LIMIT 1",
            'ARRAY',
            [$extraIncomeId]
        );
        if (!$extra) return 0;
        if ((string) ($extra['method_payment'] ?? '') !== 'efectivo') return 0;

        return self::insertMovement([
            'establishment_id' => (int) ($extra['establishment_id'] ?? 0),
            'field_id' => (int) ($extra['id_field'] ?? 0),
            'movement_date' => $extra['date_income'] ?? date('Y-m-d'),
            'movement_type' => 'extra_income_cash',
            'direction' => 'in',
            'payment_method' => 'efectivo',
            'source_type' => 'extra_income',
            'source_id' => $extraIncomeId,
            'description' => 'Ingreso extra: ' . trim((string) ($extra['description'] ?? '')),
            'amount' => (float) ($extra['amount'] ?? 0),
            'occurred_at' => ($extra['date_income'] ?? date('Y-m-d')) . ' ' . date('H:i:s'),
            'created_by_user_id' => (int) ($extra['created_by_user_id'] ?? 0),
        ]);
    }

    public static function backfillHistoricalMovements($data) {
        if (!self::isInfrastructureReady()) {
            JSON(['error' => 'La infraestructura financiera no está disponible'], 409, true);
        }

        $establishmentId = (int) ($data->establishment_id ?? 0);
        $fromDate = self::normalizeDate($data->from_date ?? date('Y-m-d', strtotime('-30 days')));
        $toDate = self::normalizeDate($data->to_date ?? date('Y-m-d'));
        $includeBookingCash = (int) ($data->include_booking_cash ?? 1) === 1;
        $includeExtraIncome = (int) ($data->include_extra_income ?? 1) === 1;

        if ($establishmentId <= 0) {
            JSON(['error' => 'Establecimiento inválido'], 400, true);
        }
        if (strtotime($fromDate) === false || strtotime($toDate) === false || strtotime($fromDate) > strtotime($toDate)) {
            JSON(['error' => 'El rango de fechas del backfill es inválido'], 400, true);
        }
        if (!$includeBookingCash && !$includeExtraIncome) {
            JSON(['error' => 'Seleccioná al menos una fuente para reconstruir'], 400, true);
        }

        self::ensureCanManage($establishmentId);

        $stats = [
            'booking_logs_supported' => self::bookingLogsBackfillReady() ? 1 : 0,
            'booking_logs_scanned' => 0,
            'booking_logs_created' => 0,
            'booking_logs_skipped_existing' => 0,
            'booking_logs_skipped_invalid' => 0,
            'extra_income_supported' => self::tableExists('extra_income') ? 1 : 0,
            'extra_income_scanned' => 0,
            'extra_income_created' => 0,
            'extra_income_skipped_existing' => 0,
            'total_created' => 0,
        ];

        if ($includeBookingCash && self::bookingLogsBackfillReady()) {
            $bookingLogs = query(
                "SELECT bl.id,
                        bl.id_booking,
                        bl.id_user,
                        bl.note,
                        bl.created_at,
                        b.id_field,
                        sf.establishment_id
                   FROM booking_logs bl
                   INNER JOIN booking b ON b.id = bl.id_booking
                   INNER JOIN soccer_field sf ON sf.id = b.id_field
                  WHERE bl.action = 'cash_payment'
                    AND sf.establishment_id = :est
                    AND DATE(bl.created_at) BETWEEN :from_date AND :to_date
                  ORDER BY bl.id ASC",
                'ALL',
                [
                    ':est' => $establishmentId,
                    ':from_date' => $fromDate,
                    ':to_date' => $toDate,
                ]
            ) ?: [];

            foreach ($bookingLogs as $log) {
                $stats['booking_logs_scanned']++;
                $sourceId = (int) ($log->id ?? 0);
                if ($sourceId <= 0) {
                    $stats['booking_logs_skipped_invalid']++;
                    continue;
                }
                if (self::hasActiveMovementSource('booking_log', $sourceId)) {
                    $stats['booking_logs_skipped_existing']++;
                    continue;
                }

                $amount = self::extractCashAmountFromNote((string) ($log->note ?? ''));
                if ($amount <= 0) {
                    $stats['booking_logs_skipped_invalid']++;
                    continue;
                }

                $movementId = self::insertMovement([
                    'establishment_id' => $establishmentId,
                    'field_id' => (int) ($log->id_field ?? 0),
                    'movement_date' => substr((string) ($log->created_at ?? $fromDate), 0, 10),
                    'movement_type' => 'booking_cash',
                    'direction' => 'in',
                    'payment_method' => 'efectivo',
                    'source_type' => 'booking_log',
                    'source_id' => $sourceId,
                    'description' => 'Backfill cobro en efectivo reserva #' . (int) ($log->id_booking ?? 0),
                    'amount' => $amount,
                    'occurred_at' => (string) ($log->created_at ?? ($fromDate . ' 00:00:00')),
                    'created_by_user_id' => (int) ($log->id_user ?? 0),
                ]);
                if ($movementId > 0) {
                    $stats['booking_logs_created']++;
                    $stats['total_created']++;
                }
            }
        }

        if ($includeExtraIncome) {
            $extraRows = query(
                "SELECT ei.id
                   FROM extra_income ei
                   INNER JOIN soccer_field sf ON sf.id = ei.id_field
                  WHERE ei.method_payment = 'efectivo'
                    AND sf.establishment_id = :est
                    AND ei.date_income BETWEEN :from_date AND :to_date
                  ORDER BY ei.id ASC",
                'ALL',
                [
                    ':est' => $establishmentId,
                    ':from_date' => $fromDate,
                    ':to_date' => $toDate,
                ]
            ) ?: [];

            foreach ($extraRows as $extra) {
                $stats['extra_income_scanned']++;
                $extraId = (int) ($extra->id ?? 0);
                if ($extraId <= 0) continue;
                if (self::hasActiveMovementSource('extra_income', $extraId)) {
                    $stats['extra_income_skipped_existing']++;
                    continue;
                }
                $movementId = self::registerExtraIncomeCashMovement($extraId);
                if ($movementId > 0) {
                    $stats['extra_income_created']++;
                    $stats['total_created']++;
                }
            }
        }

        audit('finance_cash_backfill', 'establishment', $establishmentId, [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'include_booking_cash' => $includeBookingCash ? 1 : 0,
            'include_extra_income' => $includeExtraIncome ? 1 : 0,
            'stats' => $stats,
        ]);

        JSON([
            'success' => true,
            'msg' => 'Reconstrucción histórica finalizada',
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'stats' => $stats,
        ]);
    }
}
