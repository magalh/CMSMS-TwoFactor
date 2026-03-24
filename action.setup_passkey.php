<?php
# See LICENSE for full license information.
if (!defined('CMS_VERSION')) exit;
if (!$this->CheckPermission(TwoFactor::USE_PERM)) return;

if (isset($params['cancel'])) {
    $this->RedirectToAdminTab('', '', 'user_prefs');
    return;
}

$uid = get_userid();
$provider = TwoFactorProviderPasskey::get_instance();
$error = '';

$is_configured = $provider->is_available_for_user($uid);
$credential = $provider->get_credential($uid);
$webauthn_supported = TwoFactorProviderPasskey::is_webauthn_supported();
$is_pro = TwoFactor::IsProActive();

// Build passkey cards
$passkey_cards = [];
if ($credential) {
    $auth_name = TwoFactorProviderPasskey::get_authenticator_name($credential['aaguid'] ?? '');
    $passkey_cards[] = [
        'id'          => 'base',
        'name'        => $credential['name'] ?? 'Passkey',
        'type'        => $credential['type'] ?? 'platform',
        'created_at'  => $credential['created_at'] ?? 0,
        'last_used_at'=> $credential['last_used_at'] ?? 0,
        'sign_count'  => $credential['sign_count'] ?? 0,
        'source'      => 'free',
        'authenticator' => $auth_name,
    ];
}
if ($is_pro && class_exists('TwoFactorWebAuthnPro')) {
    $pro_keys = TwoFactorWebAuthnPro::get_credentials($uid);
    foreach ($pro_keys as $k) {
        $auth_name = TwoFactorProviderPasskey::get_authenticator_name($k['aaguid'] ?? '');
        $passkey_cards[] = [
            'id'          => $k['id'],
            'name'        => $k['name'] ?: ($k['type'] === 'cross-platform' ? 'Security Key' : 'Passkey'),
            'type'        => $k['type'] ?? 'platform',
            'created_at'  => $k['created_at'] ?? 0,
            'last_used_at'=> $k['last_used_at'] ?? 0,
            'sign_count'  => $k['sign_count'] ?? 0,
            'source'      => 'pro',
            'authenticator' => $auth_name,
        ];
    }
}

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('setup_passkey.tpl'), null, null, $smarty);
$tpl->assign('mod_url', $this->GetModuleURLPath());
$tpl->assign('is_configured', $is_configured);
$tpl->assign('credential', $credential);
$tpl->assign('webauthn_supported', $webauthn_supported);
$tpl->assign('is_pro', $is_pro);
$tpl->assign('passkey_cards', $passkey_cards);
$tpl->assign('error', $error);
$tpl->display();
