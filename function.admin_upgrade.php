<?php
if( !defined('CMS_VERSION') ) exit;
$this->SetCurrentTab('upgrade');

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('admin_upgrade.tpl'));
$tpl->assign('product_url', TwoFactor::PRODUCT_URL);
$tpl->display();
