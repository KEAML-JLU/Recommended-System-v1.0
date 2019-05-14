<?php
/*
 * class mysql
 * www.php100.com 编辑器教程
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

   class MySQL{


     private $host;
     private $name;
     private $pass;
     private $database;
     private $ut;



     function __construct(){
     	$this->host="localhost";
        $this->name="root";
        $this->pass="123456";
        $this->database="retrieval system";
        $this->ut="utf8";
	
     }


     function connect(){
      $link=@mysql_connect($this->host,$this->name,$this->pass) or die ($this->error());
      mysql_select_db($this->database,$link) or die("no database named as：".$this->database);
      mysql_query("SET NAMES '$this->ut'");
     }

	function query($sql) {
	    //if(!($query = mysql_query($sql))) $this->show('Say:', $sql);
	    return $result = mysql_query($sql);
	}

    function show($message = '', $sql = '') {
		if(!$sql) echo $message;
		else echo $message.'<br>'.$sql;
	}

    function affected_rows() {
		return mysql_affected_rows();
	}

	function result($query, $row) {
		return mysql_result($query, $row);
	}

	function num_rows($query) {
		return @mysql_num_rows($query);
	}

	function num_fields($query) {
		return mysql_num_fields($query);
	}

	function free_result($query) {
		return mysql_free_result($query);
	}

	function insert_id() {
		return mysql_insert_id();
	}

	function fetch_row($result) {
		return mysql_fetch_row($result);
	}

	function fetch_array($result) {
		return mysql_fetch_array($result);
	}
	
	function version() {
		return mysql_get_server_info();
	}

	function close() {
		return mysql_close();
	}

	function __tostring(){
		return "This is MySQL.class!<br/>";
	}
//   //==============
//
//    function fn_insert($database,$name,$value){
//
//    	$this->query("insert into $database ($name) value ($value)");
//
//    }


   }

 //	echo "MySQL.class.php读入！"."<br/>";
?>
