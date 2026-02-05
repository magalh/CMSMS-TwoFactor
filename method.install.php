<?php
# See doc/LICENSE.txt for full license information.
if( !defined('CMS_VERSION') ) exit;

$this->CreatePermission(TwoFactor::MANAGE_PERM, 'Manage TwoFactor');
$this->CreatePermission(TwoFactor::USE_PERM, 'Use TwoFactor');

$db = $this->GetDb();
$dict = NewDataDictionary($db);

// User meta table for storing provider-specific data
$flds = "
    id I KEY AUTO,
    user_id I NOTNULL,
    meta_key C(255) NOTNULL,
    meta_value X
";
$sqlarray = $dict->CreateTableSQL(CMS_DB_PREFIX.'mod_twofactor_usermeta', $flds);
$dict->ExecuteSQLArray($sqlarray);

// Create index for faster lookups
$db->Execute('CREATE INDEX idx_user_key ON '.CMS_DB_PREFIX.'mod_twofactor_usermeta (user_id, meta_key)');

// Register event handlers
\Events::CreateEvent('Core', 'LoginPost');
$this->RegisterEvents();

// Copy twofactor.php to admin directory
$config = cms_config::get_instance();
$source = cms_join_path($this->GetModulePath(), 'admin_files', 'orig.twofactor.php');
$dest = cms_join_path(CMS_ROOT_PATH, $config['admin_dir'], 'twofactor.php');
if (file_exists($source)) {
    copy($source, $dest);
}

// Track installation
include_once(dirname(__FILE__) . '/lib/class.ModuleTracker.php');
ModuleTracker::track('TwoFactor', 'install');

// Create email verification template type
$email_type = new CmsLayoutTemplateType();
$email_type->set_originator($this->GetName());
$email_type->set_name('email_verification');
$email_type->set_dflt_flag(TRUE);
$email_type->set_lang_callback('TwoFactor::page_type_lang_callback');
$email_type->set_content_callback('TwoFactor::reset_page_type_defaults');
$email_type->reset_content_to_factory();
$email_type->save();

$tpl = new CmsLayoutTemplate();
$tpl->set_name($tpl::generate_unique_name('TwoFactor Email Verification'));
$tpl->set_owner(1);
$tpl->set_type($email_type);
$tpl->set_content($email_type->get_dflt_contents());
$tpl->set_type_dflt(TRUE);
$tpl->save();