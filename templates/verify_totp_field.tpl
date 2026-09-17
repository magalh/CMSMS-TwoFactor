{* TwoFactor: TOTP authentication input field, rendered from the provider's
   authentication_page() instead of echoing HTML directly. *}
<p class="pagetext">{$mod->Lang('enter_authenticator_code')}</p>
<p class="pageinput">
	<label for="authcode">{$mod->Lang('authentication_code')}:</label><br/>
	<input type="text" inputmode="numeric" name="authcode" id="authcode"
		class="input" value="" size="20" pattern="[0-9 ]*"
		placeholder="123 456" autocomplete="off" autofocus />
</p>
