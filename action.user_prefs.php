<?php
# See doc/LICENSE.txt for full license information.
if( !defined('CMS_VERSION') ) exit;
if( !$this->CheckPermission(TwoFactor::USE_PERM) ) return;

// Check if 2FA is being enforced
$enforce_mode = isset($params['enforce']) && $params['enforce'] == '1';
if ($enforce_mode) {
    echo '<div class="warning" style="margin: 20px 0; padding: 15px; background: #fff3cd; border: 1px solid #ffc107;">';
    echo '<h3 style="margin-top: 0;">' . $this->Lang('2fa_required') . '</h3>';
    echo '<p>' . $this->Lang('2fa_required_message') . '</p>';
    echo '</div>';
}

include(__DIR__ . '/function.user_methods.php');
