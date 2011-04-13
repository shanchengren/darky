<?php
!defined('P_W') && exit('Forbidden');
@include_once (R_P . 'lib/base/basedb.php');

class PW_PollsDB extends BaseDB {
	var $_tableName = 'pw_polls';
	
	/**
	 * 
	 * 获取最新投票帖数据
	 * @param array $fid 板块ID
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function getSourceByPostdate($fid,$num,$order = 'DESC'){
		$num = intval($num);
		$sqlAdd = $this->buildConditions($fid);
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$query = $this->_db->query("SELECT t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies FROM $this->_tableName p LEFT JOIN pw_threads t USING(tid) $sqlAdd AND t.ifshield != 1 AND t.locked != 2  ORDER BY t.postdate $order " . S::sqlLimit($num));
		return $this->_getAllResultFromQuery ( $query );
	}
	
	/**
	 * 
	 * 按即将截止获取投票帖数据
	 * @param array $fid 板块ID
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function getSourceByEndtime($fid,$num = 100,$order = 'DESC'){
		$num = intval($num);
		$sqlAdd = $this->buildConditions($fid);
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$query = $this->_db->query("SELECT p.timelimit,t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies FROM $this->_tableName p LEFT JOIN pw_threads t USING(tid) $sqlAdd AND t.ifshield != 1 AND t.locked != 2  ORDER BY pollid $order ".S::sqlLimit($num));
		return $this->_getAllResultFromQuery ( $query );
	}
	
	/**
	 * 
	 * 按热门投票获取投票帖数据
	 * @param array $fid 板块ID
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function getSourceByVoters($fid,$num = 10,$order = 'DESC'){
		$num = intval($num);
		$sqlAdd = $this->buildConditions($fid);
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$query = $this->_db->query("SELECT p.timelimit,t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies FROM $this->_tableName p LEFT JOIN pw_threads t USING(tid) $sqlAdd AND t.ifshield != 1 AND t.locked != 2  ORDER BY p.voters $order" . S::sqlLimit($num));
		return $this->_getAllResultFromQuery ( $query );
	}

	/**
	 * 
	 * 按回复数获取投票帖数据
	 * @param array $fid 板块ID
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function getSourceByReplys($fid,$num,$order = 'DESC'){
		$num = intval($num);
		$sqlAdd = $this->buildConditions($fid);
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$query = $this->_db->query("SELECT p.timelimit,t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies FROM $this->_tableName p LEFT JOIN pw_threads t USING(tid) $sqlAdd AND t.ifshield != 1 AND t.locked != 2  ORDER BY t.replies $order " . S::sqlLimit($num));
		return $this->_getAllResultFromQuery ( $query );
	}

	/**
	 * 
	 * 按点击数获取投票帖排行
	 * @param array $fid 板块ID
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function getSourceByHits($fid,$num,$order = 'DESC'){
		$num = intval($num);
		$sqlAdd = $this->buildConditions($fid);
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$query = $this->_db->query("SELECT p.timelimit,t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies FROM $this->_tableName p LEFT JOIN pw_threads t USING(tid) $sqlAdd AND t.ifshield != 1 AND t.locked != 2  ORDER BY t.hits $order " . S::sqlLimit($num));
		return $this->_getAllResultFromQuery ( $query );
	}
	
	/**
	 * 
	 * 组装搜索条件
	 * @param string $fid 版块id
	 * @return string
	 */
	function buildConditions($fid) {
		global $timestamp;
		$sqlAdd = ' WHERE t.postdate + p.timelimit*86400 >= ' . $timestamp;
		if ($fid) $sqlAdd .= " AND t.fid IN ($fid)";
		$sqlAdd .= ' AND t.ifcheck = 1  AND t.fid != 0' ;
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqlAdd .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		return $sqlAdd;
	}
	
	function _getBlackListedTids() {
		global $db_tidblacklist;
		return $db_tidblacklist;
	}
}

?>