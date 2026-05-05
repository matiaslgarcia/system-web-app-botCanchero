#!/usr/bin/php
<?php
// PHP-B2: SQLi cerrada — todo parametrizado.
// CLI cron: marca como cancelados (status 3) los bookings cuyo horario ya pasó hace +1h
// y siguen en estado "system user" (1 = bot, 21 = legacy import).
//
// Schedule sugerido (crontab):
//   */15 * * * * /usr/bin/php /var/www/systemWebBotCanchero/cronjob/checkBooking.php >> /var/log/botcanchero-cron.log 2>&1
//
// Hardcoded path para CLI; en local override con env CHECKBOOKING_BOOTSTRAP.
$bootstrap = getenv('CHECKBOOKING_BOOTSTRAP') ?: '/var/www/systemWebBotCanchero/app/int.php';
define('SKIP_AUTH', true); // CLI no tiene sesión web.
require $bootstrap;

date_default_timezone_set('America/Argentina/Buenos_Aires');

function cancelarSiSystemUser($id) {
    $row = query("SELECT user FROM booking WHERE id = ? LIMIT 1", '', [$id]);
    if (!$row) return;
    $u = (int) $row->user;
    if ($u === 1 || $u === 21) {
        query("UPDATE booking SET status = 3 WHERE id = ?", '', [$id]);
        // Auditoría — la tabla audit_log existe desde la migración 004.
        @query(
            "INSERT INTO audit_log (actor_user_id, action, target_type, target_id, payload)
             VALUES (NULL, 'cron_auto_cancel', 'booking', ?, NULL)",
            '',
            [$id]
        );
    }
}

$expired = query(
    "SELECT b.id
       FROM booking AS b
       INNER JOIN schedules AS s ON s.id = b.time_booking
      WHERE b.status IN (1, 6)
        AND DATE_FORMAT(b.date_booking, '%Y%m%d') <= DATE_FORMAT(NOW(), '%Y%m%d')
        AND DATE_FORMAT(DATE_ADD(s.time, INTERVAL 1 HOUR), '%H%i%s') < DATE_FORMAT(NOW(), '%H%i%s')",
    'ALL'
);

foreach ($expired ?: [] as $b) {
    cancelarSiSystemUser((int) $b->id);
}

fwrite(STDOUT, sprintf("[%s] checkBooking processed=%d\n", date('c'), count($expired ?: [])));
