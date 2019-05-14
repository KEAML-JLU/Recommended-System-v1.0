<?php
/**
 * 获取用户信息：ID,type,name
 * ID: 1,2,3...
 * type: "admin"
 * name: 张三
 *
 * @return
 * json: {"ID":1, "type":"admin", "name":"张三"}
 * @author Donghui.Wang
 */
require_once '../common/common.php';
require_once '../common/DatabaseConn.php';

// 检查传进来的数据
if(!isset($_POST['userName'])){

 	echo "Username not exists.";
	return ;
}

// 检查是否为空
if($_POST ['userName'] == "" || $_POST ['password'] == ""){
	echo "Username or password empty.";
	return;
}

// 获取数据(过滤一下，防止SQL注入)
$username = PAPI_GetSafeParam($_POST['userName']);
$pwd = PAPI_GetSafeParam($_POST['password']);

// 链接数据库
	$mysql_conn = new MySQL();
	$mysql_conn->connect();

	$mysql_conn->query("SET NAMES utf8");
	
// 比对数据库
	$sql = 'SELECT `ID`, `userName`, `password`, `type`, `name`, `status` FROM `customer` WHERE `userName`= "' . $username . '"';
	$result = $mysql_conn->query($sql);
	// 存在性检查
	if($mysql_conn->num_rows($result) == 0){	// 用户不存在
		// 释放数据库
		$mysql_conn->free_result($result);
		$mysql_conn->close();
		
		echo "Username not exists.";
		return;
	}
	
	$userInfo = $mysql_conn->fetch_array($result);
	// 有效性检查
	if($userInfo['status'] == '-1'){
		// 释放数据库
		$mysql_conn->free_result($result);
		$mysql_conn->close();
		
		echo "Account is disabled.";
		return;
	}else if($userInfo['status'] == '0'){
		// 释放数据库
		$mysql_conn->free_result($result);
		$mysql_conn->close();
		
		echo "Account is not activited.";
		return;
	}
	
	if($userInfo['password'] != md5($pwd)){	// 密码不正确
		// 释放数据库
		$mysql_conn->free_result($result);
		$mysql_conn->close();
		
		echo "Password incorrect.";
		return;
	}
	
//验证通过，写入SESSION
	session_start();
	$_SESSION['user']['ID'] = $userInfo['ID'];
// 	$_SESSION['user']['Type'] = ($userInfo ['type'] == '1') ? "expert" : "other" ;
 	$_SESSION['user']['Name'] = $userInfo['name'];
 	if($userInfo ['type'] == '0'){
 		$_SESSION['user']['Type'] = "admin";
 	}else if($userInfo ['type'] == '1'){
 		$_SESSION['user']['Type'] = "user";
 	}
 	
// 释放数据库
	$mysql_conn->free_result($result);
	$mysql_conn->close();

	if($userInfo ['type'] == '0'){
		echo "admin";
	}else if($userInfo ['type'] == '1'){
		echo "user";
	}
	return;
?>