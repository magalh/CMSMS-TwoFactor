<?php
# See LICENSE for full license information.
if (!defined('CMS_VERSION')) exit;
if (!$this->CheckPermission(TwoFactor::USE_PERM)) return;

$uid = get_userid();
$provider = TwoFactorProviderPasskey::get_instance();

$op = $params['op'] ?? '';

switch ($op) {
    case 'get_reg_options':
        $username = get_username($uid);
        $options = $provider->get_registration_options($uid, $username);
        \xt_utils::send_ajax_and_exit($options);
        break;

    case 'register':
        try {
            $response = $params['webauthn_response'] ?? '';
            $provider->process_registration($uid, $response);
            TwoFactorCore::enable_provider_for_user($uid, 'TwoFactorProviderPasskey');
            \xt_utils::send_ajax_and_exit(['success' => true]);
        } catch (\Exception $e) {
            \xt_utils::send_ajax_and_exit(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    default:
        \xt_utils::send_ajax_and_exit(['success' => false, 'error' => 'Invalid operation']);
        break;
}
