<?php
/**
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
*/

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Niubiz extends PaymentModule
{
    private $html = '';
    private $postErrors = array();
    public $merchantid;

    public $connections = array();

    public function __construct()
    {
        $this->name = 'niubiz';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.5';
        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
        $this->author = "Victor Castro";
        $this->controllers = array('checkout');

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->views = _MODULE_DIR_.$this->name.'/views/';
        $this->domain = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__;
        $this->url_return = $this->domain.'index.php?fc=module&module='.$this->name.'&controller=notifier';
        $this->callback = $this->domain.'index.php?fc=module&module='.$this->name.'&controller=callback';
        $this->module_key = '72868a598030b3e8df685fec00b4b8ed';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'Niubiz';
        $this->email = "victor@castrocontreras.com";
        $this->github = "https://github.com/victorcastro";
        $this->description = $this->trans('Accept payments with Credit card and PagoEfectivo', array(), 'Modules.Niubiz.Admin');
        $this->confirmUninstall = $this->trans('Are your sure?', array(), 'Modules.Niubiz.Admin');
        $this->acceptedCurrency = [];
        $this->psVersion = round(_PS_VERSION_, 1);

        if (function_exists('curl_init') == false) {
            $this->warning = $this->trans('In order to use this module, activate cURL (PHP extension).', array(), 'Modules.Niubiz.Admin');
        }

        $currency = new Currency($this->context->cookie->id_currency);

        switch ($currency->iso_code) {
            case 'PEN':
                $this->merchantid = Configuration::get('NBZ_MERCHANTID_PEN');
                $this->vsauser = Configuration::get('NBZ_USER_PEN');
                $this->vsapassword = Configuration::get('NBZ_PASSWORD_PEN');
                break;

            case 'USD':
                $this->merchantid = Configuration::get('NBZ_MERCHANTID_USD');
                $this->vsauser = Configuration::get('NBZ_USER_USD');
                $this->vsapassword = Configuration::get('NBZ_PASSWORD_USD');
                break;

            default:
                $this->merchantid = '';
                $this->vsauser = '';
                $this->vsapassword = '';
                break;
        }

        switch (Configuration::get('NBZ_ENVIROMENT')) {
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

        $this->createState();

        $link = new Link;
        Configuration::updateValue('NBZ_LOGO', $link->getMediaLink(_PS_IMG_.Configuration::get('PS_LOGO')));

        if (!parent::install()
            || !$this->registerHook('payment')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('paymentOptions')
            || !$this->installFixtures()
            ) {
                return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('NBZ_LOGO')
        || !Configuration::deleteByName('NBZ_PAYMENT_OPTION_TEXT')
        || !Configuration::deleteByName('NBZ_PAYMENT_OPTION_LOGO')
         || !Configuration::deleteByName('NBZ_MERCHANTID_PEN')
         || !Configuration::deleteByName('NBZ_USER_PEN')
         || !Configuration::deleteByName('NBZ_PASSWORD_PEN')
         || !Configuration::deleteByName('NBZ_MERCHANTID_USD')
         || !Configuration::deleteByName('NBZ_ACCESSKEY_USD')
         || !Configuration::deleteByName('NBZ_SECRETKEY_USD')
         || !parent::uninstall()) {
            return false;
        }

        return parent::uninstall();
    }

    protected function installFixtures()
    {
        $languages = Language::getLanguages(false);

        $filename = md5('options_cards').'.png';

        if (copy(dirname(__FILE__).DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'options_cards.png', dirname(__FILE__).DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$filename)) {
          foreach ($languages as $lang) {
              $this->installFixture((int)$lang['id_lang'], $filename);
          }

          return true;
        }

        return false;
    }

    protected function installFixture($id_lang, $image = null)
    {
        $values['NBZ_PAYMENT_OPTION_TEXT'][(int)$id_lang] = 'Page con Visa, Mastercard o PagoEfectivo';
        $values['NBZ_PAYMENT_OPTION_LOGO'][(int)$id_lang] = $image;

        Configuration::updateValue('NBZ_PAYMENT_OPTION_TEXT', $values['NBZ_PAYMENT_OPTION_TEXT']);
        Configuration::updateValue('NBZ_PAYMENT_OPTION_LOGO', $values['NBZ_PAYMENT_OPTION_LOGO']);
    }

    private function createState()
    {
        if (!Configuration::get('NBZ_STATE_WAITING_CAPTURE')) {
            $order_state = new OrderState();
            $order_state->name = array();
            foreach (Language::getLanguages() as $language) {
              $order_state->name[$language['id_lang']] = 'En espera de pago por Niubiz';
            }
            $order_state->module_name = $this->name;
            $order_state->color = '#4169E1';
            $order_state->send_email = false;
            $order_state->hidden = false;
            $order_state->paid = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = false;
            $order_state->pdf_invoice = false;
            $order_state->add();
            Configuration::updateValue('NBZ_STATE_WAITING_CAPTURE', (int)$order_state->id);
        }
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
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
                'title' => $this->trans('FRONT CONFIGURATION', array(), 'Modules.Niubiz.Admin'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->trans('Logo URL', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_LOGO',
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Nombre de pago', array(), 'Modules.Niubiz.Admin'),
                    'desc' => $this->trans('Nombre que saldra en las opciones de pago', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_PAYMENT_OPTION_TEXT',
                    'required' => true,
                    'lang' => true,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->trans('Mostrar logo', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_PAYMENT_OPTION_LOGO_SHOW',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Modules.Niubiz.Admin')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Modules.Niubiz.Admin')
                        )
                    ),
                ),
                array(
                    'type' => 'file_lang',
                    'label' => $this->trans('Logo de pago', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_PAYMENT_OPTION_LOGO',
                    'desc' => 'Si estás usando el tema predeterminado las dimensiones recomendadas son 26x220 px.',
                    'lang' => true,
                ),
            ),
        );

        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->trans('Configuracion de Modal', array(), 'Modules.Niubiz.Admin'),
                'icon' => 'icon-credit-card'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label'=>  $this->trans('Enviroment', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_ENVIROMENT',
                    'options' => array(
                        'query' => array(
                            array('id' => 'DEV', 'name' => $this->trans('Integration', array(), 'Modules.Niubiz.Admin')),
                            array('id' => 'PRD', 'name' => $this->trans('Production', array(), 'Modules.Niubiz.Admin')),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->trans('Show on PaymentOption', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_ENABLE_PAYMENTOPTION',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Modules.Niubiz.Admin')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Modules.Niubiz.Admin')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('URL PagoEfectivo', array(), 'Modules.Niubiz.Admin'),
                    'desc' => $this->trans('Send this url to Niubiz to capture the remote payment of PagoEfectivo.', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_CALLBACK',
                    'required' => false,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->trans('Debugger', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_DEBUG',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Modules.Niubiz.Admin')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Modules.Niubiz.Admin')
                        )
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->trans('Save', array(), 'Modules.Niubiz.Admin'),
            )
        );

        $fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->trans('SOLES CONFIGURATION', array(), 'Modules.Niubiz.Admin'),
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->trans('Active', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_PEN',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Modules.Niubiz.Admin')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Modules.Niubiz.Admin')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Commerce', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_MERCHANTID_PEN',
                    'required' => false
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Email', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_USER_PEN',
                    'required' => false
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Password', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_PASSWORD_PEN',
                    'required' => false
                ),
            ),
            'submit' => array(
                'title' => $this->trans('Save', array(), 'Modules.Niubiz.Admin'),
            )
        );

        $fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->trans('DOLARES CONFIGURATION', array(), 'Modules.Niubiz.Admin'),

            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->trans('Active', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_USD',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Modules.Niubiz.Admin')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Modules.Niubiz.Admin')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Commerce', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_MERCHANTID_USD',
                    'required' => false
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Email', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_USER_USD',
                    'required' => false
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Password', array(), 'Modules.Niubiz.Admin'),
                    'name' => 'NBZ_PASSWORD_USD',
                    'required' => false
                ),
            ),
            'submit' => array(
                'title' => $this->trans('Save', array(), 'Modules.Niubiz.Admin'),
            )
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $emplFormLang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $this->fields_form = array();

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = $emplFormLang ? $emplFormLang : 0;
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'uri' => $this->getPathUri(),
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm($fields_form);
    }

    protected function getConfigFormValues()
    {
        $languages = Language::getLanguages(false);
        $fields = array();

        $fields['NBZ_DEBUG'] = Tools::getValue('NBZ_DEBUG', Configuration::get('NBZ_DEBUG'));
        $fields['NBZ_LOGO'] = Tools::getValue('NBZ_LOGO', Configuration::get('NBZ_LOGO'));
        $fields['NBZ_ENVIROMENT'] = Tools::getValue('NBZ_ENVIROMENT', Configuration::get('NBZ_ENVIROMENT'));
        $fields['NBZ_MERCHANTID_PEN'] = Tools::getValue('NBZ_MERCHANTID_PEN', trim(Configuration::get('NBZ_MERCHANTID_PEN')));
        $fields['NBZ_USER_PEN'] = Tools::getValue('NBZ_USER_PEN', trim(Configuration::get('NBZ_USER_PEN')));
        $fields['NBZ_PASSWORD_PEN'] = Tools::getValue('NBZ_PASSWORD_PEN', trim(Configuration::get('NBZ_PASSWORD_PEN')));
        $fields['NBZ_MERCHANTID_USD'] = Tools::getValue('NBZ_MERCHANTID_USD', trim(Configuration::get('NBZ_MERCHANTID_USD')));
        $fields['NBZ_USER_USD'] = Tools::getValue('NBZ_USER_USD', trim(Configuration::get('NBZ_USER_USD')));
        $fields['NBZ_PASSWORD_USD'] = Tools::getValue('NBZ_PASSWORD_USD', Configuration::get('NBZ_PASSWORD_USD'));
        $fields['NBZ_PEN'] = Tools::getValue('NBZ_PEN', Configuration::get('NBZ_PEN'));
        $fields['NBZ_USD'] = Tools::getValue('NBZ_USD', Configuration::get('NBZ_USD'));
        $fields['FREE'] = Tools::getValue('FREE', Configuration::get('FREE'));
        $fields['NBZ_CALLBACK'] = Tools::getValue('NBZ_CALLBACK', $this->callback);
        $fields['NBZ_PAYMENT_OPTION_LOGO_SHOW'] = Tools::getValue('NBZ_PAYMENT_OPTION_LOGO_SHOW', Configuration::get('NBZ_PAYMENT_OPTION_LOGO_SHOW'));
        $fields['NBZ_ENABLE_PAYMENTOPTION'] = Tools::getValue('NBZ_ENABLE_PAYMENTOPTION', Configuration::get('NBZ_ENABLE_PAYMENTOPTION'));

        foreach ($languages as $lang) {
            $fields['NBZ_PAYMENT_OPTION_TEXT'][$lang['id_lang']] = Tools::getValue('NBZ_PAYMENT_OPTION_TEXT_'.$lang['id_lang'], Configuration::get('NBZ_PAYMENT_OPTION_TEXT', $lang['id_lang']));
            $fields['NBZ_PAYMENT_OPTION_LOGO'][$lang['id_lang']] = Tools::getValue('NBZ_PAYMENT_OPTION_LOGO_'.$lang['id_lang'], Configuration::get('NBZ_PAYMENT_OPTION_LOGO', $lang['id_lang']));
        }

        return $fields;
    }

    private function postValidation()
    {
        $errors = array();

        if (Tools::isSubmit('btnSubmit')) {
            if (empty(Tools::getValue('NBZ_LOGO'))) {
                $errors[] = $this->trans('El Logo de visa es obligatorio');
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
      $languages = Language::getLanguages(false);
      $values = array();
      $update_images_values = false;

        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('NBZ_LOGO', Tools::getValue('NBZ_LOGO'));
            Configuration::updateValue('NBZ_ENVIROMENT', Tools::getValue('NBZ_ENVIROMENT'));
            Configuration::updateValue('NBZ_MERCHANTID_PEN', Tools::getValue('NBZ_MERCHANTID_PEN'));
            Configuration::updateValue('NBZ_DEBUG', Tools::getValue('NBZ_DEBUG'));
            Configuration::updateValue('NBZ_USER_PEN', Tools::getValue('NBZ_USER_PEN'));
            Configuration::updateValue('NBZ_PASSWORD_PEN', Tools::getValue('NBZ_PASSWORD_PEN'));
            Configuration::updateValue('NBZ_MERCHANTID_USD', Tools::getValue('NBZ_MERCHANTID_USD'));
            Configuration::updateValue('NBZ_USER_USD', Tools::getValue('NBZ_USER_USD'));
            Configuration::updateValue('NBZ_PASSWORD_USD', Tools::getValue('NBZ_PASSWORD_USD'));
            Configuration::updateValue('NBZ_PEN', Tools::getValue('NBZ_PEN'));
            Configuration::updateValue('NBZ_USD', Tools::getValue('NBZ_USD'));
            Configuration::updateValue('NBZ_CALLBACK', $this->callback);
            Configuration::updateValue('NBZ_PAYMENT_OPTION_LOGO_SHOW', Tools::getValue('NBZ_PAYMENT_OPTION_LOGO_SHOW'));
            Configuration::updateValue('NBZ_ENABLE_PAYMENTOPTION', Tools::getValue('NBZ_ENABLE_PAYMENTOPTION'));

            foreach ($languages as $lang) {
                if (isset($_FILES['NBZ_PAYMENT_OPTION_LOGO_'.$lang['id_lang']])
                    && isset($_FILES['NBZ_PAYMENT_OPTION_LOGO_'.$lang['id_lang']]['tmp_name'])
                    && !empty($_FILES['NBZ_PAYMENT_OPTION_LOGO_'.$lang['id_lang']]['tmp_name'])) {
                    if ($error = ImageManager::validateUpload($_FILES['NBZ_PAYMENT_OPTION_LOGO_'.$lang['id_lang']], 4000000)) {
                        return $error;
                    } else {
                        $ext = substr($_FILES['NBZ_PAYMENT_OPTION_LOGO_'.$lang['id_lang']]['name'], strrpos($_FILES['NBZ_PAYMENT_OPTION_LOGO_'.$lang['id_lang']]['name'], '.') + 1);
                        $file_name = md5($_FILES['NBZ_PAYMENT_OPTION_LOGO_'.$lang['id_lang']]['name']).'.'.$ext;
                        if (!move_uploaded_file($_FILES['NBZ_PAYMENT_OPTION_LOGO_'.$lang['id_lang']]['tmp_name'], dirname(__FILE__).DIRECTORY_SEPARATOR .'img' . DIRECTORY_SEPARATOR.$file_name)) {
                            return $this->displayError($this->trans('An error occurred while attempting to upload the file.', array(), 'Admin.Notifications.Error'));
                        } else {
                            if (Configuration::hasContext('NBZ_PAYMENT_OPTION_LOGO', $lang['id_lang'], Shop::getContext())
                                && Configuration::get('NBZ_PAYMENT_OPTION_LOGO', $lang['id_lang']) != $file_name) {
                                @unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . Configuration::get('NBZ_PAYMENT_OPTION_LOGO', $lang['id_lang']));
                            }

                            $values['NBZ_PAYMENT_OPTION_LOGO'][$lang['id_lang']] = $file_name;
                        }
                    }

                    $update_images_values = true;
                }

                $values['NBZ_PAYMENT_OPTION_TEXT'][$lang['id_lang']] = Tools::getValue('NBZ_PAYMENT_OPTION_TEXT_'.$lang['id_lang']);
            }

            if ($update_images_values) {
                Configuration::updateValue('NBZ_PAYMENT_OPTION_LOGO', $values['NBZ_PAYMENT_OPTION_LOGO']);
            }

            Configuration::updateValue('NBZ_PAYMENT_OPTION_TEXT', $values['NBZ_PAYMENT_OPTION_TEXT']);

            return $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
        }

        return '';
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $cart = $this->context->cart;
        $customer = Context::getContext()->customer;
        $currency = new Currency($this->context->cookie->id_currency);
        $amount = number_format($cart->getOrderTotal(true, Cart::BOTH), 2, '.', '');
        $securityKey = $this->securityKey();
        setcookie("key", $securityKey);

        $sessionToken = $this->createToken($amount, $securityKey);
        $userTokenId = $this->userTokenId();

        if (Configuration::get('NBZ_USD'))
            $this->acceptedCurrency[] = 'USD';
        if (Configuration::get('NBZ_PEN'))
            $this->acceptedCurrency[] = 'PEN';

        $isModuleConfigured = in_array($currency->iso_code, $this->acceptedCurrency);

        $variables = array(
            'userTokenId' => $userTokenId,
            'sessionToken' => $sessionToken,
            'merchantId' => $this->merchantid,
            'urlScript' => $this->urlScript,
            'numOrden' => (int)$cart->id,
            'monto' => $amount,
        );

        $this->context->smarty->assign(array(
            'logo' => Configuration::get('VSA_LOGO'),
            'customer' => (array) $customer,
            'debug' => Configuration::get('VSA_DEBUG'),
            'psVersion' => $this->psVersion,
            'var' => $variables,
            'checkTotal' => Tools::displayPrice($cart->getOrderTotal(true, Cart::BOTH)),
            'linkReturn' => $this->context->link->getModuleLink($this->name, 'return', array(), true)
        ));

        $newOption = new PaymentOption();
        $newOption->setModuleName($this->name)
            ->setCallToActionText($isModuleConfigured ? Configuration::get('NBZ_PAYMENT_OPTION_TEXT', $this->context->language->id) : 'WARNING!! Niubiz is not enabled for current currency')
            ->setAction($this->context->link->getModuleLink($this->name, 'checkout', array(), true));

        if (Configuration::get('NBZ_ENABLE_PAYMENTOPTION')) {
            $newOption->setAdditionalInformation($this->fetch('module:niubiz/views/templates/hook/paymentoption.tpl'));
        }

        if (Configuration::get('NBZ_PAYMENT_OPTION_LOGO_SHOW')) {
            $newOption->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/img/'.Configuration::get('NBZ_PAYMENT_OPTION_LOGO', $this->context->language->id)));
        }

        $payment_options = [
            $newOption,
        ];

        return $payment_options;
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
            'cta_text' => Configuration::get('NBZ_PAYMENT_OPTION_TEXT', $this->context->language->id),
            'logo' => Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/img/'.Configuration::get('NBZ_PAYMENT_OPTION_LOGO', $this->context->language->id)),
            'action' => $this->context->link->getModuleLink($this->name, 'checkout', array(), true)
        );

        return $payment_options;
    }

    
    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $cart = new Cart($params['order']->id_cart);
        $currency = new Currency($params['order']->id_currency);
        $state = $params['order']->getCurrentState();
        $sql = 'SELECT * FROM '._DB_PREFIX_.$this->name.'_log WHERE id_order='.$params['order']->id;
        $total_to_pay = Tools::displayPrice($params['order']->total_paid, $currency, false);

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

    public function rowTransaction($id_customer)
    {
        $sql = 'SELECT c.id_customer, v.aliasname, v.date_add, v.usertokenuuid
            FROM '._DB_PREFIX_.'customer c
            INNER JOIN '._DB_PREFIX_.'niubiz_log v ON c.id_customer = v.id_customer
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

    private function securityKey()
    {
        $currency = new Currency($this->context->cookie->id_currency);

        $accessKey = $this->vsauser != '' ? $this->vsauser : die('No se ha encontrado el usuario para la moneda '.$currency->iso_code);
        $secretKey = $this->vsapassword != '' ? $this->vsapassword : die('No se ha encontrado la contraseña para la moneda '.$currency->iso_code);
        $header = array("Content-Type: application/json");
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->security_api);
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
        curl_setopt($ch, CURLOPT_URL, $this->session_api);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($ch);
        $json = json_decode($response);
        if (!isset($json->sessionKey)) {
            echo json_encode([
                'session_api' => $this->session_api,
                'response' => $json
            ]);
            die;
        }
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
