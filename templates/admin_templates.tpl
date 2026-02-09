<h3>{$mod->Lang('tab_templates')}</h3>

{if $is_pro}
{form_start}
  
  <fieldset>
    <legend>{$mod->Lang('password_reset_email')}</legend>
    
    <div class="pageoverflow">
      <p class="pagetext">{$mod->Lang('reset_email_subject')}:</p>
      <p class="pageinput">
        <input type="text" name="{$actionid}reset_email_subject" value="{$reset_email_subject|escape}" size="60" />
      </p>
    </div>
    
    <div class="pageoverflow">
      <p class="pagetext">{$mod->Lang('reset_email_body')}:</p>
      <p class="pageinput">
        {cms_textarea prefix=$actionid name='reset_email_body' value=$reset_email_body enablewysiwyg=true rows=10}
        <br/><small>{$mod->Lang('reset_email_placeholders')}</small>
      </p>
    </div>
  </fieldset>
  
  <fieldset style="margin-top:20px;">
    <legend>{$mod->Lang('admin_alert_email')}</legend>
    
    <div class="pageoverflow">
      <p class="pagetext">{$mod->Lang('alert_email_subject')}:</p>
      <p class="pageinput">
        <input type="text" name="{$actionid}alert_email_subject" value="{$alert_email_subject|escape}" size="60" />
      </p>
    </div>
    
    <div class="pageoverflow">
      <p class="pagetext">{$mod->Lang('alert_email_body')}:</p>
      <p class="pageinput">
        {cms_textarea prefix=$actionid name='alert_email_body' value=$alert_email_body enablewysiwyg=true rows=10}
        <br/><small>{$mod->Lang('alert_email_placeholders')}</small>
      </p>
    </div>
  </fieldset>
  
  <div class="pageoverflow" style="margin-top:20px;">
    <p class="pagetext"></p>
    <p class="pageinput">
      <input type="submit" name="{$actionid}save_templates" value="{$mod->Lang('save_settings')}" class="pagebutton" />
    </p>
  </div>
{form_end}
{else}
<div class="warning">
  <p>{$mod->Lang('pro_required_settings')}</p>
</div>
{/if}
