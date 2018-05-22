{*
* 2008 - 2017 Presto-Changeo
*
* Hips Payment
*
* @version   1.0.0
* @author    Presto-Changeo <info@presto-changeo.com>
* @link      http://www.presto-changeo.com
* @copyright Copyright (c) permanent, Presto-Changeo
* @license   Addons PrestaShop license limitation
*
* NOTICE OF LICENSE
*
* Don't use this module on several shops. The license provided by PrestaShop Addons 
* for all its modules is valid only once for a single shop.
*}
<div id="left_menu">

    <!-- Secondary menu - not all top menus have this option -->
    <div id="secondary_menu">
        <!-- Secondary menu - connected to First top menu item -->
        <div id="secondary_0" class="menu">

            <!-- Submenu with header -->
            <div id="secondary_0_0">
                <!-- Submenu header -->
                <div class="menu_header">                    
                    <span class="menu_header_text">{l s='Module Settings' mod='hipspayment'}</span>
                    <!-- Arrow - will allow to show / hide the submenu items -->
                    <!-- If you need a left menu item always visible just remove the span arrow -->
                    <span id="left_menu_arrow" class="arrow_up"></span>
                </div>
                <!-- END - Submenu header -->
                <!-- Submenu -->
                <div class="secondary_submenu">
                    <!-- Submenu without instructions -->
                    <div id="secondary_menu_item_0_0_1" class="secondary_menu_item" data-instructions="instructions-basic-settings" data-content="basic_settings">
                        {l s='Basic Settings' mod='hipspayment'}
                    </div>
                    <!-- END Submenu without instructions -->
                    <!-- Submenu with instructions -->
                    <div id="secondary_menu_item_0_0_2" class="secondary_menu_item" data-content="refund_transaction">
                        {l s='Refund Transaction' mod='hipspayment'}
                    </div>
                    <!-- END Submenu with instructions -->
                    <div id="secondary_menu_item_0_0_3" class="secondary_menu_item capture_transaction {if $hips_type != 'AUTH_ONLY'}hideADN{/if}" data-content="capture_transaction">
                        {l s='Capture Transaction' mod='hipspayment'}
                    </div>
                   
                </div>
                <!-- END - Submenu -->
            </div>
            <!-- END Submenu with header -->
            <!--<div id="secondary_0_1">    
                <div class="menu_header">
                    {l s='Copy Attributes' mod='hipspayment'}  
                    <span class="arrow_up"></span>
                </div>
                <div class="secondary_submenu">
                    <div id="secondary_menu_item_0_1_1" class="secondary_menu_item">
                        {l s='Basic Settings 1' mod='hipspayment'}
                    </div>
                    <div id="secondary_menu_item_0_1_2" class="secondary_menu_item">
                        {l s='Basic Settings 2' mod='hipspayment'}
                    </div>
                </div>
            </div>
            <div id="secondary_0_3">
                <div class="menu_header secondary_menu_item" id="secondary_menu_item_0_3_0">
                    {l s='Single Menu' mod='hipspayment'}               
                </div>            
            </div>       
            -->
        </div>
        <!-- END Secondary menu - connected to First top menu item -->
        <!--
        <div id="secondary_1" class="menu">
            <div id="secondary_1_0">
                <div class="menu_header">
                    {l s='Secondary 1' mod='hipspayment'}
                    <span class="arrow_up"></span>
                </div>
                <div class="secondary_submenu">
                    <div id="secondary_menu_item_1_0_1" class="secondary_menu_item">
                        {l s='Basic Settings 1' mod='hipspayment'}
                    </div>
                    <div id="secondary_menu_item_1_0_2" class="secondary_menu_item">
                        {l s='Basic Settings 2' mod='hipspayment'}
                    </div>
                </div>
            </div>
            <div id="secondary_1_1">
                <div class="menu_header secondary_menu_item" id="secondary_menu_item_1_1_1">
                    {l s='Single Menu' mod='hipspayment'}               
                </div>            
            </div>   
        </div>
        -->
    </div>
    <!-- END  Secondary menu - not all top menus have this option -->
    <!-- Instructions Block - connected to left submenu items (only some submenus have this instructions) -->
    <div class="instructions">
        <div class="instructions_block" id="instructions-basic-settings">
            <div class="instructions_title">
                <span class="icon"> </span>
                {l s='Tips' mod='hipspayment'}
            </div>
            <div class="instructions_content">
                <div class="instructions_line">
                    <span class="icon"> </span>
                    {l s='To get your API Login ID and Transaction key, visit' mod='hipspayment'}<a href="https://dashboard.hips.com/account/apikeys" target="_blank"> https://dashboard.hips.com/account/apikeys</a>{l s=', login to your account, click on the "Account" tab at the top, and then on the "API Login ID and Transaction Key" link (under Security Settings).' mod='hipspayment'}
                </div>
                <div class="instructions_line">
                    <span class="icon"> </span>
                    {l s='Unsettled transactions can only be completely voided, you cannot void/refund certain products of the order' mod='hipspayment'}
                </div>
               
            </div>
        </div>
        <!--
        <div class="instructions_block" id="instructions-">
             <div class="instructions_title">
                <span class="icon"> </span>
                {l s='Instructions' mod='hipspayment'}
            </div>
            <div class="instructions_content">
                <div class="instructions_line">
                    <span class="icon"> </span>
                    {l s='Set each group display type (radio, dropdown, checkbox, etc...), select the number of attributes to display in each row..' mod='hipspayment'}
                </div>
                <div class="instructions_line">
                    <span class="icon"> </span>
                    {l s='Select a layout ("Vertical" is better with multiple items per row, or "Horizontal") as well as image related settings.' mod='hipspayment'}
                </div>
                <div class="instructions_line">
                    <span class="icon"> </span>
                    {l s='Select an attribute type for each group, each type will open additional settings below it.' mod='hipspayment'}
                </div>
                <div class="instructions_line instructions_important">
                    <span class="icon"> </span>
                    {l s='You MUST click "Update" after changing the text, do not try to drag any groups.' mod='hipspayment'}
                </div>
            </div>
        </div>
        -->
    </div>   
    <!-- END Instructions Block - connected to left submenu items (only some submenus have this instructions) -->

    <!-- Required only for some menu items -->      
    {if $contactUsLinkPrestoChangeo != ''}
    <div class="contact_form_left_menu">
        <div class="contact_form_text">{l s='For any technical questions, or problems with the module, please contact us using our' mod='hipspayment'}</div>
        <a class="contact_button" href="{$contactUsLinkPrestoChangeo nofilter}">{l s='Contact form' mod='hipspayment'}</a>
    </div>
    {/if}

    <!-- END Required only for some menu items -->   
    <!-- Module Recommandations block -->
    <div id="module_recommandations" class="module_recommandations_top">
       {$getModuleRecommendations nofilter}
    </div>
    <!-- END Module Recommandations block -->
</div>