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

<h3>Login Flow</h3>
<ol>
  <li>Enter username and password as normal</li>
  <li>After successful authentication, you'll be redirected to 2FA verification</li>
  <li>Enter the code from your primary authentication method</li>
  <li>Click "Use a backup code" if needed to switch methods</li>
</ol>

<h3>Emergency Bypass</h3>
<div class="warning" style="padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107; margin: 15px 0;">
  <p><strong>⚠️ For Emergency Use Only</strong></p>
  <p>If you are locked out and need to bypass 2FA temporarily, add this to your <code>config.php</code> file:</p>
  <pre style="background: #f5f5f5; padding: 10px; margin: 10px 0;">$config['twofactor_bypass'] = true;</pre>
  <p><strong>Important:</strong> Remove this setting immediately after regaining access. Leaving it enabled disables all 2FA security.</p>
</div>
