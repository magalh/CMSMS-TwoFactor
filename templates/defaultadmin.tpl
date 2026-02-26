<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0px;">
  <h3 style="margin:0;">{$mod->Lang('two_factor_settings')}</h3>
  <a href="https://pixelsolutions.biz" target="_blank" rel="noopener noreferrer">
    <img src="https://pixelsolution.s3.eu-south-1.amazonaws.com/logos/LOGO_3_COLOR_300.png" alt="Pixel Solutions" style="height:40px;" />
  </a>
</div>

{if $is_pro_active}
    <div class="information">
        <p><strong>✓ {$mod->Lang('pro_active_beta')}</strong> - {$mod->Lang('pro_features_enabled')}</p>
    </div>
{/if}
