<?php
# See doc/LICENSE.txt for full license information.
if( !defined('CMS_VERSION') ) exit;

$this->CreatePermission(TwoFactor::MANAGE_PERM, 'Manage TwoFactor');

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