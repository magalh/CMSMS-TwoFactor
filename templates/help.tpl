

<div class="clear"></div>

<div class="information" style="margin-top: 20px; padding: 15px; background: #e8f4f8; border-left: 4px solid #2196F3;">
  <p style="margin: 0;"><strong>Need more help?</strong> Visit our complete documentation at <a href="https://pixelsolutions.biz/documentation/twofactor/" target="_blank">https://pixelsolutions.biz/documentation/twofactor/</a></p>
</div>

<div id="page_tabs">

  <div  id="general">
    General
  </div>
  <div id="using">
    How Do I Use It
  </div>
  <div id="customization">
    Customization
  </div>
  {if $have_2fpro}
  <div id="pro_settings">
    Pro Settings
  </div>
  {else}
    <div id="upgrade">
    How to Upgrade
    </div>
  {/if}
  <div id="hooks">
    Hooks
  </div>
</div>

<div class="clearb"></div>
<div id="page_content">

  <div id="general_c">
    {include file='module_file_tpl:TwoFactor;help_general_tab.tpl'}
  </div>
  <div id="using_c">
    {include file='module_file_tpl:TwoFactor;help_using_tab.tpl'}
  </div>
  <div id="customization_c">
    {include file='module_file_tpl:TwoFactor;help_customization_tab.tpl'}
  </div>

  {if $have_2fpro}
    <div id="pro_settings_c">
      {include file='module_file_tpl:TwoFactor;help_pro_settings_tab.tpl'}
    </div>
  {else}
  <div id="upgrade_c">
    {include file='module_file_tpl:TwoFactor;help_upgrade_tab.tpl'}
  </div>
  {/if}
    <div id="hooks_c">
    {include file='module_file_tpl:TwoFactor;help_hooks_tab.tpl'}
  </div>


  <div class="clearb"></div>
</div>