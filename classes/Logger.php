<?php
	class Logger{
		private static $delim;
		private static $fh;
		
		function __construct($filename, $delimiter){
			self::$fh = fopen($filename, 'a');
			self::$delim = $delimiter;
		}	// END __construct()
		
		function __destruct(){
			fclose(self::$fh);
		}	// END __destruct()
		
		public function LogError($title, $message){
			$output = NULL;
			
			$output = date('Y-m-d H:i:s') . self::$delim . 'ERROR' . self::$delim . $title . self::$delim . $message . "\n";
			
			fwrite(self::$fh, $output);
		}	// END LogError
		
		public function LogLine($line_array){
			$output = NULL;
			
			$output = date('Y-m-d H:i:s');
			
			foreach($line_array as $v){
				$output .= self::$delim . $v;
			}
			
			$output .= "\n";
			
			fwrite(self::$fh, $output);
		}

		public function LogResponse($title, $message){
			$output = NULL;
			
			$output = date('Y-m-d H:i:s') . self::$delim . 'RESPONSE' . self::$delim . $title . self::$delim . $message . "\n";
			
			fwrite(self::$fh, $output);
		}	// END LogResponse
	}
?>