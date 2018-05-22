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
 

<div id="hips_payment" class="payment hide_payment_form">
{if !$hips_payment_page}
	{capture name=path}{l s='Payment' mod='hipspayment'}{/capture}

	{if $hips_psv < 1.6}
	{include file="$tpl_dir./breadcrumb.tpl"}
	{/if}

	<h1 class="page-heading">{l s='Order summation' mod='hipspayment'}</h1>

	{assign var='current_step' value='payment'}
	{include file="$tpl_dir./order-steps.tpl"}

{/if}


                    
<div class="row">
	<div class="col-xs-12 col-md-6">
		<div id="hips_payment" class="payment_module pc-eidition">
		<form action="{$form_action_url|escape:'html'}" name="hips_form" id="hips_form" method="post" class="std">
				<input type="hidden" name="confirm" value="1" />
				<h2 class="title_accept">
				<div class="caption">{l s='Credit card' mod='hipspayment'}</div>
				</h2>
				<div class="accept_cards">
						{if $hips_visa}
							<img src="{$this_path_ssl|escape:'html'}views/img/visa_big.gif" alt="{l s='Visa' mod='hipspayment'}" />
						{/if}
						{if $hips_mc}
							<img src="{$this_path_ssl|escape:'html'}views/img/mc_big.gif" alt="{l s='Mastercard' mod='hipspayment'}" />
						{/if}
						{if $hips_amex}
							<img src="{$this_path_ssl|escape:'html'}views/img/amex_big.gif" alt="{l s='American Express' mod='hipspayment'}" />
						{/if}
						{if $hips_discover}
							<img src="{$this_path_ssl|escape:'html'}views/img/discover_big.gif" alt="{l s='Discover' mod='hipspayment'}" />
						{/if}
						{if $hips_jcb}
							<img src="{$this_path_ssl|escape:'html'}views/img/jcb_big.gif" alt="{l s='JCB' mod='hipspayment'}" />
						{/if}
						{if $hips_diners}
							<img src="{$this_path_ssl|escape:'html'}views/img/diners_big.gif" alt="{l s='Diners' mod='hipspayment'}" />
						{/if}
						{if $hips_enroute}
							<img src="{$this_path_ssl|escape:'html'}views/img/enroute_big.gif" alt="{l s='Diners' mod='hipspayment'}" />
						{/if}
				</div>
			{if $hips_cc_err}
			<tr>
				<td align="left" colspan="2">{$hips_cc_err|escape:'html'}</td>
			</tr>
			{/if}
                        
                       
			
				<div class="form_row">
					<label for="hips_cc_fname">{l s='Card Holder Name' mod='hipspayment'}: </label>	
					<input type="text" name="hips_cc_fname" id="hips_cc_fname" value="{$hips_cc_fname|escape:'html'} {$hips_cc_lname|escape:'html'}" class="form-control"/> 
				</div>
                                {*
				<div class="form_row half-row f-r">
					<label>{l s='Lastname' mod='hipspayment'}: </label>	
					<input type="text" name="obp_cc_lname" id="obp_cc_lname" value="{$obp_cc_lname|escape:'html'}" class="form-control"/> 
				</div>
                                *}
				
                                {if $hips_get_address}
				<div class="form_row half-row f-l">
					<label>{l s='Address' mod='hipspayment'}: </label>	
					<input type="text" name="hips_cc_address" value="{$hips_cc_address|escape:'html'}" class="form-control"/>
				</div>

				<div class="form_row half-row f-r">
					<label>{l s='City' mod='hipspayment'}: </label>
					<input type="text" name="hips_cc_city" value="{$hips_cc_city|escape:'html'}" class="form-control"/>
				</div>

				<div class="form_row half-row f-l">
					<label>{l s='Zipcode' mod='hipspayment'}: </label>	
					<input type="text" name="hips_cc_zip" size="5" value="{$hips_cc_zip|escape:'html'}" class="form-control"/>
				</div>

				<div class="form_row half-row f-r">
					<label>{l s='Country' mod='hipspayment'}: </label>
					<select name="hips_id_country" id="hips_id_country" class="form-control">{$countries_list|default:''}</select>
				</div>

				<div class="form_row half-row f-l">
					<div class="hips_id_state">
					<label>{l s='State' mod='hipspayment'}:  </label>
					<select name="hips_id_state" id="hips_id_state" class="form-control">
						<option value="">-</option>
					</select>
					</div>
				</div>
				<div class="clear"></div>
				{/if}				

				<div class="form_row">
					<label>{l s='Card Number' mod='hipspayment'}: </label>
					<input data-hips-tokenizer="number" type="text" name="hips_cc_number" id="hips_cc_number" value="{$hips_cc_number|escape:'html'}" class="form-control"/>
				</div>

				<div class="form_row half-row f-l">
					<label>{l s='Expiration' mod='hipspayment'}: </label>
					<select data-hips-tokenizer="exp_month" name="hips_cc_Month" id="hips_exp_month" class="form-control">
						{foreach from=$hips_months  key=k item=v}
							<option value="{$k}" {if !empty($cardInfo.exp_date) && $cardInfo.exp_date.1 ==$k}selected="selected"{/if}>{$v}</option>
						{/foreach}
					</select>
				</div>

				<div class="form_row half-row f-r">
					<label>&nbsp;</label>
					<select data-hips-tokenizer="exp_year" name="hips_cc_Year" id="hips_exp_year" class="form-control">
						{foreach from=$hipsyears  key=k item=v}
							<option value="{$k}" {if !empty($cardInfo.exp_date) && $cardInfo.exp_date.0 ==$k}selected="selected"{/if}>{$v}</option>
						{/foreach}
					</select>
				</div>

			
				<p class="form_row">
					<label>{l s='CVN code' mod='hipspayment'} 
                                        </label> 
					<input data-hips-tokenizer="cvc" type="text" name="hips_cc_cvv" size="4" value="{$hips_cc_cvv|escape:'html'}" class="form-control half-row" />
					{*
                                        <span class="form-caption">{l s='3-4 digit number from the back of your card.' mod='hipspayment'}</span>
                                        *}
				</p>			
			
			
                        
                                
                        
                        
              
		
		
	
                
			{if !$hips_payment_page}
				<div class="pcpm-total">
					<span style="float:left">{l s='The total amount of your order is' mod='hipspayment'}&nbsp;</span>
					<span id="amount_{$currencies.0.id_currency}" class="price">{convertPrice price=$hips_total}</span>
				</div>
				<div class="pcpm-confirm">
					{l s='Please confirm your order by clicking \'I confirm my order\'' mod='hipspayment'}.
				</div>
			{/if}			
			

		
			
		<div class="clear"></div>

                 <div id="hips_ajax_container" style="display:none; margin-bottom:10px;"></div>
                        
                       
                 
                <p class="cart_navigation">

                       {if $hips_psv >= 1.6}
                           
                            
                            <input type="submit" onclick="validate_hips(document.hips_form);" id="hips_submit" value="{l s='I confirm my order' mod='hipspayment'}" class="button btn btn-default button-medium" />
               
                        {else}
                                <input  onclick="validate_hips(document.hips_form);" type="submit" id="hips_submit" value="{l s='I confirm my order' mod='hipspayment'}" class="exclusive_large" />
                        {/if}	
                <div class="clear"></div>
                                        
                </p>	

		</form>
		
		</div>
                
        
	</div>
        
