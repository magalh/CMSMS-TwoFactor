<!doctype html>
<html>
	<head>
		<meta charset="{$encoding}" />
		<title>Two-Factor Authentication - {sitename}</title>
		<base href="{$config.admin_url}/" />
		<meta name="robots" content="noindex, nofollow" />
		<meta name="viewport" content="initial-scale=1.0 maximum-scale=1.0 user-scalable=no" />
		<link rel="shortcut icon" href="{$config.admin_url}/themes/OneEleven/images/favicon/cmsms-favicon.ico"/>
		<link rel="stylesheet" href="{$config.admin_url}/loginstyle.php" />
		{cms_jquery}
		<style>
			.security-key-icon { display: block; margin: 0 auto 10px; }
			#security-key-prompt { text-align: center; padding: 15px 0; }
			#security-key-status { color: #666; font-size: 13px; min-height: 20px; }
			#security-key-status.status-error { color: #c00; }
			#btn-retry-security-key { margin-top: 10px; }
		</style>
	</head>
	<body id="login">
		<div id="wrapper">
			<div class="login-container">
				<div class="login-box cf"{if isset($error) && $error != ''} id="error"{/if}>
					<div class="logo">
						<img src="{$config.admin_url}/themes/OneEleven/images/layout/cmsms_login_logo.png" width="180" height="36" alt="CMS Made Simple&trade;" />
					</div>
					<header>
						<h1>{$mod->Lang('security_key_verification_title')}</h1>
					</header>

					{form_start action='twofactor' module='TwoFactor' showtemplate='false'}
						{xt_form_csrf}
						<fieldset>
							<input type="hidden" name="{$actionid}webauthn_response" id="webauthn_response" value="" />
							<div id="security-key-prompt">
								<img class="security-key-icon" src="{$mod_url}/assets/canonical-passkey-icon.svg" alt="Security Key" width="48" height="48" />
								<p>{$mod->Lang('security_key_touch_prompt')}</p>
								<p id="security-key-status"></p>
								<input type="button" id="btn-retry-security-key" class="loginsubmit" value="{$mod->Lang('passkey_retry')}" style="display:none;" />
							</div>
							{if $is_pro_active}
							<div style="margin: 10px 0;">
								<label style="font-weight: normal; font-size: 13px;">
									<input type="checkbox" name="{$actionid}trust_device" value="1" /> {$mod->Lang('remember_device')}
								</label>
							</div>
							{/if}
							<input class="loginsubmit" name="{$actionid}submit" id="security-key-submit" type="submit" value="Verify" style="display:none;" />
						</fieldset>
					{form_end}

					{if isset($error) && $error != ''}
						<div class="message error" id="error-message">
							{$error}
						</div>
					{/if}

					{if !empty($alt_methods)}
						<p class="forgotpw">
							{$mod->Lang('use_other_method')}:
							{foreach $alt_methods as $alt}
								<a href="{root_url}/twofactor/verify/{$alt.slug}&_={$smarty.now}">{$alt.label}</a>{if !$alt@last} | {/if}
							{/foreach}
						</p>
					{/if}
					{if $has_backup_codes && !$using_backup}
						<p class="forgotpw">
							<a href="{root_url}/twofactor/verify/backup-codes&_={$smarty.now}">{$mod->Lang('use_backup_code')}</a> &nbsp;
						</p>
					{/if}
				</div>
				<footer>
					<small class="copyright">Copyright &copy; <a rel="external" href="http://www.cmsmadesimple.org">CMS Made Simple&trade;</a></small>
				</footer>
			</div>
		</div>

		<script>
		{literal}
		(function() {
			var webauthnOptions = {/literal}{$webauthn_options_json}{literal};
			var actionId = '{/literal}{$actionid}{literal}';
			var statusEl = document.getElementById('security-key-status');
			var retryBtn = document.getElementById('btn-retry-security-key');

			function base64UrlToBuffer(base64url) {
				var base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
				while (base64.length % 4) base64 += '=';
				var binary = atob(base64);
				var bytes = new Uint8Array(binary.length);
				for (var i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
				return bytes.buffer;
			}

			function bufferToBase64Url(buffer) {
				var bytes = new Uint8Array(buffer);
				var binary = '';
				for (var i = 0; i < bytes.length; i++) binary += String.fromCharCode(bytes[i]);
				return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
			}

			function showError(msg) {
				statusEl.textContent = msg;
				statusEl.className = 'status-error';
				retryBtn.style.display = '';
			}

			function doAuthentication() {
				statusEl.textContent = '';
				statusEl.className = '';
				retryBtn.style.display = 'none';

				var opts = {
					challenge: base64UrlToBuffer(webauthnOptions.challenge),
					rpId: webauthnOptions.rpId,
					timeout: webauthnOptions.timeout,
					userVerification: webauthnOptions.userVerification
				};

				if (webauthnOptions.allowCredentials) {
					opts.allowCredentials = [];
					for (var i = 0; i < webauthnOptions.allowCredentials.length; i++) {
						var c = webauthnOptions.allowCredentials[i];
						opts.allowCredentials.push({
							type: c.type,
							id: base64UrlToBuffer(c.id),
							transports: c.transports || ['usb', 'nfc', 'ble']
						});
					}
				}

				navigator.credentials.get({ publicKey: opts })
					.then(function(assertion) {
						statusEl.textContent = '{/literal}{$mod->Lang('passkey_verifying')}{literal}';

						var response = {
							id: assertion.id,
							clientDataJSON: bufferToBase64Url(assertion.response.clientDataJSON),
							authenticatorData: bufferToBase64Url(assertion.response.authenticatorData),
							signature: bufferToBase64Url(assertion.response.signature)
						};

						document.getElementById('webauthn_response').value = JSON.stringify(response);
						document.getElementById('security-key-submit').click();
					}, function(err) {
						if (err.name === 'NotAllowedError') {
							showError('{/literal}{$mod->Lang('security_key_cancelled')}{literal}');
						} else {
							showError('{/literal}{$mod->Lang('security_key_failed')}{literal}');
						}
					});
			}

			if (window.PublicKeyCredential) {
				setTimeout(doAuthentication, 500);
			} else {
				showError('{/literal}{$mod->Lang('passkey_unsupported')}{literal}');
			}

			retryBtn.addEventListener('click', doAuthentication);
		})();
		{/literal}
		</script>
	</body>
</html>
