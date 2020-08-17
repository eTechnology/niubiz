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

if (!defined('_PS_VERSION_')) {
    exit;
}

// IF YOU USE PRESTASHOP 1.7 uncomment below line
 use PrestaShop\PrestaShop\Core\Payment\PaymentOption;


class VisaNetPeru extends PaymentModule
{
    private $html = '';
    private $postErrors = array();

    public $posWeb = false;

    public $merchantid;
    public $accesskey;
    public $secretkey;

    public $connections = array();

    public function __construct()
    {
        $this->name = 'visanetperu';
        $this->tab = 'payments_gateways';
        $this->version = '3.0.1';
        $this->bootstrap = true;
        $this->views = _MODULE_DIR_.$this->name.'/views/';
        $this->domain = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__;
        $this->url_return = $this->domain.'index.php?fc=module&module='.$this->name.'&controller=notifier';
        $this->module_key = '72868a598030b3e8df685fec00b4b8ed';

        parent::__construct();

        $this->displayName = $this->l('VisaNet Peru');
        $this->author = "Victor Castro";
        $this->email = "victor@castrocontreras.com";
        $this->website = "www.castrocontreras.com";
        $this->description = $this->l('Accept payments with Visa on Peru. Ahora aceptamos Visa, Mastercard, Amex y Diners');
        $this->confirmUninstall = $this->l('Are your sure?');
        $this->acceptedCurrency = [];
        $this->psVersion = round(_PS_VERSION_, 1);

        if (function_exists('curl_init') == false) {
            $this->warning = $this->l('In order to use this module, activate cURL (PHP extension).');
        }

        $currency = new Currency($this->context->cookie->id_currency);

        switch ($currency->iso_code) {
            case 'PEN':
                $this->merchantid = Configuration::get('VSA_MERCHANTID_PEN');
                $this->vsauser = Configuration::get('VSA_USER_PEN');
                $this->vsapassword = Configuration::get('VSA_PASSWORD_PEN');
                break;

            case 'USD':
                $this->merchantid = Configuration::get('VSA_MERCHANTID_USD');
                $this->vsauser = Configuration::get('VSA_USER_USD');
                $this->vsapassword = Configuration::get('VSA_PASSWORD_USD');
                break;

            default:
                $this->merchantid = '';
                $this->vsauser = '';
                $this->vsapassword = '';
                break;
        }

        switch (Configuration::get('VSA_ENVIROMENT')) {
            case 'PRD':
                $this->security_api = 'https://apiprod.vnforapps.com/api.security/v1/security';
                $this->session_api = 'https://apiprod.vnforapps.com/api.ecommerce/v2/ecommerce/token/session/'.$this->merchantid;
                $this->authorization_api = 'https://apiprod.vnforapps.com/api.authorization/v3/authorization/ecommerce/'.$this->merchantid;
                $this->urlScript = 'https://static-content.vnforapps.com/v2/js/checkout.js';
                break;

            case 'DEV':
                $this->security_api = 'https://apitestenv.vnforapps.com/api.security/v1/security';
                $this->session_api = 'https://apitestenv.vnforapps.com/api.ecommerce/v2/ecommerce/token/session/'.$this->merchantid;
                $this->authorization_api = 'https://apitestenv.vnforapps.com/api.authorization/v3/authorization/ecommerce/'.$this->merchantid;
                $this->urlScript = 'https://static-content-qas.vnforapps.com/v2/js/checkout.js?qa=true';
                break;
        }
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        $link = new Link;
        Configuration::updateValue('VSA_LOGO', $link->getMediaLink(_PS_IMG_.Configuration::get('PS_LOGO')));

        if (!parent::install()
            || !$this->registerHook('payment')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('paymentOptions')
            //|| !$this->registerHook('displayAdminOrderLeft')
            ) {
                return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('VSA_LOGO')
         || !Configuration::deleteByName('VSA_MERCHANTID_PEN')
         || !Configuration::deleteByName('VSA_USER_PEN')
         || !Configuration::deleteByName('VSA_PASSWORD_PEN')
         || !Configuration::deleteByName('VSA_MERCHANTID_USD')
         || !Configuration::deleteByName('VSA_ACCESSKEY_USD')
         || !Configuration::deleteByName('VSA_SECRETKEY_USD')
         || !parent::uninstall()) {
            return false;
        }

        return parent::uninstall();
    }

    protected function displayConfiguration()
    {
        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }

    public function getContent()
    {
        if ($this->postValidation()) {
            $this->postProcess();
        } else {
            foreach ($this->postErrors as $err) {
                $this->html .= $this->displayError($err);
            }
        }

        $this->html .= $this->displayConfiguration();
        $this->html .= $this->renderForm();

        return $this->html;
    }

