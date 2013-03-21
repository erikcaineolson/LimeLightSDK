<?php
	class Connector{
		private static $db_data;
		private static $db_hndl;
		private static $db_host;
		private static $db_pass;
		private static $db_user;

		function __construct($database){
			switch($database){
				case 'system':
					self::$db_data = 'systemsdb';
					self::$db_host = 'localhost';
					self::$db_pass = 'systems_password';
					self::$db_user = 'systemsu';
					break;
				default:
					break;
			}
		}

		public function DBH(){
			self::$db_hndl = new mysqli(self::$db_host, self::$db_user, self::$db_pass, self::$db_data);
			return self::$db_hndl;
		}
	}
?>
