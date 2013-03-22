<?php
	require_once('Connector.php');
	require_once('Logger.php');
	
	class LimeLight {
		protected static $baseurl;
		protected static $fullurl;
		protected static $logger;
		protected static $method;
		protected static $output_as;
		protected static $password;
		protected static $response;
		protected static $username;
		
		// throws exception on database failure
		function __construct($sysname){
			$conn = NULL;
			$dbh = NULL;
			$is_active = NULL;
			$rslt = NULL;
			$stmt = NULL;
			
			// set up the Error/Response Logs
			$logpath = str_replace('classes', 'logs', $_SERVER['DOCUMENT_ROOT']);
			self::$logger = new Logger($logpath . '/limelight.log', '::');

			$conn = new Connector('systems');
			
			$dbh = $conn->DBH();
			$stmt = $dbh->stmt_init();

			if($stmt->prepare('SELECT sysDateAdded, AES_DECRYPT(sysUser, sysDateAdded), AES_DECRYPT(sysPassword, sysDateAdded), AES_DECRYPT(sysLocation, sysDateAdded) FROM systems WHERE sysName = ? AND sysIsActive = ?')){
				$is_active = 1;
				$stmt->bind_param('si', $sysname, $is_active);

				if(!$stmt->execute()){
					// prepare failure
					// log it and throw exception
					self::$logger->LogError('__construct() EXECUTE STATEMENT FAILURE', $stmt->error);
					throw new ErrorException(); 
				}else{
					$stmt->bind_result($rslt['DateKey'], $rslt['User'], $rslt['Pass'], $rslt['Location']);
					
					while($stmt->fetch()){
						self::$baseurl = $rslt['Location'];
						self::$password = $rslt['Pass'];
						self::$username = $rslt['User'];
					}	// END fetch loop
				}	// END execution check
			}else{
				// execution failure
				// log it and throw exception
				self::$logger->LogError('__construct() PREPARE STATEMENT FAILURE', $stmt->error);
				throw new ErrorException(); 
			}	// END prepare check
		}	//	END __construct()
		
		function __destruct(){
			self::$logger->__destruct();
		}	// END __destruct()
		
		protected function APIConnect($fields, $values){
			$api_conn = NULL;
			$api_post = NULL;
			$ch = NULL;
			$cr = NULL;
			$ct = NULL;
			$fv = NULL;
			$i = NULL;
			
			$api_conn = array('username' => self::$username,
							  'password' => self::$password,
							  'method' => self::$method);
			
			// check parameters	
			if(is_array($fields) && is_array($values)){	
				// parameters are arrays
				$ct = count($fields);
				
				for($i = 0; $i < $ct; $i++){
					$fv[$fields[$i]] = $values[$i];
				}
				
				$api_post = array_merge($api_conn, $fv);
			}else if($fields != '' && $values != ''){
				// parameters are non-empty strings
				$api_post = array_merge($api_conn, array($fields => $values));
			}else{
				// parameters are empty strings
				$api_post = $api_conn;
			}

			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $api_post);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_URL, self::$fullurl);
			
			$x = print_r(curl_exec($ch), TRUE);
			
			return($cr = curl_exec($ch));
		}	// END APIConnect()	
		
		protected function ArrayPopulate($number_of_elements, $populate_value = ''){
			$array_variable = NULL;
			$i = NULL;

			for($i = 0; $i < $number_of_elements; $i++){
				$array_variable[$i] = $populate_value;
			}	// END for loop
			
			return($array_variable);
		}	// END ArrayPopulate()
		
		protected function WriteXML(XMLWriter $xml, $data){
			foreach($data as $key => $value){
				if(is_array($value)){
					$xml->startElement($key);
					self::Write($xml, $value);
					$xml->endElement();
					continue;
				}
				$xml->writeElement($key, urldecode($value));
			}
		}	// END WriteXML()

		public function GetArray($data_string){
			$arr = NULL;
			$i = NULL;
			$tmp1 = NULL;
			$tmp2 = NULL;
			
			// break up the string into field-value pair strings
			$tmp1 = explode('&', $data_string);
			
			//break up the field-value pair strings into an associative array
			for($i = 0; $i < count($tmp1); $i++){
				$tmp2 = explode('=', $tmp1[$i]);
				$arr[$tmp2[0]] = $tmp2[1];
				unset($tmp2);
			}
			
			//parse_str($data_string, $arr);
						
			return $arr;
		}	// END GetArray()

		public function GetXML($data_array){
			$xml = NULL;
			$xml_encoding = NULL;
			$xml_version = NULL;
			
			$xml_encoding = 'UTF-8';
			$xml_version = '1.0';
			
			$xml = new XMLWriter();
			
			if(!is_array($data_array)){
				self::$logger->LogError('GetXML() INPUT ERROR', 'Data is not formatted as an array');
				return false;
			}else{
				$xml->openMemory();
				$xml->startDocument($xml_version, $xml_encoding, 'yes');
				self::WriteXML($xml, $data_array);
				$xml->endDocument();
				
				return $xml->outputMemory(true);
			}
		}	// END GetXML()
		
	}	// END class LimeLight
	
	class Membership extends LimeLight {
		/*******************************************************\
		|	Allowed options for GetResponse():					|
		|	**params are req'd unless [otherwise noted]**		|
		|		CalculateOrderRefund (order id)					|
		|		CopyProduct (product id, [new name])			|
		|		FindOrder (campaign id, start date, 			|
		|			end date, [start time], [end time],			|
		|			[search type], [return type], 				|
		|			[criteria]									|
		|		FindOverdueOrder (days overdue)					|
		|		FindCustomer (campaign id, start date, 			|
		|			end date, [start time], [end time],			|
		|			[search type], [return type], 				|
		|			[criteria]									|
		|		FindProspect (campaign id, start date, 			|
		|			end date, [start time], [end time],			|
		|			[search type], [return type], 				|
		|			[criteria]									|
		|		FindUpdatedOrder (campaign id, start date, 		|
		|			end date, [start time], [end time],			|
		|			[search type], [return type], 				|
		|			[criteria]									|
		|		ForceBill (req order id, opt gateway id)		|
		|		GetActiveCampaign								|
		|		GetAlternativeProvider (campaign id, 			|
		|			return url, cancel url, amount)				|
		|		GetCustomerProducts								|
		|		GetOrderRefund (order id, amt, recur?)			|
		|		GetProductInfo (product id)						|
		|		StopRecurringUpsell (order_id, product_id)		|
		|		UpdateOrder (order ids, actions, values)		|
		|		UpdateProduct (product ids, actions, values)	|
		|		UpdateProspect (prospect ids, actions, values)	|
		|		UpdateRecurringOrder (order id, status)			|
		|		ValidateCredentials (default)					|
		|		ViewCampaign (campaign id)						|
		|		ViewCustomer (customer id)						|
		|		ViewOrder (order id)							|
		|		ViewProspect (prospect_id)						|
		|		VoidOrder (req order id)						|
		\*******************************************************/
				
		function __construct($sysname, $output_type){
			// set the parent variables
			parent::__construct($sysname);
			
			parent::$fullurl = parent::$baseurl . 'membership.php';
			
			if($output_type == 'array' || $output_type == 'string' || $output_type == 'xml'){
				parent::$output_as = $output_type;
				//parent::$output_as = $output_type;
			}else{
				parent::$output_as = 'string';
				//parent::$output_as = 'string';
			}
		}
		
		private function CampaignFindActive(){
			self::$method = 'campaign_find_active';
			self::$response = self::APIConnect('', '');
		}	// END CampaignFindActive()
		
		private function CampaignView($campaign_id){
			self::$method = 'campaign_view';
			self::$response = self::APIConnect('campaign_id', $campaign_id);
		}	// END CampaignView
		
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
			
			self::$method = 'customer_find';
			self::$response = self::APIConnect($fields, $values);
		}	// END CustomerFind
		
		private function CustomerGetProducts($customer_id, $campaign_id = ''){
			$fields = NULL;
			$values = NULL;
			
			if($campaign_id != ''){
				$fields = array('customer_id', 'campaign_id');
				$values = array($customer_id, $campaign_id);
			}else{
				$fields = 'customer_id';
				$values = 'campaign_id';
			} 	// END campaign id check
			
			self::$method = 'customer_find_active_product';
			self::$response = self::APIConnect($fields, $values);
		}	// END CustomerGetProducts
		
		private function CustomerView($customer_id){
			self::$method = 'customer_view';
			self::$response = self::APIConnect('customer_id', $customer_id);
		}	// END CustomerView
		
		private function GetAlternativeProvider($campaign_id, $return_url, $cancel_url, $amount){
			$fields = NULL;
			$values = NULL;
			
			$fields = array('campaign_id', 'return_url', 'cancel_url', 'amount');
			$values = array($campaign_id, $return_url, $cancel_url, $amount);
			
			self::$method = 'get_alternative_provider';
			self::$response = self::APIConnect($fields, $values);
		}	// END GetAlternativeProvider()
		
		private function OrderCalculateRefund($order_id){
			self::$method = 'order_calculate_refund';
			self::$response = self::APIConnect('order_id', $order_id);
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
			
			self::$method = 'order_find';
			self::$response = self::APIConnect($fields, $values);
		}	// END OrderFind()
		
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
			
			self::$method = 'order_find';
			self::$response = self::APIConnect($fields, $values);
		}	// END OrderFindUpdated()

		private function OrderFindOverdue($days_overdue){
			self::$method = 'order_find_overdue';
			self::$response = self::APIConnect('days', $days_overdue);
		}	// END OrderFindOverdue
		
		private function OrderForceBill($order_id, $gateway_id = ''){
			$fields = NULL;
			$values = NULL;
			
			if($gateway_id != ''){
				$fields = array('order_id', 'forceGatewayId');
				$values = array($order_id, $gateway_id);
			}else{
				$fields = 'order_id';
				$values = $order_id;
			}	// END gateway check
			
			self::$method = 'order_force_bill';
			self::$response = self::APIConnect($fields, $values);
		}	// END OrderForceBill
		
		private function OrderRefund($order_id, $amount, $keep_recurring){
			$fields = NULL;
			$values = NULL;
			
			$fields = array('order_id', 'amount', 'keep_recurring');
			$values = array($order_id, $amount, $keep_recurring);
			
			self::$method = 'order_refund';
			self::$response = self::APIConnect($fields, $values);
		}	// END OrderRefund
		
		private function OrderReprocess($order_id){
			self::$method = 'order_reprocess';
			self::$response = self::APIConnect('order_id', $order_id);
		}	// END OrderReprocess()
		
		private function OrderUpdate($order_ids, $actions, $values_in){
			$fields = NULL;
			$values = NULL;
			
			$fields = array('order_ids', 'actions', 'values');
			$values = array($order_ids, $actions, $values_in);
			
			self::$method = 'order_update';
			self::$response = self::APIConnect($fields, $values);
		}	// END OrderUpdate
		
		private function OrderUpdateRecurring($order_id, $status){
			$fields = NULL;
			$values = NULL;
			
			$fields = array('order_id', 'status');
			$values = array($order_id, $status);
			
			self::$method = 'order_update_recurring';
			self::$response = self::APIConnect($fields, $values);
		}	// END OrderUpdateRecurring
		
		private function OrderView($order_id){
			self::$method = 'order_view';
			self::$response = self::APIConnect('order_id', $order_id);
		}	// END OrderView
		
		private function OrderVoid($order_id){
			self::$method = 'order_void';
			self::$response = self::APIConnect('order_id', $order_id);
		}	// END OrderVoid
		
		private function ProductCopy($product_id, $new_name = ''){
			$fields = NULL;
			$values = NULL;
			
			$fields = array('product_id', 'new_name');
			$values = array($product_id, $new_name);
			
			self::$method = 'product_copy';
			self::$response = self::APIConnect($fields, $values);
		}	// END ProductCopy()
		
		private function ProductUpdate($product_ids, $actions, $values_in){
			$fields = NULL;
			$values = NULL;
			
			$fields = array('product_ids', 'actions', 'values');
			$values = array($product_ids, $actions, $values_in);
			
			self::$method = 'product_update';
			self::$response = self::APIConnect($fields, $values);
		}	// END ProductUpdate()
		
		private function ProductIndex($product_id){
			self::$method = 'product_index';
			self::$response = self::APIConnect('product_id', $product_id);
		}	// END ProductIndex()
		
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
			
			self::$method = 'prospect_find';
			self::$response = self::APIConnect($fields, $values);
			
		}	// END ProspectFind()
		
		private function ProspectUpdate($prospect_ids, $actions, $values_in){
			$fields = NULL;
			$values = NULL;
			
			$fields = array('prospect_ids', 'actions', 'values');
			$values = array($prospect_ids, $actions, $values_in);
			
			self::$method = 'prospect_update';
			self::$response = self::APIConnect($fields, $values);
		}	// END ProspectUpdate
		
		private function ProspectView($prospect_id){
			self::$method = 'prospect_view';
			self::$response = self::APIConnect('prospect_id', $prospect_id);
		}	// END ProspectView()
		
		private function UpsellStopRecurring($order_id, $product_id){
			$fields = NULL;
			$values = NULL;
			
			$fields = array('order_id', 'product_id');
			$values = array($order_id, $product_id);
			
			self::$method = 'upsell_stop_recurring';
			self::$response = self::APIConnect($fields, $values);
		}	// END UpsellStopRecurring
		
		private function ValidateCredentials(){
			self::$method = 'validate_credentials';
			self::$response = self::APIConnect('', '');
		}	// END ValidateCredentials()
		
		// return Lime Light's response string
		// in whatever format is set
		public function GetResponse($do_what, $parameters){
			// instead of throwing an exception
			// allow Lime Light to respond with an error code
			// check for (and fix) errors that will break this code
			switch(strtolower($do_what)){
				case 'calculateorderrefund':
					self::OrderCalculateRefund($parameters);
					break;
				case 'copyproduct':
					self::ProductCopy($parameters[0], $parameters[1]);
					break;
				case 'findcustomer':
					self::CustomerFind($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7]);
					break;
				case 'findorder':
					self::OrderFind($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7]);
					break;
				case 'findupdatedorder':
					self::OrderFindUpdated($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7]);
					break;
				case 'findoverdueorder':
					self::OrderFindOverdue($parameters);
					break;
				case 'findprospect':
					self::ProspectFind($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7]);
					break;
				case 'forcebill':
					self::OrderForceBill($parameters[0], $parameters[1]);
					break;
				case 'getactivecampaign':
					self::CampaignFindActive();
					break;
				case 'getalternativeprovider':
					self::GetAlternativeProvider($parameters[0], $parameters[1], $parameters[2], $parameters[3]);
					break;
				case 'getcustomerproducts':
					self::CustomerGetProducts($parameters[0], $parameters[1]);
					break;
				case 'getorderrefund':
					self::OrderRefund($parameters[0], $parameters[1], $parameters[2]);
					break;
				case 'getproductinfo':
					self::ProductIndex($parameters);
					break;
				case 'reprocessorder':
					self::OrderReprocess($parameters);
					break;
				case 'stoprecurringupsell':
					self::UpsellStopRecurring($parameters[0], $parameters[1]);
					break;
				case 'updateorder':
					self::OrderUpdate($parameters[0], $parameters[1], $parameters[2]);
					break;
				case 'updateproduct':
					self::ProductUpdate($parameters[0], $parameters[1], $parameters[2]);
					break;
				case 'updateprospect':
					self::ProspectUpdate($parameters[0], $parameters[1], $parameters[2]);
					break;
				case 'updaterecurringorder':
