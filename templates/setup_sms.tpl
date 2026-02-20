<h3>{$mod->Lang('setup_sms')}</h3>

{if $message != ''}<div class="{$message.class}"><p class="pagemessage">{$message.text}</p></div>{/if}

{if !$twilio_configured}
  <div class="pageoverflow">
    <p class="warning">{$mod->Lang('twilio_not_configured')}</p>
  </div>
  
  <div class="pageoverflow">
    <p class="pageinput">
      <a href="{cms_action_url action='defaultadmin'}">{$mod->Lang('configure_twilio')}</a>
    </p>
  </div>
{else}
  {form_start action='setup_sms'}
    {if $pending_phone && !$is_enabled}
      <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('verification_code_sent', $pending_phone)}</p>
      </div>
      
      <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('verification_code')}:</p>
        <p class="pageinput">
          <input type="text" name="{$actionid}code" value="" size="10" placeholder="123456" autocomplete="off" />
        </p>
      </div>
      
      <div class="pageoverflow">
        <p class="pageinput">
          <input type="submit" name="{$actionid}verify_code" value="{$mod->Lang('verify_phone')}" />
          <input type="submit" name="{$actionid}resend_code" value="{$mod->Lang('resend_code')}" />
          <input type="submit" name="{$actionid}change_phone" value="{$mod->Lang('change_phone')}" />
          <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
        </p>
      </div>
      
    {elseif $is_enabled}
      <div class="pageoverflow">
        <p class="information"><strong>{$mod->Lang('verified')}</strong></p>
      </div>
      
      <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('sms_enabled_info', $phone)}</p>
      </div>
      
      <div class="pageoverflow">
        <p class="pageinput">
          <input type="submit" name="{$actionid}disable" value="{$mod->Lang('disable_sms')}" />
          <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
        </p>
      </div>
      
    {else}
      <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('sms_setup_info')}</p>
      </div>
      
      <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('phone_number')}:</p>
        <p class="pageinput">
          <input type="tel" name="{$actionid}phone" value="" size="20" placeholder="+1234567890" />
          <br/><small>{$mod->Lang('phone_format_help')}</small>
        </p>
      </div>
      
      <div class="pageoverflow">
        <p class="pageinput">
          <input type="submit" name="{$actionid}send_verification" value="{$mod->Lang('send_verification_code')}" />
          <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
        </p>
      </div>
    {/if}
  {form_end}
{/if}