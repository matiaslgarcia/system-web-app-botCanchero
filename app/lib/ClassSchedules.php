<?php

    class Schedules{
        public static function getByField(){
            $user = Users::getById(Users::infoUser('id'));
            $field = ($user->rol == 'superAdmin') ? '1=1' : 'f.id_field = ?';
            $params = ($user->rol == 'superAdmin') ? [] : [$user->id_field];

            $schedules = query("SELECT s.id, s.hour, s.time, f.id_field, f.id_day, s.hour12 FROM schedules AS s LEFT JOIN schedules_field AS f ON s.id = f.id_schedule WHERE $field ORDER BY s.id ASC", 'ALL', $params);

            return $schedules;

        }
        public static function getByfieldID($id_field){

            $schedules = query("SELECT s.id, s.hour, s.time, f.id_field, f.id_day, s.hour12 FROM schedules AS s LEFT JOIN schedules_field AS f ON s.id = f.id_schedule WHERE f.id_field = ? ORDER BY S.id ASC", 'ALL', [$id_field]);

            return $schedules;

        }
        public static function getDay(){
            $day = query("SELECT * FROM schedules_day ORDER BY id", 'ALL');

            return $day;
        }
        public static function getAllDayCancha($id_day, $id_field){
            $schedules = query("SELECT 
                    s.id,
                    s.hour,
                    s.hour12, 
                    CASE WHEN f.id IS NOT NULL THEN 'checked' ELSE NULL END AS checked
                    FROM schedules AS s 
                    LEFT JOIN schedules_field AS f ON s.id = f.id_schedule
                    AND f.id_day = ?
                    AND
                    f.id_field = ?
                    ORDER BY s.id ASC",
            'ALL', [$id_day, $id_field]);

            return $schedules;
        }
        public static function deleteAllSchedulesField($id_day, $id_field){
            query("DELETE FROM schedules_field WHERE id_day = ? AND id_field = ?", '', [$id_day, $id_field]);
        }
        public static function addField($id_day, $id_field, $id_schedule){
            query("INSERT INTO schedules_field(
                id_field,
                id_schedule,
                id_day
                ) VALUES (
                    ?,
                    ?,
                    ?
                )
            ", '', [$id_field, $id_schedule, $id_day]);
        }
        public static function getByFieldLibre($id_field, $id_day, $date, $excludeBookingId = null){
            $dow = (int) date('N', strtotime($date));
            $excludeSql = $excludeBookingId ? " AND b.id <> " . (int) $excludeBookingId : "";
            $result = query("SELECT
                    s.id,
                    d.name AS dia,
                    d.id AS num_day,
                    s.hour AS time,
                    (
                        (SELECT COUNT(*)
                           FROM booking b
                          WHERE b.id_field = sf.id_field
                            AND b.time_booking = s.id
                            AND b.date_booking = ?
                            AND b.status NOT IN (2)
                            AND (b.status <> 7 OR b.expires_at IS NULL OR b.expires_at > NOW())
                            $excludeSql)
                        +
                        (SELECT COUNT(*)
                           FROM recurring_booking rb
                          WHERE rb.field_id = sf.id_field
                            AND rb.day_of_week = ?
                            AND rb.start_time <= s.hour
                            AND ADDTIME(rb.start_time, SEC_TO_TIME(rb.duration_min * 60)) > s.hour
                            AND rb.status = 'active'
                            AND rb.valid_from <= ?
                            AND (rb.valid_until IS NULL OR rb.valid_until >= ?)
                            AND NOT EXISTS (
                                SELECT 1 FROM recurring_booking_pause p
                                 WHERE p.recurring_booking_id = rb.id
                                   AND p.status = 'approved'
                                   AND ? BETWEEN p.from_date AND p.to_date
                            )
                            AND NOT EXISTS (
                                SELECT 1 FROM booking b2
                                 WHERE b2.recurring_booking_id = rb.id
                                   AND b2.date_booking = ?
                                   AND b2.status <> 2
                            )
                        )
                    ) AS total,
                    f.threshold as threshold
                 FROM
                    schedules_field AS sf
                INNER JOIN schedules AS s
                ON
                     s.id = sf.id_schedule
                INNER JOIN schedules_day AS d
                ON
                    sf.id_day = d.id
                INNER JOIN soccer_field AS f
                ON
                    f.id = sf.id_field
                WHERE 
                    sf.id_field = ? AND sf.id_day = ?
                HAVING COALESCE(total, 0) < threshold
            ", 'ALL', [$date, $dow, $date, $date, $date, $date, $id_field, $id_day]);

            return $result;
        }
        public static function getHorasBooking($day, $cancha, $date, $excludeId = null){
            $excludeSql = ($excludeId) ? " AND id <> " . (int)$excludeId : "";
            $currentTime = date('H:i:s');
            $currentDate = date('Y-m-d');
            $dow = date('N', strtotime($date));

            // Disponibilidad unificada: bookings puntuales + reservas fijas activas para esa fecha/hora.
            $result = query("SELECT
                    s.id AS id,
                    s.hour12 AS text,
                    (
                        (SELECT COUNT(*)
                           FROM booking
                          WHERE id_field = ?
                            AND date_booking = ?
                            AND time_booking = s.id
                            AND status NOT IN (2)
                            AND (status <> 7 OR expires_at IS NULL OR expires_at > NOW())
                            $excludeSql)
                        +
                        (SELECT COUNT(*) FROM recurring_booking rb 
                         WHERE rb.field_id = ? AND rb.day_of_week = ? AND rb.start_time <= s.hour AND ADDTIME(rb.start_time, SEC_TO_TIME(rb.duration_min*60)) > s.hour
                           AND rb.status = 'active' AND rb.valid_from <= ? AND (rb.valid_until IS NULL OR rb.valid_until >= ?)
                           AND NOT EXISTS (SELECT 1 FROM recurring_booking_pause p WHERE p.recurring_booking_id = rb.id AND p.status = 'approved' AND ? BETWEEN p.from_date AND p.to_date)
                           AND NOT EXISTS (SELECT 1 FROM booking b2 WHERE b2.recurring_booking_id = rb.id AND b2.date_booking = ? AND b2.status <> 2)
                        )
                    ) AS total,
                    f.threshold
                FROM
                    schedules_field AS sf
                INNER JOIN schedules_day AS d
                    ON sf.id_day = d.id
                INNER JOIN schedules AS s
                    ON sf.id_schedule = s.id
                INNER JOIN soccer_field AS f
                    ON f.id = sf.id_field
                WHERE f.id = ? AND d.id = ?
                AND (? > ? OR s.hour > ?)
                HAVING 
                 COALESCE(total, 0) < threshold",
            'ALL', [
                $cancha, $date, 
                $cancha, $dow, $date, $date, $date, $date,
                $cancha, $day, $date, $currentDate, $currentTime
            ]);

            foreach ($result as &$slot) {
                $threshold = max(1, (int) ($slot->threshold ?? 1));
                $occupied = max(0, (int) ($slot->total ?? 0));
                $free = max(0, $threshold - $occupied);
                $slot->text = sprintf('%s (%d/%d cupos libres)', $slot->text, $free, $threshold);
            }

            JSON($result);
        }

        /** Métodos para API v2 **/
        public static function getFreeByField(){
            $data = Api::getData();
            if (!isset($data->id_field) || !isset($data->date)) {
                Api::ApiError(['error' => 'id_field and date are required'], 400);
            }

            // BOT-TENANT: scoping por establishment del token bound.
            if (class_exists('Auth')) {
                $boundEst = Auth::getEstablishmentId();
                if ($boundEst) {
                    $field = query(
                        "SELECT establishment_id FROM soccer_field WHERE id = ?",
                        'ARRAY',
                        [(int) $data->id_field]
                    );
                    if (!$field || (int) ($field['establishment_id'] ?? 0) !== (int) $boundEst) {
                        Api::ApiError(['error' => 'Forbidden access to field'], 403);
                    }
                }
            }

            $day = dayName($data->date);
            $excludeBookingId = isset($data->exclude_booking_id) ? (int) $data->exclude_booking_id : null;
            $result = self::getByFieldLibre($data->id_field, $day, $data->date, $excludeBookingId);
            JSON($result);
        }
    }
