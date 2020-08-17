<?php
/**
* 2007-2016 PrestaShop
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
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class VisaNETPeruReturnModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;
    
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $currency = $this->context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $transactionToken = Tools::getValue('transactionToken');

        if ($cart->id == ''
            || $cart->id_customer == 0
            || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0
            || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'visanetperu') {
                $authorized = true;
                break;
            }
        }
        
        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }
        
     
        
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        
        $respuesta = json_decode($this->authorization($_COOKIE["key"], $total, $transactionToken, $cart->id), true);
        
        echo "<pre>";
    	print_r($respuesta);
    	echo "<pre>";
    	
	    // die;
	    
	    $dataInput = isset($respuesta['dataMap']) ? 'dataMap' : 'data';
	    
        unset($_COOKIE["key"]);
        $sal = [];

        //print_r($rsp_dataMap); die;
        

        if ($respuesta[$dataInput]['ACTION_CODE'] == "000") {
            $ps_os_payment = Configuration::get('PS_OS_PAYMENT');
        } else {
            $ps_os_payment = Configuration::get('PS_OS_CANCELED');
        }
        
        $na = $this->module->displayName;
        $key = $customer->secure_key;
        
        $this->module->validateOrder($cart->id, $ps_os_payment, $total, $na, null, null, $currency->id, false, $key);
        
        $order = new Order($this->module->currentOrder);
        $sal['data']['id_cart'] = (int)$cart->id;
        $sal['data']['id_customer'] = (int)$customer->id;
        $sal['data']['pan'] = $respuesta[$dataInput]['CARD'];;
        $sal['data']['numorden'] = $respuesta[$dataInput]['TRACE_NUMBER'];
        $sal['data']['dsc_cod_accion'] = $respuesta[$dataInput]['ACTION_DESCRIPTION'];
        $sal['data']['dsc_eci'] = pSQL($respuesta[$dataInput]['BRAND']);
        $sal['data']['transactionToken'] = pSQL($transactionToken);
        $sal['data']['aliasName'] = pSQL($respuesta[$dataInput]['SIGNATURE']);
        $sal['data']['id_order'] = (int)$order->id;
        Db::getInstance()->insert('visanetperu_log', $sal['data']);
        
        $rdc = 'index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id;
        
        Tools::redirect($rdc.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
    }
    
    function authorization($key, $amount,$transactionToken, $purchaseNumber)
    {
        $header = array("Content-Type: application/json","Authorization: $key");
        $request_body="{
    
            \"antifraud\" : null,
            \"captureType\" : \"manual\",
            \"channel\" : \"web\",
            \"countable\" : true,
            \"order\" : {
                \"amount\" : \"$amount\",
                \"tokenId\" : \"$transactionToken\",
                \"purchaseNumber\" : \"$purchaseNumber\",
                \"currency\" : \"PEN\"
            }
        }";
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->module->authorization_api);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        #curl_setopt($ch, CURLOPT_USERPWD, "$accessKey:$secretKey");
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($ch);
        $json = json_decode($response);
        $json = json_encode($json, JSON_PRETTY_PRINT);
        //$dato = $json->sessionKey;
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $json;
    }
}