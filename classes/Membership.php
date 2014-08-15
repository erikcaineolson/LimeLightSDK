<?php
    class Membership extends LimeLight {
        /*******************************************************\
        |   Allowed options for GetResponse():                  |
        |   **params are req'd unless [otherwise noted]**       |
        |       CalculateOrderRefund (order id)                 |
        |       CopyProduct (product id, [new name])            |
        |       FindOrder (campaign id, start date,             |
        |           end date, [start time], [end time],         |
        |           [search type], [return type],               |
        |           [criteria]                                  |
        |       FindOverdueOrder (days overdue)                 |
        |       FindCustomer (campaign id, start date,          |
        |           end date, [start time], [end time],         |
        |           [search type], [return type],               |
        |           [criteria]                                  |
        |       FindProspect (campaign id, start date,          |
        |           end date, [start time], [end time],         |
        |           [search type], [return type],               |
        |           [criteria]                                  |
        |       FindUpdatedOrder (campaign id, start date,      |
        |           end date, [start time], [end time],         |
        |           [search type], [return type],               |
        |           [criteria]                                  |
        |       ForceBill (req order id, opt gateway id)        |
        |       GetActiveCampaign                               |
        |       GetAlternativeProvider (campaign id,            |
        |           return url, cancel url, amount)             |
        |       GetCustomerProducts                             |
        |       GetOrderRefund (order id, amt, recur?)          |
        |       GetProductInfo (product id)                     |
        |       StopRecurringUpsell (order_id, product_id)      |
        |       UpdateOrder (order ids, actions, values)        |
        |       UpdateProduct (product ids, actions, values)    |
        |       UpdateProspect (prospect ids, actions, values)  |
        |       UpdateRecurringOrder (order id, status)         |
        |       ValidateCredentials (default)                   |
        |       ViewCampaign (campaign id)                      |
        |       ViewCustomer (customer id)                      |
        |       ViewOrder (order id)                            |
        |       ViewProspect (prospect_id)                      |
        |       VoidOrder (req order id)                        |
        \*******************************************************/

        function __construct($api_username, $api_password, $lime_light_url, $log_directory = '', $log_file = '', $log_delimiter = '|', $output_type = 'xml'){
            // set the parent variables
            parent::__construct($api_username, $api_password, $lime_light_url, $log_directory, $log_file, $log_delimiter, $output_type);

            //parent::$fullurl = parent::$baseurl . 'membership.php';
            $this->fullurl = $this->baseurl . 'membership.php';
        }

        private function CampaignFindActive(){
            $this->method = 'campaign_find_active';
            $this->response = $this->APIConnect('', '');
        }   // END CampaignFindActive()

        private function CampaignView($campaign_id){
            $this->method = 'campaign_view';
            $this->response = $this->APIConnect('campaign_id', $campaign_id);
        }   // END CampaignView

        private function CustomerFind($campaign_id, $start_date, $end_date, $start_time = '', $end_time = '', $search_type = '', $return_type = '', $criteria = ''){
            $fields = NULL;
            $values = NULL;

            // required fields
            $fields = array('campaign_id', 'start_date', 'end_date');
            $values = array($campaign_id, $start_date, $end_date);

            // optional fields
            if($start_time != ''){
                array_push($fields, 'start_time');
                array_push($values, $start_time);
            }

            if($end_time != ''){
                array_push($fields, 'end_time');
                array_push($values, $end_time);
            }

            if($search_type != ''){
                array_push($fields, 'search_type');
                array_push($values, $search_type);
            }

            if($return_type != ''){
                array_push($fields, 'return_type');
                array_push($values, $return_type);
            }

            if($criteria != ''){
                array_push($fields, 'criteria');
                array_push($values, $criteria);
            }

            $this->method = 'customer_find';
            $this->response = $this->APIConnect($fields, $values);
        }   // END CustomerFind

        private function CustomerGetProducts($customer_id, $campaign_id = ''){
            $fields = NULL;
            $values = NULL;

            if($campaign_id != ''){
                $fields = array('customer_id', 'campaign_id');
                $values = array($customer_id, $campaign_id);
            }else{
                $fields = 'customer_id';
                $values = 'campaign_id';
            }   // END campaign id check

            $this->method = 'customer_find_active_product';
            $this->response = $this->APIConnect($fields, $values);
        }   // END CustomerGetProducts

        private function CustomerView($customer_id){
            $this->method = 'customer_view';
            $this->response = $this->APIConnect('customer_id', $customer_id);
        }   // END CustomerView

        private function GetAlternativeProvider($campaign_id, $return_url, $cancel_url, $amount){
            $fields = NULL;
            $values = NULL;

            $fields = array('campaign_id', 'return_url', 'cancel_url', 'amount');
            $values = array($campaign_id, $return_url, $cancel_url, $amount);

            $this->method = 'get_alternative_provider';
            $this->response = $this->APIConnect($fields, $values);
        }   // END GetAlternativeProvider()

        private function OrderCalculateRefund($order_id){
            $this->method = 'order_calculate_refund';
            $this->response = $this->APIConnect('order_id', $order_id);
        }

        private function OrderFind($campaign_id, $start_date, $end_date, $start_time = '', $end_time = '', $search_type = '', $return_type = '', $criteria = ''){
            $fields = NULL;
            $values = NULL;

            // required fields
            $fields = array('campaign_id', 'start_date', 'end_date');
            $values = array($campaign_id, $start_date, $end_date);

            // optional fields
            if($start_time != ''){
                array_push($fields, 'start_time');
                array_push($values, $start_time);
            }

            if($end_time != ''){
                array_push($fields, 'end_time');
                array_push($values, $end_time);
            }

            if($search_type != ''){
                array_push($fields, 'search_type');
                array_push($values, $search_type);
            }

            if($return_type != ''){
                array_push($fields, 'return_type');
                array_push($values, $return_type);
            }

            if($criteria != ''){
                array_push($fields, 'criteria');
                array_push($values, $criteria);
            }

            $this->method = 'order_find';
            $this->response = $this->APIConnect($fields, $values);
        }   // END OrderFind()

        private function OrderFindUpdated($campaign_id, $start_date, $end_date, $start_time = '', $end_time = '', $search_type = '', $return_type = '', $criteria = ''){
            $fields = NULL;
            $values = NULL;

            // required fields
            $fields = array('campaign_id', 'start_date', 'end_date');
            $values = array($campaign_id, $start_date, $end_date);

            // optional fields
            if($start_time != ''){
                array_push($fields, 'start_time');
                array_push($values, $start_time);
            }

            if($end_time != ''){
                array_push($fields, 'end_time');
                array_push($values, $end_time);
            }

            if($search_type != ''){
                array_push($fields, 'search_type');
                array_push($values, $search_type);
            }

            if($return_type != ''){
                array_push($fields, 'return_type');
                array_push($values, $return_type);
            }

            if($criteria != ''){
                array_push($fields, 'criteria');
                array_push($values, $criteria);
            }

            $this->method = 'order_find_updated';
            $this->response = $this->APIConnect($fields, $values);
        }   // END OrderFindUpdated()

        private function OrderFindOverdue($days_overdue){
            $this->method = 'order_find_overdue';
            $this->response = $this->APIConnect('days', $days_overdue);
        }   // END OrderFindOverdue

        private function OrderForceBill($order_id, $gateway_id = ''){
            $fields = NULL;
            $values = NULL;

            if($gateway_id != ''){
                $fields = array('order_id', 'forceGatewayId');
                $values = array($order_id, $gateway_id);
            }else{
                $fields = 'order_id';
                $values = $order_id;
            }   // END gateway check

            $this->method = 'order_force_bill';
            $this->response = $this->APIConnect($fields, $values);
        }   // END OrderForceBill

        private function OrderRefund($order_id, $amount, $keep_recurring){
            $fields = NULL;
            $values = NULL;

            $fields = array('order_id', 'amount', 'keep_recurring');
            $values = array($order_id, $amount, $keep_recurring);

            $this->method = 'order_refund';
            $this->response = $this->APIConnect($fields, $values);
        }   // END OrderRefund

        private function OrderReprocess($order_id){
            $this->method = 'order_reprocess';
            $this->response = $this->APIConnect('order_id', $order_id);
        }   // END OrderReprocess()

        private function OrderUpdate($order_ids, $actions, $values_in){
            $fields = NULL;
            $values = NULL;

            $fields = array('order_ids', 'actions', 'values');
            $values = array($order_ids, $actions, $values_in);

            $this->method = 'order_update';
            $this->response = $this->APIConnect($fields, $values);
        }   // END OrderUpdate

        private function OrderUpdateRecurring($order_id, $status){
            $fields = NULL;
            $values = NULL;

            $fields = array('order_id', 'status');
            $values = array($order_id, $status);

            $this->method = 'order_update_recurring';
            $this->response = $this->APIConnect($fields, $values);
        }   // END OrderUpdateRecurring

        private function OrderView($order_id){
            $this->method = 'order_view';
            $this->response = $this->APIConnect('order_id', $order_id);
        }   // END OrderView

        private function OrderVoid($order_id){
            $this->method = 'order_void';
            $this->response = $this->APIConnect('order_id', $order_id);
        }   // END OrderVoid

        private function ProductCopy($product_id, $new_name = ''){
            $fields = NULL;
            $values = NULL;

            $fields = array('product_id', 'new_name');
            $values = array($product_id, $new_name);

            $this->method = 'product_copy';
            $this->response = $this->APIConnect($fields, $values);
        }   // END ProductCopy()

        private function ProductUpdate($product_ids, $actions, $values_in){
            $fields = NULL;
            $values = NULL;

            $fields = array('product_ids', 'actions', 'values');
            $values = array($product_ids, $actions, $values_in);

            $this->method = 'product_update';
            $this->response = $this->APIConnect($fields, $values);
        }   // END ProductUpdate()

        private function ProductIndex($product_id){
            $this->method = 'product_index';
            $this->response = $this->APIConnect('product_id', $product_id);
        }   // END ProductIndex()

        private function ProspectFind($campaign_id, $start_date, $end_date, $start_time = '', $end_time = '', $search_type = '', $return_type = '', $criteria = ''){
            $fields = NULL;
            $values = NULL;

            // required fields
            $fields = array('campaign_id', 'start_date', 'end_date');
            $values = array($campaign_id, $start_date, $end_date);

            // optional fields
            if($start_time != ''){
                array_push($fields, 'start_time');
                array_push($values, $start_time);
            }

            if($end_time != ''){
                array_push($fields, 'end_time');
                array_push($values, $end_time);
            }

            if($search_type != ''){
                array_push($fields, 'search_type');
                array_push($values, $search_type);
            }

            if($return_type != ''){
                array_push($fields, 'return_type');
                array_push($values, $return_type);
            }

            if($criteria != ''){
                array_push($fields, 'criteria');
                array_push($values, $criteria);
            }

            $this->method = 'prospect_find';
            $this->response = $this->APIConnect($fields, $values);

        }   // END ProspectFind()

        private function ProspectUpdate($prospect_ids, $actions, $values_in){
            $fields = NULL;
            $values = NULL;

            $fields = array('prospect_ids', 'actions', 'values');
            $values = array($prospect_ids, $actions, $values_in);

            $this->method = 'prospect_update';
            $this->response = $this->APIConnect($fields, $values);
        }   // END ProspectUpdate

        private function ProspectView($prospect_id){
            $this->method = 'prospect_view';
            $this->response = $this->APIConnect('prospect_id', $prospect_id);
        }   // END ProspectView()

        private function UpsellStopRecurring($order_id, $product_id){
            $fields = NULL;
            $values = NULL;

            $fields = array('order_id', 'product_id');
            $values = array($order_id, $product_id);

            $this->method = 'upsell_stop_recurring';
            $this->response = $this->APIConnect($fields, $values);
        }   // END UpsellStopRecurring

        private function ValidateCredentials(){
            $this->method = 'validate_credentials';
            $this->response = $this->APIConnect('', '');
        }   // END ValidateCredentials()

        // return Lime Light's response string
        // in whatever format is set
        public function GetResponse($do_what, $parameters){
            // instead of throwing an exception
            // allow Lime Light to respond with an error code
            // check for (and fix) errors that will break this code
            switch(strtolower($do_what)){
                case 'calculateorderrefund':
                    $this->OrderCalculateRefund($parameters);
                    break;
                case 'copyproduct':
                    $this->ProductCopy($parameters[0], $parameters[1]);
                    break;
                case 'findcustomer':
                    $this->CustomerFind($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7]);
                    break;
                case 'findorder':
                    $this->OrderFind($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7]);
                    break;
                case 'findupdatedorder':
                    $this->OrderFindUpdated($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7]);
                    break;
                case 'findoverdueorder':
                    $this->OrderFindOverdue($parameters);
                    break;
                case 'findprospect':
                    $this->ProspectFind($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7]);
                    break;
                case 'forcebill':
                    $this->OrderForceBill($parameters[0], $parameters[1]);
                    break;
                case 'getactivecampaign':
                    $this->CampaignFindActive();
                    break;
                case 'getalternativeprovider':
                    $this->GetAlternativeProvider($parameters[0], $parameters[1], $parameters[2], $parameters[3]);
                    break;
                case 'getcustomerproducts':
                    $this->CustomerGetProducts($parameters[0], $parameters[1]);
                    break;
                case 'getorderrefund':
                    $this->OrderRefund($parameters[0], $parameters[1], $parameters[2]);
                    break;
                case 'getproductinfo':
                    $this->ProductIndex($parameters);
                    break;
                case 'reprocessorder':
                    $this->OrderReprocess($parameters);
                    break;
                case 'stoprecurringupsell':
                    $this->UpsellStopRecurring($parameters[0], $parameters[1]);
                    break;
                case 'updateorder':
                    $this->OrderUpdate($parameters[0], $parameters[1], $parameters[2]);
                    break;
                case 'updateproduct':
                    $this->ProductUpdate($parameters[0], $parameters[1], $parameters[2]);
                    break;
                case 'updateprospect':
                    $this->ProspectUpdate($parameters[0], $parameters[1], $parameters[2]);
                    break;
                case 'updaterecurringorder':
                    /*
                                        if(!isset($parameters) || !is_array($parameters)){
                                            unset($parameters);
                                            $parameters = $this->ArrayPopulate(2, '');
                                        }
                    */
                    $this->OrderUpdateRecurring($parameters[0], $parameters[1]);
                    break;
                case 'validatecredentials':
                default:
                    $this->ValidateCredentials();
                    break;
                case 'viewcampaign':
                    $this->CampaignView($parameters);
                    break;
                case 'viewcustomer':
                    $this->CustomerView($parameters);
                    break;
                case 'vieworder':
                    $this->OrderView($parameters);
                    break;
                case 'viewprospect':
                    $this->ProspectView($parameters);
                    break;
                case 'voidorder':
                    $this->OrderVoid($parameters);
                    break;
            }   // END switch

            if($this->response !== FALSE){
                switch($this->output_as){
                    case 'array':
                        $temp_str = $this->response;
                        unset($this->response);
                        $this->response = $this->GetArray($temp_str);
                        break;
                    case 'xml':
                        $temp_str = $this->response;
                        unset($this->response);
                        $this->response = $this->GetXML($this->GetArray($temp_str));
                        break;
                    case 'string':
                    default:
                        // do nothing, all good!
                        break;
                }
            }

            return $this->response;
        }   // END GetResponse()
    }   // END CLASS Membership()
?>
