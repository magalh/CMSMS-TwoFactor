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
						<h1>Backup Code</h1>
					</header>
					<p>Enter one of your backup codes.</p>
					{form_start showtemplate="false"}
						{xt_form_csrf}
						<fieldset>
							<label for="authcode">Backup Code</label>
							<input id="authcode" class="focus" placeholder="xxxxxxxx" name="authcode" type="text" size="15" value="" autocomplete="off" autofocus{if isset($locked_seconds) && $locked_seconds !== false} disabled{/if} />
							{if $is_pro_active}
							<div style="margin: 10px 0;">
								<label style="font-weight: normal; font-size: 13px;">
									<input type="checkbox" name="trust_device" value="1"{if isset($locked_seconds) && $locked_seconds !== false} disabled{/if} /> Remember this device for 30 days
								</label>
							</div>
							{/if}
							<input class="loginsubmit" name="submit" type="submit" value="Verify"{if isset($locked_seconds) && $locked_seconds !== false} disabled{/if} />
						</fieldset>
						{if isset($error) && $error != ''}}
						<div class="message error" id="error-message">
							{$error}
						</div>
					{/if}
					{form_end}
					
					{if isset($locked_seconds) && $locked_seconds !== false}
						<script>
						var countdown = {$locked_seconds};
						var timer = setInterval(function() {
							countdown--;
							if (countdown <= 0) {
								window.location.reload();
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
					<p class="forgotpw">
						<a href="{cms_action_url module="TwoFactor" action="twofactor" showtemplate="false" provider=""}">Back to primary method</a>
					</p>
				</div>
				<footer>
					<small class="copyright">Copyright &copy; <a rel="external" href="http://www.cmsmadesimple.org">CMS Made Simple&trade;</a></small>
				</footer>
			</div>
		</div>
	</body>
</html>
