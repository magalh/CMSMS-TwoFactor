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
    
    // Call API to validate license
    $config = cms_utils::get_config();
    $site_url = $config['root_url'];
    $api_url = 'https://pixelsolutions.local/api/validate-license?key=' . urlencode($license_key) . '&url=' . urlencode($site_url);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local dev
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 && $response) {
        $data = json_decode($response, true);
        
        if (isset($data['valid']) && $data['valid'] === true) {
            set_site_preference('twofactor_license_key', $license_key);
            set_site_preference('twofactor_pro_enabled', '1');
            set_site_preference('twofactor_license_verified', time());
            $this->SetMessage($this->Lang('license_activated'));
        } else {
            set_site_preference('twofactor_license_key', $license_key);
            set_site_preference('twofactor_pro_enabled', '0');
            set_site_preference('twofactor_license_verified', time());
            $this->SetError($this->Lang('license_invalid'));
        }
    } else {
        set_site_preference('twofactor_pro_enabled', '0');
        $this->SetError($this->Lang('license_verification_failed'));
    }
    
    //$this->RedirectToAdminTab('premium');
    $this->RedirectToAdminTab();
    return;
}

$license_key = get_site_preference('twofactor_license_key', '');
$pro_enabled = get_site_preference('twofactor_pro_enabled', '0');

$smarty->assign('license_key', $license_key);
$smarty->assign('pro_enabled', $pro_enabled);

echo $this->ProcessTemplate('premium.tpl');
