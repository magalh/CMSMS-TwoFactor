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
						<h1>{$mod->Lang('sms_verification_title')}</h1>
					</header>
					<p>{$mod->Lang('sms_verification_sent')}</p>
					{form_start action='twofactor' module='TwoFactor' showtemplate='false'}
						{xt_form_csrf}
						<fieldset>
							<label for="authcode">{$mod->Lang('verification_code_label')}</label>
							<input id="authcode" class="focus" placeholder="123456" name="{$actionid}authcode" type="text" inputmode="numeric" pattern="[0-9]*" size="15" value="" autocomplete="off" autofocus{if isset($locked_seconds) && $locked_seconds !== false} disabled{/if} />
							{if $is_pro_active}
							<div style="margin: 10px 0;">
								<label style="font-weight: normal; font-size: 13px;">
									<input type="checkbox" name="{$actionid}trust_device" value="1"{if isset($locked_seconds) && $locked_seconds !== false} disabled{/if} /> {$mod->Lang('remember_device')}
								</label>
							</div>
							{/if}
							<input class="loginsubmit" name="{$actionid}submit" type="submit" value="Verify"{if isset($locked_seconds) && $locked_seconds !== false} disabled{/if} />
						</fieldset>
					{form_end}
					{if isset($error) && $error != ''}
						<div class="message error" id="error-message">
							{$error}
						</div>
					{/if}
					{if isset($locked_seconds) && $locked_seconds !== false}
						<script>
						{literal}
						var countdown = {/literal}{$locked_seconds}{literal};
						var msgSeconds = '{/literal}{$mod->Lang('account_locked_seconds')}{literal}';
						var msgMinutes = '{/literal}{$mod->Lang('account_locked_minutes')}{literal}';
						var timer = setInterval(function() {
							countdown--;
							if (countdown <= 0) {
								window.location.reload();
							} else {
								var minutes = Math.ceil(countdown / 60);
								var msg;
								if (countdown < 60) {
									msg = msgSeconds.replace('%d', countdown).replace('%s', countdown > 1 ? 's' : '');
								} else {
									msg = msgMinutes.replace('%d', minutes).replace('%s', minutes > 1 ? 's' : '');
								}
								document.getElementById('error-message').innerHTML = msg;
							}
						}, 1000);
						{/literal}
						</script>
					{/if}
					{if !isset($locked_seconds) || $locked_seconds === false}
						<p class="forgotpw">
							<a href="{root_url}/twofactor/verify/resend&_={$smarty.now}">{$mod->Lang('resend_verification_code')}</a> &nbsp;
						</p>
					{/if}
					{if $has_backup_codes && !$using_backup && (!isset($locked_seconds) || $locked_seconds === false)}
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
	</body>
</html>
