<div class="pagecontainer">
    <h3>{$mod->Lang('premium_title')}</h3>

    <div>
    <h4>{$mod->Lang('activate_your_license')}</h4>

    <p>{$mod->Lang('premium_description')}
    </p>
        <form method="post" action="{$tab_url}" style="margin-top: 15px;">
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" name="{$actionid}license_key" value="{$license_key}" size="40" placeholder="XXXX-XXXX-XXXX-XXXX" style="max-width:500px;" />
                <input type="submit" name="{$actionid}verify_license" value="{$mod->Lang('verify_license')}" class="pagebutton" />
                {if $license_key}
                <input type="submit" name="{$actionid}remove_license" value="{$mod->Lang('remove_license')}" class="pagebutton" onclick="return confirm('{$mod->Lang('confirm_remove_license')}');" />
                {/if}
            </div>
        </form>
    </div>
    
    {include file='module_file_tpl:TwoFactor;premium_comparison.tpl'}
    
</div>
