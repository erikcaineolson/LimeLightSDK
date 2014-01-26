<?php
class Transaction extends LimeLight {
    /***************************************************\
    |													|
    |	Available inputs for GetResponse				|
    |	required fields [optional fields]				|
    |		NewOrder									|
    |													|
    \***************************************************/
    function __construct($api_username, $api_password, $log_directory = '', $log_file = '', $log_delimiter = '|'){
        // set the parent variables
        parent::__construct($api_username, $api_password, $log_directory, $log_file, $log_delimiter);

        parent::$fullurl = parent::$baseurl . 'transact.php';

        if($output_type == 'array' || $output_type == 'string' || $output_type == 'xml'){
            parent::$output_as = $output_type;
        }else{
            parent::$output_as = 'string';
        }

        $this->$fullurl = parent::$fullurl;
        $this->$output_as = parent::$output_as;
        $this->$password = parent::$password;
        $this->$username = parent::$username;
    }

    private function NewOrder($first_name, $last_name, $shipping_address1, $shipping_address2, $shipping_city, $shipping_state, $shipping_zip, $shipping_country, $phone, $email, $credit_card_type, $credit_card_number, $credit_card_exp_mmyy, $credit_card_cvv, $ip_address, $product_id, $campaign_id, $shipping_id, $paypal_token = '', $paypal_payer_id = '', $check_account = '', $check_routing = '', $billing_first_name = '', $billing_last_name = '', $billing_address1 = '', $billing_address2 = '', $billing_city = '', $billing_state = '', $billing_zip = '', $billing_country = '', $upsell_count = 0, $upsell_product_ids = '', $dynamic_product_price_array = '', $notes = '', $product_qty_array = '', $force_gateway_id = '', $thm_session_id = '', $total_installments = '', $afid = '', $sid = '', $affid = '', $c1 = '', $c2 = '', $c3 = '', $aid = '', $opt = '', $click_id = '', $created_by = ''){
        $fields = NULL;
        $values = NULL;

        $product_list = NULL;
        $tran_type = NULL;

        $tran_type = 'Sale';

        $fields = array('firstName',
            'lastName',
            'shippingAddress1',
            'shippingAddress2',
            'shippingCity',
            'shippingState',
            'shippingZip',
            'shippingCountry',
            'phone',
            'email',
            'creditCardType',
            'creditCardNumber',
            'expirationDate',
            'CVV',
            'tranType',
            'ipAddress',
            'productId',
            'campaignId',
            'shippingId',
            'upsellCount'
        );

        $values = array($first_name,
            $last_name,
            $shipping_address1,
            $shipping_address2,
            $shipping_city,
            $shipping_state,
            $shipping_zip,
            $shipping_country,
            $phone,
            $email,
            $credit_card_type,
            $credit_card_number,
            $credit_card_exp_mmyy,
            $credit_card_cvv,
            $tran_type,
            $ip_address,
            $product_id,
            $campaign_id,
            $shipping_id,
            $upsell_count
        );

        // non-credit card payments
        if(strtolower($credit_card_type) == 'paypal'){
            array_push($fields, 'paypal_token', 'paypal_payer_id');
            array_push($values, $paypal_token, $paypal_payer_id);
        }else if($credit_card_type == 'checking' || $credit_card_type == 'eft_germany'){
            array_push($fields, 'checkAccount', 'checkRouting');
            array_push($values, $check_account, $check_routing);
        }

        // billing info
        if($billing_first_name == ''){ $billing_first_name = $first_name; }
        if($billing_last_name == ''){ $billing_last_name = $last_name; }
        if($billing_address1 == ''){ $billing_address1 = $shipping_address1; }
        if($billing_address2 == ''){ $billing_address2 = $shipping_address2; }
        if($billing_city == ''){ $billing_city = $shipping_city; }
        if($billing_state == ''){ $billing_state = $shipping_state; }
        if($billing_zip == ''){ $billing_zip = $shipping_zip; }
        if($billing_country == ''){ $billing_country = $shipping_country; }

        array_push($fields, 'billingFirstName', 'billingLastName', 'billingAddress1', 'billingAddress2', 'billingCity', 'billingState', 'billingZip', 'billingCountry');
        array_push($values, $billing_first_name, $billing_last_name, $billing_address1, $billing_address2, $billing_city, $billing_state, $billing_zip, $billing_country);

        // upsell product ids
        if(($upsell_count * 1) > 0){
            array_push($fields, 'upsellProductIds');
            array_push($values, $upsell_product_ids);

            $product_list = explode(',', $upsell_product_ids);
            array_push($product_list, $product_id);
        }else{
            $product_list = $product_id;
        }

        // dynamic pricing
        if(is_array($dynamic_product_price_array)){
            if(is_array($product_list)){
                for($i = 0; $i < count($dynamic_product_price_array); $i++){
                    array_push($fields, 'dynamic_product_price_' . $product_list[$i]);
                    array_push($values, $dynamic_product_price_array[$i]);
                }
            }else{
                array_push($fields, 'dynamic_product_price_' . $product_list);
                array_push($values, $dynamic_product_price_array[0]);
            }
        }

        // extra products
        if(is_array($product_qty_array)){
            if(is_array($product_list)){
                for($i = 0; $i < count($product_qty_array); $i++){
                    array_push($fields, 'product_qty_' . $product_list[$i]);
                    array_push($values, $product_qty_array[$i]);
                }
            }else{
                array_push($fields, 'product_qty_' . $product_list);
                array_push($values, $product_qty_array[$i]);
            }
        }else{
            if($product_qty_array != ''){
                array_push($fields, 'product_qty_' . $product_list);
                array_push($values, $product_qty_array);
            }
        }

        // extra gateway stuff
        if($force_gateway_id != ''){
            array_push($fields, 'forceGatewayId');
            array_push($values, $force_gateway_id);
        }

        if($total_installments != '' && ($total_installments * 1) > 0){
            array_push($fields, 'total_installments');
            array_push($values, $total_installments);
        }

        // threat matrix session id
        if($thm_session_id != ''){
            array_push($fields, 'thm_session_id');
            array_push($values, $thm_session_id);
        }

        // notes
        if($notes != ''){
            array_push($fields, 'notes');
            array_push($values, $notes);
        }

        // affiliate/systems tracking
        if($afid != ''){
            array_push($fields, 'AFID');
            array_push($values, $afid);
        }

        if($sid != ''){
            array_push($fields, 'SID');
            array_push($values, $sid);
        }

        if($affid != ''){
            array_push($fields, 'AFFID');
            array_push($values, $affid);
        }

        if($c1 != ''){
            array_push($fields, 'C1');
            array_push($values, $c1);
        }

        if($c2 != ''){
            array_push($fields, 'C2');
            array_push($values, $c2);
        }

        if($c3 != ''){
            array_push($fields, 'C3');
            array_push($values, $c3);
        }

        if($aid != ''){
            array_push($fields, 'AID');
            array_push($values, $aid);
        }

        if($opt != ''){
            array_push($fields, 'OPT');
            array_push($values, $opt);
        }

        if($click_id != ''){
            array_push($fields, 'click_id');
            array_push($values, $click_id);
        }

        // created by
        if($created_by != ''){
            array_push($fields, 'createdBy');
            array_push($values, $created_by);
        }

        $this->$method = 'NewOrder';
        $this->$response = $this->APIConnect($fields, $values);
    }	// END NewOrder()

