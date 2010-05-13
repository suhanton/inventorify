<?php
	class mysqlConn{
		private $conn;		
		
		public function __construct($user = USERNAME, $password = PASSWORD, $host = HOST, $database = DATABASE){
			$this->conn = mysql_connect($host, $user, $password);
			if ($this->conn){
				mysql_select_db($database);
			}
		}
		
		public function query($query, $line = 0, $error = 0){
			$query = mysql_query($query);
			if ($query){
				return $query; 
			}else{
				if ($error == 0){
					$e = mysql_error() . " [LINE: " . $line . "]";
					$this->error($e);
					throw new Exception($e);
				}else{
					die('Error loop.');
				}
			}
		}
		
		public function rows($resource){
			return mysql_num_rows($resource);
		}
		
		public function fetch($resource){
			return mysql_fetch_array($resource);
		}
		
		public function id(){
			return mysql_insert_id();
		}
		
		public function error($e){
			$e = mysql_escape_string($e);
			$this->query("INSERT INTO errors (file, error) VALUES ('$_SERVER[SCRIPT_FILENAME]', '$e')", __LINE__, 1);
		}
			
		public function __destruct(){
			mysql_close($this->conn);
			empty($this->conn);
		}
	}

?>