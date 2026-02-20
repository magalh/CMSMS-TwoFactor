<?php
if (!defined('CMS_VERSION')) exit;

$mod_tf = cms_utils::get_module('TwoFactor');
$mod_pro = cms_utils::get_module('TwoFactorPro');

if (isset($params['clear_all_prefs'])) {
    $basic_prefs = [
        'twofactor_sms_product_key',
        'twofactor_smscredit_enabled',
        'twofactor_sms_available',
        'twofactor_twilio_api_key',
        'twofactor_twilio_api_secret',
        'twofactor_twilio_service_sid',
        'twofactor_twilio_enabled'
    ];
    
    $pro_prefs = [
        'twofactorpro_license_key',
        'twofactorpro_enabled',
        'twofactorpro_license_verified',
        'twofactorpro_enforce_all',
        'twofactorpro_rate_limiting_enabled',
        'twofactorpro_max_attempts_lockout',
        'twofactorpro_max_attempts_reset',
        'twofactorpro_notify_admin',
        'twofactorpro_ip_blacklist',
        'twofactorpro_reset_email_subject',
        'twofactorpro_reset_email_body',
        'twofactorpro_alert_email_subject',
        'twofactorpro_alert_email_body'
    ];
    
    foreach ($basic_prefs as $pref) {
        $this->RemovePreference($pref);
    }
    if ($mod_pro) {
        foreach ($pro_prefs as $pref) {
            $mod_pro->RemovePreference($pref);
        }
    }
    $this->SetMessage('All preferences cleared');
    $this->RedirectToAdminTab('debug');
    return;
}

$basic_prefs = [
    'twofactor_sms_product_key',
    'twofactor_smscredit_enabled',
    'twofactor_sms_available',
    'twofactor_twilio_api_key',
    'twofactor_twilio_api_secret',
    'twofactor_twilio_service_sid',
    'twofactor_twilio_enabled'
];

$pro_prefs = [
    'twofactorpro_license_key',
    'twofactorpro_enabled',
    'twofactorpro_license_verified',
    'twofactorpro_enforce_all',
    'twofactorpro_rate_limiting_enabled',
    'twofactorpro_max_attempts_lockout',
    'twofactorpro_max_attempts_reset',
    'twofactorpro_notify_admin',
    'twofactorpro_ip_blacklist',
    'twofactorpro_reset_email_subject',
    'twofactorpro_reset_email_body',
    'twofactorpro_alert_email_subject',
    'twofactorpro_alert_email_body'
];

$basic_prefs_data = [];
foreach ($basic_prefs as $pref) {
    $val = $this->GetPreference($pref, '');
    if (strpos($pref, 'secret') !== false || strpos($pref, 'key') !== false) {
        $val = $val ? '***' : '';
    }
    $basic_prefs_data[] = ['name' => $pref, 'value' => $val];
}

$pro_prefs_data = [];
if ($mod_pro) {
    foreach ($pro_prefs as $pref) {
        $val = $mod_pro->GetPreference($pref, '');
        if (strpos($pref, 'secret') !== false || strpos($pref, 'key') !== false) {
            $val = $val ? '***' : '';
        }
        $pro_prefs_data[] = ['name' => $pref, 'value' => $val];
    }
}

$smarty = cmsms()->GetSmarty();
$tpl = $smarty->CreateTemplate($this->GetTemplateResource('admin_debug.tpl'), null, null, $smarty);
$tpl->assign('is_pro_installed', $is_pro_installed);
$tpl->assign('is_pro_active', $is_pro_active);
$tpl->assign('mod_pro', $mod_pro);
$tpl->assign('basic_prefs', $basic_prefs_data);
$tpl->assign('pro_prefs', $pro_prefs_data);
$tpl->assign('clear_url', $this->create_url('m1_', 'defaultadmin', '', ['clear_all_prefs' => '1']));
$tpl->display();
