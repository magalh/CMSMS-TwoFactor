<?php
# See LICENSE for full license information.
if( !defined('CMS_VERSION') ) exit;

$this->RemovePermission(TwoFactor::MANAGE_PERM);
$this->RemovePermission(TwoFactor::USE_PERM);
$this->RemovePermission(TwoFactor::MANAGE_SMS_PERM);

$this->RemovePreference('twofactor_sms_product_key');
$this->RemovePreference('twofactor_smscredit_enabled');
$this->RemovePreference('twofactor_sms_available');
$this->RemovePreference('twofactor_twilio_api_key');
$this->RemovePreference('twofactor_twilio_api_secret');
$this->RemovePreference('twofactor_twilio_service_sid');
$this->RemovePreference('twofactor_twilio_enabled');

$this->RemoveEventHandler('TwoFactor', 'BeforeVerification');
$this->RemoveEventHandler('TwoFactor', 'AfterVerificationSuccess');
$this->RemoveEventHandler('TwoFactor', 'AfterVerificationFail');

$db = $this->GetDb();
$dict = NewDataDictionary($db);

$sqlarray = $dict->DropTableSQL(CMS_DB_PREFIX.'module_twofactor_usermeta');
$dict->ExecuteSQLArray($sqlarray);

$config = cms_config::get_instance();
$twofactor_file = cms_join_path(CMS_ROOT_PATH, $config['admin_dir'], 'twofactor.php');
if (file_exists($twofactor_file)) {
    @unlink($twofactor_file);
}

include_once(dirname(__FILE__) . '/lib/class.ModuleTracker.php');
ModuleTracker::track('TwoFactor', 'uninstall');

try {
  $types = CmsLayoutTemplateType::load_all_by_originator($this->GetName());
  if( is_array($types) && count($types) ) {
    foreach( $types as $type ) {
      $templates = $type->get_template_list();
      if( is_array($templates) && count($templates) ) {
	foreach( $templates as $template ) {
	  $template->delete();
	}
      }
      $type->delete();
    }
  }
}
catch( Exception $e ) {
  // log it
  audit('',$this->GetName(),'Uninstall Error: '.$e->GetMessage());
}