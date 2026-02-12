<h3>{$mod->Lang('setup_sms')}</h3>

{if $message != ''}<div class="{$message.class}"><p class="pagemessage">{$message.text}</p></div>{/if}

{if !$credits_configured}
  <div class="warning">
    <p>SMS credits not configured. Please contact your administrator.</p>
  </div>
  <p><a href="{cms_action_url action='defaultadmin' active_tab='smscredit'}">{$mod->Lang('configure_twilio')}</a></p>
{else}
  <h4>{$mod->Lang('sms_user_settings')}</h4>

    <div class="information">
        <p>{$mod->Lang('sms_credits_active', $credits_remaining)}</p>
    </div>

{if $pending_phone && !$is_enabled}
  <p>{$mod->Lang('verification_code_sent', $pending_phone)}</p>
  <form method="post" action="{cms_action_url action='setup_sms_credit_enabled'}">
    <div class="pageoverflow">
      <p class="pagetext">{$mod->Lang('verification_code')}:</p>
      <p class="pageinput">
        <input type="text" name="{$actionid}code" value="" size="10" placeholder="123456" autocomplete="off" />
      </p>
    </div>
    <p class="pageinput">
      <input type="submit" name="{$actionid}verify_code" value="{$mod->Lang('verify_phone')}" />
      <input type="submit" name="{$actionid}resend_code" value="{$mod->Lang('resend_code')}" />
      <input type="submit" name="{$actionid}change_phone" value="{$mod->Lang('change_phone')}" />
      <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
    </p>
  </form>
{elseif $is_enabled}
  <div class="information">
    <p><strong>{$mod->Lang('verified')}</strong></p>
  </div>
  <p>{$mod->Lang('sms_enabled_info', $phone)}</p>
  <form method="post" action="{cms_action_url action='setup_sms_credit_enabled'}">
    <input type="submit" name="{$actionid}disable" value="{$mod->Lang('disable_sms')}" />
    <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
  </form>
{else}
  <p>{$mod->Lang('sms_setup_info')}</p>
  <form method="post" action="{cms_action_url action='setup_sms_credit_enabled'}">
    <div class="pageoverflow">
      <p class="pagetext">{$mod->Lang('phone_number')}:</p>
      <p class="pageinput">
        <input type="tel" name="{$actionid}phone" value="" size="20" placeholder="+1234567890" />
        <br/><small>{$mod->Lang('phone_format_help')}</small>
      </p>
    </div>
    <p class="pageinput">
      <input type="submit" name="{$actionid}send_verification" value="{$mod->Lang('send_verification_code')}" />
      <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
    </p>
  </form>
{/if}
{/if}