    public function renderForm()
    {
        $fields_form = array();

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('GENERAL CONFIGURATION'),
                'icon' => 'icon-bug'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label'=>  $this->l('Ambiente'),
                    'name' => 'VSA_ENVIROMENT',
                    'options' => array(
                        'query' => array(
                            array('id' => 'DEV', 'name' => $this->l('Development')),
                            array('id' => 'PRD', 'name' => $this->l('Production')),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Debugger'),
                    'name' => 'VSA_DEBUG',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('URL de Logo'),
                    'name' => 'VSA_LOGO',
                    'required' => true,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Guardar'),
            )
        );

        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('SOLES CONFIGURATION'),
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Activo'),
                    'name' => 'VSA_PEN',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Comercio'),
                    'name' => 'VSA_MERCHANTID_PEN',
                    'required' => false
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Usuario'),
                    'name' => 'VSA_USER_PEN',
                    'required' => false
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Password'),
                    'name' => 'VSA_PASSWORD_PEN',
                    'required' => false
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save Soles'),
            )
        );

        $fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('DOLARES CONFIGURATION'),

            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Activo'),
                    'name' => 'VSA_USD',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Comercio'),
                    'name' => 'VSA_MERCHANTID_USD',
                    'required' => false
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Usuario'),
                    'name' => 'VSA_USER_USD',
                    'required' => false
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Password'),
                    'name' => 'VSA_PASSWORD_USD',
                    'required' => false
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save Soles'),
            )
        );


        $emplFormLang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = $emplFormLang ? $emplFormLang : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.
            $this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm($fields_form);
    }

    protected function getConfigFormValues()
    {
        return array(
            'VSA_DEBUG' => Tools::getValue('VSA_DEBUG', Configuration::get('VSA_DEBUG')),
            'VSA_LOGO' => Tools::getValue('VSA_LOGO', Configuration::get('VSA_LOGO')),
            'VSA_ENVIROMENT' => Tools::getValue('VSA_ENVIROMENT', Configuration::get('VSA_ENVIROMENT')),
            'VSA_MERCHANTID_PEN' => Tools::getValue('VSA_MERCHANTID_PEN', trim(Configuration::get('VSA_MERCHANTID_PEN'))),
            'VSA_USER_PEN' => Tools::getValue('VSA_USER_PEN', trim(Configuration::get('VSA_USER_PEN'))),
            'VSA_PASSWORD_PEN' => Tools::getValue('VSA_PASSWORD_PEN', trim(Configuration::get('VSA_PASSWORD_PEN'))),
            'VSA_MERCHANTID_USD' => Tools::getValue('VSA_MERCHANTID_USD', trim(Configuration::get('VSA_MERCHANTID_USD'))),
            'VSA_USER_USD' => Tools::getValue('VSA_USER_USD', trim(Configuration::get('VSA_USER_USD'))),
            'VSA_PASSWORD_USD' => Tools::getValue('VSA_PASSWORD_USD', Configuration::get('VSA_PASSWORD_USD')),
            'VSA_PEN' => Tools::getValue('VSA_PEN', Configuration::get('VSA_PEN')),
            'VSA_USD' => Tools::getValue('VSA_USD', Configuration::get('VSA_USD')),
            'FREE' => Tools::getValue('FREE', Configuration::get('FREE')),
        );
    }

    private function postValidation()
    {
        $errors = array();

        if (Tools::isSubmit('btnSubmit')) {
            if (empty(Tools::getValue('VSA_LOGO'))) {
                $errors[] = $this->l('El Logo de visa es obligatorio');
            }
        }

        if (count($errors)) {
            $this->html .= $this->displayError(implode('<br />', $errors));
            return false;
        }

        return true;
    }

    private function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('VSA_LOGO', Tools::getValue('VSA_LOGO'));
            Configuration::updateValue('VSA_ENVIROMENT', Tools::getValue('VSA_ENVIROMENT'));
            Configuration::updateValue('VSA_DEBUG', Tools::getValue('VSA_DEBUG'));
            Configuration::updateValue('VSA_MERCHANTID_PEN', Tools::getValue('VSA_MERCHANTID_PEN'));
            Configuration::updateValue('VSA_USER_PEN', Tools::getValue('VSA_USER_PEN'));
            Configuration::updateValue('VSA_PASSWORD_PEN', Tools::getValue('VSA_PASSWORD_PEN'));
            Configuration::updateValue('VSA_MERCHANTID_USD', Tools::getValue('VSA_MERCHANTID_USD'));
            Configuration::updateValue('VSA_USER_USD', Tools::getValue('VSA_USER_USD'));
            Configuration::updateValue('VSA_PASSWORD_USD', Tools::getValue('VSA_PASSWORD_USD'));
            Configuration::updateValue('VSA_PEN', Tools::getValue('VSA_PEN'));
            Configuration::updateValue('VSA_USD', Tools::getValue('VSA_USD'));
        }

        $this->html .= $this->displayConfirmation($this->l('Guardado Correctamente'));
    }

    public function hookPayment($params)
    {
        if (Configuration::get('VSA_USD'))
            $this->acceptedCurrency[] = 'USD';
        if (Configuration::get('VSA_PEN'))
            $this->acceptedCurrency[] = 'PEN';

        $currency = new Currency($this->context->cookie->id_currency);
        $this->context->controller->addCSS($this->_path.'/views/css/visanetperu.css');

        $this->context->smarty->assign(array(
            'views' => $this->views,
            'currency_code' => $currency->iso_code,
            'acceptedCurrency' => in_array($currency->iso_code, $this->acceptedCurrency),
            'debug' => Configuration::get('VSA_DEBUG')
        ));

        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookDisplayPaymentEU($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $payment_options = array(
            'cta_text' => $this->l('Pay with  Visa'),
            'logo' => $this->views.'img/logo-visanet.png',
            'action' => $this->context->link->getModuleLink($this->name, 'checkout', array(), true)
        );

        return $payment_options;
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        //print_r($params);
        //die;
        switch ($this->psVersion) {
            case 1.7:
                $cart = new Cart($params['order']->id_cart);
                $currency = new Currency($params['order']->id_currency);
                $state = $params['order']->getCurrentState();
                $sql = 'SELECT * FROM '._DB_PREFIX_.$this->name.'_log WHERE id_order='.$params['order']->id;
                $total_to_pay = Tools::displayPrice($params['order']->total_paid, $currency, false);
                break;

            default:
                $cart = new Cart($params['objOrder']->id_cart);
                $currency = new Currency($params['objOrder']->id_currency);
                $state = $params['objOrder']->getCurrentState();
                $sql = 'SELECT * FROM '._DB_PREFIX_.$this->name.'_log WHERE id_order='.$params['objOrder']->id;
                $total_to_pay = Tools::displayPrice($params['objOrder']->total_paid, $currency, false);
                break;
        }


        $in_array = in_array(
            $state,
            array(
                Configuration::get('PS_OS_PAYMENT'),
                Configuration::get('PS_OS_OUTOFSTOCK'),
                Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')
            )
        );

        if ($in_array) {
            $this->smarty->assign('status', 'ok');
        } else {
            $this->smarty->assign('status', 'failed');
        }


        $result = Db::getInstance()->getRow($sql);

        $cms_condiions = new CMS(Configuration::get('PS_CONDITIONS_CMS_ID'), $this->context->language->id);

        $this->context->smarty->assign(array(
            'customerName' => $this->context->customer->firstname.' '.$this->context->customer->lastname,
            'total_to_pay' => $total_to_pay,
            'moneda' => Currency::getCurrencyInstance($this->context->currency->id)->name,
            'link_conditions' => $this->context->
            link->getCMSLink($cms_condiions, $cms_condiions->link_rewrite, Configuration::get('PS_SSL_ENABLED')),
            'products' => $cart->getProducts(),
            'id_cart' => $cart->id,
            'result' => $result,
            'total' => $cart->getOrderTotal(),
        ));

        return $this->display(__FILE__, 'confirmation.tpl');
    }

    public function hookFooter()
    {
        $this->context->smarty->assign(array(
            'views' => $this->views,
        ));

        return $this->display(__FILE__, 'footer.tpl');
    }

    public function hookdisplayAdminOrderLeft()
    {
        $order_current = Tools::getValue('id_order');
        $sql_1 = 'SELECT COUNT(*) FROM '._DB_PREFIX_.$this->name.'_log WHERE id_order='.$order_current;
        $order_bd = Db::getInstance()->getValue($sql_1);

        $sql_2 = 'SELECT * FROM '._DB_PREFIX_.$this->name.'_log WHERE id_order='.$order_current;
        $results = Db::getInstance()->ExecuteS($sql_2);

        $this->context->smarty->assign(array(
            'order_current' => $order_current,
            'order_bd' => array($order_bd),
            'results' => array($results),
            'views' => $this->views,
        ));
        return $this->display(__FILE__, 'displayAdminOrder.tpl');
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $this->smarty->assign(
            $this->getTemplateVars()
        );

        $newOption = new PaymentOption();
        $newOption->setModuleName($this->name)
            ->setLogo(Media::getMediaPath(dirname(__FILE__).'/views/img/visanet-paymentoptions.jpg'))
            ->setCallToActionText($this->trans('Visanet Perú'))
            ->setAction($this->context->link->getModuleLink($this->name, 'checkout', array(), true))
            ->setAdditionalInformation($this->fetch('module:visanetperu/views/templates/hook/paymentoption.tpl'));
        $payment_options = [
            $newOption,
        ];

        return $payment_options;
    }

    public function rowTransaction($id_customer)
    {
        $sql = 'SELECT c.id_customer, v.aliasname, v.date_add, v.usertokenuuid
            FROM '._DB_PREFIX_.'customer c
            INNER JOIN '._DB_PREFIX_.'visanetperu_log v ON c.id_customer = v.id_customer
            AND c.id_customer = '.$id_customer.'
            ORDER BY date_add DESC';

        return Db::getInstance()->getRow($sql);
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getTemplateVars()
    {
        $cart = $this->context->cart;

        return [
            'checkTotal' => Tools::displayPrice($cart->getOrderTotal(true, Cart::BOTH)),
        ];
    }
}