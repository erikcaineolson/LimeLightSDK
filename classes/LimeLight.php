<?php
	class LimeLight {
		protected $baseurl;
		protected $fullurl;
		protected $logger;
		protected $method;
		protected $output_as;
		protected $password;
		protected $response;
		protected $username;
		
		// throws exception on database failure
		function __construct($api_username, $api_password, $lime_light_url, $log_directory = '', $log_file = '', $log_delimiter = '|', $output_type = 'xml'){
			// set up the Error/Response Logs
            // check to see if log parameters were passed
            // if not, create defaults
            if(!isset($log_directory) || $log_directory == '' || $log_directory === NULL){
			    $log_directory = str_replace('public_html', 'logs', $_SERVER['DOCUMENT_ROOT']);
            }

            if(!isset($log_file) || $log_file == '' || $log_file === NULL){
                $log_file = 'limelight.log';
            }

            if($output_type == 'array' || $output_type == 'string' || $output_type == 'xml'){
                $this->output_as = $output_type;
            }else{
                $this->output_as = 'string';
            }

            print_r($log_directory . '/' . $log_file);

			$this->$logger = new Logger($log_directory . '/' . $log_file, $log_delimiter);

            $this->baseurl = $lime_light_url;

            $this->username = $api_username;
            $this->password = $api_password;
		}	//	END __construct()
		
		function __destruct(){
			$this->$logger->__destruct();
		}	// END __destruct()
		
		protected function APIConnect($fields, $values){
			$api_conn = NULL;
			$api_post = NULL;
			$ch = NULL;
			$cr = NULL;
			$ct = NULL;
			$fv = NULL;
			$i = NULL;
			
			$api_conn = array('username' => $this->$username,
							  'password' => $this->$password,
							  'method' => $this->$method);
			
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
			curl_setopt($ch, CURLOPT_URL, $this->$fullurl);
			
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
		
		protected function AssociativeArrayToArray($associative_array){
			$array_out = NULL;
			$i = NULL;
			$v = NULL;
			
			$i = 0;
			
			// convert associative array to non-associative
			foreach($associative_array as $v){
				$array_out[$i] = $v;
				$i++;
			}
			
			return $array_out;
		}
		
		protected function WriteXML(XMLWriter $xml, $data){
			foreach($data as $key => $value){
				if(is_array($value)){
					$xml->startElement($key);
					$this->Write($xml, $value);
					$xml->endElement();
					continue;
				}
				$xml->writeElement($key, urldecode($value));
			}
		}	// END WriteXML()

		public function GetArray($data_string){
			parse_str($data_string, $arr);
						
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
				$this->$logger->LogError('GetXML() INPUT ERROR', 'Data is not formatted as an array');
				return false;
			}else{
				$xml->openMemory();
				$xml->startDocument($xml_version, $xml_encoding, 'yes');
				$this->WriteXML($xml, $data_array);
				$xml->endDocument();
				
				return $xml->outputMemory(true);
			}
		}	// END GetXML()
		
	}	// END class LimeLight
?>