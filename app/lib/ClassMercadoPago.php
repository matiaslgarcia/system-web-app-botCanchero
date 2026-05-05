<?php

    

    Class MercadoPago{

        const url_auth      = "https://auth.mercadopago.com/authorization";
        const url_token     = "https://api.mercadopago.com/oauth/token";

        private static function oauthClientId() {
            return trim((string) ($_ENV['MP_OAUTH_CLIENT_ID'] ?? ''));
        }
        private static function oauthClientSecret() {
            return trim((string) ($_ENV['MP_OAUTH_CLIENT_SECRET'] ?? ''));
        }
        public static function isOAuthConfigured() {
            return self::oauthClientId() !== '' && self::oauthClientSecret() !== '';
        }
        private static function oauthRedirectUri() {
            $fromEnv = trim((string) ($_ENV['MP_OAUTH_REDIRECT_URI'] ?? ''));
            if ($fromEnv !== '') return $fromEnv;

            // Fallback local/dev: host actual + /app/mercadopago_OAuth
            if (!empty($_SERVER['HTTP_HOST'])) {
                $isHttps = (
                    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
                );
                $scheme = $isHttps ? 'https' : 'http';
                return $scheme . '://' . $_SERVER['HTTP_HOST'] . '/app/mercadopago_OAuth';
            }

            // Fallback final: usar BASE_URL configurada.
            return rtrim(URL, '/') . '/mercadopago_OAuth';
        }

        private static function getCurrentEstablishmentId() {
            $fieldId = (int) Users::infoUser('id_field');
            if ($fieldId <= 0) return null;
            $row = query("SELECT establishment_id FROM soccer_field WHERE id = ? LIMIT 1", "ARRAY", [$fieldId]);
            return (!empty($row['establishment_id'])) ? (int) $row['establishment_id'] : null;
        }

        private static function buildState($establishmentId) {
            $payload = $establishmentId . ':' . time();
            return base64_encode($payload);
        }

        private static function parseState($state) {
            if (empty($state)) return null;
            $decoded = base64_decode($state, true);
            if ($decoded === false) return null;
            $parts = explode(':', $decoded, 2);
            $est = (int) ($parts[0] ?? 0);
            return $est > 0 ? $est : null;
        }

        public static function getUrlOAuth(){
            if (!self::isOAuthConfigured()) {
                return URL . 'account_settings?error=mp_oauth_not_configured';
            }
            $establishmentId = self::getCurrentEstablishmentId();
            $state = self::buildState($establishmentId ?: 0);
            $url = self::url_auth
                . "?client_id=" . rawurlencode(self::oauthClientId())
                . "&response_type=code&platform_id=mp&state=" . rawurlencode($state)
                . "&redirect_uri=" . rawurlencode(self::oauthRedirectUri());

            return $url;
        }
        public static function createRefreshToken($code, $state = null){
            if (!self::isOAuthConfigured()) {
                header('Location: ' . URL . 'account_settings?error=mp_oauth_not_configured');
                die();
            }
            $establishmentId = self::parseState($state) ?: self::getCurrentEstablishmentId();
            if (!$establishmentId) {
                header('Location: ' . URL . 'account_settings?error=mp_establishment_not_found');
                die();
            }

            $data = array(
                'client_secret' => self::oauthClientSecret(),
                'client_id'     => self::oauthClientId(),
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => self::oauthRedirectUri()
            );
            $ch = curl_init(self::url_token);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            $data = json_decode($response);

            if (empty($data) || empty($data->access_token)) {
                $mpError = '';
                if (!empty($curlErr)) {
                    $mpError = 'curl_error:' . $curlErr;
                } elseif (!empty($data->message)) {
                    $mpError = (string) $data->message;
                } elseif (!empty($data->error_description)) {
                    $mpError = (string) $data->error_description;
                } elseif (!empty($response)) {
                    $mpError = (string) $response;
                } else {
                    $mpError = 'oauth_token_exchange_failed';
                }

                header('Location: ' . URL . 'account_settings?error=mp_oauth_failed&mp_http=' . $httpCode . '&mp_error=' . rawurlencode(substr($mpError, 0, 240)));
                die();
            }

            self::setAccessToken($establishmentId, $data);

            header('Location: ' . URL . 'account_settings');
            die();
        }

        private static function setAccessToken($establishmentId, $oauthData){
            $accessToken = $oauthData->access_token ?? null;
            $refreshToken = $oauthData->refresh_token ?? null;
            $publicKey = $oauthData->public_key ?? null;
            $userId = isset($oauthData->user_id) ? (string) $oauthData->user_id : null;
            $expiresIn = isset($oauthData->expires_in) ? (int) $oauthData->expires_in : 0;
            $expiresAt = $expiresIn > 0 ? date('Y-m-d H:i:s', time() + $expiresIn) : null;

            query(
                "UPDATE establishment
                    SET mp_access_token = ?,
                        mp_refresh_token = ?,
                        mp_public_key = ?,
                        mp_user_id = ?,
                        mp_token_expires_at = ?
                  WHERE id = ?",
                '',
                [$accessToken, $refreshToken, $publicKey, $userId, $expiresAt, (int) $establishmentId]
            );

            // Fallback temporal para compatibilidad legacy.
            query(
                "UPDATE soccer_field
                    SET token_mercadopago = ?
                  WHERE establishment_id = ?",
                '',
                [$accessToken, (int) $establishmentId]
            );
        }

        public static function unlinkCurrentEstablishment() {
            $establishmentId = self::getCurrentEstablishmentId();
            if (!$establishmentId) {
                JSON(['icon' => 'error', 'msg' => 'No se encontró el establecimiento asociado al usuario.'], 400);
            }

            query(
                "UPDATE establishment
                    SET mp_access_token = NULL,
                        mp_refresh_token = NULL,
                        mp_public_key = NULL,
                        mp_user_id = NULL,
                        mp_token_expires_at = NULL
                  WHERE id = ?",
                '',
                [(int) $establishmentId]
            );

            // Mantener consistencia con fallback legacy.
            query(
                "UPDATE soccer_field
                    SET token_mercadopago = NULL
                  WHERE establishment_id = ?",
                '',
                [(int) $establishmentId]
            );

            audit('mp_unlink', 'establishment', (int) $establishmentId, [
                'message' => 'Mercado Pago desvinculado desde account_settings'
            ]);

            JSON(['icon' => 'success', 'msg' => 'Mercado Pago desvinculado correctamente.']);
        }
    }
