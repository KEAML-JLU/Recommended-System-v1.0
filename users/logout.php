<?php
/**
 * 获取用户信息：ID,type,name
 * ID: 1,2,3...
 * type: "expert"
 * name: 张三
 * 
 * @return
 * json: {"ID":1, "type":"expert", "name":"张三"}
 * @author Donghui.Wang
 */

session_start();
if(!isset($_SESSION["user"])){
	return false;
}

unset($_SESSION["user"]);

?>