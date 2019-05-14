<?php
require_once 'Stemmer.php';
require_once 'InfoQuery.php';
/**
 * 分词组件：处理搜索关键字，返回token数组
 * @param unknown $str
 * @return multitype:
 */
function word_filter($str){
	//可以加入过滤虚词
	
	// 1.字母、-、'被保留
	$pattern = "/[^a-zA-Z0-9\\-\\']+/";
	$tokens_str = preg_replace($pattern, " ", strtolower($str));
	// 2.再去掉单引号，如it's -> its，这样就可以被过滤掉了
	$pattern = "/[\\']/";
	$tokens_str = preg_replace($pattern, "", $tokens_str);
	// 3.再将空白（尤指空格）进行压缩
	$pattern = "/\\s+/";
	$tokens_str = preg_replace($pattern, " ", $tokens_str);	
	// 4.再将'-'前后的空格去除
	$pattern = "/\\s*\\-\\s*/";
	$tokens_str = preg_replace($pattern, "-", $tokens_str);	
	
	
	
	$tokens_arr = explode( " ", trim($tokens_str) );
	//去掉停用词
	$stopwords = array("a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "isnt", "it", "its", "itself", "keep", "keeps", "kept", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "names", "named", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "ok", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "puts", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "sees", "serious", "several", "she", "should", "show", "shows", "showed", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
	$tokens_arr_filter = array_diff($tokens_arr, $stopwords);//去掉停用词,数组key（键值关系）会被保留

	// 5.去除含有数字、--的项
	$pattern = "/[0-9]/";
	$pattern2 = "/\\-\\-/";
	foreach($tokens_arr_filter as $key=>$value){
		// BUG修复，将update替换为updates，词根不会改变
		if(strcmp($value, "update") == 0) {
			$tokens_arr_filter[$key] = "updates";
		}
		if(preg_match($pattern, $value) || preg_match($pattern2, $value)){
			unset($tokens_arr_filter[$key]);
		}
	}
	
	return array_values($tokens_arr_filter);//将Key重置成0....n
}

/**
 * 从文章中使用TF-IDF抽取出10个关键词
 * @param Array $terms_arr
 * @return Array 10个关键词
 */
function get10KeyWords($terms_arr){


	//生成信息查询类，查询数据库里的各种信息
	$info_query1 = new InfoQuery();

	$terms_count = array();
	//统计单词频率，实现vector的作用
	foreach($terms_arr as $term){
		if(!array_key_exists($term, $terms_count)){
			$terms_count["$term"] = 1;//初始化
		}else{
			$terms_count["$term"]++;//频率++
		}
	}
	// 	print_r($terms_count);
	//统计单词频率，TF = 频次/总次数；统计单词的文档频率DF
	$term_TF_IDF = Array();//单词tf-idf
	$length = sizeof($terms_arr);//用户搜索的摘要单词总数
	$total = $info_query1->get_num_of_doc();//数据库中文章总数
	while(list($key,$value) = each($terms_count)){//对于每个单词
		// 		echo $key;
		$TF = $value/$length;//归一化
		// 		echo "<br/>";
		$DF = $info_query1->get_lex_df($key);
		// 		echo "<br/>";
		$IDF = $DF == 0 ? 0 : log(($total + 1) / $DF);//如果众多词汇中都不包含该词，即DF=0，则这个词无用。加+1保证该词所有文章都包含时，逆文档率不为0
		// 		echo "<br/>";
		$TF_IDF = $TF * $IDF;
		// 		echo "<br/>";
		$term_TF_IDF["$key"] = $TF_IDF;
	}
	// 	print_r($term_TF_IDF);
	$top10words = Array();
	arsort($term_TF_IDF);//降序排列
	
	//var_dump($term_TF_IDF);
	// 	print_r($term_TF_IDF);
	$k = 0;
	while($key = key($term_TF_IDF)) {
		if($k == 30){
			break;
		}
		$top10words[$k++] = $key;
		next($term_TF_IDF);//下一个
	}
// 	$info_query1->closeConn();
	// 	print_r($top10words);
	return $top10words;
}

/**
 * 从文章中使用TF-IDF抽取出50个关键词
 * @param Array $terms_arr
 * @return Array 10个关键词
 */
function get50KeyWords($terms_arr){


	//生成信息查询类，查询数据库里的各种信息
	$info_query1 = new InfoQuery();

	$terms_count = array();
	//统计单词频率，实现vector的作用
	foreach($terms_arr as $term){
		if(!array_key_exists($term, $terms_count)){
			$terms_count["$term"] = 1;//初始化
		}else{
			$terms_count["$term"]++;//频率++
		}
	}
	// 	print_r($terms_count);
	//统计单词频率，TF = 频次/总次数；统计单词的文档频率DF
	$term_TF_IDF = Array();//单词tf-idf
	$length = sizeof($terms_arr);//用户搜索的摘要单词总数
	$total = $info_query1->get_num_of_doc();//数据库中文章总数
	while(list($key,$value) = each($terms_count)){//对于每个单词
		// 		echo $key;
		$TF = $value/$length;//归一化
		// 		echo "<br/>";
		$DF = $info_query1->get_lex_df($key);
		// 		echo "<br/>";
		$IDF = $DF == 0 ? 0 : log(($total + 1) / $DF);//如果众多词汇中都不包含该词，即DF=0，则这个词无用。加+1保证该词所有文章都包含时，逆文档率不为0
		// 		echo "<br/>";
		$TF_IDF = $TF * $IDF;
		// 		echo "<br/>";
		$term_TF_IDF["$key"] = $TF_IDF;
	}
	// 	print_r($term_TF_IDF);
	$top50words = Array();
	arsort($term_TF_IDF);//降序排列

	//var_dump($term_TF_IDF);
	// 	print_r($term_TF_IDF);
	$k = 0;
	while($key = key($term_TF_IDF)) {
		if($k == 50){
			break;
		}
		$top50words[$k++] = $key;
		next($term_TF_IDF);//下一个
	}
	// 	$info_query1->closeConn();
	// 	print_r($top10words);
	return $top50words;
}


function toDisplay($doc_info, $top10_doc_simility, $cid_name_pair){
	if($doc_info == null){
		return "";
	}
	$content= "为您推荐的会议/期刊：". current($cid_name_pair)."<br/>";
	$k = 0;
	foreach($doc_info as $item){//对于每项，形成一个div
		$content .= '<div class="gs_r">
    	<div class="gs_ri">

        	<h3 class="gs_rt">
            	<a href="' . $item["Link"] . '" target="_blank">
                	' . $item["Title"] . '
                </a>
             </h3>
                			
             <div class="gs_a">
             	' . $item["Author"] . '<br/>
             </div>
        
            <div class="gs_a">
             	发表时间：'. $item["PublishDateFrom"] .'至'. $item["PublishDateTo"] . '<br/>相似度：' . $top10_doc_simility[$k++] . '
             </div>
        
             <div class="gs_rs">
				' . substr($item["Abstract"], 0, 300) . '
             </div>
             <div class="gs_fl">
		<!--
				<a href="#" target="_blank">
                	关键词：' . $item["KeyWord"] . '
                </a>
        -->
                <a href="#" target="_blank">
                 <!--	所属会议/期刊：' . $cid_name_pair[$item["ConferenceID"]] . '-->
					所属会议/期刊：' . $item["ConferenceID"] . '
                </a>
        <!--
             	<a href="#" target="_blank">
                	被引用次数：' . $item["Cited"] . '
                </a>
                <a href="#" target="_blank">
                	被下载次数：' . $item["Downloaded"] . '
                </a>
		-->
          </div>
       </div>
    </div>';
	}

	return $content;
}
/**
 * 根据用户搜索的摘要与被搜索到的文章摘要的相似度，只取出前十名
 * 余弦相似度
 * @param unknown $simiValue
 * @return multitype:unknown
 */
function getTop10($simiValue){

	$top10doc = array();
	$k = 0;
	while($key = key($simiValue)) {
		if($k == 10){
			break;
		}
		$top10doc[$key] = $simiValue[$key];
		next($simiValue);//下一个
// 		$k++;
	}
	reset($simiValue);
	
	// 	var_dump($top10doc);
	// 	var_dump($top10doc_id);
	return $top10doc;
}


/**
 * 计算用户搜索的摘要与被搜索到的文章摘要的相似度
 * 余弦相似度
 * @param Array $abs_arr 用户的摘要(标准化后，即分词、词根)
 * @param Array $doc_id_arr 被搜索到的文章id
 * @return array 搜索到的文章的相似度(降序)：docid->similarity
 */
function similarity($abs_arr, $doc_id_arr, $info_query){
	//生成信息查询类，查询数据库里的各种信息

	$similarity = array();//相似度数值数组：文档Id=>相似度
	foreach($doc_id_arr as $doc_id){//对于每个摘要
		$doc_abs_str = $info_query->getAbs($doc_id);
		
		//标准化：分词，形成以一个空格为单词分隔符的字符串；找词根
		//分词：处理搜索关键字，返回token数组
		$tokens_arr = word_filter(strtolower($doc_abs_str));
		//语言处理组件：对以空格为分隔符的字符串进行找词根，结果为数组形式
		// $tokens_arr = explode( " ", $tokens_str );
		$size = sizeof($tokens_arr);
		$doc_abs_arr = array();
		for($i = 0; $i < $size; $i++){
			$doc_abs_arr[$i] = PorterStemmer::Stem($tokens_arr[$i]);
		}
		//计算相似度
		$similarity[$doc_id] = getCosSimiValue($abs_arr, $doc_abs_arr);
	}
	// 	var_dump($similarity);
	arsort($similarity);//降序排列
	// 	var_dump($similarity);
	return $similarity;
}

/**
 * 计算两个标准化后的摘要（数组）的余弦相似度(摘要全文相似度，也可以提取摘要的关键字进行比较)
 * @param array $abs1
 * @param array $abs2
 */
function getCosSimiValue($abs1, $abs2){
	
	
	//方案1：摘要全文使用词频向量进行比较余弦相似度
	//所有单词
	$abs_merge = array_merge($abs1, $abs2);
	//统计词频
	$abs1_count = array_count_values($abs1);
	$abs2_count = array_count_values($abs2);
	//词频向量。每个值为
	$abs1_vec = array();
	$abs1_vec = array();
	$abs1_size = sizeof($abs1);
	$abs2_size = sizeof($abs2);
	foreach($abs_merge as $term){
		$abs1_vec[$term] = in_array($term, $abs1) ? $abs1_count[$term]/$abs1_size : 0;//是否在abs1中，不在，则0，在则向量在该处的值为词频%
		$abs2_vec[$term] = in_array($term, $abs2) ? $abs2_count[$term]/$abs2_size : 0;
	}//方案1
	
	
// 	//方案2:提取各篇文章的关键字，直接进行向量相似度比较
// 	$abs1_keyWords = get10KeyWords($abs1);
// 	$abs2_keyWords = get10KeyWords($abs2);
	
// 	//所有单词
// 	$abs_keyWords_merge = array_merge($abs1_keyWords, $abs2_keyWords);
	
// 	//向量。
// 	$abs1_vec = array();
// 	$abs1_vec = array();
// 	foreach($abs_keyWords_merge as $term){
// 		$abs1_vec[$term] = in_array($term, $abs1_keyWords) ? 1 : 0;//是否在$abs1_keyWords中，不在，则0，在则向量在该处的值为1
// 		$abs2_vec[$term] = in_array($term, $abs2_keyWords) ? 1 : 0;
// 	}//方案2	
	
	//计算余弦值
	//向量的模
	$abs1_mold = getMold(array_values($abs1_vec));
	$abs2_mold = getMold(array_values($abs2_vec));
	$inner_product = getInner_product(array_values($abs1_vec), array_values($abs2_vec));

	return $inner_product / ($abs1_mold * $abs2_mold);//返回余弦值
}

/**
 * 计算向量模
 * @param unknown $vec_arr
 */
function getMold($vec_arr){
	$sum = 0;
	foreach($vec_arr as $item){
		$sum += $item * $item;
	}
	return sqrt($sum);
}
/**
 * 计算向量内积
 * @param unknown $vec_arr1
 * @param unknown $vec_arr2
 */
function getInner_product($vec_arr1, $vec_arr2){
	$length1 = sizeof($vec_arr1);
	$length2 = sizeof($vec_arr2);
	if($length1 != $length2){
		echo "Dimension of vectors is not consistent.";
		exit;
	}
	$sum = 0;
	for($i = 0; $i < $length1; $i++){
		$sum += $vec_arr1[$i] * $vec_arr2[$i];
	}

	return $sum;
}
/**
 * 计算被检索到的会议的得分
 * @param Array $simiValue 按照相似度排好序的文章，格式: id->similarity
 */
function getConfScore($simiValue){

	//获取文档和所属会议的键值对
	$docid_cid_pair = getDocCidPair(array_keys($simiValue));
	
	//获取相关会议中的文章数
	$flip = array_flip($docid_cid_pair);//去重,cid->docid
	$cid_totalPaperNum_pair = getCPNumPair(array_keys($flip));

	
	//1.获取会议最终得分第一项：会议相似度，格式：cid->conf simi
	$confSimi = array();
	$confSimi = getAvgSimi($simiValue, $docid_cid_pair);
	// $confSimi = getMaxSimi($simiValue, $docid_cid_pair);
	
	//归一化
	$normalizedConfSimi = array();
	$confSimiSum = array_sum($confSimi);

	while($cid = key($confSimi)){//归一化
		$normalizedConfSimi[$cid] = $confSimi[$cid] / $confSimiSum;
		next($confSimi);
	}
	reset($confSimi);
	
	
	//2.获取会议最终得分第二项：文章归一化频率: cid->normalized frequency
	$normalizedFreq = array();
	$normalizedFreq = getNormalizedFreq($docid_cid_pair, $cid_totalPaperNum_pair);


	
	//3.计算会议最终得分
	$alpha = 0.5;
	$beta = 0.5;
	$confScore = array();
	while($cid = key($normalizedConfSimi)) {
		$confScore[$cid] = $alpha * $normalizedConfSimi[$cid] + $beta * $normalizedFreq[$cid];

		next($normalizedConfSimi);
	}
	reset($normalizedConfSimi);

	arsort($confScore);//降序排列
	return $confScore;
}

/**
 * 获取文章id与所属的会议的id键值对
 * @param array &$docids 文章id 
 */
function getDocCidPair($docids){
	//生成信息查询类，查询数据库里的各种信息
	$info_query1 = new InfoQuery();
	
	
	$docid_cid_pair = array();
	foreach ($docids as $docid){
		$docid_cid_pair[$docid] = $info_query1->getCid($docid);
	}
//  	$info_query1->closeConn();
 	
 	return $docid_cid_pair;
}
/**
 * 获取相关会议中的文章数
 * @param unknown $cids
 * @return multitype:NULL
 */
function getCPNumPair($cids){
	//生成信息查询类，查询数据库里的各种信息
	$info_query1 = new InfoQuery();
	
	$cid_PaperNum_pair = array();
	foreach ($cids as $cid){
		$cid_PaperNum_pair[$cid] = $info_query1->getPaperNum($cid);
	}
// 	$info_query1->closeConn();
	
	return $cid_PaperNum_pair;
}
/**
 * 获取会议最终得分第一项：最大相似度
 * @param array $simiValue
 * @param array $docid_cid_pair
 */
function getMaxSimi($simiValue, $docid_cid_pair){
	$maxSimi = array();
	$cids = array_keys(array_flip($docid_cid_pair));//获取所有cid

	foreach($cids as $cid){//初始化
		$maxSimi[$cid] = 0;
	}
	
	while($docid = key($docid_cid_pair)){
		//遍历所有文章的相似度，比较该文章的相似度与该文章所属会议的最大相似度
		if($simiValue[$docid] > $maxSimi[$docid_cid_pair[$docid]]){//若大于
			$maxSimi[$docid_cid_pair[$docid]] = $simiValue[$docid];//更新
		}
		next($docid_cid_pair);
	}
	return $maxSimi;
}
/**
 * 获取会议最终得分第一项：平均相似度
 * @param array $simiValue
 * @param array $docid_cid_pair
 */
function getAvgSimi($simiValue, $docid_cid_pair){
	$avgSimi = array();//每个会议的文章相似度的平均值
	$numOfDoc = array();//每个会议的文章数
	$cids = array_keys(array_flip($docid_cid_pair));//获取所有cid

	foreach($cids as $cid){//初始化
		$avgSimi[$cid] = 0;
		$numOfDoc[$cid] = 0;

	}

	//求和
	while($docid = key($docid_cid_pair)){
		//遍历所有文章的相似度
		$avgSimi[$docid_cid_pair[$docid]] += $simiValue[$docid];
		$numOfDoc[$docid_cid_pair[$docid]]++;
		next($docid_cid_pair);
	}
	reset($docid_cid_pair);

	//求均值
	while($cid = key($avgSimi)){
		$avgSimi[$cid] /= $numOfDoc[$cid];
		next($avgSimi);
	}
	reset($avgSimi);

	return $avgSimi;
}


/**
 * 获取会议最终得分第二项：文章归一化频率: cid->normalized frequency
 * @param array $docid_cid_pair 被搜索到的文章Id与所属会议的id键值对
 * @param array $cid_totalPaperNum_pair 被搜索到的每个会议的数据库中文章总数
 */
function getNormalizedFreq($docid_cid_pair, $cid_totalPaperNum_pair){
	

	
	//1.统计被搜索到的会议的被搜索文章数
	$cid_paperNum_pair = array();
	$cid_paperNum_pair = array_flip($docid_cid_pair);

	//初始化
	while($cid = key($cid_paperNum_pair)){
		$cid_paperNum_pair[$cid] = 0;
		next($cid_paperNum_pair);
	}
	reset($cid_paperNum_pair);
	reset($docid_cid_pair);
	//开始统计
	while($docid = key($docid_cid_pair)){
		$cid_paperNum_pair[$docid_cid_pair[$docid]]++;
		next($docid_cid_pair);
	}
	reset($docid_cid_pair);

	//2.统计被搜索到的会议的被搜索文章数 占该会议的总文章比例
	$cid_paperPercent_pair = array();
	while($cid = key($cid_paperNum_pair)){
		
		$cid_paperPercent_pair[$cid] = $cid_paperNum_pair[$cid] / $cid_totalPaperNum_pair[$cid];
		
		next($cid_paperNum_pair);
	}
	reset($cid_paperNum_pair);
	
	
	//3.频率归一化
	$normalizedFreq = array();
	//求和
	$sum = 0;
	while($cid = key($cid_paperPercent_pair)){
		$sum += $cid_paperPercent_pair[$cid];
		next($cid_paperPercent_pair);
	}
	reset($cid_paperPercent_pair);
	
	//归一化
	while($cid = key($cid_paperPercent_pair)){
		$normalizedFreq[$cid] = $cid_paperPercent_pair[$cid] / $sum;
		next($cid_paperPercent_pair);
	}
	reset($cid_paperPercent_pair);
	return $normalizedFreq;
}
/**
 * 获取会议名称
 * @param unknown $cids
 */
function getConfName($cids){
	//生成信息查询类，查询数据库里的各种信息
	$info_query1 = new InfoQuery();
	$cid_name_pair = array();
	foreach ($cids as $cid){
		$cid_name_pair[$cid] = $info_query1->getConfName($cid);
	}
// 	$info_query1->closeConn();
	return $cid_name_pair;
}
?>