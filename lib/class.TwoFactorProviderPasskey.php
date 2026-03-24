<?php
# See LICENSE for full license information.

require_once __DIR__ . '/WebAuthn/WebAuthn.php';
require_once __DIR__ . '/WebAuthn/CborDecoder.php';

use TwoFactor\WebAuthn\WebAuthn;

class TwoFactorProviderPasskey extends TwoFactorProvider
{
    const CREDENTIAL_META_KEY = '_two_factor_passkey_credential';

    public function get_key()
    {
        return 'TwoFactorProviderPasskey';
    }

    public function get_label()
    {
        return 'Passkey';
    }

    public function get_alternative_label()
    {
        return 'Use your passkey (biometrics / device)';
    }

    public function is_available_for_user($user_id)
    {
        $cred = $this->get_credential($user_id);
        return !empty($cred);
    }

    public function authentication_page($user)
    {
        // The actual UI is handled by verify_passkey.tpl + webauthn.js
        // This is a fallback for the provider interface
        echo '<p class="pagetext">Use your passkey to verify your identity.</p>';
        echo '<input type="hidden" name="webauthn_response" id="webauthn_response" value="" />';
    }

    public function validate_authentication($user_id, $params = [])
    {
        $response = $params['webauthn_response'] ?? '';
        if (empty($response)) return false;

        // CMSMS form processing HTML-encodes values — decode before JSON parse
        $response = html_entity_decode($response, ENT_QUOTES, 'UTF-8');

        $challenge = $_SESSION['twofactor_webauthn_challenge'] ?? '';
        if (empty($challenge)) return false;

        $decoded = json_decode($response, true);
        if (!$decoded || !isset($decoded['clientDataJSON'], $decoded['authenticatorData'], $decoded['signature'])) return false;

        // Try base credential first
        $cred = $this->get_credential($user_id);
        if ($cred) {
            try {
                $webauthn = self::get_webauthn_instance();
                $result = $webauthn->processGet(
                    $decoded['clientDataJSON'],
                    $decoded['authenticatorData'],
                    $decoded['signature'],
                    $cred['public_key'],
                    $challenge,
                    $cred['sign_count'] ?? 0
                );
                $cred['sign_count'] = $result->signCount;
                $cred['last_used_at'] = time();
                $this->save_credential($user_id, $cred);
                unset($_SESSION['twofactor_webauthn_challenge']);
                return true;
            } catch (\Exception $e) {
                error_log('TwoFactor Passkey: base credential validation failed: ' . $e->getMessage());
                // Fall through to Pro credentials
            }
        }

        // Try Pro credentials (multi-key)
        if (\TwoFactor::IsProActive() && class_exists('TwoFactorWebAuthnPro')) {
            $matched = TwoFactorWebAuthnPro::validate_authentication($user_id, $response, $challenge);
            if ($matched) {
                unset($_SESSION['twofactor_webauthn_challenge']);
                return true;
            }
        }

        return false;
    }

    public function pre_process_authentication($user)
    {
        // Generate challenge for the authentication ceremony
        $webauthn = self::get_webauthn_instance();
        $challenge = $webauthn->createChallenge();
        $_SESSION['twofactor_webauthn_challenge'] = $challenge;
        return true;
    }

    // --- Credential storage (single key for free tier) ---

    public function get_credential($user_id)
    {
        return TwoFactorUserMeta::get($user_id, self::CREDENTIAL_META_KEY);
    }

    public function save_credential($user_id, $credential)
    {
        return TwoFactorUserMeta::update($user_id, self::CREDENTIAL_META_KEY, $credential);
    }

    public function delete_credential($user_id)
    {
        return TwoFactorUserMeta::delete($user_id, self::CREDENTIAL_META_KEY);
    }

    // --- Registration helpers ---

    public function get_registration_options($user_id, $username)
    {
        $webauthn = self::get_webauthn_instance();
        $challenge = $webauthn->createChallenge();
        $_SESSION['twofactor_webauthn_reg_challenge'] = $challenge;

        $exclude = [];
        $cred = $this->get_credential($user_id);
        if ($cred) {
            $exclude[] = $cred['credential_id'];
        }

        // Pro: also exclude credentials from the multi-key table
        if (\TwoFactor::IsProActive() && class_exists('TwoFactorWebAuthnPro')) {
            $proKeys = TwoFactorWebAuthnPro::get_credentials($user_id);
            foreach ($proKeys as $k) {
                $exclude[] = $k['credential_id'];
            }
        }

        return $webauthn->getCreateArgs(
            (string)$user_id,
            $username,
            $challenge,
            $exclude,
            'platform',       // Free tier: platform authenticators only (passkeys)
            'preferred',
            'preferred'
        );
    }

    public function process_registration($user_id, $response_json)
    {
        $data = json_decode($response_json, true);
        if (!$data) throw new \Exception('Invalid registration response');

        $challenge = $_SESSION['twofactor_webauthn_reg_challenge'] ?? '';
        if (empty($challenge)) throw new \Exception('No registration challenge found');

        $webauthn = self::get_webauthn_instance();
        $result = $webauthn->processCreate(
            $data['clientDataJSON'],
            $data['attestationObject'],
            $challenge
        );

        $credential = [
            'credential_id' => $result->credentialId,
            'public_key'    => $result->credentialPublicKey,
            'sign_count'    => $result->signCount,
            'type'          => 'platform',
            'name'          => 'Passkey',
            'created_at'    => time(),
        ];

        $this->save_credential($user_id, $credential);
        unset($_SESSION['twofactor_webauthn_reg_challenge']);

        return $credential;
    }

    public function get_authentication_options($user_id)
    {
        $webauthn = self::get_webauthn_instance();
        $challenge = $webauthn->createChallenge();
        $_SESSION['twofactor_webauthn_challenge'] = $challenge;

        $allow = [];
        $cred = $this->get_credential($user_id);
        if ($cred) {
            $allow[] = $cred['credential_id'];
        }

        // Pro: also include credentials from the multi-key table
        if (\TwoFactor::IsProActive() && class_exists('TwoFactorWebAuthnPro')) {
            $proKeys = TwoFactorWebAuthnPro::get_credentials($user_id);
            foreach ($proKeys as $k) {
                $allow[] = $k['credential_id'];
            }
        }

        return $webauthn->getGetArgs($challenge, $allow, 'preferred');
    }

    // --- Utility ---

    public static function get_webauthn_instance()
    {
        $config = \cms_utils::get_config();
        $rootUrl = $config['root_url'] ?? '';
        $parsed = parse_url($rootUrl);
        $rpId = $parsed['host'] ?? 'localhost';
        $sitename = get_site_preference('sitename', 'CMS Made Simple');

        return new WebAuthn($sitename, $rpId);
    }

    public static function is_webauthn_supported()
    {
        // Server-side: need OpenSSL
        if (!function_exists('openssl_verify')) return false;
        // HTTPS check (WebAuthn requires secure context)
        $config = \cms_utils::get_config();
        $rootUrl = $config['root_url'] ?? '';
        $isSecure = (strpos($rootUrl, 'https://') === 0) || (strpos($rootUrl, 'http://localhost') === 0);
        return $isSecure;
    }
}
