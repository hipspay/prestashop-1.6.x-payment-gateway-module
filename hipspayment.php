<?php
/**
 * 2008 - 2017 Presto-Changeo
 *
 * MODULE Hips Payment
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
 */

require_once(_PS_MODULE_DIR_ . 'hipspayment/PrestoChangeoClasses/init.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

class HipsPayment extends PaymentModule
{

    protected $html = '';
    protected $postErrors = array();
    public $hips_private = '';
    public $hips_public = '';
    public $hips_type = '';
    public $hips_secure_key = '';
    public $hips_payment_page = '';
    public $hips_auth_status = '';
    public $hips_ac_status = '';

    /* Failed Transaction */
    public $hips_ft = '';
    public $hips_ft_email = '';
    public $hips_get_address = '';
    public $hips_get_cvm = '';
    public $hips_show_left = '';
    public $hips_visa = '';
    public $hips_mc = '';
    public $hips_amex = '';
    public $hips_discover = '';
    public $hips_jcb = '';
    public $hips_diners = '';
    public $hips_enroute = '';

    /**
     * ft = failed_transaction
     */
    protected $full_version = 10000;

    public function __construct()
    {
        $this->name = 'hipspayment';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';

        //$this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->author = 'Hips';
        $this->controllers = array('validation');
        $this->is_eu_compatible = 1;

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->bootstrap = true;
        parent::__construct();

        $this->_refreshProperties();

        $this->displayName = $this->l('Hips Payment');
        $this->description = $this->l('Receive and Refund payments using Hips Payment');

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
        if ($this->hips_private == '' || $this->hips_public == '') {
            $this->warning = $this->l('You must enter your Hips Payment API infomation, for details on how to get them click "Configure"');
        }
    }

    public function install()
    {
        $secure_key = md5(mt_rand() . time());

        if (!parent::install() ||
            !$this->registerHook('backOfficeHeader') ||
            !$this->registerHook('header') ||
            !$this->registerHook('adminOrder') ||
            !$this->registerHook('updateOrderStatus') ||
            !$this->registerHook('payment') ||
            !$this->registerHook('paymentReturn')
        ) {
            return false;
        }


        if (!Configuration::updateValue('HIPS_AC_STATUS', '0') ||
            !Configuration::updateValue('HIPS_AUTH_STATUS', '') ||
            !Configuration::updateValue('HIPS_PAYMENT_PAGE', '1') ||
            !Configuration::updateValue('HIPS_TYPE', 'AUTH_CAPTURE') ||
            !Configuration::updateValue('HIPS_VISA', '1') ||
            !Configuration::updateValue('HIPS_GET_ADDRESS', '0') ||
            !Configuration::updateValue('HIPS_GET_CVM', '1') ||
            !Configuration::updateValue('HIPS_SECURE_KEY', $secure_key) ||
            !Configuration::updateValue('HIPS_MC', '1') ||
            !Configuration::updateValue('HIPS_FT', '0') ||
            !Configuration::updateValue('HIPS_FT_EMAIL', '') ||
            !Configuration::updateValue('HIPS_ADMINHOOK_ADDED', '1')
        ) {
            return false;
        }


        $this->installDBTables();
        return true;
    }

    protected function installDBTables()
    {
        $query = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hips_refunds` (
              `id_order` int(11) NOT NULL,
			  `id_cart` int(11) NOT NULL,
              `card_mask` varchar(50) NOT NULL,
              `fingerprint` varchar(50) NOT NULL,			  
              `card_token` varchar(60) NOT NULL,
              `payment_status` varchar(20) NOT NULL,
			  `payment_id` varchar(100) NOT NULL,
			  `order_id` varchar(100) NOT NULL,
			  `captured` TINYINT( 1 ) NOT NULL DEFAULT \'0\',
              `authorization_code` varchar(60) NOT NULL,
			  PRIMARY KEY  (`id_order`)
			) ENGINE=MyISAM;';
        Db::getInstance()->execute($query);

        $query = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hips_refund_history` (
				`id_refund` int(11) unsigned NOT NULL auto_increment,
				`id_order` int(11) NOT NULL,
                `order_id_hips` varchar(60) NOT NULL,
				`amount` varchar(20) NOT NULL,
				`date` datetime NOT NULL,
				`details` varchar(255) NOT NULL,
				PRIMARY KEY  (`id_refund`)
				) ENGINE=MyISAM;';
        Db::getInstance()->execute($query);
    }

    protected function isColumnExistInTable($column, $table)
    {
        $sqlExistsColumn = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE()
                        AND COLUMN_NAME="' . $column . '" AND TABLE_NAME="' . _DB_PREFIX_ . $table . '"; ';
        $exists = Db::getInstance()->ExecuteS($sqlExistsColumn);
        return !empty($exists);
    }

    /**
     * Method for register hook for installed module
     */
    public function registerHookWithoutInstall($hookname, $module_prefix)
    {
        $varName = $module_prefix . '_' . Tools::strtoupper($hookname) . '_ADDED';

        if (Configuration::get($varName) != 1) {
            $hookId = Hook::getIdByName($hookname);
            $isExistModule = Hook::getModulesFromHook($hookId, $this->id);

            if (empty($isExistModule)) {
                if ($this->registerHook($hookname)) {
                    Configuration::updateValue($varName, '1');
                }
            } else {
                // if module already istalled just set variable = 1
                Configuration::updateValue($varName, '1');
            }
        }
    }

    protected function applyUpdatesAlertTable()
    {
        
    }

    private function _refreshProperties()
    {

        $this->hips_private = Configuration::get('HIPS_PRIVATE');
        $this->hips_public = Configuration::get('HIPS_PUBLIC');
        $this->hips_type = Configuration::get('HIPS_TYPE');
        $this->hips_payment_page = (int) Configuration::get('HIPS_PAYMENT_PAGE');
        $this->hips_auth_status = (int) Configuration::get('HIPS_AUTH_STATUS');
        $this->hips_ac_status = (int) Configuration::get('HIPS_AC_STATUS');
        $this->hips_secure_key = Configuration::get('HIPS_SECURE_KEY');

        $this->hips_ft = (int) Configuration::get('HIPS_FT');
        $this->hips_ft_email = Configuration::get('HIPS_FT_EMAIL');

        $this->hips_get_address = (int) Configuration::get('HIPS_GET_ADDRESS');
        $this->hips_get_cvm = (int) Configuration::get('HIPS_GET_CVM');
        $this->hips_show_left = (int) Configuration::get('HIPS_SHOW_LEFT');
        $this->hips_visa = (int) Configuration::get('HIPS_VISA');
        $this->hips_mc = (int) Configuration::get('HIPS_MC');
        $this->hips_amex = (int) Configuration::get('HIPS_AMEX');
        $this->hips_discover = (int) Configuration::get('HIPS_DISCOVER');
        $this->hips_jcb = (int) Configuration::get('HIPS_JCB');
        $this->hips_diners = (int) Configuration::get('HIPS_DINERS');
        $this->hips_enroute = (int) Configuration::get('HIPS_ENROUTE');


        $this->_last_updated = Configuration::get('PRESTO_CHANGEO_UC');
    }

    protected function applyUpdates()
    {

        $this->applyUpdatesAlertTable();
        $this->installDBTables();
        /**
         * update hook module without reinstall module
         */
        $this->registerHookWithoutInstall('adminOrder', 'HIPS');
        $this->registerHookWithoutInstall('header', 'HIPS');
        $this->registerHookWithoutInstall('backOfficeHeader', 'HIPS');

        $this->registerHookWithoutInstall('updateOrderStatus', 'HIPS');
    }

    public function hookUpdateOrderStatus($params)
    {
        $id_order = $params['id_order'];
        $id_new_status = $params['newOrderStatus']->id;
        $ret = $this->doCaptureByOrderState($id_order, $id_new_status);
    }

    public function doCapture($id_order)
    {

        $hipsAPI = new HipsPaymentAPI($this->hips_private, $this->hips_public);

        $result = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'hips_refunds WHERE id_order=' . (int) $id_order);
        //print_r($result);
        $hipsOrderId = $result[0]['payment_id'];
        $post_values = array(
            'hipsOrderId' => $hipsOrderId
        );

        $doRefundResp = $hipsAPI->doCapture($post_values);


        return $doRefundResp;
    }

    public function doCaptureByOrderId($id_order)
    {

        $ret = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'hips_refunds WHERE id_order=' . (int) $id_order);
        if (!empty($ret) && $ret[0]['captured'] == 0) {

            $payment_id = $ret[0]['payment_id'];
            $hipsOrderId = $ret[0]['order_id'];
            $amount = NULL;


            $hipsAPI = new HipsPaymentAPI($this->hips_private, $this->hips_public);
            $post_values = array(
                'hipsOrderId' => $payment_id
            );

            $doCaptureResp = $hipsAPI->doCapture($post_values);


            if (isset($doCaptureResp['status']) && $doCaptureResp['status'] == 'successful') {
                Db::getInstance()->Execute('UPDATE ' . _DB_PREFIX_ . 'hips_refunds SET captured = 1  WHERE id_order=' . (int) $id_order);


                //Db::getInstance()->Execute('UPDATE ' . _DB_PREFIX_ . 'hips_refunds SET trx_ref_id = ' . $doCaptureResp->MarkForCaptureResp->TxRefIdx . '  WHERE id_order=' . (int) $id_order);


                $order = new Order($id_order);
                $message = new Message();
                $message->message = $this->l('Transaction has been captured.') .
                    $this->l('Transaction ID: ') .
                    $doCaptureResp['id'];

                $message->id_customer = $order->id_customer;
                $message->id_order = $order->id;
                $message->private = 1;
                $message->id_employee = $this->getContext()->cookie->id_employee;
                $message->id_cart = $order->id_cart;
                $message->add();

                return $doCaptureResp;
            } else {
                return false;
            }
        }
    }

    public function doCaptureByOrderState($id_order, $id_new_status)
    {

        if ($this->hips_ac_status == $id_new_status && $this->hips_type == 'AUTH_ONLY') {
            return $this->doCaptureByOrderId($id_order);
        }

        return false;
    }

    public function hookAdminOrder()
    {
        $smarty = $this->context->smarty;
        $cookie = $this->context->cookie;

        $orderId = Tools::getValue('id_order');


        $order = new Order($orderId);
        $refundsRecord = Db::getInstance()->ExecuteS('SELECT * FROM  `' . _DB_PREFIX_ . 'hips_refunds` WHERE id_order = "' . ((int) $orderId ) . '"');

        if (!empty($refundsRecord)) {
            $refundsHistory = Db::getInstance()->ExecuteS('SELECT * FROM  `' . _DB_PREFIX_ . 'hips_refund_history` WHERE id_order = "' . ((int) $orderId ) . '"');

            $id_shop = Shop::getContextShopID();

            $smarty->assign(array(
                'order_id' => $orderId,
                'cookie' => $cookie,
                'path' => $this->_path,
                'id_shop' => $id_shop,
                'hips_secure_key' => $this->hips_secure_key,
                'module_basedir' => _MODULE_DIR_ . 'hipspayment/',
                'isCanCapture' => !$refundsRecord[0]['captured'] && $this->hips_type == 'AUTH_ONLY'
            ));
            return $this->display(__FILE__, 'views/templates/admin/adminOrder.tpl');
        }

        return '';
    }

    public function hookHeader()
    {
        $page_name = Dispatcher::getInstance()->getController();
        $smarty = $this->context->smarty;
        if ($page_name == '') {
            $page_name = Configuration::get('PS_FORCE_SMARTY_2') == 0 ? $smarty->tpl_vars['page_name']->value : $smarty->get_template_vars('page_name');
        }

        if (!in_array($page_name, array('order', 'orderopc'))) {
            return;
        }

        $this->context->controller->addJS('https://cdn.hips.com/js/v1/hips.js');
        
        $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/hipspayment/views/js/hipspayment.js');
        $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/hipspayment/views/js/statesManagement.js');
        $this->context->controller->addCSS(__PS_BASE_URI__ . 'modules/hipspayment/views/css/hipspayment.css');
    }

    private function createCombo($adn_visa, $adn_mc, $adn_amex, $adn_discover, $adn_jcb, $adn_diners)
    {
        $imgBuf = array();
        if ($adn_visa) {
            array_push($imgBuf, imagecreatefromgif(dirname(__FILE__) . '/views/img/visa.gif'));
        }
        if ($adn_mc) {
            array_push($imgBuf, imagecreatefromgif(dirname(__FILE__) . '/views/img/mc.gif'));
        }
        if ($adn_amex) {
            array_push($imgBuf, imagecreatefromgif(dirname(__FILE__) . '/views/img/amex.gif'));
        }
        if ($adn_discover) {
            array_push($imgBuf, imagecreatefromgif(dirname(__FILE__) . '/views/img/discover.gif'));
        }
        if ($adn_jcb) {
            array_push($imgBuf, imagecreatefromgif(dirname(__FILE__) . '/views/img/jcb.gif'));
        }
        if ($adn_diners) {
            array_push($imgBuf, imagecreatefromgif(dirname(__FILE__) . '/views/img/diners.gif'));
        }
        $iOut = imagecreatetruecolor('86', ceil(count($imgBuf) / 2) * 26);
        $bgColor = imagecolorallocate($iOut, 255, 255, 255);
        imagefill($iOut, 0, 0, $bgColor);
        foreach ($imgBuf as $i => $img) {
            imagecopy($iOut, $img, ($i % 2 == 0 ? 0 : 49) - 1, floor($i / 2) * 26 - 1, 0, 0, imagesx($img), imagesy($img));
            imagedestroy($img);
        }
        imagejpeg($iOut, dirname(__FILE__) . '/views/img/combo.jpg', 100);
    }

    public function getContent()
    {
        $this->_postProcess();
        $output = $this->_displayForm();
        return $this->html . $output;
    }

    private function _displayForm()
    {
        $this->applyUpdates();
        $this->prepareAdminVars();

        $topMenuDisplay = $this->display(__FILE__, 'views/templates/admin/top_menu.tpl');
        $leftMenuDisplay = $this->display(__FILE__, 'views/templates/admin/left_menu.tpl');

        $basicSettingsDisplay = $this->display(__FILE__, 'views/templates/admin/basic_settings.tpl');
        $captureTransactionDisplay = $this->display(__FILE__, 'views/templates/admin/capture_transaction.tpl');
        $refundTransactionDisplay = $this->display(__FILE__, 'views/templates/admin/refund_transaction.tpl');

        $bottomSettingsDisplay = $this->display(__FILE__, 'views/templates/admin/bottom_menu.tpl');
        return $topMenuDisplay . $leftMenuDisplay . $basicSettingsDisplay . $captureTransactionDisplay . $refundTransactionDisplay . $bottomSettingsDisplay;
    }

    private function prepareAdminVars()
    {
        $states = OrderState::getOrderStates((int) ($this->context->cookie->id_lang));

        $displayUpgradeCheck = '';
        if (file_exists(dirname(__FILE__) . '/PrestoChangeoClasses/PrestoChangeoUpgrade.php')) {
            if (!in_array('PrestoChangeoUpgrade', get_declared_classes())) {
                require_once(dirname(__FILE__) . '/PrestoChangeoClasses/PrestoChangeoUpgrade.php');
            }
            $initFile = new PrestoChangeoUpgrade($this, $this->_path, $this->full_version);

            $upgradeCheck = $initFile->displayUpgradeCheck('HIPS');
            if (isset($upgradeCheck) && !empty($upgradeCheck)) {
                $displayUpgradeCheck = $upgradeCheck;
            }
        }

        $getModuleRecommendations = '';
        if (file_exists(dirname(__FILE__) . '/PrestoChangeoClasses/PrestoChangeoUpgrade.php')) {

            if (!in_array('PrestoChangeoUpgrade', get_declared_classes())) {
                require_once(dirname(__FILE__) . '/PrestoChangeoClasses/PrestoChangeoUpgrade.php');
            }
            $initFile = new PrestoChangeoUpgrade($this, $this->_path, $this->full_version);

            $getModuleRecommendations = $initFile->getModuleRecommendations('HIPS');
        }

        $logoPrestoChangeo = '';
        $contactUsLinkPrestoChangeo = '';
        if (file_exists(dirname(__FILE__) . '/PrestoChangeoClasses/PrestoChangeoUpgrade.php')) {
            if (!in_array('PrestoChangeoUpgrade', get_declared_classes())) {
                require_once(dirname(__FILE__) . '/PrestoChangeoClasses/PrestoChangeoUpgrade.php');
            }
            $initFile = new PrestoChangeoUpgrade($this, $this->_path, $this->full_version);


            $logoPrestoChangeo = $initFile->getPrestoChangeoLogo();
            $contactUsLinkPrestoChangeo = $initFile->getContactUsOnlyLink();
        }

        $id_shop = Shop::getContextShopID();
        $this->context->smarty->assign(array(
            'base_uri' => __PS_BASE_URI__,
            'displayUpgradeCheck' => $displayUpgradeCheck,
            'getModuleRecommendations' => $getModuleRecommendations,
            'id_lang' => $this->context->cookie->id_lang,
            'id_shop' => $id_shop,
            'id_employee' => $this->context->cookie->id_employee,
            'hips_private' => $this->hips_private,
            'hips_public' => $this->hips_public,
            'hips_type' => $this->hips_type,
            'hips_payment_page' => $this->hips_payment_page,
            'hips_auth_status' => $this->hips_auth_status,
            'hips_ac_status' => $this->hips_ac_status,
            'hips_secure_key' => $this->hips_secure_key,
            'hips_ft' => $this->hips_ft,
            'hips_ft_email' => $this->hips_ft_email,
            'hips_get_address' => $this->hips_get_address,
            'hips_get_cvm' => $this->hips_get_cvm,
            'hips_show_left' => $this->hips_show_left,
            'hips_visa' => $this->hips_visa,
            'hips_mc' => $this->hips_mc,
            'hips_amex' => $this->hips_amex,
            'hips_discover' => $this->hips_discover,
            'hips_jcb' => $this->hips_jcb,
            'hips_diners' => $this->hips_diners,
            //'hips_enroute' => $this->hips_enroute,
            'states' => $states,
            'path' => $this->_path,
            'module_name' => $this->displayName,
            'module_dir' => _MODULE_DIR_,
            'module_basedir' => _MODULE_DIR_ . 'hipspayment/',
            'request_uri' => $_SERVER['REQUEST_URI'],
            'mod_version' => $this->version,
            'upgradeCheck' => (isset($upgradeCheck) && !empty($upgradeCheck) ? true : false),
            'logoPrestoChangeo' => $logoPrestoChangeo,
            'contactUsLinkPrestoChangeo' => $contactUsLinkPrestoChangeo
        ));
    }

    private function _postProcess()
    {
        if (Tools::isSubmit('submitChanges')) {
            if (Tools::getValue('hips_type') == 'AUTH_ONLY') {
                $hips_ac_status = Tools::getValue('hips_ac_status');
            } else {
                $_POST['hips_ac_status'] = 0;
                $hips_ac_status = 0;
            }

            if (!Configuration::updateValue('HIPS_AC_STATUS', $hips_ac_status) ||
                !Configuration::updateValue('HIPS_AUTH_STATUS', Tools::getValue('hips_auth_status')) ||
                !Configuration::updateValue('HIPS_PAYMENT_PAGE', Tools::getValue('hips_payment_page')) ||
                !Configuration::updateValue('HIPS_PRIVATE', Tools::getValue('hips_private')) ||
                !Configuration::updateValue('HIPS_PUBLIC', Tools::getValue('hips_public')) ||
                !Configuration::updateValue('HIPS_TYPE', Tools::getValue('hips_type')) ||
                !Configuration::updateValue('HIPS_FT', Tools::getValue('hips_ft')) ||
                !Configuration::updateValue('HIPS_FT_EMAIL', Tools::getValue('hips_ft_email')) ||
                !Configuration::updateValue('HIPS_GET_ADDRESS', Tools::getValue('hips_get_address')) ||
                !Configuration::updateValue('HIPS_GET_CVM', Tools::getValue('hips_get_cvm')) ||
                !Configuration::updateValue('HIPS_SHOW_LEFT', Tools::getValue('hips_show_left')) ||
                !Configuration::updateValue('HIPS_VISA', Tools::getValue('hips_visa')) ||
                !Configuration::updateValue('HIPS_MC', Tools::getValue('hips_mc')) ||
                !Configuration::updateValue('HIPS_AMEX', Tools::getValue('hips_amex')) ||
                !Configuration::updateValue('HIPS_DISCOVER', Tools::getValue('hips_discover')) ||
                !Configuration::updateValue('HIPS_JCB', Tools::getValue('hips_jcb')) ||
                !Configuration::updateValue('HIPS_DINERS', Tools::getValue('hips_diners')) ||
                !Configuration::updateValue('HIPS_ENROUTE', Tools::getValue('hips_enroute'))
            ) {
                $this->html .= $this->displayError($this->l('Cannot update settings'));
            } else {
                $this->html .= $this->displayConfirmation($this->l('Settings updated'));
            }
            $this->createCombo(
                Tools::getValue('hips_visa'), Tools::getValue('hips_mc'), Tools::getValue('hips_amex'), Tools::getValue('hips_discover'), Tools::getValue('hips_jcb'), Tools::getValue('hips_diners')
            );
        }
        $this->_refreshProperties();
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {

        if (Tools::getValue('configure') == $this->name) {
            //$this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/globalBack.css');
            $this->context->controller->addCSS($this->_path . 'views/css/specificBack.css');
        }
    }

    /**
     * Return path to http module directory.
     */
    public function getHttpPathModule()
    {
        return (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/' . $this->name . '/';
    }

    public function getRedirectBaseUrl()
    {


        $redirect_url = Context::getContext()->link->getPageLink('order-confirmation');
        return $redirect_url = strpos($redirect_url, '?') !== false ? $redirect_url . '&' : $redirect_url . '?';
    }

    public function doPayment($token)
    {
        $cart = $this->context->cart;
        $cookie = $this->context->cookie;

        $address_delivery = new Address((int) $cart->id_address_delivery);
        $address_billing = new Address((int) $cart->id_address_invoice);
        $customer = new Address();

        // get default currency
        $currency_module = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        // recalculate currency if Currency: User Selected
        if ($cart->id_currency != $currency_module) {
            $old_id = $cart->id_currency;
            $cart->id_currency = $currency_module;
            if (is_object($cookie))
                $cookie->id_currency = $currency_module;

            if ($this->getPSV() >= 1.5)
                $this->context->currency = new Currency($currency_module);

            $cart->update();
        }

        // get cart currency for set to ADN request
        $currencyOrder = new Currency($cart->id_currency);

        $products = $cart->getProducts();

        if ($this->getPSV() >= 1.4) {
            $shippingCost = number_format($cart->getOrderTotal(!Product::getTaxCalculationMethod(), Cart::ONLY_SHIPPING), 2, '.', '');

            if ($cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING) == $cart->getOrderTotal(true, Cart::BOTH)) {
                $shippingCost = 0;
            }
            $x_amount_wot = number_format($cart->getOrderTotal(false, Cart::BOTH), 2, '.', '');
            $x_amount = number_format($cart->getOrderTotal(true, Cart::BOTH), 2, '.', '');

            $tax = $x_amount - $x_amount_wot;
        } else {
            $shippingCost = number_format($cart->getOrderTotal(!Product::getTaxCalculationMethod(), 5), 2, '.', '');

            if ($cart->getOrderTotal(true, 3) == $cart->getOrderTotal(true, 4)) {
                $shippingCost = 0;
            }
            $x_amount_wot = number_format($cart->getOrderTotal(false, 3), 2, '.', '');
            $x_amount = number_format($cart->getOrderTotal(true, 3), 2, '.', '');

            $tax = $x_amount - $x_amount_wot;
        }

        $country = new Country(Tools::getValue('obp_id_country'), (int) (Configuration::get('PS_LANG_DEFAULT')));
        $state = Tools::getIsset('obp_id_state') ? new State(Tools::getValue('obp_id_state')) : '';

        $del_state = new State($address_delivery->id_state);
        $address_delivery->state = $del_state->iso_code;
        $i = 1;
        $id_lang = 0;
        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'en') {
                $id_lang = $language['id_lang'];
            }
        }
        if ($id_lang == $cart->id_lang) {
            $id_lang = 0;
        }


        $customerObj = new Customer($cart->id_customer);
        $x_email = $customerObj->email;

        $billingCountry = new Country($address_billing->id_country);
        $shippingCountry = new Country($address_delivery->id_country);
        $ip_address = $_SERVER['REMOTE_ADDR'];

        if ($shippingCost > 0) {
            $require_shipping = true;
        } else {
            $require_shipping = false;
        }
        $post_values = array(
            'cart_id' => $cart->id,
            'purchase_currency' => $currencyOrder->iso_code,
            'token' => $token,
            'amount' => $x_amount,
            'capture' => ($this->hips_type == 'AUTH_CAPTURE' ? true : false),
            'email' => $customerObj->email,
            'name' => Tools::getValue('hips_cc_fname'),
            'street' => !empty($address_billing->address1) ? $address_billing->address1 : $address_billing->address2,
            'postal_code' => !empty($address_billing->postcode) ? $address_billing->postcode : '',
            'country' => $billingCountry->iso_code,
            'ip_address' => $ip_address,
            'id_customer' => $cart->id_customer,
            'require_shipping' => $require_shipping,
            'shipping_address_firstname' => $address_delivery->firstname,
            'shipping_address_lastname' => $address_delivery->lastname,
            'shipping_address_addr' => !empty($address_delivery->address1) ? $address_delivery->address1 : $address_delivery->address2,
            'shipping_address_postal_code' => !empty($address_delivery->postcode) ? $address_delivery->postcode : '',
            'shipping_address_state' => $address_delivery->state,
            'shipping_address_city' => $address_delivery->city,
            'shipping_address_country' => $shippingCountry->iso_code,
            'shipping_address_email' => $customerObj->email,
            'shipping_address_phone' => !empty($address_delivery->phone) ? $address_delivery->phone : $address_delivery->phone_mobile,
        );

        $products = $cart->getProducts();
        $cartProducts = array();
        foreach ($products as $product) {
            $name = $product['name'];
            if ($id_lang > 0) {
                $eng_product = new Product($product['id_product']);
                $name = $eng_product->name[$id_lang];
            }
            $name = utf8_decode($name);
            $cartProducts[] = [
                "type" => $product['is_virtual'] ? "digital" : "physical",
                "sku" => $product['reference'],
                "name" => $name,
                "quantity" => $product['cart_quantity'],
                "unit_price" => number_format($product['price_wt'], 2, '.', '') * 100,
                "discount_rate" => 0,
                "vat_rate" => $product['rate'],
                "meta_data_1" => $product['attributes']
            ];
        }
        $id_carrier = $this->context->cart->id_carrier;
        $carrier = new Carrier((int) $id_carrier);

        $carrierName = $carrier->name[$id_lang];

        $carrier_tax = $carrier->getTaxesRate($address_delivery);

        $cartProducts[] = [
            "type" => "shipping_fee",
            "sku" => '1',
            "name" => $carrierName,
            "quantity" => '1',
            "unit_price" => number_format($shippingCost, 2, '.', '') * 100,
            "discount_rate" => 0,
            "vat_rate" => $carrier_tax
        ];

        $post_values['cartProducts'] = $cartProducts;
        $hipsAPI = new HipsPaymentAPI($this->hips_private, $this->hips_public);


        $doPaymentResp = $hipsAPI->doPayment($post_values);

        return $doPaymentResp;
    }

    public function doRefund($id_order, $is_void, $paymentId, $card, $amount)
    {


        $hipsAPI = new HipsPaymentAPI($this->hips_private, $this->hips_public);

        $result = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'hips_refunds WHERE id_order=' . (int) $id_order);
        $hipsOrderId = $result[0]['payment_id'];
        $post_values = array(
            'paymentId' => $paymentId,
            'hipsOrderId' => $hipsOrderId,
            'amount' => $amount,
            'is_void' => $is_void
        );

        $doRefundResp = $hipsAPI->doRefund($post_values);


        return $doRefundResp;
    }

    public function getHipsFilename()
    {
        return 'validation';
    }

    /**
     * Retrun validation for all version prestashop
     */
    public function getValidationLink($file = 'validation')
    {

        $validationLink = Context::getContext()->link->getModuleLink($this->name, $file, array(), true);

        return $validationLink;
    }

    public function hookPayment($params)
    {


        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        if ($this->hips_payment_page == 1) {
            require_once('controllers/front/validation.php');
            // hack for presta 1.5
            $_POST['module'] = 'hipspayment';
            $addresses = $this->context->customer->getAddresses($this->context->language->id);
            $this->context->smarty->assign('addresses', $addresses);

            hipspaymentvalidationModuleFrontController::prepareVarsView($this->context, $this, $hips_cc_err = '', time());
            $this->context->smarty->assign(array(
                'hips_payment_page' => 1,
                'hips_public' => $this->hips_public
            ));

            return $this->display(__FILE__, 'views/templates/front/validation.tpl');
        }


        $currencies = Currency::getCurrencies();


        $cart = $this->context->cart;
        $address = new Address((int) ($cart->id_address_invoice));
        $customer = new Customer((int) ($cart->id_customer));
        $state = new State((int) $address->id_state);
        $selectedCountry = (int) ($address->id_country);
        $address_delivery = new Address((int) ($cart->id_address_delivery));
        $countries = Country::getCountries((int) ($this->context->cookie->id_lang), true);
        $countriesList = '';
        foreach ($countries as $country) {
            $countriesList .= '<option value="' . ($country['id_country']) . '" ' . ($country['id_country'] == $selectedCountry ? 'selected="selected"' : '') . '>' . htmlentities($country['name'], ENT_COMPAT, 'UTF-8') . '</option>';
        }
        if ($address->id_state) {
            $this->context->smarty->assign('id_state', $state->iso_code);
        }


        $hips_cards = '';
        if ($this->hips_visa)
            $hips_cards .= $this->l('Visa') . ', ';
        if ($this->hips_mc)
            $hips_cards .= $this->l('Mastercard') . ', ';
        if ($this->hips_amex)
            $hips_cards .= $this->l('Amex') . ', ';
        if ($this->hips_discover)
            $hips_cards .= $this->l('Discover') . ', ';
        if ($this->hips_jcb)
            $hips_cards .= $this->l('JCB') . ', ';
        if ($this->hips_diners)
            $hips_cards .= $this->l('Diners') . ', ';
        //if ($this->hips_enroute)
        //    $hips_cards .= $this->l('Enroute') . ', ';


        $currencies = Currency::getCurrencies();
        $this->context->smarty->assign('countries_list', $countriesList);
        $this->context->smarty->assign('countries', $countries);
        $this->context->smarty->assign('address', $address);
        $this->context->smarty->assign('currencies', $currencies);


        $hips_filename = 'validation';
        $this->context->smarty->assign(array(
            'hips_payment_page' => $this->hips_payment_page,
            'currencies' => $currencies,
            'this_path' => $this->_path,
            'active' => ($this->hips_private != '' && $this->hips_public != '') ? true : false,
            'hips_visa' => $this->hips_visa,
            'hips_mc' => $this->hips_mc,
            'hips_amex' => $this->hips_amex,
            'hips_discover' => $this->hips_discover,
            'hips_jcb' => $this->hips_jcb,
            'hips_diners' => $this->hips_diners,
            'hips_public' => $this->hips_public,
            //'hips_enroute' => $this->hips_enroute,
            'hips_filename' => $hips_filename,
            'hips_get_address' => $this->hips_get_address,
            'hips_get_cvm' => $this->hips_get_cvm,
            'hips_cards' => $hips_cards,
            'this_path' => __PS_BASE_URI__ . 'modules/' . $this->name . '/',
            'this_validation_link' => $this->getValidationLink($hips_filename) . '',
        ));


        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
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

    /**
     * Send email error
     * $email - email which will be sent error
     * $cartObj - PS cart object
     * $errorText - text that return payment gateway
     */
    public function sendErrorEmail($email, $cartObj, $errorText, $template = 'error', $cartInfo = array(), $isCustomAddress = 0)
    {
        $customerObj = new Customer($cartObj->id_customer);
        $address = new Address((int) ($cartObj->id_address_invoice));

        $addressHTML = '';
        $addressHTML .= $this->l('Cart ') . '# ' . $cartObj->id . '<br /><br />' . '\n\r' . '\n\r';

        if (!empty($cartInfo['number'])) {
            $addressHTML .= $this->l('Card Number') . ': XXXX XXXX XXXX ' . $cartInfo['number'] . '<br /><br />' . '\n\r' . '\n\r';
        }
        if ($isCustomAddress) {
            $addressHTML .= $cartInfo['firstname'] . ' ' . $cartInfo['lastname'] . '<br />' . '\n\r';
            $addressHTML .= $cartInfo['address'] . '<br />' . '\n\r';
            $addressHTML .= $cartInfo['city'] . ' ' . $cartInfo['zip'] . '<br />' . '\n\r';

            if (!empty($cartInfo['country'])) {
                $country = new Country($cartInfo['country']);
                $addressHTML .= $this->l('Country') . ': ' . $country->name[$cartObj->id_lang] . '<br />' . '\n\r';
            } elseif (!empty($cartInfo['country_name']))
                $addressHTML .= $this->l('Country') . ': ' . $cartInfo['country_name'] . '<br />' . '\n\r';

            if (!empty($cartInfo['state'])) {
                $state = new State($cartInfo['state']);
                $addressHTML .= $this->l('State') . ': ' . $state->name . '<br />' . '\n\r';
            } elseif (!empty($cartInfo['state_name']))
                $addressHTML .= $this->l('State') . ': ' . $cartInfo['state_name'] . '<br />' . '\n\r';
        } else {
            $addressHTML .= $address->firstname . ' ' . $address->lastname . '<br />' . '\n\r';
            $addressHTML .=!empty($address->company) ? $address->company . '<br />' . '\n\r' : '';
            $addressHTML .= $address->address1 . ' ' . $address->address2 . '<br />' . '\n\r';
            $addressHTML .= $address->postcode . ' ' . $address->city . '<br />' . '\n\r';

            if (!empty($address->country)) {
                $addressHTML .= $this->l('Country') . ': ' . $address->country . '<br />' . '\n\r';
            }
            if (!empty($address->id_state)) {
                $state = new State($address->id_state);
                $addressHTML .= $this->l('State') . ': ' . $state->name . '<br />' . '\n\r';
            }
        }

        $cartHTML = '<table cellpadding="2">' . '\n\r';
        foreach ($cartObj->getProducts() as $product) {
            $cartHTML .= '<tr>';
            $cartHTML .= '<td> ' . $product['quantity'] . '</td>';
            $cartHTML .= '<td>x</td>';
            $cartHTML .= '<td> ' . Tools::displayPrice($product['price']) . '</td>';
            $cartHTML .= '<td> ' . Tools::displayPrice($product['total']) . '</td>';

            $cartHTML .= '<td> ' . $product['name'] . '</td>';
            $cartHTML .= '</tr>' . '\n\r';
        }

        $cartHTML .= '<tr>';
        $cartHTML .= '<td colspan="2"></td>';

        $cartHTML .= '<td align="right"> ' . $this->l('Total') . '</td>';
        $cartHTML .= '<td> ' . Tools::displayPrice($cartObj->getOrderTotal()) . '</td>';
        $cartHTML .= '</tr>' . '\n\r';

        $cartHTML .= '</table>';
        Mail::Send(Language::getIdByIso('en'), $template, $this->l('Transaction failed'), array(
            '{customer_email}' => $customerObj->email,
            '{customer_ip}' => $_SERVER['REMOTE_ADDR'],
            '{error}' => $errorText,
            '{cartHTML}' => $cartHTML,
            '{cartTXT}' => strip_tags($cartHTML),
            '{addressHTML}' => $addressHTML,
            '{addressTXT}' => strip_tags($addressHTML)
            ), $email, null, null, null, null, null, _PS_MODULE_DIR_ . Tools::strtolower($this->name) . '/views/templates/emails/'
        );
    }

    /**
     * get version of PrestaShop
     * return float value version
     */
    public function getPSV()
    {
        return (float) Tools::substr($this->getRawPSV(), 0, 3);
    }

    /**
     * get raw version of PrestaShop
     */
    private function getRawPSV()
    {
        return _PS_VERSION_;
    }
}
