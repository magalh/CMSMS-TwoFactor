<?php
# See LICENSE for full license information.
if( !defined('CMS_VERSION') ) exit;
$this->SetCurrentTab('verify_logs');

if (!$this->CheckPermission(TwoFactor::MANAGE_SMS_PERM)) return;

$product_key = $this->GetPreference('twofactor_sms_product_key', '');
$page = isset($params['page']) ? (int)$params['page'] : 1;
$per_page = 25;

$logs = [];
$error = '';
$total_logs = 0;
$total_pages = 0;

if (!empty($product_key)) {
    $result = TwoFactorAPI::get_verification_logs($product_key);

    if ($result && isset($result['success']) && $result['success'] === true) {
        $all_logs = isset($result['logs']) ? $result['logs'] : [];
        
        foreach ($all_logs as &$log) {
            if (isset($log['timestamp']) && $log['timestamp'] > 9999999999) {
                $log['timestamp'] = floor($log['timestamp'] / 1000);
            }
        }
        
        $total_logs = count($all_logs);
        $total_pages = ceil($total_logs / $per_page);
        $offset = ($page - 1) * $per_page;
        $logs = array_slice($all_logs, $offset, $per_page);
    } else {
        $error = isset($result['error']) ? $result['error'] : 'Failed to fetch logs';
    }
}

$smarty = $this->GetActionTemplateObject();
$tpl = $smarty->CreateTemplate($this->GetTemplateResource('admin_verify_logs.tpl'));
$tpl->assign('logs', $logs);
$tpl->assign('error', $error);
$tpl->assign('page', $page);
$tpl->assign('total_pages', $total_pages);
$tpl->assign('total_logs', $total_logs);
$tpl->assign('actionid', $this->GetActionId());
$tpl->display();
