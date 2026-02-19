<?php
if( !defined('CMS_VERSION') ) exit;

$db = $this->GetDb();
$oldver = $oldversion;
$newver = $this->GetVersion();

if( version_compare($oldver, '2.0.0') < 0 ) {
    $this->CreatePermission(TwoFactor::USE_PERM, 'Use TwoFactor');
    $this->CreatePermission(TwoFactor::MANAGE_SMS_PERM, 'Manage TwoFactor SMS');

    $this->RegisterEvents();

    $old_table = cms_db_prefix() . 'mod_twofactor_usermeta';
    $new_table = cms_db_prefix() . 'module_twofactor_usermeta';
    
    $query = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?";
    $old_exists = $db->GetOne($query, [$old_table]);
    $new_exists = $db->GetOne($query, [$new_table]);
    
    if( $old_exists && !$new_exists ) {
        $db->Execute("RENAME TABLE $old_table TO $new_table");
    }

    $uid = get_userid();

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
}

// Always update twofactor.php file on upgrade
$config = cms_config::get_instance();
$source = cms_join_path($this->GetModulePath(), 'admin_files', 'orig.twofactor.php');
$dest = cms_join_path(CMS_ROOT_PATH, $config['admin_dir'], 'twofactor.php');
if (file_exists($source)) {
    copy($source, $dest);
}

include_once(dirname(__FILE__) . '/lib/class.ModuleTracker.php');
ModuleTracker::track('TwoFactor', 'upgrade');