    private function NewOrderCardOnFile($product_id, $campaign_id, $shipping_id, $previous_order_id, $initialize_new_subscription = '', $upsell_count = 0, $upsell_product_ids = '', $dynamic_product_price_array = '', $notes = '', $product_qty_array = '', $force_gateway_id = '', $created_by = ''){
        $fields = NULL;
        $values = NULL;

        $product_list = NULL;
        $tran_type = NULL;

        $tran_type = 'Sale';

        $fields = array('tranType',
            'productId',
            'campaignId',
            'shippingId',
            'previousOrderId',
            'upsellCount'
        );

        $values = array($tran_type,
            $product_id,
            $campaign_id,
            $previous_order_id,
            $shipping_id,
            $upsell_count
        );

        // upsell product ids
        if(($upsell_count * 1) > 0){
            array_push($fields, 'upsellProductIds');
            array_push($values, $upsell_product_ids);

            $product_list = explode(',', $upsell_product_ids);
            array_push($product_list, $product_id);
        }else{
            $product_list = $product_id;
        }

        // dynamic pricing
        if(is_array($dynamic_product_price_array)){
            if(is_array($product_list)){
                for($i = 0; $i < count($dynamic_product_price_array); $i++){
                    array_push($fields, 'dynamic_product_price_' . $product_list[$i]);
                    array_push($values, $dynamic_product_price_array[$i]);
                }
            }else{
                array_push($fields, 'dynamic_product_price_' . $product_list);
                array_push($values, $dynamic_product_price_array[0]);
            }
        }

        // extra products
        if(is_array($product_qty_array)){
            if(is_array($product_list)){
                for($i = 0; $i < count($product_qty_array); $i++){
                    array_push($fields, 'prodct_qty_' . $product_list[$i]);
                    array_push($values, $product_qty_array[$i]);
                }
            }else{
                array_push($fields, 'product_qty_' . $product_list);
                array_push($values, $product_qty_array[$i]);
            }
        }else{
            if($product_qty_array != ''){
                array_push($fields, 'product_qty_' . $product_list);
                array_push($values, $product_qty_array);
            }
        }

        // extra gateway stuff
        if($force_gateway_id != ''){
            array_push($fields, 'forceGatewayId');
            array_push($values, $force_gateway_id);
        }

        // notes
        if($notes != ''){
            array_push($fields, 'notes');
            array_push($values, $notes);
        }

        // created by
        if($created_by != ''){
            array_push($fields, 'createdBy');
            array_push($values, $created_by);
        }

        $this->$method = 'NewOrderCardOnFile';
        $this->$response = $this->APIConnect($fields, $values);
    }	// END NewOrderCardOnFile()

