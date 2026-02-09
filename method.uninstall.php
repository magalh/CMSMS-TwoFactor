<?php
if( !defined('CMS_VERSION') ) exit;

$this->RemovePermission(TwoFactor::MANAGE_PERM);
$this->RemovePermission(TwoFactor::USE_PERM);
$this->RemovePermission(TwoFactor::VIEW_USERS_PERM);
$this->RemovePermission(TwoFactor::MANAGE_USERS_PERM);
$this->RemovePermission(TwoFactor::MANAGE_TEMPLATES_PERM);
$this->RemovePermission(TwoFactor::MANAGE_SMS_PERM);

$db = $this->GetDb();
$dict = NewDataDictionary($db);

$sqlarray = $dict->DropTableSQL(CMS_DB_PREFIX.'mod_twofactor_usermeta');
$dict->ExecuteSQLArray($sqlarray);

$sqlarray = $dict->DropTableSQL(CMS_DB_PREFIX.'mod_twofactor_failed_attempts');
$dict->ExecuteSQLArray($sqlarray);

$sqlarray = $dict->DropTableSQL(CMS_DB_PREFIX.'mod_twofactor_trusted_devices');
$dict->ExecuteSQLArray($sqlarray);

$this->RemoveEventHandler('Core', 'LoginPost');

$config = cms_config::get_instance();
$twofactor_file = cms_join_path(CMS_ROOT_PATH, $config['admin_dir'], 'twofactor.php');
if (file_exists($twofactor_file)) {
    @unlink($twofactor_file);
}

include_once(dirname(__FILE__) . '/lib/class.ModuleTracker.php');
ModuleTracker::track('TwoFactor', 'uninstall');

$type = CmsLayoutTemplateType::load($this->GetName(), 'email_verification');
if ($type) $type->delete();

remove_site_preference('twofactor_rate_limiting_enabled');
remove_site_preference('twofactor_max_attempts_lockout');
remove_site_preference('twofactor_max_attempts_reset');
remove_site_preference('twofactor_notify_admin');
remove_site_preference('twofactor_license_key');
remove_site_preference('twofactor_license_verified');
remove_site_preference('twofactor_pro_enabled');
remove_site_preference('twofactor_enforce_all');