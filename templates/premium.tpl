<div class="pagecontainer">
    <h3>{$mod->Lang('premium_title')}</h3>

    <div style="max-width: 600px;">
    <h4>Activate Your License</h4>
        <form method="post" action="{$tab_url}" style="margin-top: 15px;">
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" name="{$actionid}license_key" value="{$license_key}" size="40" placeholder="XXXX-XXXX-XXXX-XXXX" style="flex: 1;" />
                <input type="submit" name="{$actionid}verify_license" value="{$mod->Lang('verify_license')}" class="pagebutton" />
                {if $license_key}
                <input type="submit" name="{$actionid}remove_license" value="{$mod->Lang('remove_license')}" class="pagebutton" onclick="return confirm('{$mod->Lang('confirm_remove_license')}');" />
                {/if}
            </div>
        </form>
    </div>
    
    {include file='module_file_tpl:TwoFactor;premium_comparison.tpl'}
    
</div>
