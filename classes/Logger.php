<?php
	class Logger{
		private $delim;
		private $fh;
		
		function __construct($filename, $delimiter){
			$this->fh = fopen($filename, 'a');
			$this->delim = $delimiter;
		}	// END __construct()
		
		function __destruct(){
			fclose($this->fh);
		}	// END __destruct()
		
		public function LogError($title, $message){
			$output = NULL;
			
			$output = date('Y-m-d H:i:s') . $this->delim . 'ERROR' . $this->delim . $title . $this->delim . $message . "\n";
			
			fwrite($this->fh, $output);
		}	// END LogError
		
		public function LogLine($line_array){
			$output = NULL;
			
			$output = date('Y-m-d H:i:s');
			
			foreach($line_array as $v){
				$output .= $this->delim . $v;
			}
			
			$output .= "\n";
			
			fwrite($this->fh, $output);
		}

		public function LogResponse($title, $message){
			$output = NULL;
			
			$output = date('Y-m-d H:i:s') . $this->delim . 'RESPONSE' . $this->delim . $title . $this->delim . $message . "\n";
			
			fwrite($this->fh, $output);
		}	// END LogResponse
	}
?>