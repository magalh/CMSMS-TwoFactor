<?php
if( !defined('CMS_VERSION') ) exit;

$this->CreatePermission(TwoFactor::MANAGE_PERM, 'Manage TwoFactor');
$this->CreatePermission(TwoFactor::USE_PERM, 'Use TwoFactor');
$this->CreatePermission(TwoFactor::MANAGE_SMS_PERM, 'Manage TwoFactor SMS');

$pro = cms_utils::get_module('TwoFactorPro');
if ($pro) {
    $this->CreatePermission(TwoFactor::MANAGE_PRO_PERM, 'Manage TwoFactor Pro');
}

$uid = null;
if( cmsms()->test_state(CmsApp::STATE_INSTALL) ) {
  $uid = 1; // hardcode to first user
} else {
  $uid = get_userid();
}

$db = $this->GetDb();
$dict = NewDataDictionary($db);

$flds = "
    id I KEY AUTO,
    user_id I NOTNULL,
    meta_key C(255) NOTNULL,
    meta_value X
";
$sqlarray = $dict->CreateTableSQL(CMS_DB_PREFIX.'mod_twofactor_usermeta', $flds);
$dict->ExecuteSQLArray($sqlarray);
$db->Execute('CREATE INDEX idx_user_key ON '.CMS_DB_PREFIX.'mod_twofactor_usermeta (user_id, meta_key)');

\Events::CreateEvent('Core', 'LoginPost');
$this->RegisterEvents();

$config = cms_config::get_instance();
$source = cms_join_path($this->GetModulePath(), 'admin_files', 'orig.twofactor.php');
$dest = cms_join_path(CMS_ROOT_PATH, $config['admin_dir'], 'twofactor.php');
if (file_exists($source)) {
    copy($source, $dest);
}

include_once(dirname(__FILE__) . '/lib/class.ModuleTracker.php');
ModuleTracker::track('TwoFactor', 'install');

$email_type = new CmsLayoutTemplateType();
$email_type->set_originator($this->GetName());
$email_type->set_name('email_verification');
$email_type->set_dflt_flag(TRUE);
$email_type->set_lang_callback('TwoFactor::page_type_lang_callback');
$email_type->set_content_callback('TwoFactor::reset_page_type_defaults');
$email_type->reset_content_to_factory();
$email_type->save();

$tpl = new CmsLayoutTemplate();
$tpl->set_name('Email Verification');
$tpl->set_owner($uid);
$tpl->set_type($email_type);
$tpl->set_content($email_type->get_dflt_contents());
$tpl->set_type_dflt(TRUE);
$tpl->save();