<?php
# See doc/LICENSE.txt for full license information.
class TwoFactor extends CMSModule
{
    const MANAGE_PERM = 'manage_twofactor';

    public function GetVersion() { return '1.0.0'; }
    public function MinimumCMSVersion() {
        return '2.1.6';
    }
    public function GetFriendlyName() { return $this->Lang('friendlyname'); }
    public function GetAdminDescription() { return $this->Lang('admindescription'); }
    public function IsPluginModule() { return FALSE; }
    public function HasAdmin() { return TRUE; }
    public function VisibleToAdminUser() { return $this->CheckPermission(self::MANAGE_PERM); }
    public function GetAuthor() { return 'Magal Hezi'; }
    public function GetAuthorEmail() { return 'magal@pixelsolutions.biz'; }

    public function __construct()
    {
        $autoload_file = cms_join_path($this->GetModulePath(), 'vendor', 'autoload.php');
        if (file_exists($autoload_file)) {
            require_once $autoload_file;
        }
        
        spl_autoload_register([$this, '_autoloader']);
        parent::__construct();
    }

    private function _autoloader($classname)
    {
        $parts = explode('\\', $classname);
        $classname = end($parts);
        
        $fn = cms_join_path(
            $this->GetModulePath(),
            'lib',
            'class.' . $classname . '.php'
        );
        
        if (file_exists($fn)) {
            require_once($fn);
        }
    }

    public function InitializeAdmin()
    {
        TwoFactorCore::register_providers();
    }

    public function RegisterEvents()
    {
        $this->AddEventHandler('Core', 'LoginPost', false);
    }

    function DoEvent($originator, $eventname, &$params)
    {
        if ($originator == 'Core' && $eventname == 'LoginPost') {
            $this->InterceptLogin($params);
        }
    }

    private function InterceptLogin($params)
    {
        if (!isset($params['user'])) return;
        
        $uid = $params['user']->id;

        if (!TwoFactorCore::is_user_using_two_factor($uid)) {
            return;
        }
        
        // Store user info
        $_SESSION['twofactor_user_id'] = $uid;
        $_SESSION['twofactor_rememberme'] = isset($_POST['loginremember']) ? 1 : 0;
        
        // Get the session key that was just created
        $key = $_SESSION[CMS_USER_KEY] ?? '';
        
        // Redirect to 2FA verification page
        $config = cms_utils::get_config();
        $url = $config['admin_url'] . '/twofactor.php';
        error_log('TwoFactor InterceptLogin: Redirecting to ' . $url);
        redirect($url);
        exit;
    }

    public function GetHelp() {
        $base_dir = realpath(__DIR__);
        $file = realpath(__DIR__.'/README.md');
        if (!$file || !$base_dir || !is_file($file) || !is_readable($file)) return '';
        if (strpos($file, $base_dir) !== 0) return '';
        if (basename($file) !== 'README.md') return '';
        return @file_get_contents($file);
    }

    public function GetChangeLog() {
        $base_dir = realpath(__DIR__);
        $file = realpath(__DIR__.'/doc/changelog.inc');
        if (!$file || !$base_dir || !is_file($file) || !is_readable($file)) return '';
        if (strpos($file, $base_dir) !== 0) return '';
        if (basename($file) !== 'changelog.inc') return '';
        return @file_get_contents($file);
    }


}
