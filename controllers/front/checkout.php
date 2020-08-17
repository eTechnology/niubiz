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
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class visanetperucheckoutModuleFrontController extends ModuleFrontController
{
    //public $ssl = true;
    //public $display_column_left = false;

    public function initContent()
    {
        parent::initContent();

        $cart = Context::getContext()->cart;
        $customer = Context::getContext()->customer;

        $amount = number_format($cart->getOrderTotal(true, Cart::BOTH), 2, '.', '');

        $securityKey = $this->securityKey();
        setcookie("key", $securityKey);

        $sessionToken = $this->createToken($amount, $securityKey);
        $userTokenId = $this->userTokenId();


        $session = array();
        $session['id_cart'] = (int)$cart->id;
        $session['id_customer'] = (int)$customer->id;
        $session['sessiontoken'] = pSQL($sessionToken);
        $session['sessionkey'] = pSQL($userTokenId);

        Db::getInstance()->insert('visanetperu_session', $session);

        $variables = array(
            'userTokenId' => $userTokenId,
            'sessionToken' => $sessionToken,
            'merchantId' => $this->module->merchantid,
            'urlScript' => $this->module->urlScript,
            'numOrden' => (int)$cart->id,
            'monto' => $amount,
            //'rowT' => $this->module->rowTransaction($customer->id),
        );

        $this->context->smarty->assign(array(
            'logo' => Configuration::get('VSA_LOGO'),
            'customer' => $customer,
            'debug' => Configuration::get('VSA_DEBUG'),
            'psVersion' => $this->module->psVersion,
            'var' => $variables,
        ));

        switch ($this->module->psVersion) {
            case 1.7:
                $this->setTemplate('module:visanetperu/views/templates/front/checkout17.tpl');
                break;

            default:
                $this->setTemplate('checkout16.tpl');
                break;
        }
}

    private function securityKey()
    {
        $currency = new Currency($this->context->cookie->id_currency);

        $accessKey = $this->module->vsauser != '' ? $this->module->vsauser : die('No se ha encontrado el usuario para la moneda '.$currency->iso_code);
        $secretKey = $this->module->vsapassword != '' ? $this->module->vsapassword : die('No se ha encontrado la contraseña para la moneda '.$currency->iso_code);
        $url = $this->module->security_api;
        $header = array("Content-Type: application/json");
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$accessKey:$secretKey");
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $key = curl_exec($ch);
        if ($key !== 'Unauthorized access')
            return $key;
        else
            die($key);
    }

    public function createToken($amount, $key)
    {
        $header = ["Content-Type: application/json", "Authorization: $key"];
        $request_body = '{
            "amount" : '.$amount.',
            "channel" : "web",
            "antifraud" : {
                "clientIp" : "'.$_SERVER["REMOTE_ADDR"].'",
                "merchantDefineData" : {
                    "MDD1" : "web",
                    "MDD2" : "Canl",
                    "MDD3" : "Canl"
                }
            }
        }';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->module->session_api);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($ch);
        $json = json_decode($response);

        $dato = $json->sessionKey;

        return $dato;
    }

    public function userTokenId()
    {
        mt_srand((double)microtime()*10000);
        $charid = Tools::strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);
        $uuid = chr(123)
            .Tools::substr($charid, 0, 8).$hyphen
            .Tools::substr($charid, 8, 4).$hyphen
            .Tools::substr($charid, 12, 4).$hyphen
            .Tools::substr($charid, 16, 4).$hyphen
            .Tools::substr($charid, 20, 12).$hyphen
            .chr(125);
        $uuid = Tools::substr($uuid, 1, 36);

        return $uuid;
    }
}
