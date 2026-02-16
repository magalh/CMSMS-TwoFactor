<?php
if( !defined('CMS_VERSION') ) exit;
$this->SetCurrentTab('upgrade');

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('admin_upgrade.tpl'));
$tpl->display();
