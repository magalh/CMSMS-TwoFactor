<div class="pagecontainer">
    <h3>{$mod->Lang('premium_title')}</h3>
    
    {if $pro_enabled == '1'}
        <div class="pageinput" style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            <strong style="color: #155724;">✓ {$mod->Lang('pro_activated')}</strong>
            <p style="margin: 10px 0 0 0; color: #155724;">{$mod->Lang('pro_features_unlocked')}</p>
        </div>
    {/if}
    
    <form method="post" action="{$tab_url}">
        <div class="pageoverflow">
            <p class="pagetext">{$mod->Lang('license_key')}:</p>
            <p class="pageinput">
                <input type="text" name="{$actionid}license_key" value="{$license_key}" size="40" placeholder="XXXX-XXXX-XXXX-XXXX" />
            </p>
        </div>
        
        <div class="pageoverflow">
            <p class="pagetext"></p>
            <p class="pageinput">
                <input type="submit" name="{$actionid}verify_license" value="{$mod->Lang('verify_license')}" class="pagebutton" />
            </p>
        </div>
    </form>
    
    {if $pro_enabled != '1'}
        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 4px;">
            <h4>{$mod->Lang('pro_features_title')}</h4>
            <ul style="line-height: 1.8;">
                <li>{$mod->Lang('pro_feature_rate_limiting')}</li>
                <li>{$mod->Lang('pro_feature_session_revalidation')}</li>
                <li>{$mod->Lang('pro_feature_trusted_devices')}</li>
                <li>{$mod->Lang('pro_feature_user_management')}</li>
                <li>{$mod->Lang('pro_feature_enforce_2fa')}</li>
                <li>{$mod->Lang('pro_feature_audit_logs')}</li>
                <li>{$mod->Lang('pro_feature_email_notifications')}</li>
            </ul>
            <p style="margin-top: 15px;">
                <a href="https://pixelsolutions.local/en/plugins/twofactor/" target="_blank" class="pagebutton">{$mod->Lang('get_pro_license')}</a>
            </p>
        </div>
    {/if}
</div>
