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
<p>{l s='Your order on' mod='hipspayment'} <span class="bold">{$shop_name|escape:'html'}</span> {l s='is complete.' mod='hipspayment'}
	<br /><br />
	{l s='You have chosen the Hips Credit Card method.' mod='hipspayment'}
	<br /><br /><span class="bold">{l s='Your order will be sent very soon.' mod='hipspayment'}</span>
	<br /><br />{l s='For any questions or for further information, please contact our' mod='hipspayment'} <a href="{$base_dir_ssl|escape:'html'}contact-form.php">{l s='customer support' mod='hipspayment'}</a>.
</p>
