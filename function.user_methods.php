<?php
# See doc/LICENSE.txt for full license information.
if (!defined('CMS_VERSION')) exit;

$uid = get_userid(false);

// Handle set primary
if (isset($params['set_primary'])) {
    $primary_value = $params['set_primary'];
    if (!empty($primary_value)) {
        TwoFactorCore::set_primary_provider($uid, $primary_value);
        $this->SetMessage($this->Lang('primary_updated'));
    }
    $this->RedirectToAdminTab('methods', '', 'user_prefs');
    return;
}

$providers = TwoFactorCore::get_providers();
$enabled_providers = TwoFactorUserMeta::get_enabled_providers($uid);
$primary_provider = TwoFactorUserMeta::get_primary_provider($uid);
$is_2fa_active = !empty($enabled_providers) && $primary_provider && $primary_provider != 'disabled';
$smscredit_enabled = $this->GetPreference('twofactor_smscredit_enabled', '0');
$sms_available = $this->GetPreference('twofactor_sms_available', false);
$sms_action = ($smscredit_enabled == '1') ? 'setup_sms_credit_enabled' : 'setup_sms';

// Build primary method options
$primary_options = [
    ['label' => $this->Lang('disabled'), 'value' => 'disabled']
];
foreach ($providers as $key => $provider) {
    if (in_array($key, $enabled_providers) && $key != 'TwoFactorProviderBackupCodes') {
        if ($key == 'TwoFactorProviderSMS' && !$sms_available) {
            continue;
        }
        $primary_options[] = ['label' => $provider->get_label(), 'value' => $key];
    }
}

$smarty = cmsms()->GetSmarty();
$tpl = $smarty->CreateTemplate($this->GetTemplateResource('user_methods.tpl'), null, null, $smarty);
$tpl->assign('providers', $providers);
$tpl->assign('enabled_providers', $enabled_providers);
$tpl->assign('primary_provider', $primary_provider);
$tpl->assign('primary_options', $primary_options);
$tpl->assign('is_2fa_active', $is_2fa_active);
$tpl->assign('user_id', $uid);
$tpl->assign('actionid', $id);
$tpl->assign('smscredit_enabled', $smscredit_enabled);
$tpl->assign('sms_available', $sms_available);
$tpl->assign('sms_action', $sms_action);
$tpl->assign('mod', $this);
$tpl->display();
