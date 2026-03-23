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
$message = '';

// Handle registration response (AJAX)
if (isset($params['register_passkey'])) {
    header('Content-Type: application/json');
    try {
        $response = $params['webauthn_response'] ?? '';
        $provider->process_registration($uid, $response);
        TwoFactorCore::enable_provider_for_user($uid, 'TwoFactorProviderPasskey');
        echo json_encode(['success' => true]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Handle get registration options (AJAX)
if (isset($params['get_reg_options'])) {
    header('Content-Type: application/json');
    $username = get_username($uid);
    $options = $provider->get_registration_options($uid, $username);
    echo json_encode($options);
    exit;
}

// Handle reset
if (isset($params['reset'])) {
    $provider->delete_credential($uid);
    TwoFactorCore::disable_provider_for_user($uid, 'TwoFactorProviderPasskey');
    $this->SetMessage($this->Lang('passkey_reset'));
    $this->RedirectToAdminTab('', '', 'user_prefs');
    return;
}

$is_configured = $provider->is_available_for_user($uid);
$credential = $provider->get_credential($uid);
$webauthn_supported = TwoFactorProviderPasskey::is_webauthn_supported();

// Check if Pro allows physical keys
$is_pro = TwoFactor::IsProActive();

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('setup_passkey.tpl'), null, null, $smarty);
$tpl->assign('is_configured', $is_configured);
$tpl->assign('credential', $credential);
$tpl->assign('webauthn_supported', $webauthn_supported);
$tpl->assign('is_pro', $is_pro);
$tpl->assign('error', $error);
$tpl->assign('message', $message);
$tpl->display();
