<?php
# See doc/LICENSE.txt for full license information.
if( !defined('CMS_VERSION') ) exit;
$this->SetCurrentTab('premium');

// Handle license verification
if (isset($params['verify_license'])) {
    $license_key = trim($params['license_key']);
    
    if (empty($license_key)) {
        $this->SetError($this->Lang('license_key_required'));
        $this->RedirectToAdminTab();
        return;
    }
    
    $config = cms_utils::get_config();
    $site_url = $config['root_url'];
    $data = TwoFactorAPI::validate_license($license_key, $site_url);
    if ($data !== false) {
        if (isset($data['valid']) && $data['valid'] === true) {
            set_site_preference('twofactor_license_key', $license_key);
            set_site_preference('twofactor_pro_enabled', '1');
            set_site_preference('twofactor_license_verified', time());
            $this->SetMessage($this->Lang('license_activated'));
        } else {
            set_site_preference('twofactor_license_key', "");
            set_site_preference('twofactor_pro_enabled', '0');
            $this->SetError($data["error"]);
        }
    } else {
        set_site_preference('twofactor_pro_enabled', '0');
        $this->SetError($this->Lang('license_verification_failed'));
    }
    
    $this->RedirectToAdminTab();
    return;
}

if (isset($params['remove_license'])) {
    set_site_preference('twofactor_license_key', '');
    set_site_preference('twofactor_pro_enabled', '0');
    set_site_preference('twofactor_license_verified', '');
    $this->SetMessage($this->Lang('license_removed'));
    $this->RedirectToAdminTab();
    return;
}

$license_key = get_site_preference('twofactor_license_key', '');
$pro_enabled = get_site_preference('twofactor_pro_enabled', '0');

$smarty->assign('license_key', $license_key);
$smarty->assign('pro_enabled', $pro_enabled);

echo $this->ProcessTemplate('premium.tpl');
