<?php
# See doc/LICENSE.txt for full license information.
if( !defined('CMS_VERSION') ) exit;
$this->SetCurrentTab('settings');

// Handle save
if (isset($params['save_settings'])) {
    // Save enforce 2FA setting (available to all)
    set_site_preference('twofactor_enforce_all', isset($params['enforce_2fa_all']) ? '1' : '0');
    
    if (TwoFactor::IsProEnabled()) {
        set_site_preference('twofactor_rate_limiting_enabled', isset($params['rate_limiting_enabled']) ? '1' : '0');
        set_site_preference('twofactor_max_attempts_lockout', (int)$params['max_attempts_lockout']);
        set_site_preference('twofactor_max_attempts_reset', (int)$params['max_attempts_reset']);
        set_site_preference('twofactor_notify_admin', isset($params['notify_admin']) ? '1' : '0');
        set_site_preference('twofactor_ip_blacklist', trim($params['ip_blacklist']));
    }
    $this->SetMessage($this->Lang('settings_saved'));
    $this->RedirectToAdminTab('settings');
    return;
}

$is_pro = TwoFactor::IsProEnabled();

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('admin_settings.tpl'), null, null, $smarty);

$tpl->assign('is_pro', $is_pro);
$tpl->assign('enforce_2fa_all', get_site_preference('twofactor_enforce_all', '0'));

if ($is_pro) {
    $tpl->assign('rate_limiting_enabled', get_site_preference('twofactor_rate_limiting_enabled', '1'));
    $tpl->assign('max_attempts_lockout', get_site_preference('twofactor_max_attempts_lockout', '3'));
    $tpl->assign('max_attempts_reset', get_site_preference('twofactor_max_attempts_reset', '6'));
    $tpl->assign('notify_admin', get_site_preference('twofactor_notify_admin', '1'));
    $tpl->assign('ip_blacklist', get_site_preference('twofactor_ip_blacklist', ''));
}

$tpl->display();
