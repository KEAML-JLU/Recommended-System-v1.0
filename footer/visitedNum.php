<?php
/**
 * 生成测试样本特征文件
 */
require_once '../common/DatabaseConn.php';


// 获取ip
$iipp = $_SERVER["REMOTE_ADDR"];


// 链接数据库
$db_conn = new MySQL();
$db_conn->connect();

$db_conn->query("SET NAMES 'utf8'");//设置为utf8编码

// 加入IP
$sql = "INSERT INTO `count`(`IP`) VALUES('$iipp')"; 
$db_conn->query($sql);

// 获取总次数
$sql = "SELECT COUNT(*) FROM `count`"; 
$result = $db_conn->query($sql);
$row = $db_conn->fetch_array($result);
echo $row[0];


$db_conn->free_result($result);
$db_conn->close();
?>