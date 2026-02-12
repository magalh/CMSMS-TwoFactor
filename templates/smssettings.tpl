<h3>{$mod->Lang('sms_settings')}</h3>

<fieldset style="margin-bottom: 30px;">
    <legend>{$mod->Lang('sms_credits_option')}</legend>
    <p style="margin-bottom: 15px;">{$mod->Lang('sms_credits_description')}</p>
{if $smscredit_enabled}
    <div class="information">
        <p>{$mod->Lang('sms_credits_active', $credits_remaining)}</p>
    </div>
{/if}
    {form_start}
    <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('product_key')}:</p>
        <p class="pageinput">
            <input type="text" name="{$actionid}product_key" value="{$product_key}" size="50" placeholder="XXXX-XXXX-XXXX-XXXX" />
            <br/><span class="help">{$mod->Lang('product_key_help')}</span>
        </p>
    </div>
    
    <div class="pageoverflow">
        <p class="pageinput">
            <input type="submit" name="{$actionid}submit_credits" value="{$mod->Lang('save_product_key')}" class="pagebutton" />
            <a href="{TwoFactor::PRODUCT_URL}" target="_blank" class="pagebutton" style="margin-left: 10px;">{$mod->Lang('purchase_credits')}</a>
        </p>
    </div>
    {form_end}
</fieldset>

<fieldset>
    <legend>{$mod->Lang('twilio_api_option')}</legend>
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
        </p>
    </div>
    {form_end}
</fieldset>