<script type="text/javascript">
    //<![CDATA[


    hips_idSelectedCountry = {if isset($id_state)}{$id_state|intval}{elseif isset($address->id_state)}{$address->id_state|intval}{else}false{/if};
    hips_countries = new Array();
    hips_countriesNeedIDNumber = new Array();
    {foreach from=$countries item='country'}
            {if isset($country.states) && $country.contains_states}
                    hips_countries[{$country.id_country|intval}] = new Array();
                    {foreach from=$country.states item='state' name='states'}
                            hips_countries[{$country.id_country|intval}].push({ldelim}'id' : '{$state.id_state}', 'name' : '{$state.name|escape:'htmlall':'UTF-8'}'{rdelim});
                    {/foreach}
            {/if}
    {/foreach}
    $(function(){ldelim}
        $('.hips_id_state option[value={if isset($id_state)}{$id_state}{else}{$address->id_state|escape:'htmlall':'UTF-8'}{/if}]').attr('selected', 'selected');
    {rdelim});


   

    var ajax_hips_url     = '{$this_path}{$hips_filename}.php';

    function validate_hips(form, sendhips)
    {ldelim}

        
        if (form.hips_cc_fname.value == "")
        {ldelim}
                alert("{l s='You must enter your' mod='hipspayment'} {l s='Card Holder Name' mod='hipspayment'}");
                return false;
        {rdelim}
        if (form.hips_cc_address && form.hips_cc_address.value == "")
        {ldelim}
                alert("{l s='You must enter your' mod='hipspayment'} {l s='Address' mod='hipspayment'}");
                return false;
        {rdelim}
        if (form.hips_cc_city && form.hips_cc_city.value == "")
        {ldelim}
                alert("{l s='You must enter your' mod='hipspayment'} {l s='City' mod='hipspayment'}");
                return false;
        {rdelim}
        if (form.hips_cc_zip && form.hips_cc_zip.value == "")
        {ldelim}
                alert("{l s='You must enter your' mod='hipspayment'} {l s='Zipcode' mod='hipspayment'}");
                return false;
        {rdelim}
        if (form.hips_cc_email && form.hips_cc_email.value == "")
        {ldelim}
                alert("{l s='You must enter your' mod='hipspayment'} {l s='Email' mod='hipspayment'}");
                return false;
        {rdelim}

        // console.log(form.adn_cc_number && (form.adn_cc_number.value == "" || !creditCardValidator.validate(form.adn_cc_number.value)));

        {*
        if (form.hips_cc_number && (form.hips_cc_number.value == "" || !creditCardValidator.validate(form.hips_cc_number.value)))
        {ldelim}
                alert("{l s='You must enter a valid' mod='hipspayment'} {l s='Card Number' mod='hipspayment'}");
                return false;
        {rdelim}

        *}
        if (form.hips_cc_cvv && form.hips_cc_cvv.value == "")
        {ldelim}
                alert("{l s='You must enter your' mod='hipspayment'} {l s='CVM code' mod='hipspayment'}");
                return false;
        {rdelim}



        {literal}

        $('#hips_submit').val('{/literal}{l s='Please wait' mod='hipspayment'}{literal}');
        //$('#hips_submit').attr('disabled','disabled');


        {/literal}

    {rdelim}


        Hips.public_key='{$hips_public}';
        Hips.tokenizeCard('#hips_form', function(response){
            if(response.error) {

                $('#hips_ajax_container').show();
                $('#hips_ajax_container').html(response.error.message);
                $('#hips_ajax_container').addClass('alert alert-danger');

            } else {
              //On success, token is available in response.payload.token
              //Return false to stop form submission even on success.
            
              
              {literal} 
                    $.ajax({
                            url: ajax_hips_url,
                            type: "post",
                            dataType: "html",
                            data: 'fingerprint=' + response.payload.card.fingerprint + '&mask=' + response.payload.card.mask + '&token=' + response.payload.token + '&confirm=1&hips_cc_fname=' +  $('#hips_cc_fname').val(),
                            success: function(strData) {

                                    $('#hips_submit_order').val("{/literal}{l s='Order now' mod='hipspayment'}{literal}");
                                    $('#hips_submit_order').removeAttr('disabled');

                                    if (strData.substring(0, 4) == 'url:') {

                                            window.location = strData.substring(4);

                                    } else {
                                            $('#hips_ajax_container').show();
                                            $('#hips_ajax_container').html(strData);
                                            $('#hips_ajax_container').addClass('alert alert-danger');
                                            $('#hips_submit').val('{/literal}{l s='Continue' mod='hipspayment'}{literal}');
                                            $('#hips_submit').attr('disabled',false);
                                            
                                    }
                            }
                    });
            {/literal} 	
    
            }
        
            return false;
        });


 
    function placeOrder(form)
    {ldelim}
        $('#obp_ajax_container').hide();

        $('#obp_submit_order').val("{l s='Please wait' mod='hipspayment'}");
        $('#obp_submit_order').attr('disabled','disabled');

        {literal} 
            $.ajax({
                    url: ajax_obp_url,
                    type: "post",
                    dataType: "html",
                    data:$(form).serialize() + '&obp_save_card=' + ($('#obp_save_card').is(':checked') ? 1:0)  ,
                    success: function(strData) {

                            $('#obp_submit_order').val("{/literal}{l s='Order now' mod='hipspayment'}{literal}");
                            $('#obp_submit_order').removeAttr('disabled');

                            if (strData.substring(0, 4) == 'url:') {

                                    window.location = strData.substring(4);

                            } else {
                                    $('#obp_ajax_container').show();
                                    $('#obp_ajax_container').html(strData);
                                    $('#obp_ajax_container').addClass('error');
                                    $('#obp_submit').val('{/literal}{l s='Continue' mod='hipspayment'}{literal}');
                                    $('#obp_submit').attr('disabled',false);
                                    returnToStep2();
                            }
                    }
            });
        {/literal} 	
    {rdelim}

    {literal}
    //Create an object
    var creditCardValidator = {};
    // Pin the cards to them
    creditCardValidator.cards = {
      'mc':'5[1-5][0-9]{14}',
      'ec':'5[1-5][0-9]{14}',
      'vi':'4(?:[0-9]{12}|[0-9]{15})',
      'ax':'3[47][0-9]{13}',
      'dc':'3(?:0[0-5][0-9]{11}|[68][0-9]{12})',
      'bl':'3(?:0[0-5][0-9]{11}|[68][0-9]{12})',
      'di':'6011[0-9]{12}',
      'jcb':'(?:3[0-9]{15}|(2131|1800)[0-9]{11})',
      'er':'2(?:014|149)[0-9]{11}'
    };
    // Add the card validator to them
    creditCardValidator.validate = function(value,ccType) {
      value = String(value).replace(/[- ]/g,''); //ignore dashes and whitespaces

      var cardinfo = creditCardValidator.cards, results = [];
      if(ccType){
        var expr = '^' + cardinfo[ccType.toLowerCase()] + '$';
        return expr ? !!value.match(expr) : false; // boolean
      }

      for(var p in cardinfo){
        if(value.match('^' + cardinfo[p] + '$')){
          results.push(p);
        }
      }
      return results.length ? results.join('|') : false; // String | boolean
    }
    {/literal}

    function regIsDigit(fData)
    {ldelim}
            var reg = new RegExp("^[0-9]+$");
            return (reg.test(fData));
    {rdelim}


	
   

        
</script>
                
<style>

	#hips_payment td {ldelim}
		height:20px;
	{rdelim}	

	#hips_payment input, #hips_payment select {ldelim}
		margin:0;
	{rdelim}

	#hips_payment td.td_label {ldelim}
		white-space:nowrap;
		padding-right: 20px;
	{rdelim}
	
	#hips_payment td.td_input {ldelim}
		width:90%;
	{rdelim}
	
	#hips_payment form.std p span {ldelim}
		width:auto;
	{ldelim}
	
</style>
</div>
</div>
