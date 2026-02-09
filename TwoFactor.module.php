<?php
# See doc/LICENSE.txt for full license information.
class TwoFactor extends CMSModule
{
    const MANAGE_PERM = 'manage_twofactor';
    const USE_PERM = 'use_twofactor';

    public function GetVersion() { return '1.2.1'; }
    public function MinimumCMSVersion() {return '2.1.6';}
    public function GetFriendlyName() { return $this->Lang('friendlyname'); }
    public function GetAdminDescription() { return $this->Lang('admindescription'); }
    public function IsPluginModule() { return FALSE; }
    public function HasAdmin() { return TRUE; }
    public function VisibleToAdminUser() { return FALSE; }
    public function GetAuthor() { return 'Magal Hezi'; }
    public function GetAuthorEmail() { return 'magal@pixelsolutions.biz'; }
    public function GetAdminSection() { return 'siteadmin'; }

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

    public static function page_type_lang_callback($str)
    {
        $mod = cms_utils::get_module('TwoFactor');
        if (is_object($mod)) return $mod->Lang('type_'.$str);
    }

    public static function reset_page_type_defaults(CmsLayoutTemplateType $type)
    {
        $mod = cms_utils::get_module('TwoFactor');
        if ($type->get_originator() != $mod->GetName()) throw new CmsLogicException('Cannot reset contents for this template type');

        if ($type->get_name() == 'email_verification') {
            $fn = __DIR__.'/templates/orig_email_verification.tpl';
            if (file_exists($fn)) return @file_get_contents($fn);
        }
    }

    public function GetAdminMenuItems()
    {
        $out = [];
        
        if ($this->CheckPermission(self::MANAGE_PERM)) {
            $obj = new CmsAdminMenuItem();
            $obj->module = $this->GetName();
            $obj->section = 'siteadmin';
            $obj->title = 'Settings - TwoFactor';
            $obj->action = 'defaultadmin';
            $obj->url = $this->create_url('m1_', $obj->action);
            $out[] = $obj;
        }
        
        if ($this->CheckPermission(self::USE_PERM)) {
            $obj = new CmsAdminMenuItem();
            $obj->module = $this->GetName();
            $obj->section = 'myprefs';
            $obj->title = 'TwoFactor';
            $obj->action = 'user_prefs';
            $obj->url = $this->create_url('m1_', $obj->action);
            $out[] = $obj;
        }
        
        return $out;
    }

    /**
     * Check if Pro features are enabled with license validation
     * @param bool $revalidate Force API revalidation (default: checks cache)
     * @return bool
     */
    public static function IsProEnabled($revalidate = false)
    {
        $license_key = get_site_preference('twofactor_license_key', '');
        
        if (empty($license_key)) {
            return false;
        }
        
        // Check cache (valid for 24 hours)
        $cache_time = get_site_preference('twofactor_license_verified', 0);
        $cache_valid = (time() - $cache_time) < 86400;
        
        if (!$revalidate && $cache_valid) {
            return get_site_preference('twofactor_pro_enabled', '0') == '1';
        }
        
        // Revalidate with API
        $api_url = 'https://pixelsolutions.local/api/validate-license?key=' . urlencode($license_key);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 && $response) {
            $data = json_decode($response, true);
            $is_valid = isset($data['valid']) && $data['valid'] === true;
            
            set_site_preference('twofactor_pro_enabled', $is_valid ? '1' : '0');
            set_site_preference('twofactor_license_verified', time());
            
            return $is_valid;
        }
        
        // On API failure, keep existing status if recently verified
        if ($cache_valid) {
            return get_site_preference('twofactor_pro_enabled', '0') == '1';
        }
        
        return false;
    }


}
