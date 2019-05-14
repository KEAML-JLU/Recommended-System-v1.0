<?php

// 时区设置
date_default_timezone_set('Asia/Shanghai');



//SQL注入过滤函数，所有插入的数据库的变量先经过此函数过滤
define("XH_PARAM_INT",0);    //数值型变量
define("XH_PARAM_TXT",1);    //文本型变量
function PAPI_GetSafeParam($pi_strName, $pi_iType = XH_PARAM_TXT)
{

	// INT
	if ( XH_PARAM_INT == $pi_iType)
	{
		if (is_numeric($pi_strName))
			return $pi_strName;
		else
			return 0;
	}

	// String
	$pi_strName = str_replace(" ", "",$pi_strName);
	$pi_strName = str_replace("&", "&amp;",$pi_strName);
	$pi_strName = str_replace("<", "&lt;",$pi_strName);
	$pi_strName = str_replace(">", "&gt;",$pi_strName);
	if ( get_magic_quotes_gpc() )
	{
		$pi_strName = str_replace("\\\"", "&quot;",$pi_strName);
		$pi_strName = str_replace("\\''", "&#039;",$pi_strName);
	}
	else
	{
		$pi_strName = str_replace("\"", "&quot;",$pi_strName);
		$pi_strName = str_replace("'", "&#039;",$pi_strName);
	}
	return $pi_strName;
}
