<?php
# See LICENSE for full license information.

class TwoFactor extends CMSModule
{
    const MANAGE_PERM = 'manage_twofactor';
    const USE_PERM = 'use_twofactor';
    const MANAGE_SMS_PERM = 'manage_twofactor_sms';
    const MANAGE_PRO_PERM = 'manage_twofactor_pro';
    const PRODUCT_URL = 'https://pixelsolutions.biz/plugins/twofactor/';

    public function GetName() { return 'TwoFactor'; }
    public function GetVersion() { return '4.0.0'; }
    public function MinimumCMSVersion() {return '2.2.23';}
    public function GetFriendlyName() { return $this->Lang('friendlyname'); }
    public function GetAdminDescription() { return $this->Lang('admindescription'); }
    public function IsPluginModule() { return TRUE; }
    public function HasAdmin() { return TRUE; }
    public function VisibleToAdminUser() {
        return $this->CheckPermission(self::MANAGE_PERM) || $this->CheckPermission(self::USE_PERM);
    }
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

        $smarty->registerClass('TwoFactorSmarty', 'TwoFactorSmarty');
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
        $this->SetParameterType('subaction', CLEAN_STRING);
        $this->SetParameterType('_', CLEAN_INT);
        $this->RegisterRoute('/[Tt]wofactor\/verify$/', ['action' => 'default']);
        $this->RegisterRoute('/[Tt]wofactor\/verify\/(?P<subaction>.*)$/', ['action' => 'default']);
    }

    /**
     * Generate the pretty URL path for the verification route.
     *
     * Called automatically by CMSMS whenever create_url() / {cms_action_url}
     * builds a URL for this module's "default" action on a frontend request.
     * Returning a path here makes the core emit the SEF form
     * (twofactor/verify[/<subaction>]) when url_rewriting is enabled, and the
     * standard mact URL when it is not. CMSMS handles the mod_rewrite vs
     * internal vs none difference for us.
     *
     * @param string $id       The module action id (e.g. cntnt01).
     * @param string $action   The action name.
     * @param string $returnid The page id the action renders on.
     * @param array  $params   Action parameters.
     * @param bool   $inline   Whether the action is rendered inline.
     * @return string|null The pretty path, or null to use the default URL.
     */
    public function get_pretty_url($id, $action, $returnid = '', $params = [], $inline = false)
    {
        if ($action !== 'default') return null;

        $url = 'twofactor/verify';
        if (!empty($params['subaction'])) {
            $url .= '/' . trim((string) $params['subaction'], '/');
        }
        return $url;
    }

    public function InitializeAdmin()
    {
        TwoFactorCore::register_providers();
        \CMSMS\HookManager::add_hook('Core::LoginVerified', function($params) {
            $this->InterceptLogin($params);
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
        $header_links .= '<script src="'.$module_path.'/assets/twofactor_admin.js"></script>';
        return $header_links;
    }

    public function RegisterEvents()
    {
        \Events::CreateEvent($this->GetName(), 'BeforeVerification');
        \Events::CreateEvent($this->GetName(), 'AfterVerificationSuccess');
        \Events::CreateEvent($this->GetName(), 'AfterVerificationFail');
    }

    // LoginVerified fires BEFORE finalization, session has cms_pending_auth_userid
    private function InterceptLogin($params)
    {
        if (!isset($params['user'])) return;

        $config = cms_utils::get_config();
        if (isset($config['twofactor_bypass']) && $config['twofactor_bypass'] == 1) return;

        $uid = $params['user']->id;
        if (!TwoFactorCore::is_user_using_two_factor($uid)) return;

        // Ensure effective user is set (same as auth user if not impersonating)
        if (empty($_SESSION['cms_pending_effective_userid'])) {
            $_SESSION['cms_pending_effective_userid'] = $uid;
        }
        
        $post_data = filter_input_array(INPUT_POST) ?: [];
        $_SESSION['twofactor_rememberme'] = !empty($post_data['loginremember']) ? 1 : 0;
        $redirect_url = $this->GetVerifyUrl();
        
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        session_write_close();
        header('Location: ' . $redirect_url, true, 302);
        exit;
    }

    /**
     * Build the absolute URL to the 2FA verification page.
     *
     * Prefers the native CMSMS create_url() (which calls get_pretty_url()
     * above and therefore honours the site's url_rewriting mode: SEF path,
     * index.php/... , or a mact query URL). Falls back to an explicit builder
     * for the admin login-hook context, where a frontend URL cannot be
     * generated reliably.
     *
     * @param string $subaction Optional route sub-action (e.g. 'resend',
     *                          'backup-codes', 'primary', or a method slug).
     * @param array  $extra     Extra query parameters to append (e.g. cache-buster).
     * @return string
     */
    public function GetVerifyUrl($subaction = '', array $extra = [])
    {
        $subaction = trim((string) $subaction, '/');
        $params = $extra;
        if ($subaction !== '') $params['subaction'] = $subaction;

        // Native path: create_url() -> get_pretty_url(), honouring url_rewriting.
        // Guard in try/catch because create_url() relies on frontend request
        // context that is not present during the admin login hook.
        try {
            $url = $this->create_url('cntnt01', 'default', '', $params);
            if (is_string($url) && $url !== ''
                && strpos($url, 'moduleinterface.php') === false
                && strpos($url, '/admin/') === false) {
                // Frontend URL only. create_url() escapes '&' as '&amp;' for
                // HTML output; undo it for a raw Location header / redirect.
                return str_replace('&amp;', '&', $url);
            }
        } catch (\Throwable $e) {
            // fall through to explicit builder
        }

        return $this->buildVerifyUrlFallback($subaction, $extra);
    }

    /**
     * Explicit url_rewriting-aware builder used when create_url() is not
     * available (admin login hook). Mirrors what get_pretty_url()/create_url()
     * would produce.
     */
    private function buildVerifyUrlFallback($subaction, array $extra)
    {
        $config = cms_utils::get_config();
        $root = defined('CMS_ROOT_URL') ? CMS_ROOT_URL : '';
        if ($root === '' && !empty($config['root_url'])) {
            $root = $config['root_url'];
        }
        $root = rtrim((string) $root, '/');
        $mode = isset($config['url_rewriting']) ? $config['url_rewriting'] : 'none';
        $subaction = trim((string) $subaction, '/');

        if ($mode === 'mod_rewrite' || $mode === 'internal') {
            $prefix = ($mode === 'internal') ? '/index.php' : '';
            $url = $root . $prefix . '/twofactor/verify';
            if ($subaction !== '') $url .= '/' . rawurlencode($subaction);
            if ($extra) $url .= '?' . http_build_query($extra);
            return $url;
        }

        // No pretty URLs: canonical frontend module action. Commas in the mact
        // value must NOT be url-encoded or CMSMS will fail to parse it.
        // Frontend action params must be prefixed with the module action id
        // (cntnt01) so CMSMS extracts them into $params; a bare "subaction"
        // is ignored.
        $qs = [];
        if ($subaction !== '') $qs['cntnt01subaction'] = $subaction;
        $qs = array_merge($qs, $extra);
        $url = $root . '/index.php?mact=TwoFactor,cntnt01,default,0';
        if ($qs) $url .= '&' . http_build_query($qs);
        return $url;
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
        $file = realpath(__DIR__.'/doc/CHANGELOG.md');
        if (!$file || !$base_dir || !is_file($file) || !is_readable($file)) return '';
        if (strpos($file, $base_dir) !== 0) return '';
        $markdown = file_get_contents($file);
        if (!$markdown) return '';
        return TwoFactorSmarty::mdToHTML($markdown);
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
