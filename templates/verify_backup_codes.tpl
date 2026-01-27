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
						<h1>Backup Code</h1>
					</header>
					<p>Enter one of your backup codes.</p>
					<form method="post" action="twofactor.php">
						<fieldset>
							<label for="authcode">Backup Code</label>
							<input id="authcode" class="focus" placeholder="xxxxxxxx" name="authcode" type="text" size="15" value="" autocomplete="off" autofocus />
							<input class="loginsubmit" name="submit" type="submit" value="Verify" />
						</fieldset>
					</form>
					{if isset($error) && $error != ''}
						<div class="message error">
							{$error}
						</div>
					{/if}
					<p class="forgotpw">
						<a href="twofactor.php?provider=">Back to primary method</a>
					</p>
				</div>
				<footer>
					<small class="copyright">Copyright &copy; <a rel="external" href="http://www.cmsmadesimple.org">CMS Made Simple&trade;</a></small>
				</footer>
			</div>
		</div>
	</body>
</html>