/*
					if(!isset($parameters) || !is_array($parameters)){
						unset($parameters);
						$parameters = self::ArrayPopulate(2, '');
					}
*/
					self::OrderUpdateRecurring($parameters[0], $parameters[1]);
					break;
				case 'validatecredentials':
				default:
					self::ValidateCredentials();
					break;
				case 'viewcampaign':
					self::CampaignView($parameters);
					break;
				case 'viewcustomer':
					self::CustomerView($parameters);
					break;
				case 'vieworder':
					self::OrderView($parameters);
					break;
				case 'viewprospect':
					self::ProspectView($parameters);
					break;
				case 'voidorder':
					self::OrderVoid($parameters);
					break;
			}	// END switch
			
			if(self::$response !== FALSE){
				switch(parent::$output_as){
					case 'array':
						$temp_str = self::$response;
						unset(self::$response);
						self::$response = self::GetArray($temp_str);
						break;
					case 'xml':
						$temp_str = self::$response;
						unset(self::$response);
						self::$response = self::GetXML(self::GetArray($temp_str));
						break;
					case 'string':
					default:
						// do nothing, all good!
						break;
				}
			}
			
			return self::$response;
		}	// END GetResponse()
		
	}	// END class Membership

	class Transaction extends LimeLight {
		/***************************************************\
		|													|
		|	Available inputs for GetResponse				|
		|	required fields [optional fields]				|
		|		NewOrder									|
		|													|
		\***************************************************/
		protected static $fullurl;
		private static $output_as;
		
		function __construct($sysname, $output_type){
			// set the parent variables
			parent::__construct($sysname);
			
			parent::$fullurl = parent::$baseurl . 'transact.php';
			
			if($output_type == 'array' || $output_type == 'string' || $output_type == 'xml'){
				parent::$output_as = $output_type;
			}else{
				parent::$output_as = 'string';
			}
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
						array_push($fields, 'prodct_qty_' . $product_list[$i]);
						array_push($values, $product_qty_array[$i]);	
					}
				}else{
					array_push($fields, 'product_qty_' . $product_list);
					array_push($values, $product_qty_array[$i]);
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

			self::$method = 'NewOrder';
			self::$response = self::APIConnect($fields, $values);
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

			self::$method = 'NewOrderCardOnFile';
			self::$response = self::APIConnect($fields, $values);
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
			
			self::$method = 'NewOrderWithProspect';
			self::$response = self::APIConnect($fields, $values);
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
			
			self::$method = 'NewProspect';
			self::$response = self::APIConnect($fields, $values);
		}	// END New Prospect()
		
		// return Lime Light's response string
		// in whatever format is set
		public function GetResponse($do_what, $parameters){
			// instead of throwing an exception
			// allow Lime Light to respond with an error code
			// check for (and fix) errors that will break this code
			switch(strtolower($do_what)){
				case 'neworder':
				default:
					self::NewOrder($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7], $parameters[8], $parameters[9], $parameters[10], $parameters[11], $parameters[12], $parameters[13], $parameters[14], $parameters[15], $parameters[16], $parameters[17], $parameters[18], $parameters[19], $parameters[20], $parameters[21], $parameters[22], $parameters[23], $parameters[24], $parameters[25], $parameters[26], $parameters[27], $parameters[28], $parameters[29], $parameters[30], $parameters[31], $parameters[32], $parameters[33], $parameters[34], $parameters[35], $parameters[36], $parameters[37], $parameters[38], $parameters[39], $parameters[40], $parameters[41], $parameters[42], $parameters[43], $parameters[44], $parameters[45], $parameters[46], $parameters[47]);
					break;
				case 'newordercardonfile':
					self::NewOrderCardOnFile($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7], $parameters[8], $parameters[9], $parameters[10], $parameters[11]);
					break;
				case 'neworderwithprospect':
					self::NewOrderWithProspect($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7], $parameters[8], $parameters[9], $parameters[10], $parameters[11], $parameters[12], $parameters[13], $parameters[14], $parameters[15], $parameters[16], $parameters[17], $parameters[18], $parameters[19], $parameters[20], $parameters[21], $parameters[22], $parameters[23], $parameters[24], $parameters[25], $parameters[26], $parameters[27], $parameters[28], $parameters[29], $parameters[30]);
					break;
				case 'newprospect':
					self::NewPropspect($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7], $parameters[8], $parameters[9], $parameters[10], $parameters[11], $parameters[12], $parameters[13], $parameters[14], $parameters[15], $parameters[16], $parameters[17], $parameters[18], $parameters[19], $parameters[20], $parameters[21]);
					break;
			}	// END switch

			if(self::$response !== FALSE){
				switch(parent::$output_as){
					case 'array':
						$temp_str = self::$response;
						unset(self::$response);
						self::$response = self::GetArray($temp_str);
						break;
					case 'xml':
						$temp_str = self::$response;
						unset(self::$response);
						self::$response = self::GetXML(self::GetArray($temp_str));
						break;
					case 'string':
					default:
						// do nothing, all good!
						break;
				}
			}
			
			return self::$response;
		}

	}	// END class Transaction
?>