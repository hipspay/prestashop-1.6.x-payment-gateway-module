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
class HipsPaymentAPI
{

    protected $hips_private = '';
    protected $hips_public = '';

    /*
      protected $hips_create_orders_url = 'https://api.hips.com/v1/orders';
      protected $hips_view_orders_url = 'https://api.hips.com/v1/orders/(:id)';
      protected $hips_update_orders_url = 'https://api.hips.com/v1/orders/(:id)';
      protected $hips_capture_orders_url = 'https://api.hips.com/v1/orders/(:id)/fulfill';
      protected $hips_refund_orders_url = 'https://api.hips.com/v1/orders/(:id)/revoke';
     * */

    public function __construct($hips_private, $hips_public)
    {
        $this->hips_private = $hips_private;
        $this->hips_public = $hips_public;
    }
    /* Payment */

    public function doPayment($post_values)
    {

        /*
         *  "customer":{  
          "email":"' . $post_values['email'] . '",
          "name":"' . $post_values['name'] . '",
          "street_address":"' . $post_values['street'] . '",
          "postal_code":"' . $post_values['postal_code'] . '",
          "country":"' . $post_values['country'] . '",
          "ip_address":"' . $post_values['ip_address'] . '",
          },
          "user_session_id":"",
          "user_identifier":"' . $post_values['id_customer'] . '",
          "meta_data_1":"",
         * 
         */
        $json = '{  
            "source":"card_token",    
            "order_id":"' . $post_values['cart_id'] . '",   
            "purchase_currency":"' . $post_values['purchase_currency'] . '",
            "amount":"' . $post_values['amount'] * 100 . '",  
            "card_token":"' . $post_values['token'] . '",       
            "capture":"' . ($post_values['capture'] ? 'true' : 'false') . '",
            "customer":{  
                "email":"' . $post_values['email'] . '",
                "name":"' . utf8_encode($post_values['name']) . '",
                "street_address":"' . utf8_encode($post_values['street']) . '",
                "postal_code":"' . $post_values['postal_code'] . '",
                "country":"' . $post_values['country'] . '",
                "ip_address":"' . $post_values['ip_address'] . '"
            },
            "preflight":"true"
         }';
        /*
         * 
         * 
          "cart":{
          "items":[';

          foreach ($post_values['cartProducts'] as $prod) {
          $json .= '
          {
          "type":"' . $prod['type'] . '",
          "sku":"' . $prod['sku'] . '",
          "name":"' . $prod['name'] . '",
          "quantity":' . $prod['quantity'] . ',
          "unit_price":' . $prod['unit_price'] . ',
          "discount_rate":' . $prod['discount_rate'] . ',
          "vat_rate":' . $prod['vat_rate'] . (isset($prod['meta_data_1']) ? ',
          "meta_data_1":"' . $prod['meta_data_1'] . '"' : '' ) . '
          }' . ($prod['type'] == 'shipping_fee' ? '' : ',' ) . '
          ';
          }



          $json .= '
          ]
          },
          "shipping_address":{
          "given_name":"' . $post_values['shipping_address_firstname'] . '",
          "family_name":"' . $post_values['shipping_address_lastname'] . '",
          "street_address":"' . $post_values['shipping_address_addr'] . '",
          "postal_code":"' . $post_values['shipping_address_postal_code'] . '",
          "state":"' . $post_values['shipping_address_state'] . '",
          "city":"' . $post_values['shipping_address_city'] . '",
          "country":"' . $post_values['shipping_address_country'] . '",
          "email":"' . $post_values['shipping_address_email'] . '",
          "phone":"' . $post_values['shipping_address_phone'] . '",
          },
          "require_shipping":' . ($post_values['require_shipping'] ? 'true' : 'false') . ',
          "hooks":{
          "user_return_url_on_success":"",
          "user_return_url_on_fail":"",
          }
         */

        $ch = curl_init('https://api.hips.com/v1/payments');
        curl_setopt($ch, CURLOPT_USERPWD, $this->hips_private . ":");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json))
        );
        $result = curl_exec($ch);
        if ($result === false) {
            $return = array();
            $return['error']['type'] = 'Unkown error!';
            $return['error']['message'] = 'Unkown error!';
            return $return;
        } else {

            $json_result = json_decode($result, true);
            return $json_result;
        }
    }
    /* Capture if module is set to Authorize only */

    public function doCapture($post_values)
    {
        $json = '';
        $ch = curl_init('https://api.hips.com/v1/payments/' . $post_values['hipsOrderId'] . '/capture');
        //echo 'https://api.hips.com/v1/payments/' . $post_values['hipsOrderId'] . '/capture';
        curl_setopt($ch, CURLOPT_USERPWD, $this->hips_private . ":");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json))
        );
        $result = curl_exec($ch);
        if ($result === false) {
            $return = array();
            $return['error']['type'] = 'Unkown error!';
            $return['error']['message'] = 'Unkown error!';
            return $return;
        } else {

            $json_result = json_decode($result, true);

            return $json_result;
        }
    }
    /* Refund Order */

    public function doRefund($post_values)
    {
        $post_values['amount'] = Tools::ps_round($post_values['amount'], 2);
        $json = '{
            "id":"' . $post_values['hipsOrderId'] . '"' . ($post_values['is_void'] ? '' : ',
            "amount":"' . $post_values['amount'] * 100 . '"' ) . '
         }';

        $ch = curl_init('https://api.hips.com/v1/payments/' . $post_values['hipsOrderId'] . '/refund');
        curl_setopt($ch, CURLOPT_USERPWD, $this->hips_private . ":");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json))
        );
        $result = curl_exec($ch);
        if ($result === false) {
            $return = array();
            $return['error']['type'] = 'Unkown error!';
            $return['error']['message'] = 'Unkown error!';
            return $return;
        } else {

            $json_result = json_decode($result, true);

            return $json_result;
        }
    }
}
