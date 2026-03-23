<h3>For Administrators</h3>

<h4>1. Configure SMS Settings (Optional)</h4>
<p>Go to <strong>TwoFactor Settings > SMS Settings</strong> tab:</p>

<p><strong>Option A: SMS Credits from Pixel Solutions</strong></p>
<ol>
  <li>Purchase SMS credits from <a href="https://pixelsolutions.biz/en/plugins/twofactor/" target="_blank">pixelsolutions.biz</a></li>
  <li>Enter your product key</li>
  <li>Click "Save Product Key"</li>
</ol>

<p><strong>Option B: Use Your Own Twilio Account</strong></p>
<ol>
  <li>Create a Twilio account and Verify Service</li>
  <li>Generate API Key and Secret in Twilio Console</li>
  <li>Enter credentials (API Key SID, API Secret, Verify Service SID)</li>
  <li>Click "Save Twilio Settings"</li>
</ol>

<h3>For Users</h3>

<h4>Enable Two-Factor Authentication</h4>
<p>Go to <strong>My Preferences > TwoFactor</strong>:</p>

<h5>TOTP (Authenticator App)</h5>
<ol>
  <li>Click "Configure" for Authenticator App</li>
  <li>Scan the QR code with your authenticator app (Google Authenticator, Authy, etc.)</li>
  <li>Enter the 6-digit code to verify</li>
  <li>Select as primary method and save</li>
</ol>

<h5>Email Verification</h5>
<ol>
  <li>Click "Configure" for Email Verification</li>
  <li>Click "Enable Email Verification"</li>
  <li>Select as primary method and save</li>
</ol>

<h5>SMS Verification</h5>
<ol>
  <li>Click "Configure" for SMS Verification</li>
  <li>Enter phone number in E.164 format (e.g., +1234567890)</li>
  <li>Verify the code sent to your phone</li>
  <li>Select as primary method and save</li>
</ol>

<h5>Backup Codes</h5>
<ol>
  <li>Click "Configure" for Backup Codes</li>
  <li>Click "Generate Backup Codes"</li>
  <li>Save codes in a secure location (each code works only once)</li>
</ol>

<h5>Passkey (Biometrics / Device)</h5>
<div class="information" style="padding: 10px; background: #e8f4f8; border-left: 4px solid #2196F3; margin: 15px 0;">
  <p><strong>ℹ️ Requires HTTPS</strong> — Passkeys use the WebAuthn standard which only works over secure connections (HTTPS or localhost).</p>
</div>
<ol>
  <li>Click "Configure" for Passkey</li>
  <li>Click "Register Passkey"</li>
  <li>Your browser will prompt you to use your device biometrics (fingerprint, face recognition) or device PIN</li>
  <li>Once registered, select Passkey as your primary method and save</li>
</ol>
<p><strong>How it works:</strong> When logging in, instead of entering a code, your browser will automatically prompt you to verify using your device's biometric sensor or PIN. This is the most convenient and secure 2FA method available.</p>
<p><strong>Supported authenticators:</strong> Touch ID (macOS), Windows Hello (Windows), Face ID / Touch ID (iOS/iPadOS), Android biometrics</p>

{if $have_2fpro}
<h5>Security Keys &amp; Multiple Passkeys (Pro)</h5>
<p>With TwoFactor Pro, you can also:</p>
<ul>
  <li>Register <strong>multiple passkeys</strong> (e.g., laptop + phone)</li>
  <li>Register <strong>physical security keys</strong> (YubiKey, Google Titan Key, Feitian, etc.)</li>
  <li>Manage all your keys from the <strong>Security Keys</strong> tab in My Preferences</li>
  <li>Name each key for easy identification</li>
</ul>
<p>Go to <strong>My Preferences > TwoFactor > Security Keys</strong> tab to register additional keys.</p>
{/if}

<h3>Login Flow</h3>
<ol>
  <li>Enter username and password as normal</li>
  <li>After successful authentication, you'll be redirected to 2FA verification</li>
  <li>Depending on your primary method:
    <ul>
      <li><strong>TOTP / Email / SMS:</strong> Enter the verification code</li>
      <li><strong>Passkey:</strong> Your browser will automatically prompt for biometric verification or security key touch</li>
    </ul>
  </li>
  <li>Click "Use a backup code" if needed to switch methods</li>
</ol>

<h3>Emergency Bypass</h3>
<div class="warning" style="padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107; margin: 15px 0;">
  <p><strong>⚠️ For Emergency Use Only</strong></p>
  <p>If you are locked out and need to bypass 2FA temporarily, add this to your <code>config.php</code> file:</p>
  <pre style="background: #f5f5f5; padding: 10px; margin: 10px 0;">$config['twofactor_bypass'] = true;</pre>
  <p><strong>Important:</strong> Remove this setting immediately after regaining access. Leaving it enabled disables all 2FA security.</p>
</div>
