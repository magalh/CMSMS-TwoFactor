<h3>Hooks</h3>
<p>This module uses Hooks to tie in external functionality. The following hooks are available:</p>
<ul>
    <li><code>TwoFactor::BeforeVerification</code>
        <p>Sent before verification code is checked.</p>
        <p>Parameters (as an associative array)</p>
        <ul>
            <li><code>user_id</code> : int - The user id attempting verification</li>
            <li><code>provider</code> : string - Provider key</li>
            <li><code>code</code> : string - The verification code entered</li>
        </ul>
    </li>
    <li><code>TwoFactor::AfterVerificationSuccess</code>
        <p>Sent after successful verification.</p>
        <p>Parameters (as an associative array)</p>
        <ul>
            <li><code>user_id</code> : int - The user id who verified successfully</li>
            <li><code>provider</code> : string - Provider key used for verification</li>
        </ul>
    </li>
    <li><code>TwoFactor::AfterVerificationFail</code>
        <p>Sent after failed verification attempt.</p>
        <p>Parameters (as an associative array)</p>
        <ul>
            <li><code>user_id</code> : int - The user id who failed verification</li>
            <li><code>provider</code> : string - Provider key attempted</li>
            <li><code>code</code> : string - The incorrect code entered</li>
        </ul>
    </li>
</ul>

<h3>Permissions</h3>
<ul>
    <li><strong>Manage TwoFactor:</strong> Access to TwoFactor settings and configuration</li>
    <li><strong>Use TwoFactor:</strong> Access to TwoFactor user preferences (enable/configure 2FA methods)</li>
    <li><strong>Manage TwoFactor SMS:</strong> Access to SMS settings configuration</li>
    <li><strong>Manage TwoFactor Pro:</strong> Access to TwoFactor Pro settings (requires Pro module)</li>
</ul>

<h3>Available Providers</h3>
<ul>
    <li><code>TwoFactorProviderTOTP</code> &mdash; Authenticator app (Google Authenticator, Authy, etc.)</li>
    <li><code>TwoFactorProviderEmail</code> &mdash; Email verification codes</li>
    <li><code>TwoFactorProviderSMS</code> &mdash; SMS verification codes (Twilio / Managed Credits)</li>
    <li><code>TwoFactorProviderPasskey</code> &mdash; Passkeys and security keys (WebAuthn/FIDO2)</li>
    <li><code>TwoFactorProviderBackupCodes</code> &mdash; One-time backup codes</li>
</ul>

<h3>WebAuthn / Passkey Notes</h3>
<p>The Passkey provider uses the WebAuthn (FIDO2) standard. Key technical details:</p>
<ul>
    <li><strong>HTTPS required:</strong> WebAuthn only works over secure connections (HTTPS or localhost)</li>
    <li><strong>Browser support:</strong> All modern browsers support WebAuthn (Chrome, Firefox, Safari, Edge)</li>
    <li><strong>Relying Party ID:</strong> Automatically set to your site's domain from <code>root_url</code> in config</li>
    <li><strong>Attestation:</strong> Set to "none" (no attestation verification) for maximum compatibility</li>
    <li><strong>Free tier:</strong> Platform authenticators only, single credential per user (stored in usermeta)</li>
    <li><strong>Pro tier:</strong> All authenticator types, multiple credentials per user (stored in dedicated DB table)</li>
    <li>Authentication tries the base credential first, then all Pro credentials</li>
</ul>
