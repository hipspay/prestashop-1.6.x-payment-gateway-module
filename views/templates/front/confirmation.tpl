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
<p>{l s='Your order on' d='Modules.AuthorizeDotNet'} <span class="bold">{$shop_name|escape:'htmlall':'UTF-8'}</span> {l s='is complete.' d='Modules.AuthorizeDotNet'}
	<br /><br />
	{l s='You have chosen the Credit Card method.' d='Modules.AuthorizeDotNet'}
	<br /><br /><span class="bold">{l s='Your order will be sent very soon.' d='Modules.AuthorizeDotNet'}</span>
	<br /><br />{l s='For any questions or for further information, please contact our' d='Modules.AuthorizeDotNet'} <a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}contact-form.php">{l s='customer support' d='Modules.AuthorizeDotNet'}</a>.
</p>
