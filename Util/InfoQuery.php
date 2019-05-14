<?php

class InfoQuery{


	private $conn;//数据库
	
	function __construct(){
		//初始化数据库并连接
		$this->conn = new MySQL();
		$this->conn->connect();
	}
	function closeConn(){
		$this->conn->close();
	}
	
	
	
	function get_posting_list($lex_arr){
		$doc_id_list_arr = null;
		foreach($lex_arr as $lex){
			$lex_id["$lex"] = $this->get_lex_id($lex);//获取lexicon在表中的Id
			//若该单词不在词汇表中，这里选择跳过
			if($lex_id["$lex"] == null){
// 				echo $lex."不存在";
 				continue;//直接下一个循环
			}
			//debug
// 			echo "<br/>";
// 			echo $lex."--id:";
// 			echo var_dump($lex_id["$lex"]);
			//debug
			
			$doc_id_list_arr["$lex"] = $this->get_doc_id_list($lex_id["$lex"]);//从倒排表中获取每个单词所在的文章
			//debug
// 			echo "<br/>";
// 			echo "doc id list arr:";
// 			var_dump($doc_id_list_arr["$lex"]);
			//debug
		
		}
// 		var_dump($doc_id_list_arr);
		return $doc_id_list_arr;
		
	}
	
	
	/**
	 * 获取Lex的id,无则返回null
	 * @param String $lex
	 */
	function get_lex_id($lex){
		$sql = "SELECT `ID` FROM `lexicon` WHERE `LexContent` = '$lex'";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row == false ? null : $row["ID"];
	}

	/**
	 * 获取Lex的df,返回0
	 * @param String $lex
	 */
	function get_lex_df($lex){
		$sql = "SELECT `DF` FROM `lexicon` WHERE `LexContent` = '$lex'";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row == false ? 0 : $row["DF"];
	}
	/**
	 * 获取文章总数
	 * @param String $lex
	 */
	function get_num_of_doc(){
		$sql = "SELECT COUNT(*) FROM `doc_collection`";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row == false ? null : $row[0];
	}
		
	/**
	 * 从倒排表中获取该单词（id）所在的文章，以数组形式返回
	 * @param String $lex
	 */
	function get_doc_id_list($lex_id){
		$sql = "SELECT `DocID` FROM `posting_list` WHERE `LexID` = '$lex_id'";
		$result = $this->conn->query($sql);
		$doc_id_list = "";
		while($row = $this->conn->fetch_array($result)){
			$doc_id_list .= $row["DocID"].",";
		}
		$doc_id_list = substr($doc_id_list, 0, strlen($doc_id_list) - 1);//去除最后一个逗号
		return  explode(",", $doc_id_list);//分割成数组

	}
	
	/**
	 * 获取包含任意三个关键字的文章id，为软取合，以数组形式返回，
	 * 根据每个单词的倒排表，包含任意三个单词的摘要就可以作为备选（可被搜索）。
	 * @param Array $doc_id_list_arr
	 */
	function get_doc_ids($doc_id_list_arr){
		if($doc_id_list_arr == null){
			return null;
		}
		
		//求交集，软取合
		$length = sizeof($doc_id_list_arr);//数组长度
		$result = array();//这里是任意三个单词的倒排表的交集
		$doc_id_list_arr_v = array_values($doc_id_list_arr);
		for($i = 0; $i < $length; $i++){
			for($j = $i + 1; $j < $length; $j++){
				$tmp1 = array_intersect($doc_id_list_arr_v[$i], $doc_id_list_arr_v[$j]);
				for($k = $j + 1; $k < $length; $k++){
					$tmp2 = array_intersect($tmp1, $doc_id_list_arr_v[$k]);
					for($m = $k + 1; $m < $length; $m++){
						$tmp3 = array_intersect($tmp2, $doc_id_list_arr_v[$m]);
						$result = array_values(array_unique(array_merge($result,$tmp3)));
					}
				}	
			}
		}
		
		
		
		//求交集，软取合
// 		$curr = current($doc_id_list_arr);
// 		$result = array();//交集结果
// 		while($next = next($doc_id_list_arr)){
// 			$tmp = array_intersect($curr, $next);//求交集
// 			$result = array_merge($result,$tmp);
// 		}
		return array_unique($result);
		
	}
	
