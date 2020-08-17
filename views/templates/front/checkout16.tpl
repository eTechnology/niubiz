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
*  @copyright  2007-2019 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="text-center">
    <h2>{l s='Pay with Visa' mod='visanetperu'}</h2>
    <p>{l s='To confirm your purchase please click on the following button' mod='visanetperu'}</p>
    <br><br>
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <form action="{$link->getModuleLink('visanetperu', 'return')|escape:'html':'UTF-8'}" method="post">
                <script src="{$var.urlScript|escape:'html':'UTF-8'}"
    			data-sessiontoken="{$var.sessionToken|escape:'htmlall':'UTF-8'}"
    			data-merchantid="{$var.merchantId|escape:'htmlall':'UTF-8'}"
    			data-channel="web"
    			data-buttonsize=""
    			data-buttoncolor="" 
    			data-merchantlogo="http://{$logo|escape:'htmlall':'UTF-8'}"
    			data-merchantname=""
    			data-formbuttoncolor="#0A0A2A"
    			data-showamount=""
    			data-purchasenumber="{$var.numOrden|escape:'htmlall':'UTF-8'}"
    			data-amount="{$var.monto|escape:'htmlall':'UTF-8'}"
    			data-cardholdername="{$customer->firstname|escape:'htmlall':'UTF-8'}"
    			data-cardholderlastname="{$customer->lastname|escape:'htmlall':'UTF-8'}"
    			data-cardholderemail="{$customer->email|escape:'htmlall':'UTF-8'}"
    			data-usertoken="{$var.userTokenId|escape:'htmlall':'UTF-8'}"
    			data-recurrence=""
    			data-frequency=""
    			data-recurrencetype=""
    			data-recurrenceamount=""
    			data-documenttype="0"
    			data-documentid=""
    			data-beneficiaryid="VISANET"
    			data-productid=""
    			data-phone=""
    			data-timeouturl="http://localhost:8080"
    		/></script>
            </form>
            
            
        </div>
    </div>
</div>

{if $debug > 0}
    <ul>
        <li>userTokenId = {$var.userTokenId|escape:'htmlall':'UTF-8'}</li>
        <li>sessionToken = {$var.sessionToken|escape:'htmlall':'UTF-8'} </li>
        <li>merchantId = {$var.merchantId|escape:'htmlall':'UTF-8'} </li>
        <li>numOrden = {$var.numOrden|escape:'htmlall':'UTF-8'} </li>
        <li>monto = {$var.monto|escape:'htmlall':'UTF-8'} </li>
    </ul>
{/if}
