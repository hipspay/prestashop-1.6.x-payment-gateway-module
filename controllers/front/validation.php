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

class hipspaymentvalidationModuleFrontController extends ModuleFrontController
{

    public $ssl = true;

    public function setMedia()
    {
        parent::setMedia();
        $this->addJS(__PS_BASE_URI__ . 'modules/hipspayment/views/js/hipspayment.js');
        $this->addJS(__PS_BASE_URI__ . 'modules/hipspayment/views/js/statesManagement.js');
        $this->addCSS(__PS_BASE_URI__ . 'modules/hipspayment/views/css/hipspayment.css');
    }

    public function initContent()
    {
        if (Configuration::get('HIPS_SHOW_LEFT') == 0) {
            $this->display_column_left = false;
        }
        parent::initContent();
    }

    public function init()
    {
        if (Configuration::get('HIPS_SHOW_LEFT') == 0) {
            $this->display_column_left = false;
        }
        parent::init();
    }

    public function getAddressInformation($id_address)
    {
        $address = new Address($id_address);
        $state = new State($address->id_state);


        return array(
            'email' => $this->context->customer->email,
            'lastname' => Tools::htmlentitiesUTF8($address->lastname),
            'firstname' => Tools::htmlentitiesUTF8($address->firstname),
            'vat_number' => Tools::htmlentitiesUTF8($address->vat_number),
            'dni' => Tools::htmlentitiesUTF8($address->dni),
            'address1' => Tools::htmlentitiesUTF8($address->address1),
            'address2' => Tools::htmlentitiesUTF8($address->address2),
            'company' => Tools::htmlentitiesUTF8($address->company),
            'postcode' => Tools::htmlentitiesUTF8($address->postcode),
            'city' => Tools::htmlentitiesUTF8($address->city),
            'phone' => Tools::htmlentitiesUTF8($address->phone),
            'phone_mobile' => Tools::htmlentitiesUTF8($address->phone_mobile),
            'id_country' => (int) ($address->id_country),
            'name_country' => $address->country,
            'id_state' => (int) ($address->id_state),
            'name_state' => ($state->name),
            'id_address' => $address->id
        );
    }

    public function sendError($hips, $error_code, $error_message)
    {
        $hips_cc_err = $hips->l('There was an error processing your payment') .
            '<br />Details: ' . $error_code .
            ' <br/> ' . $error_message;

        if ($hips->hips_ft == 1 && !empty($hips->hips_ft_email)) {
            $cartInfo = array();

            if ($hips->hips_get_address) {
                $cartInfo = array(
                    'firstname' => Tools::getValue('hips_cc_fname'),
                    'lastname' => Tools::getValue('hips_cc_lname'),
                    'address' => Tools::getValue('hips_cc_address'),
                    'city' => Tools::getValue('hips_cc_city'),
                    'state' => Tools::getValue('hips_id_state'),
                    'country' => Tools::getValue('hips_id_country'),
                    'zip' => Tools::getValue('hips_cc_zip')
                );
            }

            $cartInfo['number'] = Tools::substr(Tools::getValue('hips_cc_number'), -4);

            $hips->sendErrorEmail($hips->hips_ft_email, $cart, $error_code . ' - ' . $error_message, 'error', $cartInfo, $hips->hips_get_address);
        }

        if ($hips->hips_payment_page) {
            echo $hips_cc_err;
            exit();
        } else {
            echo $hips_cc_err;
            exit();
        }
    }

