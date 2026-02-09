<style>
.twofactor_feat_table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.twofactor_feat_table__header td {
    background: #f8f9fa;
    padding: 20px;
    text-align: center;
    font-weight: bold;
    border-bottom: 2px solid #dee2e6;
}
.twofactor_feat_table tr {
    border-bottom: 1px solid #dee2e6;
}
.twofactor_feat_table td {
    padding: 15px;
    vertical-align: middle;
}
.twofactor_feat_table td:first-child {
    width: 50%;
}
.twofactor_feat_table td:nth-child(2),
.twofactor_feat_table td:nth-child(3) {
    width: 25%;
    text-align: center;
}
.twofactor_feat_table h4 {
    margin: 10px 0 5px 0;
    color: #333;
}
.twofactor_feat_table p {
    margin: 5px 0;
    color: #666;
    font-size: 13px;
}
.twofactor-yes {
    color: #28a745;
    font-size: 24px;
}
.twofactor-no {
    color: #dc3545;
    font-size: 24px;
}
.installed {
    display: inline-block;
    padding: 8px 15px;
    background: #d4edda;
    color: #155724;
    border-radius: 4px;
    font-weight: bold;
}
</style>

<table class="twofactor_feat_table">
    <tbody>
    <tr class="twofactor_feat_table__header">
        <td></td>
        <td>
            <div style="font-size: 18px; margin-bottom: 5px;">🔓</div>
            Free
        </td>
        <td>
            <div style="font-size: 18px; margin-bottom: 5px;">🔐</div>
            Premium
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            {if $pro_enabled == '1'}
                <span class="installed">✓ Active</span>
            {else}
                <span style="color: #6c757d;">Current Plan</span>
            {/if}
        </td>
        <td>
            {if $pro_enabled == '1'}
                <span class="installed">✓ Active</span>
            {else}
                <a href="https://pixelsolutions.biz/en/plugins/twofactor/" target="_blank" class="pagebutton" style="display: inline-block;">Upgrade Now</a>
            {/if}
        </td>
    </tr>
    <tr>
        <td>
            <h4>Basic 2FA Methods</h4>
            <p>TOTP (Google Authenticator), Email verification, SMS via Twilio, Backup codes</p>
        </td>
        <td><span class="twofactor-yes">✓</span></td>
        <td><span class="twofactor-yes">✓</span></td>
    </tr>
    <tr>
        <td>
            <h4>Enforce 2FA for All Users</h4>
            <p>Mandatory 2FA for all admin users, redirects to setup page until enabled</p>
        </td>
        <td><span class="twofactor-no">✗</span></td>
        <td><span class="twofactor-yes">✓</span></td>
    </tr>
    <tr>
        <td>
            <h4>Rate Limiting & Brute Force Protection</h4>
            <p>Exponential backoff, IP blacklist, automatic lockout after failed attempts</p>
        </td>
        <td><span class="twofactor-no">✗</span></td>
        <td><span class="twofactor-yes">✓</span></td>
    </tr>
    <tr>
        <td>
            <h4>Trusted Devices</h4>
            <p>Remember devices for 30 days with device fingerprinting and IP tracking</p>
        </td>
        <td><span class="twofactor-no">✗</span></td>
        <td><span class="twofactor-yes">✓</span></td>
    </tr>
    <tr>
        <td>
            <h4>Email Notifications</h4>
            <p>Auto-send password reset and admin alerts after failed attempts with customizable templates</p>
        </td>
        <td><span class="twofactor-no">✗</span></td>
        <td><span class="twofactor-yes">✓</span></td>
    </tr>
    <tr>
        <td>
            <h4>User Management Dashboard</h4>
            <p>View all users' 2FA status, failed attempts, trusted devices, disable 2FA for locked-out users</p>
        </td>
        <td><span class="twofactor-no">✗</span></td>
        <td><span class="twofactor-yes">✓</span></td>
    </tr>
    <tr>
        <td>
            <h4>Granular Permissions</h4>
            <p>6 permission levels for fine-grained access control to module features</p>
        </td>
        <td><span class="twofactor-no">✗</span></td>
        <td><span class="twofactor-yes">✓</span></td>
    </tr>
    <tr>
        <td>
            <h4>Priority Support</h4>
            <p>Fast, personal support from the developers whenever you need it</p>
        </td>
        <td><span class="twofactor-no">✗</span></td>
        <td><span class="twofactor-yes">✓</span></td>
    </tr>
    <tr>
        <td></td>
        <td>
            {if $pro_enabled == '1'}
                <span class="installed">✓ Active</span>
            {else}
                <span style="color: #6c757d;">Current Plan</span>
            {/if}
        </td>
        <td>
            {if $pro_enabled == '1'}
                <span class="installed">✓ Active</span>
            {else}
                <a href="https://pixelsolutions.biz/en/plugins/twofactor/" target="_blank" class="pagebutton" style="display: inline-block;">Upgrade Now</a>
            {/if}
        </td>
    </tr>
    </tbody>
</table>