    private function NewOrderWithProspect($credit_card_type, $credit_card_number, $credit_card_exp_mmyy, $credit_card_cvv, $product_id, $campaign_id, $shipping_id, $prospect_id, $billing_same_as_shipping = 'YES', $paypal_token = '', $paypal_payer_id = '', $check_account = '', $check_routing = '', $billing_first_name = '', $billing_last_name = '', $billing_address1 = '', $billing_address2 = '', $billing_city = '', $billing_state = '', $billing_zip = '', $billing_country = '', $upsell_count = 0, $upsell_product_ids = '', $dynamic_product_price_array = '', $notes = '', $product_qty_array = '', $force_gateway_id = '', $thm_session_id = '', $total_installments = '', $created_by = '', $missing_fields_array = ''){
        $fields = NULL;
        $values = NULL;

        $k = NULL;
        $v = NULL;

        $product_list = NULL;
        $tran_type = NULL;

        $tran_type = 'Sale';

        $fields = array('creditCardType',
            'creditCardNumber',
            'expirationDate',
            'CVV',
            'tranType',
            'prospectId',
            'billingSameAsShipping',
            'productId',
            'campaignId',
            'shippingId',
            'upsellCount'
        );

        $values = array($credit_card_type,
            $credit_card_number,
            $credit_card_exp_mmyy,
            $credit_card_cvv,
            $tran_type,
            $prospect_id,
            $billing_same_as_shipping,
            $product_id,
            $campaign_id,
            $shipping_id,
            $upsell_count
        );

        // missing fields
        if($missing_fields_array != ''){
            if(is_array($missing_fields_array)){
                foreach($missing_fields_array as $k => $v){
                    array_push($fields, $k);
                    array_push($values, $v);
                }
            }
        }

        // non-credit card payments
        if(strtolower($credit_card_type) == 'paypal'){
            array_push($fields, 'paypal_token', 'paypal_payer_id');
            array_push($values, $paypal_token, $paypal_payer_id);
        }else if($credit_card_type == 'checking' || $credit_card_type == 'eft_germany'){
            array_push($fields, 'checkAccount', 'checkRouting');
            array_push($values, $check_account, $check_routing);
        }

        // billing info
        if(strtoupper($billing_same_as_shipping) != 'YES'){
            array_push($fields, 'billingFirstName', 'billingLastName', 'billingAddress1', 'billingAddress2', 'billingCity', 'billingState', 'billingZip', 'billingCountry');
            array_push($values, $billing_first_name, $billing_last_name, $billing_address1, $billing_address2, $billing_city, $billing_state, $billing_zip, $billing_country);
        }

        // upsell product ids
        if(($upsell_count * 1) > 0){
            array_push($fields, 'upsellProductIds');
            array_push($values, $upsell_product_ids);

            $product_list = explode(',', $upsell_product_ids);
            array_push($product_list, $product_id);
        }else{
            $product_list = $product_id;
        }

        // dynamic pricing
        if(is_array($dynamic_product_price_array)){
            if(is_array($product_list)){
                for($i = 0; $i < count($dynamic_product_price_array); $i++){
                    array_push($fields, 'dynamic_product_price_' . $product_list[$i]);
                    array_push($values, $dynamic_product_price_array[$i]);
                }
            }else{
                array_push($fields, 'dynamic_product_price_' . $product_list);
                array_push($values, $dynamic_product_price_array[0]);
            }
        }

        // extra products
        if(is_array($product_qty_array)){
            if(is_array($product_list)){
                for($i = 0; $i < count($product_qty_array); $i++){
                    array_push($fields, 'prodct_qty_' . $product_list[$i]);
                    array_push($values, $product_qty_array[$i]);
                }
            }else{
                array_push($fields, 'product_qty_' . $product_list);
                array_push($values, $product_qty_array[$i]);
            }
        }else{
            if($product_qty_array != ''){
                array_push($fields, 'product_qty_' . $product_list);
                array_push($values, $product_qty_array);
            }
        }

        // extra gateway stuff
        if($force_gateway_id != ''){
            array_push($fields, 'forceGatewayId');
            array_push($values, $force_gateway_id);
        }

        if($total_installments != '' && ($total_installments * 1) > 0){
            array_push($fields, 'total_installments');
            array_push($values, $total_installments);
        }

        // threat matrix session id
        if($thm_session_id != ''){
            array_push($fields, 'thm_session_id');
            array_push($values, $thm_session_id);
        }

        // notes
        if($notes != ''){
            array_push($fields, 'notes');
            array_push($values, $notes);
        }

        // created by
        if($created_by != ''){
            array_push($fields, 'createdBy');
            array_push($values, $created_by);
        }

        $this->$method = 'NewOrderWithProspect';
        $this->$response = $this->APIConnect($fields, $values);
    }	// END NewOrderWithProspect()

