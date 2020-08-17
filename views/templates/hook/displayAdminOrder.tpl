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

{if $order_bd[0] neq 0}
<div class="panel">
	<div class="panel-heading">
		<i class="icon-credit-card"></i>
		{l s='Response' mod='visanetperu'}
		<span class="badge">Visanet</span>
	</div>

	<ul class="nav nav-tabs" id="tabModulevisanetperunt">
		<li class="active">
			<a href="#resume">
				{l s='Summary' mod='visanetperu'}
			</a>
		</li>
		<li>
			<a href="#records">
				<i class="icon-file-text"></i>
				{l s='Records BD' mod='visanetperu'}
			</a>
		</li>
	</ul>
	<div class="tab-content panel">
		<div class="tab-pane active clearfix" id="resume">
			<div class="col-sm-7 ">
			{foreach from=$results key=k item=i}
				{foreach from=$i item=s}
					<dl class="well list-detail">
						{if $s.numorden != '-'}
							<dt>{l s='Purchase Operation Number' mod='visanetperu'}</dt>
							<dd>{$s.numorden|escape:'htmlall':'UTF-8'}</dd>
						{/if}
						{if $s.dsc_cod_accion != '-'}
							<dt>{l s='Response' mod='visanetperu'}</dt>
							<dd>{$s.dsc_cod_accion|escape:'htmlall':'UTF-8'}</dd>
						{/if}
						{if $s.fechayhora_tx != '-'}
							<dt>{l s='Date operation' mod='visanetperu'}</dt>
							<dd>{$s.fechayhora_tx|escape:'htmlall':'UTF-8'}</dd>
						{/if}
						{if $s.pan != '-'}
							<dt>{l s='Card number' mod='visanetperu'}</dt>
							<dd>{$s.pan|escape:'htmlall':'UTF-8'} </dd>
						{/if}
					</dl>
				{/foreach}
			{/foreach}
			</div>
			<div class="col-sm-4">
				<img src="{$views|escape:'htmlall':'UTF-8'}img/logo-visanet.png" class="img-responsive pull-right">
			</div>
		</div>
		<div class="tab-pane" id="records">
			{foreach from=$results key=k item=i}
	    		<pre>{$i|print_r:true|escape:'htmlall':'UTF-8'}</pre>
			{/foreach}
		</div>
	</div>

</div>

<script>
	$('#tabModulevisanetperunt a').click(function (e) {
		e.preventDefault()
		$(this).tab('show')
	})
</script>
{/if}