	/**
	 * 获取文章的信息，数组格式返回
	 * @param Array $doc_ids
	 */
	function get_doc_info_json($doc_ids){
		if($doc_ids == null){
			return "";
		}
		$res = array();
		//提取出对应的文章
		foreach($doc_ids as $doc_id){
			$sql = "SELECT * FROM `doc_collection` WHERE `ID`='$doc_id'";
			
			$result = $this->conn->query($sql);

			$row = $this->conn->fetch_array($result);
			
			array_push($res, $row);
		}
		return $res;
	}
	
	/**
	 * 获取一篇文章的摘要
	 * @param unknown $doc_id_arr
	 */
	function getAbs($doc_id){
		$sql = "SELECT `Abstract` FROM `doc_collection` WHERE `ID`='$doc_id'";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row["Abstract"];
	}

	/**
	 * 获取文章所属会议的id
	 * @param unknown $cid
	 */
	function getCid($docid){
		$sql = "SELECT `ConferenceID` FROM `doc_collection` WHERE `ID`='$docid'";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row["ConferenceID"];
	}
	
	/**
	 * 获取相关会议中的文章数
	 * @param unknown $cid 会议Id
	 * @return unknown
	 */
	function getPaperNum($cid){
		$sql = "SELECT count(*) FROM `doc_collection` WHERE `ConferenceID`='$cid'";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row[0];
	}
	
	/**
	 * 获取会议id->Name
	 * @param unknown $cid
	 * @return Ambigous <>
	 */
	function getConfName($cid){
		$sql = "SELECT `name` FROM `conference_journal` WHERE `ID`='$cid'";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row[0];		
	}
	
	
	/*********************************************测试部分*****************************/
	
	
	
	/**
	 * 获取所有测试文章摘要
	 */
	function getTestAbs(){
		$sql = "SELECT `ID`,`Abstract` FROM `test`";
		$result = $this->conn->query($sql);
		return $result;
	}
	
	/**
	 * 更新测试样本中预测会议ID
	 * @param unknown $test_paper_ID
	 * @param unknown $predictConfID
	 */
	function UpdateConfPreID($test_paper_ID, $predictConfID){
		$sql = "UPDATE `test` SET `Predict` = '$predictConfID' WHERE `ID` = '$test_paper_ID'";
		$this->conn->query($sql);
	}
	
	/**
	 * 检索正确的文章数
	 * @return unknown
	 */
	function getCorrectNum($i){
		$sql = "SELECT `ConferenceID`, `Predict` FROM `test`";
		$result = $this->conn->query($sql);
		$k = 0;//计数
		while($row = $this->conn->fetch_array($result)){
			if( in_array($row["ConferenceID"], array_slice(explode(",", $row["Predict"]),0,$i) ) ){
				$k++;
			}
		}
		return $k;
	}
	/**
	 * 被检索成功的文章数
	 * @return unknown
	 */
	function getRetrievaledNum(){
		$sql = "SELECT count(*) FROM `test` WHERE `Predict` != '-1' && `Predict` != '-2' && `Predict` != '0' && `Predict` != ''";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row[0];		
	}
	/**
	 * 测试文章总数
	 * @return unknown
	 */
	function getTotalTestCaseNum(){
		$sql = "SELECT count(*) FROM `test`";
		$result = $this->conn->query($sql);
		$row = $this->conn->fetch_array($result);
		return $row[0];
	}
	
	/**
	 * 将预测值设为默认值-2
	 */
	function setPredictToDefault(){
		$sql = "UPDATE `test` SET `Predict` = -2";
		$this->conn->query($sql);
	}
}

?>