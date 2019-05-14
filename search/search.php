<?php
/**
 * 推荐
 */

require_once "../Util/Stemmer.php";
require_once '../Util/functions.php';
require_once '../common/DatabaseConn.php';

ini_set ('memory_limit', '256M');    
// 链接数据库
$db_conn = new MySQL();
$db_conn->connect();

$db_conn->query("SET NAMES 'utf8'");//设置为utf8编码

/************************************ 变量定义区 ***********************************/

//会议/期刊数量
$con_num = 67;

//数据库中文章总数
$trainSample_num = 0;
$sql = "SELECT count(*) FROM `doc_collection`";
$result = $db_conn->query($sql);
$row = $db_conn->fetch_array($result );
$trainSample_num = $row[0];
$db_conn->free_result($result);

//特征
$fea = array();


/************************************ 计算区 ***********************************/


	// 1.读入该类的特征文件,格式fea_index <-> fea_word
	$fea = array();
	$feafile = fopen("featureDictionary.txt", "r") or die("Unable to open file!");
	while(!feof($feafile)){
		$line = array();
		$line = explode(" ", str_replace("\n", "", fgets($feafile)));
		if(sizeof($line) == 2){
			$fea[$line[0]] = $line[1];
		}
	}
	fclose($feafile);
	
	

		// 1.分词组件：处理搜索关键字，返回token数组
		$tokens_arr = array();
		$tokens_arr = word_filter(strtolower($_POST["abs"]));
	
		// 2.语言处理组件：对以空格为分隔符的字符串找词根，结果为数组形式
		// $tokens_arr = explode( " ", $tokens_str );
		$size = sizeof($tokens_arr);
		$terms_arr = array();
		for($j = 0; $j < $size; $j++){
			$terms_arr[$j] = PorterStemmer::Stem($tokens_arr[$j]);
		}
		
		// 3.统计类特征在该篇文章摘要中的词频
		$terms_count = array();
		// 4.统计单词频次，只统计类特征中的词
		foreach($terms_arr as $term){
			if(!in_array($term, $fea)){//若该单词不在类的特征中，则跳过
				continue;
			}
			if(!array_key_exists($term, $terms_count)){
				$terms_count["$term"] = 1;//初始化
			}else{
				$terms_count["$term"]++;//频率++
			}
		}
		
		// 5.统计单词频率，TF = 频次/总次数；统计单词的文档频率DF
		$term_TF_IDF = Array();//单词tf-idf
		$length = sizeof($terms_arr);//用户搜索的摘要单词总数
		$total = $trainSample_num;//数据库中文章总数
		while(list($key,$value) = each($terms_count)){//对于每个单词
			// 		echo $key;
			$TF = $value / $length;//归一化
			// 		echo "<br/>";
			$DF = get_lex_df($key, $db_conn);
			// 		echo "<br/>";
			$IDF = $DF == 0 ? 0 : log(($total + 1) / $DF);//如果众多词汇中都不包含该词，即DF=0，则这个词无用。加+1保证该词所有文章都包含时，逆文档率不为0
			// 		echo "<br/>";
			$TF_IDF = $TF * $IDF;
			// 		echo "<br/>";
			$term_TF_IDF["$key"] = $TF_IDF;

		}
		

		
		$train_instance = array();//格式为：fea_index <-> tfidf value
		$fea_flip = array_flip($fea);// 键值颠倒一下：fea_word <-> fea_index
		foreach($term_TF_IDF as $fea_word => $fea_tfidf){
			$train_instance[$fea_flip[$fea_word]] = $fea_tfidf;
		}
		ksort($train_instance);//根据键，以升序对关联数组进行排序
		
		$i = 0;
		$feature_input = array();	// 输入摘要的特征向量
		$sample_keys = array_keys($train_instance);
		foreach($fea as $key => $value){
			if(in_array($key, $sample_keys)){
				$feature_input[$i] = $train_instance[$key];
				
			}else{
				$feature_input[$i] = 0;
			}
			$i++;
		}
		
		//var_dump($feature_input);
		
		
		$i = 0;
		// 读入 theta[66][11521]
		$theta = array();
		$thetaFile = fopen("theta.csv", "r") or die("Unable to open file!");
		while(!feof($thetaFile)){
			$line = array();
			$line = explode(",", str_replace("\n", "", fgets($thetaFile)));
			for($j = 0; $j < sizeof($line); $j++){
				$theta[$i][$j] = $line[$j];
			}
			$i++;	

		}
		fclose($thetaFile);
		

		// 计算结果
		$result = array();
		for($i = 0; $i < 66; $i++){
			$result[$i] = 0;
			for($j = 0; $j < 11521; $j++){
				$result[$i] += $feature_input[$j] * $theta[$i][$j];
			}
		}
		
		//var_dump($result);
		
		// $first_index = array_keys($result, max($result));
		// for($i = 0; $i < sizeof($first_index); $i++){
			// unset($result[$first_index[$i]]);			
		// }
		// $second_index = array_keys($result, max($result));
		// for($i = 0; $i < sizeof($second_index); $i++){
			// unset($result[$second_index[$i]]);
		// }
		// $third_index = array_keys($result, max($result));

		//类别+1
		// for($i = 0; $i < sizeof($first_index); $i++){
			// $first_index[$i]++;			
		// }
		// for($i = 0; $i < sizeof($second_index); $i++){
			// $second_index[$i]++;			
		// }
		// for($i = 0; $i < sizeof($third_index); $i++){
			// $third_index[$i]++;			
		// }		

		// $conf_ids = array_merge($first_index, $second_index, $third_index);
		
		// $conf_info = array();
		// for($i = 0; $i < sizeof($conf_ids); $i++){
			// $info = array();
			// $sql = "SELECT * FROM `conference_journal` WHERE `ID`='$conf_ids[$i]'";
			// $result = $db_conn->query($sql);
			// $row = $db_conn->fetch_array($result);
			// $info['name'] = $row['name'];
			// $info['type'] = $row['type'];
			// $info['link'] = $row['link'];
			// $conf_info[$i] = (object)$info;
			// $db_conn->free_result($result);
		// }
		// echo json_encode($conf_info);
		
		// 转换为键值对
		for($i = 1; $i <= sizeof($result); $i++){
			$result_kv["$i"] = $result[$i-1];
		}
		// 根据值降序排序
		arsort($result_kv);
		$keys = array_keys($result_kv);
		$conf_info = array();
		$journal_info = array();
		for($i = 0; $i < sizeof($result_kv); $i++){
			$info = array();
			$sql = "SELECT * FROM `conference_journal` WHERE `ID`='$keys[$i]'";
			$result = $db_conn->query($sql);
			$row = $db_conn->fetch_array($result);
			$info['name'] = $row['name'];
			$info['type'] = $row['type'];
			$info['link'] = $row['link'];
			$info['isconf'] = $row['isconf'];
			if($info['isconf'] == 1){
				array_push($conf_info, (object)$info);
			}else{
				array_push($journal_info, (object)$info);
			}
			$db_conn->free_result($result);
		}		
		
		//var_dump($conf_info);
		//var_dump($journal_info);
		// 选取一定数量的journal and conference 
		$rank_n = $_POST["rank_num"];
		$info = array();
		for($i = 0; $i < $rank_n; $i++){
			$info[$i] = $journal_info[$i];	
		}
		for($i = $rank_n; $i < $rank_n*2; $i++){
			$info[$i] = $conf_info[$i-$rank_n];	
		}
		
		echo json_encode($info);
		
		
		$db_conn->close();
/**
 * 获取Lex的df,返回0
 * @param String $lex
 */
function get_lex_df($lex, &$db_conn){
	$sql = "SELECT `DF` FROM `lexicon` WHERE `LexContent` = '$lex'";
	$result = $db_conn->query($sql);
	$row = $db_conn->fetch_array($result);
	return $row == false ? 0 : $row["DF"];
}
?>
