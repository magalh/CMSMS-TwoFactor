<h3>{$mod->Lang('sms_settings')}</h3>

{if $sms_available}
<div class="information" style="margin-bottom: 20px;">
    <strong>✓ {$mod->Lang('sms_status_available')}</strong>
    {if $smscredit_enabled && $twilio_enabled}
        - {$mod->Lang('sms_both_methods_active')} ({$mod->Lang('sms_credits_priority')})
    {elseif $smscredit_enabled}
        - {$mod->Lang('sms_credits_method_active')}
    {elseif $twilio_enabled}
        - {$mod->Lang('sms_twilio_method_active')}
    {/if}
</div>
{else}
<div class="warning" style="margin-bottom: 20px;">
    <strong>{$mod->Lang('sms_status_unavailable')}</strong> - {$mod->Lang('sms_configure_method')}
</div>
{/if}

<fieldset style="margin-bottom: 30px;">
    <legend>{$mod->Lang('sms_credits_option')} {if $smscredit_enabled}<span style="color: #28a745;">✓ {$mod->Lang('active')}</span>{/if}</legend>
    <p style="margin-bottom: 5px;">{$mod->Lang('sms_credits_description')}</p>
    {form_start}
    <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('product_key')} {if $smscredit_enabled}{$mod->Lang('sms_credits_active', $credits_remaining)}{/if}:</p>
        <p class="pageinput">
            <input type="text" name="{$actionid}product_key" value="{$product_key}" size="50" placeholder="XXXX-XXXX-XXXX-XXXX" />
            <br/><span class="help">{$mod->Lang('product_key_help')}</span>
        </p>
    </div>
    
    <div class="pageoverflow">
        <p class="pageinput">
            <input type="submit" name="{$actionid}submit_credits" value="{$mod->Lang('save_product_key')}" class="pagebutton" />
            {if $product_key}
            <input type="submit" name="{$actionid}remove_credits" value="{$mod->Lang('remove_license')}" class="pagebutton" onclick="return confirm('{$mod->Lang('confirm_remove_license')}');" />
            {/if}
            
            <a href="{TwoFactor::PRODUCT_URL}" target="_blank" class="cta" data-icon="ui-icon-star" style="margin-left: 10px;">{$mod->Lang('purchase_credits')}</a>
        </p>
    </div>
    {form_end}
</fieldset>

<fieldset>
    <legend>{$mod->Lang('twilio_api_option')} {if $twilio_enabled}<span style="color: #28a745;">✓ {$mod->Lang('active')}</span>{/if}</legend>
    <p style="margin-bottom: 15px;">{$mod->Lang('twilio_api_description')}</p>
    
    {form_start}
    <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('twilio_api_key_sid')}:</p>
        <p class="pageinput">
            <input type="text" name="{$actionid}api_key" value="{$api_key}" size="50" />
        </p>
    </div>
    
    <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('twilio_api_secret')}:</p>
        <p class="pageinput">
            <input type="text" name="{$actionid}api_secret" value="{$api_secret}" size="50" />
        </p>
    </div>
    
    <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('twilio_service_sid')}:</p>
        <p class="pageinput">
            <input type="text" name="{$actionid}service_sid" value="{$service_sid}" size="50" />
            <br/><span class="help">{$mod->Lang('twilio_service_sid_help')}</span>
        </p>
    </div>
    
    <div class="pageoverflow">
        <p class="pagetext">&nbsp;</p>
        <p class="pageinput">
            <input type="submit" name="{$actionid}submit_twilio" value="{$mod->Lang('save_settings')}" class="pagebutton" />
            {if $twilio_enabled}
            <input type="submit" name="{$actionid}remove_twilio" value="{$mod->Lang('remove_twilio')}" class="pagebutton" onclick="return confirm('{$mod->Lang('confirm_remove_twilio')}');" />
            {/if}
        </p>
    </div>
    {form_end}
</fieldset>
