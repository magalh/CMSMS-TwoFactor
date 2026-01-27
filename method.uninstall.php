<?php
# See doc/LICENSE.txt for full license information.
if( !defined('CMS_VERSION') ) exit;

$this->RemovePermission(TwoFactor::MANAGE_PERM);

$db = $this->GetDb();
$dict = NewDataDictionary($db);
$sqlarray = $dict->DropTableSQL(CMS_DB_PREFIX.'mod_twofactor_usermeta');
$dict->ExecuteSQLArray($sqlarray);

$this->RemoveEventHandler('Core', 'LoginPost');

// Remove twofactor.php from admin directory
$config = cms_config::get_instance();
$twofactor_file = cms_join_path(CMS_ROOT_PATH, $config['admin_dir'], 'twofactor.php');
if (file_exists($twofactor_file)) {
    @unlink($twofactor_file);
}