    public function postProcess()
    {
        $cart = $this->context->cart;
        $link = new Link();
        $psv = (float) (Tools::substr(_PS_VERSION_, 0, 3));

        $confirm = Tools::getValue('confirm');

        $hips = new HipsPayment();

        /* Validate order */
        $time = time();
        $hips_cc_err = '';

        if ($confirm) {

            $orderToken = Tools::getValue('token');
            $cardFingerprint = Tools::getValue('fingerprint');
            $cardMask = Tools::getValue('mask');

            $doPaymentResp = $hips->doPayment($orderToken); //explode('|', $obp->doPayment());
  
            if (isset($doPaymentResp['error']) && !empty($doPaymentResp['error'])) {
                $this->sendError($hips, $doPaymentResp['error']['type'], $doPaymentResp['error']['message']);           
            }
            if (isset($doPaymentResp['status']) && (($doPaymentResp['status'] == 'successful') || ($doPaymentResp['status'] == 'authorized'))) {
                /* Success */
                $customer = new Customer((int) ($cart->id_customer));

                if ($hips->getPSV() >= 1.4) {
                    $x_amount = number_format($cart->getOrderTotal(true, Cart::BOTH), 2, '.', '');
                } else {
                    $x_amount = number_format($cart->getOrderTotal(true, 3), 2, '.', '');
                }
                $x_amount = $doPaymentResp['amount'] / 100;
                
                $total = $x_amount;

                $hips->validateOrder(
                    (int) ($cart->id), $hips->hips_type == 'AUTH_ONLY' ? $hips->hips_auth_status : _PS_OS_PAYMENT_, $total, $hips->displayName, null, array(), null, false, $customer->secure_key);

                $order = new Order((int) ($hips->currentOrder));

                $message = new Message();
                $message->message = ($hips->hips_type == 'AUTH_ONLY' ? $hips->l('Authorization Only - ') : '') .
                    $hips->l('Transaction ID: ') .
                    $doPaymentResp['id'] .
                    $hips->l(' - Last 4 digits of the card: ') .
                    Tools::substr($cardMask, -4) .                    
                    $hips->l(' - Card Auth Code Response: ') .
                    $doPaymentResp['authorization_code'] .
                    $message->id_customer = $cart->id_customer;
                $message->id_order = $order->id;
                $message->private = 1;
                $message->id_employee = 0;
                $message->id_cart = $cart->id;
                $message->add();

                Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . "hips_refunds` VALUES " .
                    "('$order->id','" .
                     (int)($cart->id) . "','" .
                    pSQL($cardMask) . "','" .
                    pSQL($cardFingerprint) . "','" .
                    pSQL($orderToken) . "','" .
                    pSQL($doPaymentResp['status']) . "','" .
                    pSQL($doPaymentResp['id']) . "','" .
                    pSQL($doPaymentResp['order']['id']) . "','" .
                    ($hips->hips_type == 'AUTH_ONLY' ? '0' : '1') . "','" .
                    pSQL($doPaymentResp['authorization_code']) .
                    "')");
                
                if (!$hips->hips_payment_page) {
                    Tools::redirectLink($hips->getRedirectBaseUrl() . 'key=' . $customer->secure_key . '&id_cart=' . (int) ($cart->id) . '&id_module=' . (int) ($hips->id) . '&id_order=' . (int) ($hips->currentOrder));
                } else {
                    /**
                     * Redirect to ordr-confirmation - ajax verision 
                     * $adn->_adn_payment_page == 1
                     */
                    @ob_end_clean();

                    echo 'url:' . $hips->getRedirectBaseUrl() . 'key=' . $customer->secure_key . '&id_cart=' . (int) ($cart->id) . '&id_module=' . (int) ($hips->id) . '&id_order=' . (int) ($hips->currentOrder);
                    exit();
                }
            } else {
                /* Unknown error */
                $this->sendError($hips, $hips->l('Unknown Error'), $hips->l('Unknown Error - no error message or ok result from HIPSs'));
            }

            $time = mktime(0, 0, 0, Tools::getValue('hips_cc_Month'), 1, Tools::getValue('hips_cc_Year'));
            $address = new Address((int) ($cart->id_address_invoice));
            $selectedState = (int) (Tools::getValue('hips_id_state'));
            $selectedCountry = (int) (Tools::getValue('hips_id_country'));
            $this->context->smarty->assign('id_state', $selectedState);
        }

        self::prepareVarsView($this->context, $hips, $hips_cc_err, $time);

