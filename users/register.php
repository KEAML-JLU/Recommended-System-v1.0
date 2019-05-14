<?php
/**
 * 注册用户信息：
 * userName
 * password
 * type
 * name
 * @author Donghui.Wang
 */
require_once '../common/common.php';
require_once '../common/DatabaseConn.php';

// 检查传进来的数据
if (! isset ( $_POST ['userName'] )) {
	echo "No data input.";
	return ;
}

// 检查是否为空
if($_POST ['userName'] == "" || $_POST ['password'] == "" || $_POST ['name'] == ""){
	echo "Username or password or name empty.";
	return ;
}

// 获取数据(过滤一下，防止SQL注入)
$username = PAPI_GetSafeParam ( $_POST ['userName'] );
$pwd = PAPI_GetSafeParam ( $_POST ['password'] );
$name = PAPI_GetSafeParam ( $_POST ['name'] );
$type = 1;	// 默认为普通用户
// 链接数据库
$mysql_conn = new MySQL();
$mysql_conn->connect();

$mysql_conn->query("SET NAMES utf8");

// 比对数据库
$sql = 'SELECT `ID` FROM `customer` WHERE `userName`="' . $username . '"';
$result = $mysql_conn->query ( $sql );
// 比对
if ($mysql_conn->num_rows($result) == 1) { // 用户已存在
	// 释放数据库
	$mysql_conn->free_result($result);
	$mysql_conn->close();
	
	echo "Username exist.";
	return;
}
$mysql_conn->free_result($result);

// 插入新用户
$pwd_md5 = md5 ( $pwd );
$sql = "INSERT INTO `customer`(`userName`, `password`, `name`, `type`, `status`) VALUES('$username', '$pwd_md5', '$name', '$type', '1')";
$result = $mysql_conn->query ( $sql );
// 插入失败
if ($result != true) {
	// 释放数据库
	$mysql_conn->free_result($result);
	$mysql_conn->close();
	
	echo "Fail to insert date to database.";
	return;
}
// 插入成功，写入SESSION
$sql = 'SELECT `ID`, `type`, `name` FROM `customer` WHERE `userName`="' . $username . '"';
$result = $mysql_conn->query($sql);
$userInfo = $mysql_conn->fetch_array($result);

session_start ();
$_SESSION['user']['ID'] = $userInfo ['ID'];
$_SESSION['user']['Type'] = "user";
$_SESSION['user']['Name'] = $userInfo ['name'];



// 释放数据库
$mysql_conn->free_result($result);
$mysql_conn->close();

echo "Register successfully.";
return;
?>