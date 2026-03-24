<?php
# See LICENSE for full license information.
if (!defined('CMS_VERSION')) exit;
if (!$this->CheckPermission(TwoFactor::USE_PERM)) return;

// The Security Keys tab in user_prefs handles registration/deletion.
// This action just redirects there.
$this->RedirectToAdminTab('security_keys', '', 'user_prefs');
