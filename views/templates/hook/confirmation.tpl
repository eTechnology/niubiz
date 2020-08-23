{*
* 2007-2021 PrestaShop
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


    <h2 class="text-center">
    {if $status == 'ok'}
        <b style="color:green">{l s='Your order completed successfully' d='Modules.Niubiz.Checkout'}</b>
    {else}
        <style>
            #content-hook_order_confirmation,
            #content.page-content.page-order-confirmation.card {
                display: none;
            }
        </style>
        <b style="color:red">{l s='Your order has NOT been processed ' d='Modules.Niubiz.Checkout'}</b>
        :: {$result.dsc_cod_accion|escape:'htmlall':'UTF-8'}
    {/if}
    </h2>
    <br>
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3 clearfix">
        	<ul style="list-style:none">
                <li>{l s='Order number' d='Modules.Niubiz.Checkout'} : {$id_cart|escape:'htmlall':'UTF-8'}</li>
                <li>{l s='Cardholder Name' d='Modules.Niubiz.Checkout'} : {$customerName|escape:'htmlall':'UTF-8'}</li>
                <li>{l s='Marca' d='Modules.Niubiz.Checkout'} : {$result.dsc_eci|escape:'htmlall':'UTF-8'}</li>
                <li>{l s='Card' d='Modules.Niubiz.Checkout'} : {$result.pan|escape:'htmlall':'UTF-8'}</li>
                <li>{l s='Currency' d='Modules.Niubiz.Checkout'} : {$moneda|escape:'htmlall':'UTF-8'}</li>
                <li>{l s='Amount' d='Modules.Niubiz.Checkout'} : {$total|escape:'htmlall':'UTF-8'}</li>
                <li>{l s='Rason' d='Modules.Niubiz.Checkout'} : {$result.dsc_cod_accion|escape:'htmlall':'UTF-8'}</li>
            </ul>

            <b>{l s='Products' d='Modules.Niubiz.Checkout'} :</b>
            {foreach from=$products item=product}
        	<li> {$product.name|escape:'htmlall':'UTF-8'} </li>
        	{/foreach}
            <br>
            <br />{l s='Save and/or print this information as a transaction receipt. You can also consult our' d='Modules.Niubiz.Checkout'}
            <a href="{$link_conditions|escape:'html':'UTF-8'}" class="iframe" data-ajax="false" target="_blank">{l s='Terms of sale' d='Modules.Niubiz.Checkout'}</a>.
            <br><br>
            <a href="{$link->getPageLink('history', true)|escape:'htmlall':'UTF-8'}" title="{l s='Mis pedidos' d='Modules.Niubiz.Checkout'}" data-ajax="false">{l s='View All My Orders' d='Modules.Niubiz.Checkout'}</a><br />

        	<br /><br />{l s='To resolve any doubts do not hesitate to contact us' d='Modules.Niubiz.Checkout'}
        	<a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}" data-ajax="false" target="_blank">{l s='Customer service' d='Modules.Niubiz.Checkout'}</a>.
        </div>
    </div>

