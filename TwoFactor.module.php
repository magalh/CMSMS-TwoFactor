<?php
# See LICENSE for full license information.

class TwoFactor extends CMSModule
{
    const MANAGE_PERM = 'manage_twofactor';
    const USE_PERM = 'use_twofactor';
    const MANAGE_SMS_PERM = 'manage_twofactor_sms';
    const MANAGE_PRO_PERM = 'manage_twofactor_pro';
    const PRODUCT_URL = 'https://pixelsolutions.biz/plugins/twofactor/';

    public function GetVersion() { return '3.0.0'; }
    public function MinimumCMSVersion() {return '2.2.1';}
    public function GetFriendlyName() { return $this->Lang('friendlyname'); }
    public function GetAdminDescription() { return $this->Lang('admindescription'); }
    public function IsPluginModule() { return TRUE; }
    public function HasAdmin() { return TRUE; }
    public function VisibleToAdminUser() { return FALSE; }
    public function GetAuthor() { return 'Pixel Solutions'; }
    public function GetAuthorEmail() { return 'info@pixelsolutions.biz'; }
    public function GetAdminSection() { return 'siteadmin'; }
    public function GetDependencies() { return ['CMSMSExt' => '1.5.2']; }

    public function __construct()
    {
        $autoload_file = cms_join_path($this->GetModulePath(), 'vendor', 'autoload.php');
        if (file_exists($autoload_file)) {
            require_once $autoload_file;
        }
        
        spl_autoload_register([$this, '_autoloader']);
        parent::__construct();
        $smarty = cmsms()->GetSmarty();
        if(!$smarty){return;}

        $smarty->registerClass('tf_smarty', 'tf_smarty');
        $plugins_dir = cms_join_path( $this->GetModulePath(), 'lib', 'plugins' );
        $smarty->addPluginsDir($plugins_dir);
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

    public function InitializeFrontend()
    { 
        $this->SetParameterType(CLEAN_REGEXP . '/subaction.*/', CLEAN_STRING);
        $this->RegisterRoute('/[Tt]wofactor\/verify$/', ['action' => 'default']);
        $this->RegisterRoute('/[Tt]wofactor\/verify\/(?P<subaction>.*)$/', ['action' => 'default']);
    }

    private static $new_login_flow = null;

    public static function hasNewLoginFlow()
    {
        // If we've already determined the flow, return the cached value
        if (self::$new_login_flow !== null) {
            return self::$new_login_flow;
        }
        // Check if we're in new flow by looking for the session variable
        return isset($_SESSION['cms_pending_auth_userid']);
    }

    public function InitializeAdmin()
    {
        TwoFactorCore::register_providers();
        self::$new_login_flow = true;
        \CMSMS\HookManager::add_hook('Core::LoginVerified', function($params) {
            error_log('TwoFactor: LoginVerified hook fired for user ' . (isset($params['user']) ? $params['user']->username : 'unknown'));
            $this->InterceptLoginNew($params);
        });
        
    }

    public function GetHeaderHTML()
    {
        $module_path = $this->GetModuleURLPath();
        $header_links = '<link rel="stylesheet" type="text/css" href="'.$module_path.'/assets/twofactor_admin.css">';
        // see if custom.css file exists
        $customCSSfile = 'assets/module_custom/TwoFactor/twofactor_admin.css';
        if ( file_exists(CMS_ROOT_PATH.'/'.$customCSSfile) ) {
            $header_links .= '<link rel="stylesheet" type="text/css" href="../'.$customCSSfile.'">';
        }
        $header_links .= '<script language="javascript" src="'.$module_path.'/assets/twofactor_admin.js"></script>';
        return $header_links;
    }

    public function RegisterEvents()
    {
        $this->AddEventHandler('Core', 'LoginPost', false);
        \Events::CreateEvent($this->GetName(), 'BeforeVerification');
        \Events::CreateEvent($this->GetName(), 'AfterVerificationSuccess');
        \Events::CreateEvent($this->GetName(), 'AfterVerificationFail');
    }

    function DoEvent($originator, $eventname, &$params)
    {
        if ($originator !== 'Core' || $eventname !== 'LoginPost') {
            return;
        }

        if (class_exists('\CMSMS\LoginOperations') &&
            method_exists(\CMSMS\LoginOperations::class, 'initialize_authentication')) {
            return;
        }

        error_log('TwoFactor: LoginPost hook fired for user ' . (isset($params['user']) ? $params['user']->username : 'unknown'));
        $this->InterceptLoginLegacy($params);
    }

    // New core: LoginVerified fires BEFORE finalization, session has cms_pending_auth_userid
    private function InterceptLoginNew($params)
    {
        if (!isset($params['user'])) return;

        error_log('TwoFactor: InterceptLoginNew - user is set');
        $config = cms_utils::get_config();
        if (isset($config['twofactor_bypass']) && $config['twofactor_bypass'] == 1) {
            error_log('TwoFactor: InterceptLoginNew - 2FA bypass enabled');
            return;
        }

        $uid = $params['user']->id;
        error_log('TwoFactor: InterceptLoginNew - checking if user ' . $uid . ' uses 2FA');
        if (!TwoFactorCore::is_user_using_two_factor($uid)) {
            error_log('TwoFactor: InterceptLoginNew - user ' . $uid . ' does not use 2FA');
            return;
        }

        error_log('TwoFactor: InterceptLoginNew - user ' . $uid . ' uses 2FA, blocking login and redirecting to verify');
        // Clear old flow session variable
        unset($_SESSION['twofactor_user_id']);
        
        // Ensure effective user is set (same as auth user if not impersonating)
        if (empty($_SESSION['cms_pending_effective_userid'])) {
            $_SESSION['cms_pending_effective_userid'] = $uid;
            error_log('TwoFactor: Set cms_pending_effective_userid to ' . $uid);
        }
        
        $_SESSION['twofactor_rememberme'] = isset($_POST['loginremember']) ? 1 : 0;
        $redirect_url = $config['root_url'] . '/twofactor/verify';
        error_log('TwoFactor: InterceptLoginNew - redirecting to ' . $redirect_url);
        
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        error_log('TwoFactor: About to send Location header to ' . $redirect_url);
        session_write_close();
        header('Location: ' . $redirect_url, true, 302);
        error_log('TwoFactor: Location header sent, throwing exception to stop core processing');
        throw new \RuntimeException('TwoFactor: Redirect in progress');
    }

    // Old core: LoginPost fires AFTER full auth, must deauthenticate and redirect to frontend route
    private function InterceptLoginLegacy($params)
    {
        if (!isset($params['user'])) return;

        $config = cms_utils::get_config();
        if (isset($config['twofactor_bypass']) && $config['twofactor_bypass'] == 1) return;

        $uid = $params['user']->id;
        if (!TwoFactorCore::is_user_using_two_factor($uid)) return;

        $login_ops = \CMSMS\LoginOperations::get_instance();
        $login_ops->deauthenticate();

        $_SESSION['twofactor_user_id'] = $uid;
        $_SESSION['twofactor_rememberme'] = isset($_POST['loginremember']) ? 1 : 0;

        $url = $config['root_url'] . '/twofactor/verify';
        redirect($url);
        exit;
    }

    public function GetHelp() {
        $mods = \ModuleOperations::get_instance()->GetInstalledModules();
        $have_2fpro = in_array('TwoFactorPro', $mods);
        
        $smarty = \cms_utils::get_smarty();
        $smarty->assign('have_2fpro', $have_2fpro);
        
        $tpl_file = cms_join_path($this->GetModulePath(), 'templates', 'help.tpl');
        if (file_exists($tpl_file)) {
            $tpl = $smarty->CreateTemplate($this->GetTemplateResource('help.tpl'));
            return $tpl->fetch();
        }
        
        return '';
    }

    public function GetChangeLog() {
        $base_dir = realpath(__DIR__);
        $file = realpath(__DIR__.'/CHANGELOG.md');
        if (!$file || !$base_dir || !is_file($file) || !is_readable($file)) return '';
        if (strpos($file, $base_dir) !== 0) return '';
        if (basename($file) !== 'CHANGELOG.md') return '';
        $markdown = @file_get_contents($file);
        if (!$markdown) return '';
        return tf_smarty::mdToHTML($markdown);
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
        
        if ($this->CheckPermission(self::MANAGE_PERM) || $this->CheckPermission(self::MANAGE_SMS_PERM)) {
            $obj = new CmsAdminMenuItem();
            $obj->module = $this->GetName();
            $obj->section = 'siteadmin';
            $obj->title = 'TwoFactor Settings';
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

    public function UninstallPreMessage()
    {
        return $this->Lang('ask_uninstall');
    }

    public static function IsProInstalled()
    {
        $pro = cms_utils::get_module('TwoFactorPro');
        return $pro !== false && is_object($pro);
    }

    public static function IsProActive()
    {
        static $result = null;
        if ($result !== null) return $result;

        $result = false;

        $pro = cms_utils::get_module('TwoFactorPro');
        if (!$pro) return false;
        
        if (!method_exists($pro, 'IsProEnabled')) return false;
        
        $enabled = $pro->IsProEnabled();
        
        if (!$enabled) return false;
        
        $hash = self::_verify_pro_integrity();
        if (!$hash) return false;
        
        $result = true;
        return true;
    }
    
    private static function _verify_pro_integrity()
    {
        $pro = cms_utils::get_module('TwoFactorPro');
        if (!$pro) return false;
        
        $pro_path = $pro->GetModulePath();
        $core_files = [
            'TwoFactorPro.module.php',
            'lib/class.TwoFactorRateLimiter.php',
            'lib/class.TwoFactorTrustedDevice.php',
            'lib/class.TwoFactorWebAuthnPro.php'
        ];
        
        $hash_data = '';
        foreach ($core_files as $file) {
            $path = cms_join_path($pro_path, $file);
            if (!file_exists($path)) return false;
            $hash_data .= md5_file($path);
        }
        
        $license_key = $pro->GetPreference('twofactorpro_license_key', '');
        if (empty($license_key)) return false;
        
        $hash_data .= $license_key;
        
        return hash('sha256', $hash_data);
    }




}