    private function NewPropspect($email, $ip_address, $campaign_id, $first_name = '', $last_name = '', $address1 = '', $address2 = '', $city = '', $state = '', $zip = '', $country = '', $phone = '', $afid = '', $sid = '', $affid = '', $c1 = '', $c2 = '', $c3 = '', $aid = '', $opt = '', $click_id = '', $notes = ''){
        $fields = NULL;
        $values = NULL;

        $fields = array('email', 'ipAddress', 'campaignId');
        $values = array($email, $ip_address, $campaign_id);

        // optional contact info
        if($first_name != ''){
            array_push($fields, 'firstName');
            array_push($values, $first_name);
        }

        if($last_name != ''){
            array_push($fields, 'lastName');
            array_push($values, $last_name);
        }

        if($address1 != ''){
            array_push($fields, 'address1');
            array_push($values, $address1);
        }

        if($address2 != ''){
            array_push($fields, 'address2');
            array_push($values, $address2);
        }

        if($city != ''){
            array_push($fields, 'city');
            array_push($values, $city);
        }

        if($state != ''){
            array_push($fields, 'state');
            array_push($values, $state);
        }

        if($zip != ''){
            array_push($fields, 'zip');
            array_push($values, $zip);
        }

        if($country != ''){
            array_push($fields, 'country');
            array_push($values, $country);
        }

        if($phone != ''){
            array_push($fields, 'phone');
            array_push($values, $phone);
        }

        // notes
        if($notes != ''){
            array_push($fields, 'notes');
            array_push($values, $notes);
        }

        // affiliate/systems tracking
        if($afid != ''){
            array_push($fields, 'AFID');
            array_push($values, $afid);
        }

        if($sid != ''){
            array_push($fields, 'SID');
            array_push($values, $sid);
        }

        if($affid != ''){
            array_push($fields, 'AFFID');
            array_push($values, $affid);
        }

        if($c1 != ''){
            array_push($fields, 'C1');
            array_push($values, $c1);
        }

        if($c2 != ''){
            array_push($fields, 'C2');
            array_push($values, $c2);
        }

        if($c3 != ''){
            array_push($fields, 'C3');
            array_push($values, $c3);
        }

        if($aid != ''){
            array_push($fields, 'AID');
            array_push($values, $aid);
        }

        if($opt != ''){
            array_push($fields, 'OPT');
            array_push($values, $opt);
        }

        if($click_id != ''){
            array_push($fields, 'click_id');
            array_push($values, $click_id);
        }

        // created by
        if($created_by != ''){
            array_push($fields, 'createdBy');
            array_push($values, $created_by);
        }

        $this->$method = 'NewProspect';
        $this->$response = $this->APIConnect($fields, $values);
    }	// END New Prospect()

