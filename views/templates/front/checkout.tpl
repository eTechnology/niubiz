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
*  @copyright  2007-2019 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file='page.tpl'}

{block name='page_title'}
  {l s='Confirmacion de pago' d='Modules.Niubiz.Checkout'}
{/block}

{block name='page_content'}
<div class="text-center">
  <p>{l s='Para procesar el pago por favor haga click en el siguiente botón y complete los datos de su tarjeta.' d='Modules.Niubiz.Checkout'}</p>
    <br>
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <form id="niubizform" action="{$linkReturn|escape:'html':'UTF-8'}" method="POST">
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
                  data-cardholdername="{$var.firstname|escape:'htmlall':'UTF-8'}"
                  data-cardholderlastname="{$var.lastname|escape:'htmlall':'UTF-8'}"
                  data-cardholderemail="{$var.email|escape:'htmlall':'UTF-8'}"
                  data-usertoken="{$var.userTokenId|escape:'htmlall':'UTF-8'}"
                  data-recurrence=""
                  data-frequency=""
                  data-recurrencetype=""
                  data-recurrenceamount=""
                  data-documenttype="0"
                  data-documentid=""
                  data-beneficiaryid="NIUBIZ"
                  data-productid=""
                  data-phone=""
                  data-expirationminutes='5'
                  data-timeouturl="{$linkReturn|escape:'html':'UTF-8'}"
                /></script>
            </form>
        </div>
    </div>
</div>

{if $debug > 0}
  <br>
  <pre>
  {$var|print_r:true}
  </pre>
{/if}

{/block}
