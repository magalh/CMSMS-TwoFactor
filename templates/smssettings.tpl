<h3>{$mod->Lang('twilio_settings')}</h3>

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
        <input type="submit" name="{$actionid}submit" value="{$mod->Lang('save_settings')}" />
    </p>
</div>

{form_end}