        $this->setTemplate('validation.tpl');
    }

    public static function prepareVarsView($context = null, $hips = null, $hips_cc_err, $time)
    {
        $years = array();
        for ($i = date('Y'); $i < date('Y') + 10; $i++) {
            $years[$i] = $i;
        }

        $months = array();
        for ($i = 1; $i < 13; $i++) {
            $pi = $i < 10 ? '0' . $i : $i;
            $months[$pi] = $pi;
        }

        $cart = $context->cart;

        if (is_null($hips)) {
            $hips = new HipsPayment();
        }

        $currency_module = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        // recalculate currency if Currency: User Selected
        if ($cart->id_currency != $currency_module) {
            $old_id = $cart->id_currency;
            $cart->id_currency = $currency_module;
            if (is_object($context->cookie)) {
                $context->cookie->id_currency = $currency_module;
            }
            $context->currency = new Currency($currency_module);

            $cart->update();
        }

        $confirm = Tools::getValue('confirm');

        if (!$confirm) {
            $address = new Address((int) ($cart->id_address_invoice));
            $customer = new Customer((int) ($cart->id_customer));
            $state = new State($address->id_state);
            $selectedCountry = (int) ($address->id_country);
        } else {
            $selectedCountry = (int) (Tools::getValue('adn_id_country'));
            $address = new Address((int) ($cart->id_address_invoice));
        }

        $address = new Address(intval($cart->id_address_invoice));
        $selectedState = intval(Tools::getValue('hips_id_state'));
        $selectedCountry = intval(Tools::getValue('hips_id_country'));
        $context->smarty->assign('id_state', $selectedState);

        $countries = Country::getCountries((int) ($context->cookie->id_lang), true);
        $countriesList = '';
        foreach ($countries as $country) {
            $countriesList .= '<option value="' . (int) ($country['id_country']) . '" ' . ($country['id_country'] == $selectedCountry ? 'selected="selected"' : '') . '>' . htmlentities($country['name'], ENT_COMPAT, 'UTF-8') . '</option>';
        }

        $this_path_ssl = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/hipspayment/';

        $hips_filename = $hips->getHipsFilename();
        $currencies = Currency::getCurrencies();
        $context->smarty->assign('countries_list', $countriesList);
        $context->smarty->assign('countries', $countries);
        $context->smarty->assign('address', $address);
        $context->smarty->assign('currencies', $currencies);

        $context->smarty->assign(
            array(
                'hips_public' => $hips->hips_public,
                'action' => $context->link->getModuleLink($hips->name, 'validation', array(), true),
                'hips_payment_page' => $hips->hips_payment_page,
                'hips_filename' => $hips_filename,
                'hips_total' => number_format($cart->getOrderTotal(true, 3), 2, '.', ''),
                'this_path_ssl' => $hips->getHttpPathModule(),
                'hips_cc_fname' => $confirm ? Tools::getValue('hips_cc_fname') : "$address->firstname",
                'hips_cc_lname' => $confirm ? Tools::getValue('hips_cc_lname') : "$address->lastname",
                'hips_cc_address' => $confirm ? Tools::getValue('hips_cc_address') : $address->address1,
                'hips_cc_city' => $confirm ? Tools::getValue('hips_cc_city') : $address->city,
                'hips_cc_state' => $confirm ? Tools::getValue('hips_cc_state') : $state->iso_code,
                'hips_cc_zip' => $confirm ? Tools::getValue('hips_cc_zip') : $address->postcode,
                'hips_cc_number' => Tools::getValue('hips_cc_number'),
                'hips_cc_cvv' => Tools::getValue('hips_cc_cvv'),
                'hips_cc_err' => $hips_cc_err,
                'hips_get_address' => Configuration::get('HIPS_GET_ADDRESS'),
                'hips_get_cvv' => Configuration::get('HIPS_GET_CVM'),
                'hips_visa' => $hips->hips_visa,
                'hips_mc' => $hips->hips_mc,
                'hips_amex' => $hips->hips_amex,
                'hips_discover' => $hips->hips_discover,
                'hips_jcb' => $hips->hips_jcb,
                'hips_diners' => $hips->hips_diners,
                'hips_enroute' => $hips->hips_enroute,
                'hipsyears' => $years,
                'hips_months' => $months,
                'hips_ajax_path' => __PS_BASE_URI__ . 'modules/hipspayment/validation.php',
                'time' => $time,
                'hips_psv' => $hips->getPSV(),
                'form_action_url' => $this_path_ssl . 'validation.php'
            )
        );
        if ((int) (ceil(number_format($cart->getOrderTotal(true, 3), 2, '.', ''))) == 0) {
            Tools::redirect('order.php?step=1');
        }
        $context->smarty->assign('this_path', __PS_BASE_URI__ . 'modules/hipspayment/');
    }
}
