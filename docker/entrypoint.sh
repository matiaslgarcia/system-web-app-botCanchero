#!/bin/sh
set -eu

SMTP_HOST="${SMTP_HOST:-}"
SMTP_PORT="${SMTP_PORT:-587}"
SMTP_USER="${SMTP_USER:-}"
SMTP_PASS="${SMTP_PASS:-}"
SMTP_FROM="${SMTP_FROM:-}"
SMTP_SECURE="${SMTP_SECURE:-starttls}"

if [ -n "$SMTP_HOST" ] && [ -n "$SMTP_USER" ] && [ -n "$SMTP_PASS" ] && [ -n "$SMTP_FROM" ]; then
  {
    echo "defaults"
    echo "auth on"
    echo "tls on"
    echo "tls_trust_file /etc/ssl/certs/ca-certificates.crt"
    echo "account default"
    echo "host $SMTP_HOST"
    echo "port $SMTP_PORT"
    echo "from $SMTP_FROM"
    echo "user $SMTP_USER"
    echo 'passwordeval "printf %s \"$SMTP_PASS\""'
    case "$SMTP_SECURE" in
      ssl)
        echo "tls_starttls off"
        ;;
      none)
        echo "tls off"
        ;;
      *)
        echo "tls_starttls on"
        ;;
    esac
  } > /etc/msmtprc

  chmod 644 /etc/msmtprc
  echo "[entrypoint] SMTP configurado para envio de emails."
else
  echo "[entrypoint] SMTP no configurado (faltan SMTP_HOST/SMTP_USER/SMTP_PASS/SMTP_FROM)."
fi

exec "$@"