    // return Lime Light's response string
    // in whatever format is set
    public function GetResponse($do_what, $parameters){
        // instead of throwing an exception
        // allow Lime Light to respond with an error code
        // check for (and fix) errors that will break this code
        $tmp = NULL;

        $tmp = $parameters;
        unset($parameters);
        $parameters = $this->AssociativeArrayToArray($tmp);

        switch(strtolower($do_what)){
            case 'neworder':
            default:
                $this->NewOrder($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7], $parameters[8], $parameters[9], $parameters[10], $parameters[11], $parameters[12], $parameters[13], $parameters[14], $parameters[15], $parameters[16], $parameters[17], $parameters[18], $parameters[19], $parameters[20], $parameters[21], $parameters[22], $parameters[23], $parameters[24], $parameters[25], $parameters[26], $parameters[27], $parameters[28], $parameters[29], $parameters[30], $parameters[31], $parameters[32], $parameters[33], $parameters[34], $parameters[35], $parameters[36], $parameters[37], $parameters[38], $parameters[39], $parameters[40], $parameters[41], $parameters[42], $parameters[43], $parameters[44], $parameters[45], $parameters[46], $parameters[47]);
                break;
            case 'newordercardonfile':
                $this->NewOrderCardOnFile($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7], $parameters[8], $parameters[9], $parameters[10], $parameters[11]);
                break;
            case 'neworderwithprospect':
                $this->NewOrderWithProspect($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7], $parameters[8], $parameters[9], $parameters[10], $parameters[11], $parameters[12], $parameters[13], $parameters[14], $parameters[15], $parameters[16], $parameters[17], $parameters[18], $parameters[19], $parameters[20], $parameters[21], $parameters[22], $parameters[23], $parameters[24], $parameters[25], $parameters[26], $parameters[27], $parameters[28], $parameters[29], $parameters[30]);
                break;
            case 'newprospect':
                $this->NewPropspect($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7], $parameters[8], $parameters[9], $parameters[10], $parameters[11], $parameters[12], $parameters[13], $parameters[14], $parameters[15], $parameters[16], $parameters[17], $parameters[18], $parameters[19], $parameters[20], $parameters[21]);
                break;
        }	// END switch

        if($this->$response !== FALSE){
            switch($this->$output_as){
                case 'array':
                    $temp_str = $this->$response;
                    unset($this->$response);
                    $this->$response = $this->GetArray($temp_str);
                    break;
                case 'xml':
                    $temp_str = $this->$response;
                    unset($this->$response);
                    $this->$response = $this->GetXML($this->GetArray($temp_str));
                    break;
                case 'string':
                default:
                    // do nothing, all good!
                    break;
            }
        }

        return $this->$response;
    }   // END CLASS Transaction()
}?>