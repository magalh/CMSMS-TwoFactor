<h3>Template Customization</h3>

<p>TwoFactor creates email templates that you can customize in <strong>Layout > Design Manager</strong>.</p>

<h3>Email Templates</h3>

<h4>Email Verification</h4>
<p><strong>Template Type:</strong> TwoFactor::Email Verification</p>
<p>Customize the email design sent to users when they request an email verification code.</p>
<p>Find this template in <strong>Layout > Design Manager</strong> under the "TwoFactor::Email Verification" type.</p>

<h3>CSS Customization</h3>
<p>You can override the default admin styles by creating a custom CSS file at:</p>
<pre style="background: #f5f5f5; padding: 10px; margin: 10px 0;">assets/module_custom/TwoFactor/twofactor_admin.css</pre>
<p>This file will be loaded after the default styles, allowing you to customize the appearance of the 2FA admin interface and verification pages.</p>

<h3>Verification Page Templates</h3>
<p>The following verification page templates are used during login:</p>
<ul>
  <li><code>verify_totp.tpl</code> &mdash; TOTP code entry page</li>
  <li><code>verify_email.tpl</code> &mdash; Email code entry page</li>
  <li><code>verify_sms.tpl</code> &mdash; SMS code entry page</li>
  <li><code>verify_passkey.tpl</code> &mdash; Passkey/WebAuthn authentication page (auto-triggers browser prompt)</li>
  <li><code>verify_backup_codes.tpl</code> &mdash; Backup code entry page</li>
</ul>

{if $have_2fpro}
<h3>TwoFactor Pro Email Templates</h3>

<p>TwoFactor Pro adds additional customizable email templates. Configure these in <strong>TwoFactor Settings > Templates</strong> tab:</p>

<h4>Password Reset Email</h4>
<p>Sent when a user is forced to reset their password after excessive failed attempts.</p>
<p><strong>Available placeholders:</strong> <code>{literal}{username}, {reset_url}{/literal}</code></p>

<h4>Admin Alert Email</h4>
<p>Sent to administrators when suspicious activity is detected.</p>
<p><strong>Available placeholders:</strong> <code>{literal}{username}, {ip_address}, {attempts}, {time}{/literal}</code></p>
{/if}
