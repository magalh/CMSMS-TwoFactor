<h3>{$mod->Lang('setup_sms')}</h3>

{if $verify_result}
  {if $verify_success}
    <div class="pagemessage" style="background:#d4edda;color:#155724;padding:15px;margin:10px 0;border:1px solid #c3e6cb;">
      <strong>{$mod->Lang('verification_sent')}</strong>
    </div>
  {else}
    <div class="pagemessage" style="background:#f8d7da;color:#721c24;padding:15px;margin:10px 0;border:1px solid #f5c6cb;">
      <strong>{$error_message|default:$mod->Lang('verification_failed', 'Unknown error')}</strong>
    </div>
  {/if}
{/if}

<h4>{$mod->Lang('twilio_settings')}</h4>
<form method="post" action="{cms_action_url action='setup_sms'}">
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('twilio_api_key_sid')}:</p>
    <p class="pageinput">
      <input type="text" name="{$actionid}twilio_api_key_sid" value="{$twilio_api_key_sid}" size="50" />
    </p>
  </div>
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('twilio_api_secret')}:</p>
    <p class="pageinput">
      <input type="password" name="{$actionid}twilio_api_secret" value="{$twilio_api_secret}" size="50" />
    </p>
  </div>
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('twilio_service_sid')}:</p>
    <p class="pageinput">
      <input type="text" name="{$actionid}twilio_service_sid" value="{$twilio_service_sid}" size="50" />
      <br/><small>{$mod->Lang('twilio_service_sid_help')}</small>
    </p>
  </div>
  <p class="pageinput">
    <input type="submit" name="{$actionid}save_settings" value="{$mod->Lang('save_settings')}" />
  </p>
</form>

<hr/>

<h4>{$mod->Lang('sms_user_settings')}</h4>
{if $pending_phone}
  <p>{$mod->Lang('verification_code_sent', $pending_phone)}</p>
  <form method="post" action="{cms_action_url action='setup_sms'}">
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
  <p>{$mod->Lang('sms_enabled_info', $phone)}</p>
  <form method="post" action="{cms_action_url action='setup_sms'}">
    <input type="submit" name="{$actionid}disable" value="{$mod->Lang('disable_sms')}" />
    <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
  </form>
{else}
  <p>{$mod->Lang('sms_setup_info')}</p>
  <form method="post" action="{cms_action_url action='setup_sms'}">
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
