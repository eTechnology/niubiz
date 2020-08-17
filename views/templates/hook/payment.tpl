{*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $debug}
<div class="alert alert-warning" role="alert">
	VISANETPERU DEBUG ENABLED: <br>
	<ul>
	{if !$acceptedCurrency}
		<li> No se ha configurado en el modulo los parámetros para {$currency_code}</li>
	{else}
		<li> Modulo configurado correctamente para {$currency_code} </li>
	{/if}
	</ul>
</div>
{/if}

{if $acceptedCurrency}
<p id="visanetperu" class="payment_module">
	<a href="{$link->getModuleLink('visanetperu', 'checkout')|escape:'html'}" title="{l s='Pay with Visa' mod='visanetperu'}">
		<img src="{$views|escape:'htmlall':'UTF-8'}img/logo-visa.png" alt="{l s='Pay with Visa' mod='visanetperu'}" width="70" style="margin-right:10px"/>
		{l s='Pay with Visa' mod='visanetperu'}
	</a>
</p>
{/if}