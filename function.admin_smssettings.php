<?php
if( !defined('CMS_VERSION') ) exit;
$this->SetCurrentTab('smssettings');

if (!$this->CheckPermission(TwoFactor::MANAGE_SMS_PERM)) return;

if (isset($params['submit_credits'])) {
    $product_key = trim($params['product_key']);
    
    if (empty($product_key)) {
        $this->SetError($this->Lang('error_empty_license'));
    } else {
        $config = cms_utils::get_config();
        $domain = $_SERVER['HTTP_HOST'];
        
        $result = TwoFactorAPI::validate_license($product_key, $domain);
        
        if ($result && isset($result['valid']) && $result['valid'] === true) {
            set_site_preference('twofactor_sms_product_key', $product_key);
            set_site_preference('twofactor_smscredit_enabled', '1');
            $this->SetMessage($this->Lang('sms_credits_saved'));
        } else {
            $error = isset($result['error']) ? $result['error'] : $this->Lang('error_invalid_license');
            $this->SetError($error);
        }
    }
    
    $this->RedirectToAdminTab();
    return;
}

if (isset($params['submit_twilio'])) {
    set_site_preference('twofactor_twilio_api_key', trim($params['api_key']));
    set_site_preference('twofactor_twilio_api_secret', trim($params['api_secret']));
    set_site_preference('twofactor_twilio_service_sid', trim($params['service_sid']));
    $this->SetMessage($this->Lang('twilio_settings_saved'));
    $this->RedirectToAdminTab();
    return;
}

$product_key = get_site_preference('twofactor_sms_product_key', '');
$smscredit_enabled = get_site_preference('twofactor_smscredit_enabled', 0);
$credits_remaining = 0;
$license_plan = '';

if (!empty($product_key)) {
    $domain = $_SERVER['HTTP_HOST'];
    $result = TwoFactorAPI::validate_license($product_key, $domain);
    
    if ($result && isset($result['valid']) && $result['valid'] === true) {
        $credits_remaining = isset($result['credits_remaining']) ? $result['credits_remaining'] : 0;
        $license_plan = isset($result['plan']) ? $result['plan'] : '';
    }
}

$api_key = get_site_preference('twofactor_twilio_api_key', '');
$api_secret = get_site_preference('twofactor_twilio_api_secret', '');
$service_sid = get_site_preference('twofactor_twilio_service_sid', '');

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('smssettings.tpl'), null, null, $smarty);
$tpl->assign('product_key', $product_key);
$tpl->assign('smscredit_enabled', $smscredit_enabled);
$tpl->assign('credits_remaining', $credits_remaining);
$tpl->assign('license_plan', $license_plan);
$tpl->assign('api_key', $api_key);
$tpl->assign('api_secret', $api_secret);
$tpl->assign('service_sid', $service_sid);
$tpl->display();
