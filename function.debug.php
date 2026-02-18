<?php
if (!defined('CMS_VERSION')) exit;

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
    if ($pro) {
        foreach ($pro_prefs as $pref) {
            $pro->RemovePreference($pref);
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

echo '<h4>Debug Info</h4>';
echo '<p><strong>$is_pro:</strong> ' . ($is_pro ? 'true' : 'false') . '</p>';
echo '<p><strong>$pro module:</strong> ' . ($pro ? 'loaded' : 'not loaded') . '</p>';
if ($pro && method_exists($pro, 'IsProEnabled')) {
    echo '<p><strong>$pro->IsProEnabled():</strong> ' . ($pro->IsProEnabled() ? 'true' : 'false') . '</p>';
}

echo '<h4>Basic Preferences (TwoFactor)</h4>';
echo '<table class="pagetable">';
echo '<thead><tr><th>Preference</th><th>Value</th></tr></thead><tbody>';
foreach ($basic_prefs as $pref) {
    $val = $this->GetPreference($pref, '');
    if (strpos($pref, 'secret') !== false || strpos($pref, 'key') !== false) {
        $val = $val ? '***' : '';
    }
    echo '<tr><td>' . htmlspecialchars($pref) . '</td><td>' . htmlspecialchars($val) . '</td></tr>';
}
echo '</tbody></table>';

if ($pro) {
    echo '<h4>Pro Preferences (TwoFactorPro)</h4>';
    echo '<table class="pagetable">';
    echo '<thead><tr><th>Preference</th><th>Value</th></tr></thead><tbody>';
    foreach ($pro_prefs as $pref) {
        $val = $pro->GetPreference($pref, '');
        if (strpos($pref, 'secret') !== false || strpos($pref, 'key') !== false) {
            $val = $val ? '***' : '';
        }
        echo '<tr><td>' . htmlspecialchars($pref) . '</td><td>' . htmlspecialchars($val) . '</td></tr>';
    }
    echo '</tbody></table>';
}
echo '<p><a href="' . $this->create_url('m1_', 'defaultadmin', '', ['clear_all_prefs' => '1']) . '" onclick="return confirm(\'Are you sure you want to clear all preferences?\');">Clear All Preferences</a></p>';
