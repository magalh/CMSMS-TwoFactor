<!doctype html>
<html>
	<head>
		<meta charset="{$encoding}" />
		<title>Two-Factor Authentication - {sitename}</title>
		<base href="{$config.admin_url}/" />
		<meta name="robots" content="noindex, nofollow" />
		<meta name="viewport" content="initial-scale=1.0 maximum-scale=1.0 user-scalable=no" />
		<link rel="shortcut icon" href="{$config.admin_url}/themes/OneEleven/images/favicon/cmsms-favicon.ico"/>
		<link rel="stylesheet" href="loginstyle.php" />
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
						<h1>SMS Verification</h1>
					</header>
					<p>A verification code has been sent to your phone.</p>
					<form method="post" action="twofactor.php">
						<fieldset>
							<label for="authcode">Verification Code</label>
							<input id="authcode" class="focus" placeholder="123456" name="authcode" type="text" inputmode="numeric" pattern="[0-9]*" size="15" value="" autocomplete="off" autofocus{if isset($locked_seconds) && $locked_seconds !== false} disabled{/if} />
							<div style="margin: 10px 0;">
								<label style="font-weight: normal; font-size: 13px;">
									<input type="checkbox" name="trust_device" value="1"{if isset($locked_seconds) && $locked_seconds !== false} disabled{/if} /> Remember this device for 30 days
								</label>
							</div>
							<input class="loginsubmit" name="submit" type="submit" value="Verify"{if isset($locked_seconds) && $locked_seconds !== false} disabled{/if} />
						</fieldset>
					</form>
					{if !isset($locked_seconds) || $locked_seconds === false}
						<p class="forgotpw">
							<a href="twofactor.php?resend=1">Resend verification code</a>
						</p>
					{/if}
					{if isset($error) && $error != ''}
						<div class="message error" id="error-message">
							{$error}
						</div>
					{/if}
					{if isset($locked_seconds) && $locked_seconds !== false}
						<script>
						var countdown = {$locked_seconds};
						var timer = setInterval(function() {
							countdown--;
							if (countdown <= 0) {
								window.location.href = 'twofactor.php';
							} else {
								var minutes = Math.ceil(countdown / 60);
								var seconds = countdown % 60;
								if (countdown < 60) {
									document.getElementById('error-message').innerHTML = 'Too many failed attempts. Please try again in ' + countdown + ' second' + (countdown > 1 ? 's' : '') + '.';
								} else {
									document.getElementById('error-message').innerHTML = 'Too many failed attempts. Please try again in ' + minutes + ' minute' + (minutes > 1 ? 's' : '') + '.';
								}
							}
						}, 1000);
						</script>
					{/if}
					{if $has_backup_codes && !$using_backup}
						<p class="forgotpw">
							<a href="twofactor.php?provider=TwoFactorProviderBackupCodes">Use a backup code</a>
						</p>
					{/if}
				</div>
				<footer>
					<small class="copyright">Copyright &copy; <a rel="external" href="http://www.cmsmadesimple.org">CMS Made Simple&trade;</a></small>
				</footer>
			</div>
		</div>
	</body>
</html>
