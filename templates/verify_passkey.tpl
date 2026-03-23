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
	</head>
	<body id="login">
		<div id="wrapper">
			<div class="login-container">
				<div class="login-box cf"{if isset($error) && $error != ''} id="error"{/if}>
					<div class="logo">
						<img src="{$config.admin_url}/themes/OneEleven/images/layout/cmsms_login_logo.png" width="180" height="36" alt="CMS Made Simple&trade;" />
					</div>
					<header>
						<h1>{$mod->Lang('passkey_verification_title')}</h1>
					</header>

					{form_start action='twofactor' module='TwoFactor' showtemplate='false'}
						{xt_form_csrf}
						<fieldset>
							<input type="hidden" name="{$actionid}webauthn_response" id="webauthn_response" value="" />
							<div id="passkey-prompt" style="text-align: center; padding: 20px 0;">
								<p>{$mod->Lang('passkey_touch_prompt')}</p>
								<div id="passkey-spinner" style="font-size: 2em;">🔐</div>
								<p id="passkey-status" style="color: #666; font-size: 13px;"></p>
							</div>
							{if $is_pro_active}
							<div style="margin: 10px 0;">
								<label style="font-weight: normal; font-size: 13px;">
									<input type="checkbox" name="{$actionid}trust_device" value="1" /> {$mod->Lang('remember_device')}
								</label>
							</div>
							{/if}
							<input class="loginsubmit" name="{$actionid}submit" id="passkey-submit" type="submit" value="Verify" style="display:none;" />
						</fieldset>
					{form_end}

					{if isset($error) && $error != ''}
						<div class="message error" id="error-message">
							{$error}
						</div>
						<p style="text-align: center;">
							<button type="button" id="btn-retry-passkey" class="loginsubmit">{$mod->Lang('passkey_retry')}</button>
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

			function doAuthentication() {
				var statusEl = document.getElementById('passkey-status');
				statusEl.textContent = 'Waiting for authenticator...';

				var opts = Object.assign({}, webauthnOptions);
				opts.challenge = base64UrlToBuffer(opts.challenge);
				if (opts.allowCredentials) {
					opts.allowCredentials.forEach(function(c) {
						c.id = base64UrlToBuffer(c.id);
					});
				}

				navigator.credentials.get({ publicKey: opts })
					.then(function(assertion) {
						var response = {
							clientDataJSON: bufferToBase64Url(assertion.response.clientDataJSON),
							authenticatorData: bufferToBase64Url(assertion.response.authenticatorData),
							signature: bufferToBase64Url(assertion.response.signature)
						};

						document.getElementById('webauthn_response').value = JSON.stringify(response);
						document.getElementById('passkey-submit').click();
					})
					.catch(function(err) {
						statusEl.textContent = 'Authentication cancelled or failed: ' + err.message;
						console.error('Passkey auth error:', err);
					});
			}

			// Auto-trigger on page load
			if (window.PublicKeyCredential) {
				setTimeout(doAuthentication, 500);
			} else {
				document.getElementById('passkey-status').textContent = 'Passkeys are not supported in this browser.';
			}

			// Retry button
			var retryBtn = document.getElementById('btn-retry-passkey');
			if (retryBtn) {
				retryBtn.addEventListener('click', doAuthentication);
			}
		})();
		{/literal}
		</script>
	</body>
</html>
