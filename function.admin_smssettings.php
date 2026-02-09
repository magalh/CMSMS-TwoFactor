<?php
# See doc/LICENSE.txt for full license information.
if( !defined('CMS_VERSION') ) exit;
$this->SetCurrentTab('smssettings');

// Handle save
if (isset($params['submit'])) {
    set_site_preference('twofactor_twilio_api_key', trim($params['api_key']));
    set_site_preference('twofactor_twilio_api_secret', trim($params['api_secret']));
    set_site_preference('twofactor_twilio_service_sid', trim($params['service_sid']));
    
    $this->SetMessage($this->Lang('twilio_settings_saved'));
    $this->RedirectToAdminTab('smssettings');
    return;
}

$api_key = get_site_preference('twofactor_twilio_api_key', '');
$api_secret = get_site_preference('twofactor_twilio_api_secret', '');
$service_sid = get_site_preference('twofactor_twilio_service_sid', '');

$smarty->assign('api_key', $api_key);
$smarty->assign('api_secret', $api_secret);
$smarty->assign('service_sid', $service_sid);

echo $this->ProcessTemplate('smssettings.tpl');
