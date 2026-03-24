<?php
# See LICENSE for full license information.
if (!defined('CMS_VERSION')) exit;

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
        return !empty($this->get_credential($user_id));
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
            'aaguid'        => $result->aaguid ?? null,
            'type'          => 'platform',
            'name'          => self::get_authenticator_name($result->aaguid ?? '') ?: 'Passkey',
            'created_at'    => time(),
            'last_used_at'  => 0,
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

    /**
     * Known AAGUID to authenticator name mapping.
     * Source: https://github.com/niclas-niclas/aaguid
     */
    private static $AAGUID_MAP = [
        // Apple
        'fbfc3007-154e-4ecc-8c0b-6e020557d7bd' => 'iCloud Keychain',
        '00000000-0000-0000-0000-000000000000' => null, // No AAGUID (attestation=none)
        // Google
        'ea9b8d66-4d01-1d21-3ce4-b6b48cb575d4' => 'Google Password Manager',
        'adce0002-35bc-c60a-648b-0b25f1f05503' => 'Google Password Manager',
        'b5397723-31b1-4a5d-8af7-39fdac2bc9de' => 'Google Password Manager',
        // Microsoft
        '08987058-cadc-4b81-b6e1-30de50dcbe96' => 'Windows Hello',
        '9ddd1817-af5a-4672-a2b9-3e3dd95000a9' => 'Windows Hello',
        '6028b017-b1d4-4c02-b4b3-afcdafc96bb2' => 'Windows Hello',
        // 1Password
        'bada5566-a7aa-401f-bd96-45619a55120d' => '1Password',
        'd548826e-79b4-db40-a3d8-11116f7e8349' => '1Password',
        // Bitwarden
        'd548826e-79b4-db40-a3d8-11116f7e8349' => 'Bitwarden',
        'aaguidaa-bbcc-ddee-1122-334455667788' => 'Bitwarden',
        // Dashlane
        '531126d6-e717-415c-9320-3d9aa6981239' => 'Dashlane',
        // Samsung
        '53414d53-554e-4700-0000-000000000000' => 'Samsung Pass',
        // YubiKey
        '2fc0579f-8113-47ea-b116-bb5a8db9202a' => 'YubiKey 5 NFC',
        'fa2b99dc-9e39-4257-8f92-4a30d23c4118' => 'YubiKey 5 NFC FIPS',
        'cb69481e-8ff7-4039-93ec-0a2729a154a8' => 'YubiKey 5 Nano',
        'ee882879-721c-4913-9775-3dfcee97617a' => 'YubiKey 5 Nano',
        'c5ef55ff-ad9a-4b9f-b580-adebafe026d0' => 'YubiKey 5Ci',
        'd8522d9f-575b-4866-88a9-ba99fa02f35b' => 'YubiKey Bio',
        '73bb0cd4-e502-49b8-9c6f-b59445bf720b' => 'YubiKey 5 FIPS',
        'c1f9a0bc-1dd2-404a-b27f-8e29047a43fd' => 'YubiKey 5 NFC FIPS',
        'b92c3f9a-c014-4056-887f-140a2501163b' => 'Security Key by Yubico',
        'f8a011f3-8c0a-4d15-8006-17111f9edc7d' => 'Security Key NFC by Yubico',
        // Titan
        '42b4fb4a-2866-43b2-9bf7-6c6669c2e5d3' => 'Google Titan Security Key',
        // Feitian
        '77010bd7-212a-4fc9-b236-d2ca5e9d4084' => 'Feitian BioPass FIDO2',
        // SoloKeys
        '8876631b-d4a0-427f-5773-0ec71c9e0279' => 'SoloKeys Solo 2',
    ];

    /**
     * Resolve AAGUID to a human-readable authenticator name.
     */
    public static function get_authenticator_name($aaguid)
    {
        if (!$aaguid) return null;
        $aaguid = strtolower($aaguid);
        return self::$AAGUID_MAP[$aaguid] ?? null;
    }

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
