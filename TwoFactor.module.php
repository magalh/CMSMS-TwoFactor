<?php
# See LICENSE for full license information.

class TwoFactor extends CMSModule
{
    const MANAGE_PERM = 'manage_twofactor';
    const USE_PERM = 'use_twofactor';
    const MANAGE_SMS_PERM = 'manage_twofactor_sms';
    const MANAGE_PRO_PERM = 'manage_twofactor_pro';
    const PRODUCT_URL = 'https://pixelsolutions.biz/en/plugins/twofactor/';

    public function GetVersion() { return '2.0.0'; }
    public function MinimumCMSVersion() {return '2.2.1';}
    public function GetFriendlyName() { return $this->Lang('friendlyname'); }
    public function GetAdminDescription() { return $this->Lang('admindescription'); }
    public function IsPluginModule() { return FALSE; }
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

    public function InitializeAdmin()
    {
        TwoFactorCore::register_providers();
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
        if ($originator == 'Core' && $eventname == 'LoginPost') {
            $this->InterceptLogin($params);
        }
    }

    private function InterceptLogin($params)
    {
        if (!isset($params['user'])) return;
        
        $uid = $params['user']->id;
        $config = cms_utils::get_config();
        
        // Check if user has 2FA enabled
        $has_2fa = TwoFactorCore::is_user_using_two_factor($uid);
        
        if ($has_2fa) {
            // User has 2FA, proceed with verification
            $_SESSION['twofactor_user_id'] = $uid;
            $_SESSION['twofactor_rememberme'] = isset($_POST['loginremember']) ? 1 : 0;
            
            $url = $config['admin_url'] . '/twofactor.php';
            redirect($url);
            exit;
        }
        
        // Let login complete normally
        return;
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

    public static function IsProInstalled()
    {
        $pro = cms_utils::get_module('TwoFactorPro');
        return $pro !== false && is_object($pro);
    }

    public static function IsProActive()
    {
        $pro = cms_utils::get_module('TwoFactorPro');
        if (!$pro) return false;
        
        if (!method_exists($pro, 'IsProEnabled')) return false;
        
        $enabled = $pro->IsProEnabled();
        
        if (!$enabled) return false;
        
        $hash = self::_verify_pro_integrity();
        if (!$hash) return false;
        
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
            'lib/class.TwoFactorTrustedDevice.php'
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
