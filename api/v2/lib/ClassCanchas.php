<?php

    
    class Canchas{
        private static function ensurePriceRangesTable(){
            query("CREATE TABLE IF NOT EXISTS price_ranges (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_field INT NOT NULL,
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                INDEX idx_price_ranges_field_time (id_field, start_time, end_time)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
        public static function getall(){
            // PHP-9: NO devolver token_mercadopago en JSON público.
            // PHP-10: filtro server-side por establishment_id si vino en querystring.
            // BOT-TENANT: si el token está vinculado a un establishment, forzar
            //   ese filtro siempre (un bot = un establishment). El querystring
            //   solo se respeta si NO hay token bound (uso público/admin).
            $where = ["sf.status = 1"];
            $params = [];

            $boundEst = Auth::getEstablishmentId();
            if ($boundEst) {
                $where[] = "sf.establishment_id = ?";
                $params[] = (int) $boundEst;
            } elseif (isset($_GET['establishment_id'])) {
                $where[] = "sf.establishment_id = ?";
                $params[] = (int) $_GET['establishment_id'];
            }
            $whereSql = ' WHERE ' . implode(' AND ', $where);

            $canchas = query("SELECT
                sf.id,
                sf.full_name AS name,
                sf.phone,
                sf.latitude,
                sf.length,
                sf.logo,
                sf.price_hour,
                sf.tax_id,
                sf.establishment_id,
                p.name as province,
                p.id as id_province,
                c.name as city,
                c.id as id_city
            FROM
                soccer_field sf
                INNER JOIN province p ON p.id = sf.id_province
                INNER JOIN city c on c.id = sf.id_city
            $whereSql", 'ALL', $params);

            foreach($canchas AS $cancha){
               $cancha->logo = (empty($cancha->logo)) ? 'assets/img/cancha.png' : 'upload/cancha/' . $cancha->logo;
            }

            JSON($canchas);
        }
        public static function getByHora(){
            self::ensurePriceRangesTable();
            $data = Api::getData();
            $data->day = dayName($data->fecha);
            $dow = (int) date('N', strtotime($data->fecha));

            // BOT-TENANT: scoping por establishment del token bound.
            $boundEst = Auth::getEstablishmentId();
            $estFilter = '';
            $params = [
                $data->hora,
                $data->day,
                $data->fecha,
                $dow,
                $data->fecha,
                $data->fecha,
                $data->fecha,
                $data->fecha,
            ];
            if ($boundEst) {
                $estFilter = ' AND sf.establishment_id = ? ';
                $params[] = (int) $boundEst;
            }

            $canchas = query("SELECT
                                sf.id,
                                sf.full_name as name,
                                sf.phone as phone,
                                sf.latitude,
                                sf.length,
                                sf.logo,
                                COALESCE(
                                    (
                                        SELECT pr.price
                                        FROM price_ranges pr
                                        INNER JOIN schedules sh ON sh.id = h.id_schedule
                                        WHERE pr.id_field = sf.id
                                          AND sh.hour >= pr.start_time
                                          AND sh.hour < pr.end_time
                                        ORDER BY pr.start_time DESC
                                        LIMIT 1
                                    ),
                                    sf.price_hour
                                ) AS price_hour,
                                sd.name as day,
                                h.id_schedule as id_hora,
                                (select hour12 from schedules where id = h.id_schedule) as hora12,
                                sf.threshold,
                                c.name as city,
                                c.id as id_city
                            FROM soccer_field sf
                            INNER JOIN province p ON p.id = sf.id_province
                            INNER JOIN city c ON c.id = sf.id_city
                            INNER JOIN schedules_field h ON sf.id = h.id_field
                            INNER JOIN schedules_day sd ON h.id_day = sd.id
                            WHERE
                                h.id_schedule = ?
                                AND h.id_day = ?
                                AND (
                                    (
                                        SELECT COUNT(*)
                                          FROM booking b
                                         WHERE b.id_field = h.id_field
                                           AND b.date_booking = ?
                                           AND b.time_booking = h.id_schedule
                                           AND b.status NOT IN (2)
                                           AND (b.status <> 7 OR b.expires_at IS NULL OR b.expires_at > NOW())
                                    )
                                    +
                                    (
                                        SELECT COUNT(*)
                                          FROM recurring_booking rb
                                          INNER JOIN schedules s ON s.id = h.id_schedule
                                         WHERE rb.field_id = h.id_field
                                           AND rb.day_of_week = ?
                                           AND rb.start_time <= s.hour
                                           AND ADDTIME(rb.start_time, SEC_TO_TIME(rb.duration_min * 60)) > s.hour
                                           AND rb.status = 'active'
                                           AND rb.valid_from <= ?
                                           AND (rb.valid_until IS NULL OR rb.valid_until >= ?)
                                           AND NOT EXISTS (
                                                SELECT 1
                                                  FROM recurring_booking_pause p
                                                 WHERE p.recurring_booking_id = rb.id
                                                   AND p.status = 'approved'
                                                   AND ? BETWEEN p.from_date AND p.to_date
                                           )
                                           AND NOT EXISTS (
                                                SELECT 1
                                                  FROM booking b2
                                                 WHERE b2.recurring_booking_id = rb.id
                                                   AND b2.date_booking = ?
                                                   AND b2.status <> 2
                                           )
                                    )
                                ) < sf.threshold
                                $estFilter
                            ", 'ALL', $params);

            if (!empty($canchas)) {
                foreach ($canchas as $cancha) {
                    $occupiedRow = query(
                        "SELECT
                            (
                                (
                                    SELECT COUNT(*)
                                      FROM booking b
                                     WHERE b.id_field = ?
                                       AND b.date_booking = ?
                                       AND b.time_booking = ?
                                       AND b.status NOT IN (2)
                                       AND (b.status <> 7 OR b.expires_at IS NULL OR b.expires_at > NOW())
                                )
                                +
                                (
                                    SELECT COUNT(*)
                                      FROM recurring_booking rb
                                      INNER JOIN schedules s ON s.id = ?
                                     WHERE rb.field_id = ?
                                       AND rb.day_of_week = ?
                                       AND rb.start_time <= s.hour
                                       AND ADDTIME(rb.start_time, SEC_TO_TIME(rb.duration_min * 60)) > s.hour
                                       AND rb.status = 'active'
                                       AND rb.valid_from <= ?
                                       AND (rb.valid_until IS NULL OR rb.valid_until >= ?)
                                       AND NOT EXISTS (
                                            SELECT 1
                                              FROM recurring_booking_pause p
                                             WHERE p.recurring_booking_id = rb.id
                                               AND p.status = 'approved'
                                               AND ? BETWEEN p.from_date AND p.to_date
                                       )
                                       AND NOT EXISTS (
                                            SELECT 1
                                              FROM booking b2
                                             WHERE b2.recurring_booking_id = rb.id
                                               AND b2.date_booking = ?
                                               AND b2.status <> 2
                                       )
                                )
                            ) AS occupied",
                        'ARRAY',
                        [
                            (int) $cancha->id,
                            $data->fecha,
                            (int) $data->hora,
                            (int) $data->hora,
                            (int) $cancha->id,
                            $dow,
                            $data->fecha,
                            $data->fecha,
                            $data->fecha,
                            $data->fecha,
                        ]
                    );

                    $threshold = max(1, (int) ($cancha->threshold ?? 1));
                    $occupied = (int) ($occupiedRow['occupied'] ?? 0);
                    $cancha->occupied_slots = $occupied;
                    $cancha->free_slots = max(0, $threshold - $occupied);
                }
            }
            JSON($canchas);
        }
    }
