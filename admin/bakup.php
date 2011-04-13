<?php
!function_exists('adminmsg') && exit('Forbidden');
S::gp(array('adminitem'));
empty($adminitem) && $adminitem = 'bakout';
@set_time_limit(1800);
require_once (R_P.'admin/table.php');
$job = S::getGP('job');
!$job && $job = 'bakout';
$basename = "$admin_file?adminjob=bakup";
$bakPath = "data/sqlback";
if ($job == 'bakout') {
	//$basename .= "&job=bakout";
	if (empty($action)) {
		$type = S::getGP('t');
		list($pwdb, $otherdb) = N_getTabledb(true);
		if ($type == 'pw') {
			$tables = $pwdb;
		} elseif ($type == 'other') {
			$tables = $otherdb;
		} else {
			$tables = array_merge($pwdb, $otherdb);
		}
		$existTables = array();
		$query = $db->query("SHOW TABLES");
		while ($rt = $db->fetch_array($query, MYSQL_NUM)) {
			$value = trim($rt[0]);
			$existTables[$value] = true;
		}

		include PrintEot('bakup');exit;
	} elseif ($action == 'bak'){
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('tabledb','sizelimit','start','tableid','step','pre','rows'));
		$bak = "#\n# phpwind bakfile\n# version:".$wind_version."\n# time: ".get_date($timestamp,'Y-m-d H:i')."\n# tablepre: $PW\n# phpwind: http://www.phpwind.net\n# --------------------------------------------------------\n\n\n";
		$pre = 'pw_'.get_date($timestamp,'YmdHis').'_'.randstr(5);
		@mkdir(D_P.'/'.$bakPath.'/'.$pre, 0777);
		@chmod(D_P.'/'.$bakPath.'/'.$pre, 0777);
		if(writeover(S::escapePath(D_P.'/'.$bakPath.'/'.$pre.'/bak_table.tmp'), implode("|", $tabledb), 'rb+') && 
		   (writeover(S::escapePath(D_P.'/'.$bakPath.'/'.$pre.'/bak_info.tmp'), $bak, 'rb+'))){
			$j_url = "$basename&action=bakstep&start=$start&tableid=0&sizelimit=$sizelimit&step=$step&pre=$pre&rows=$rows";
			$count = count($tabledb);
			adminmsg('bakup_start', EncodeUrl($j_url), 2);
		}else{
			adminmsg('bakup_error');
		}
		
	} elseif ($action == 'bakstep') {
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('sizelimit','start','tableid','step','pre','rows','totaldb','files'));
		$start = intval($start);
		$bak = "#\n# phpwind bakfile\n# version:".$wind_version."\n# time: ".get_date($timestamp,'Y-m-d H:i')."\n# tablepre: $PW\n# phpwind: http://www.phpwind.net\n# --------------------------------------------------------\n\n\n";
		$db->query("SET SQL_QUOTE_SHOW_CREATE = 0");
		$tablesel = readover(S::escapePath(D_P.'/'.$bakPath.'/'.$pre.'/bak_table.tmp'),'rb');
		$tabledb = explode("|", $tablesel);
		!$tabledb && adminmsg('operate_error');
		if ($tableid >= count($tabledb)){	///完成
			$bakfile = $bakPath.'/'.$pre;
			@unlink(S::escapePath(D_P.'/'.$bakPath.'/'.$pre.'/bak_table.tmp'));
			adminmsg('bakup_out');
		}else{	//未完成
			if (isset($tabledb[$tableid])) {
				$sqlPath = D_P.'/'.$bakPath.'/'.$pre.'/'.$tabledb[$tableid];
				$bakupdata  = bakupdata($tabledb[$tableid], $start);
				if (!$step) {
					$step  = 0;
					$start = 0;
					@mkdir($sqlPath, 0777);
					@chmod($sqlPath, 0777);
					$bakuptable = bakuptable(array($tabledb[$tableid]));
				}
				$f_num = ceil($step/2);
				$filename = $tabledb[$tableid].'_'.$f_num.'.sql';
				$writedata = $bakuptable ? $bakuptable.$bakupdata : $bakupdata;
				$files = $tableid+1;
				trim($writedata) && writeover($sqlPath.'/'.$filename, $bak.$writedata, 'w');
				if($stop==1){
					$step++ ;
				}else{
					$step = 0;
					$tableid++;
					$totaldb++;
				}
				$t_name = $tabledb[$tableid];
				$c_n = $start;
				$f_num = $files++;
				$j_url = "$basename&action=$action&start=$start&tableid=$tableid&sizelimit=$sizelimit&step=$step&pre=$pre&rows=$rows&files=$files&totaldb=$totaldb";
				adminmsg('bakup_step', EncodeUrl($j_url), 0);
			}
		}
	} elseif ($action == 'repair' || $action == 'optimize') {

		!$_POST['tabledb'] && adminmsg('db_empty_tables');
		$table = S::escapeChar(implode(', ',$_POST['tabledb']));

		$db->dbpre = 'pw_';
		if ($action == 'repair') {
			$query = $db->query("REPAIR TABLE $table EXTENDED");
		} else {
			$query = $db->query("OPTIMIZE TABLE $table");
		}
		while ($rt = $db->fetch_array($query)) {
			$rt['Table']  = substr(strrchr($rt['Table'] ,'.'),1);
			$msgdb[] = $rt;
		}
		$db->free_result($query);
		$db->dbpre = $GLOBALS['PW'];

		include PrintEot('bakup');exit;
	}
}elseif ($job == 'bakin') {	///备份恢复
	unset($tabledb);
	$basename .= "&job=bakin";
	if (empty($action)) {
		$path = D_P.$bakPath;
		S::gp(array('fpath'));
		isset($fpath) && $path .= '/' . $fpath;
		$filedb = getBakInDir($path);
		!isset($fpath) && $filedb = array_reverse($filedb);
		include PrintEot('bakup');exit;
	} elseif($action=='bakincheck'){
		S::gp(array('fpath', 'pre', 'dir','delfile'));
		S::isArray($delfile) && $pres = implode("|", $delfile);
		include PrintEot('bakup');exit;
	} elseif($action=='bakin'){
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('pres','pre','fpath','dir'));
		$tmp_pre = 'bakin_table_'.get_date($timestamp,'YmdHis').'_'.randstr(5);
		if ($dir && $fpath) {	#表名文件夹
			if ($pres) {
				$tabledb = explode("|", $pres);
			}else{
				$tabledb = array($pre);
			}
		}elseif(!$dir && $pre){	#备份记录目录列表，需要遍历
			$filedb = getBakInDir(D_P.'/'.$bakPath.'/'.$pre);
			foreach($filedb as $v){
				$tabledb[] = $v['name'];
			}
			$fpath = $pre;
		}
		if(writeover(S::escapePath(D_P.'/'.$bakPath.'/'.$fpath.'/'.$tmp_pre.'.tmp'), implode("|", $tabledb), 'rb+')){
			$j_url="$basename&action=bakinstep&tableid=0&step=0&count=0&pre=$pre&tmp=$tmp_pre&fpath=$fpath";
			$count = count($tabledb);
			adminmsg('bakup_instart',EncodeUrl($j_url),2);
		}else{
			adminmsg('bakin_error');
		}
		
	} elseif($action=='bakinstep'){
		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		S::gp(array('tableid','step','count','pre','tmp','fpath'));
		$tablesel = readover(S::escapePath(D_P.'/'.$bakPath.'/'.$fpath.'/'.$tmp.'.tmp'),'rb');
		$tabledb = explode("|", $tablesel);
		!$tabledb && adminmsg('operate_error');
		if ($tableid >= count($tabledb)){	///完成
			@unlink(S::escapePath(D_P.'/'.$bakPath.'/'.$fpath.'/'.$tmp.'.tmp'));
			updatecache();
			extract(pwCache::getData(D_P . 'data/bbscache/config.php', false));
			adminmsg('operate_success');
		}else{	//未完成
			if (isset($tabledb[$tableid])) {
				if(!$count){
					$count=0;
					$handle=opendir(D_P.'/'.$bakPath.'/'.$fpath.'/'.$tabledb[$tableid]);
					while($file = readdir($handle)){
						if(eregi("^$pre",$file) && eregi("\.sql$",$file)){
							$count++;
						}
					}
				}
				!$step && $step=0;
				bakindata(D_P.'/'.$bakPath.'/'.$fpath.'/'.$tabledb[$tableid].'/'.$tabledb[$tableid].'_'.$step.'.sql');
				$i=$step+1;
				if($count > 1 && $step <= $count){
					$step++;
				}else{
					$tableid++;
				}
				$j_url="$basename&action=bakinstep&tableid=$tableid&step=$step&count=$count&pre=$pre&tmp=$tmp&fpath=$fpath";
				$tb_name = $tabledb[$tableid];
				$tb_sign = $tableid . "/" . count($tabledb);
				$sql_sign = $i . "/" . ($count+1);
				adminmsg('bakup_in',EncodeUrl($j_url),0);
			}
		}
		
	} elseif($action=='del'){
		S::gp(array('delfile'),'P');
		S::gp(array('fpath'),'G');
		if(!$delfile) adminmsg('operate_error');
		foreach($delfile as $key => $value){
			if(!$PW || eregi("^pw_",$value) || eregi("^$PW",$value)){
				$fpath && $value = $fpath.'/'.$value;
				pwCache::deleteData(D_P.'/'.$bakPath.'/'.$value);
				deleteDirectory(D_P.'/'.$bakPath.'/'.$value);
			}
		}
		adminmsg('operate_success');
	}
} elseif ($job =='ptable') {
	$basename .="&job=ptable";
	$db_tlist = (array)$db_tlist;
	if (!$action) {
	if (!$_POST['step']) {

		$tmsgdb  = $postdb = array();
		$tlistdb = $db_tlist;
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_tmsgs%'");
		while($rs=$db->fetch_array($query)){
			if($GLOBALS['PW']){
				$key = substr(str_replace($GLOBALS['PW'],'pw_',$rs['Name']),8);
			}else{
				$key = substr($rs['Name'],5);
			}
			if ($key && !is_numeric($key)) continue;
			$pw_tmsgs = 'pw_tmsgs'.$key;
			@extract($db->get_one("SELECT MIN(tid) AS tmin,MAX(tid) AS tmax FROM $pw_tmsgs"));
			$rs['tmin'] = $tmin;
			$rs['tmax'] = $tmax;
			list($rs['tidmin'],$rs['tidmax'])=maxmin($key);
			$rs['Data_length'] = round(($rs['Data_length']+$rs['Index_length'])/1048576,2);
			$tmsgdb[$key] = $rs;
		}

		$plistdb = $db_plist ? $db_plist : array();
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_posts%'");
		while($rs=$db->fetch_array($query)){
			if($GLOBALS['PW']){
				$key = substr(str_replace($GLOBALS['PW'],'pw_',$rs['Name']),8);
			}else{
				$key = substr($rs['Name'],5);
			}
			if ($key && !is_numeric($key)) continue;
			$rs['sel'] = $key==$db_ptable ? 'checked' : '';
			$pw_posts  = GetPtable($key);
			@extract($db->get_one("SELECT MIN(tid) AS tmin,MAX(tid) AS tmax FROM $pw_posts"));
			$rs['tmin'] = $tmin;
			$rs['tmax'] = $tmax;
			$rs['Data_length'] = round(($rs['Data_length']+$rs['Index_length'])/1048576,2);
			count($plistdb) > 1 && $rs['name'] = $plistdb[$key];
			$postdb[$key] = $rs;
		}
		require_once PrintEot('ptable');

	} elseif ($_POST['step'] == '3') {

		S::gp(array('ttable'),'P');
		if (is_array($ttable)) {
			$ttable = arraySort($ttable,1);
			foreach ($ttable as $key => $value) {
				$key != 0 && !is_numeric($value[1]) && adminmsg('numerics_checkfailed');
			}
		} else {
			$ttable = '';
		}
		setConfig('db_tlist', $ttable);
		updatecache_c();
		adminmsg('operate_success');

	} elseif ($_POST['step'] == '5') {

		S::gp(array('ktable','plistdb'),'P');
		setConfig('db_ptable', $ktable);

		$plist = array();
		$plist[0] = $plistdb[0];
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_posts%'");
		while ($rs = $db->fetch_array($query)) {
			$j = str_replace($PW.'posts','',$rs['Name']);
			if ($j && !is_numeric($j)) continue;
			if ($j) {
				$plist[$j] = $plistdb[$j] ? $plistdb[$j] : '';
			}
		}
		$plist = is_array($plist) && (count($plist) > 1) ? $plist : '';
		setConfig('db_plist', $plist);
		updatecache_c();
		adminmsg('operate_success');
	}
} elseif ($action == 'create') {

	$num_a = array();
	if ($type == 1) {
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_tmsgs%'");
		while ($rs = $db->fetch_array($query)) {
			$j = str_replace($PW.'tmsgs','',$rs['Name']);
			$num_a[] = (int)$j;
		}
		$num = max($num_a)+1;
		$table = 'pw_tmsgs'.$num;
		$CreatTable = $db->get_one("SHOW CREATE TABLE pw_tmsgs");
		$sql = str_replace($CreatTable['Table'],$table,$CreatTable['Create Table']);
		$db->query($sql);
		if ($db_tlist) {
			$tlistdb = $db_tlist;
			$current_tlist = current($tlistdb);
			$tidmax = $current_tlist[1];
		} else {
			$tlistdb = array();
			$tidmax  = 0;
		}
		$tlistdb = (array)$tlistdb;
		@extract($db->get_one("SELECT MAX(tid) AS tid FROM pw_threads"));
		$tidmax = max($tidmax,$tid);
		$tlistdb[$num] = array(1=>($tidmax + 100),2=>'');
		if (count($tlistdb) == 1) {
			$tlistdb[0] = array(1=>'',2=>'');
		}
		$tlistdb = arraySort($tlistdb,1);
		$db_tlist = $tlistdb;
		setConfig('db_tlist', $db_tlist);
	} else {
		$i = 0;
		$plistdb = is_array($db_plist) ? $db_plist : array();
		$plistdb['0'] = $plistdb['0'] ? $plistdb['0'] : '';
		$plist = array(0=>$plistdb['0']);
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_posts%'");
		while ($rs = $db->fetch_array($query)) {
			$j = str_replace($PW.'posts','',$rs['Name']);
			if ($j && !is_numeric($j)) continue;
			$i++;
			if ($j) {
				$plist[$j] = $plistdb[$j] ? $plistdb[$j] : '';
			}
			$num_a[]=$j;
		}
		if ($i == 1) {
			extract($db->get_one("SELECT MAX(pid) AS pid FROM pw_posts"));
			$db->update("REPLACE INTO pw_pidtmp SET pid=".S::sqlEscape($pid,false));
			$num = 1;
		} else{
			$num = max($num_a)+1;
		}
		$table = 'pw_posts'.$num;
		$CreatTable = $db->get_one("SHOW CREATE TABLE pw_posts");
		$sql = str_replace($CreatTable['Table'],$table,$CreatTable['Create Table']);
		$db->query($sql);
		$plist[$num] = '';
		setConfig('db_ptable', $num);
		setConfig('db_plist', $plist);
	}
	updatecache_c();
	adminmsg('operate_success');

} elseif ($action == 'movedata') {

	S::gp(array('step'));

	if (!$step) {

		$table_sel = array();
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_posts%'");
		while ($rs = $db->fetch_array($query)) {
			$key = substr(str_replace($GLOBALS['PW'],'pw_',$rs['Name']),8);
			if ($key && !is_numeric($key)) continue;
			$table_sel[$key] = $rs[Name];
		}
		require_once PrintEot('ptable');

	} else {
		$pwServer['REQUEST_METHOD'] != 'POST' && PostCheck($verify);
		set_time_limit(0);
		$db_bbsifopen && adminmsg('bbs_open');
		S::gp(array('tstart','tend','tfroms','tto','lines','tableid','tmp'));
		if ($step == 1){
			$tfromTable = array_diff($tfroms,array($tto));
			!S::isArray($tfromTable) && adminmsg('table_same',$basename.'&action=movedata');
			$j_url="$basename&action=$action&step=2&tstart=$tstart&tend=$tend&tto=$tto&lines=$lines&tableid=0&tfroms=".implode("|", $tfromTable);
			adminmsg('共'.count($tfromTable).'个表需要转移<br>准备开始转移...',EncodeUrl($j_url),2);
		}
		$tabledb = explode("|", $tfroms);
		!$tabledb && adminmsg('operate_error');
		if ($tableid >= count($tabledb)){	///完成
			$_cacheService = perf::gatherCache('pw_membersdbcache');
			$_cacheService->flush();
			if (Perf::checkMemcache()){
				$_cacheService = L::loadClass('cacheservice', 'utility');
				$_cacheService->flush(PW_CACHE_MEMCACHE);			
			}	
			adminmsg('operate_success',$basename.'&action=movedata');
		}else{
			if(isset($tabledb[$tableid])){
				$tfrom = (int) $tabledb[$tableid];
				$tto   = (int) $tto;
				!$lines && $lines=200;
				!$tstart && $tstart=0;
				$ftable = $tfrom ? 'pw_posts'.$tfrom : 'pw_posts';
				$ttable = $tto   ? 'pw_posts'.$tto   : 'pw_posts';
				if (!$tend) {
					@extract($db->get_one("SELECT MAX(tid) AS tend FROM $ftable"));
				}
				$end = $tstart + $lines;
				$end > $tend && $end = $tend;
				$db->update("INSERT INTO $ttable SELECT * FROM $ftable WHERE tid>".S::sqlEscape($tstart).'AND tid<='.S::sqlEscape($end));
				//$db->update("DELETE FROM $ftable WHERE tid>".S::sqlEscape($tstart)."AND tid<=".S::sqlEscape($end));
				pwQuery::delete($ftable, 'tid>:tid1 AND tid<=:tid2', array($tstart, $end));
				//$db->update("UPDATE pw_threads SET ptable=".S::sqlEscape($tto)."WHERE tid>".S::sqlEscape($tstart)."AND tid<=".S::sqlEscape($end)."AND ptable=".S::sqlEscape($tfrom));
				pwQuery::update('pw_threads', 'tid>:tid AND tid<=:end AND ptable=:ptable', array($tstart, $end, $tfrom), array('ptable'=>$tto));
				Perf::gatherInfo('changeThreadListWithThreadIds', array('tid'=>$tstart+1));
				if ($end < $tend) {
					$step++;
				}else{
					$tableid++;
				}
				$j_url="$basename&action=$action&step=$step&tstart=$end&tend=$tend&tfroms=$tfroms&tto=$tto&lines=$lines&tableid=$tableid";
				adminmsg('table_change',EncodeUrl($j_url),0);
			}
		}
		
	}
} elseif ($action == 'movetmsg') {

	S::gp(array('step','id'));
	$tlistdb = $db_tlist;

	if (!$step) {

		$id < 1 && $id = '';
		$pw_tmsgs = 'pw_tmsgs'.($id > 0 ? intval($id) : '');
		@extract($db->get_one("SELECT MIN(tid) AS tmin,MAX(tid) AS tmax FROM $pw_tmsgs"));
		list($tidmin,$tidmax) = maxmin($id);
		$tiderror = '';
		$tmin<=$tidmin && $tiderror .= "$tmin - ".($tmax > $tidmin ? $tidmin : $tmax)." &nbsp;&nbsp;";
		$tidmax && $tmax > $tidmax && $tiderror .= ($tidmax+1)." - $tmax";
		$tiderror=='' && adminmsg('operate_undefined');
		require_once PrintEot('ptable');

	} else {

		$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
		set_time_limit(0);
		$db_bbsifopen && adminmsg('bbs_open');
		S::gp(array('tstart','lines','tmax','tmin'));
		list($tidmin,$tidmax) = maxmin($id);
		!$lines && $lines=5000;

		if ($tmin <= $tidmin && $step < 3) {
			!$tstart && $tstart = $tmin-1;
			$end  = $tstart + $lines;
			$tend = $tmax > $tidmin ? $tidmin : $tmax;
			$end > $tend && $end = $tend;
			$ttable = GetTtable($end);
			$step = 2;
		} else {
			!$tstart && $tstart = $tidmax;
			$end  = $tstart + $lines;
			$tend = $tmax;
			$end > $tend && $end = $tend;
			$ttable = GetTtable($tstart+1);
			$step = 3;
		}
		$ftable = 'pw_tmsgs'.($id > 0 ? intval($id) : '');
		$ftable == $ttable && adminmsg('table_same');

		$db->update("INSERT INTO $ttable SELECT * FROM $ftable WHERE tid>".S::sqlEscape($tstart).'AND tid<='.S::sqlEscape($end));
		$db->update("DELETE FROM $ftable WHERE tid>".S::sqlEscape($tstart).'AND tid<='.S::sqlEscape($end));

		if ($end < $tend) {
			$j_url = "$basename&action=$action&step=$step&tstart=$end&lines=$lines&tmax=$tmax&tmin=$tmin&id=$id";
			adminmsg('table_change',EncodeUrl($j_url),2);
		} elseif ($step == 2 && $tidmax && $tmax > $tidmax) {
			$step  = 3;
			$j_url = "$basename&action=$action&step=$step&lines=$lines&tmax=$tmax&tmin=$tmin&id=$id";
			adminmsg('table_change',EncodeUrl($j_url),2);
		} else {
			adminmsg('operate_success');
		}
	}
} elseif ($action == 'delttable') {

	S::gp('id','GP',2);
	$rt = $db->get_one("SHOW TABLE STATUS LIKE 'pw_tmsgs$id'");
	if ($rt && $rt['Rows']) {
		adminmsg('deltable_error2');
	}
	$rt && $db->update("DROP TABLE pw_tmsgs$id",0);
	$tlistdb = $db_tlist;
	unset($tlistdb[$id]);
	$db_tlist = count($tlistdb)>1 ? $tlistdb : '';
	setConfig('db_tlist', $db_tlist);
	updatecache_c();
	adminmsg('operate_success');

} elseif ($action == 'delptable') {

	S::gp('id','GP',2);
	if ($id == $db_ptable) {
		adminmsg('delptable_error');
	}
	$rt = $db->get_one("SHOW TABLE STATUS LIKE 'pw_posts$id'");
	if ($rt && $rt['Rows']) {
		adminmsg('deltable_error2');
	}
	$rt && $db->update("DROP TABLE pw_posts$id",0);
	$plistdb = is_array($db_plist) ? $db_plist : array();
	$plistdb['0'] = $plistdb['0'] ? $plistdb['0'] : '';
	$plist = array(0=>$plistdb['0']);
	$query = $db->query("SHOW TABLE STATUS LIKE 'pw_posts%'");
	while ($rs = $db->fetch_array($query)) {
		$j = str_replace($PW.'posts','',$rs['Name']);
		if ($j && !is_numeric($j)) continue;
		if ($j) {
			$plist[$j] = $plistdb[$j] ? $plistdb[$j] : '';
		}
	}
	$plist = is_array($plist) && (count($plist) > 1) ? $plist : '';
	setConfig('db_plist', $plist);
	updatecache_c();
	adminmsg('operate_success');
}}
function arraySort ($array,$sortkey) {
	foreach ($array as $key => $value) {
		$keyValue[$key] = $value[$sortkey];
	}
	arsort($keyValue);
	foreach ($keyValue as $key2 => $value2) {
		$keySort[] = $key2;
	}
	for($i=0; $i<count($keySort); $i++){
		$newArray[$keySort[$i]] = $array[$keySort[$i]]; 
	}
	return $newArray;
}
function bakupdata($tabledb,$start=0){
	global $db,$sizelimit,$tableid,$start,$stop,$rows;
	$tableid=$tableid?$tableid:0;
	$stop=0;
	$db->dbpre = 'pw_';
	//$t_count=count($tabledb);
	//for($i=$tableid;$i<$t_count;$i++){
		$ts=$db->get_one("SHOW TABLE STATUS LIKE ".S::sqlEscape($tabledb));
		$rows=$ts['Rows'];
		$flag = true;
		while($flag){
			$limitadd = S::sqlLimit($start,100000);
			$query = $db->query("SELECT * FROM $tabledb $limitadd");
			$num_F = $db->num_fields($query);

			while ($datadb = $db->fetch_array($query,MYSQL_NUM)){
				$start++;
				$bakupdata .= "INSERT INTO $tabledb VALUES("."'".$db->escape_string($datadb[0])."'";
				$tempdb='';
				for($j=1;$j<$num_F;$j++){
					$tempdb.=",'".$db->escape_string($datadb[$j])."'";
				}
				$bakupdata .=$tempdb. ");\n";
				if($sizelimit && strlen($bakupdata)>$sizelimit*1000){
					$flag = false;
					break;
				}
			}
			$db->free_result($query);
			if($start>=$rows){
				$flag = false;
				$start=0;
			}
		}

		$bakupdata .="\n";
		if($sizelimit && strlen($bakupdata)>$sizelimit*1000){
			$stop=1;
		}
	//}
	
	$db->dbpre = $GLOBALS['PW'];
	return $bakupdata;
}

function bakuptable($tabledb){
	global $db;
	$db->dbpre = 'pw_';
	foreach($tabledb as $key=>$table){
		$creattable.= "DROP TABLE IF EXISTS $table;\n";
		$CreatTable = $db->get_one("SHOW CREATE TABLE $table");
		$CreatTable['Create Table']=str_replace($CreatTable['Table'],$table,$CreatTable['Create Table']);
		$creattable.=$CreatTable['Create Table'].";\n\n";
	}
	$db->dbpre = $GLOBALS['PW'];
	return $creattable;
}

function getBakInDir($path){
	global $PW;
	$filedb = array();
	$handle = opendir($path);
	while($file = readdir($handle)){
		if(!$PW || eregi("^pw_",$file) || eregi("^$PW",$file)){
			if (!is_file($path."/bak_info.tmp")) {
				$bak_info = $path."/".$file."/bak_info.tmp";
			}else{
				$bak_info = $path."/bak_info.tmp";
			}
			$fp=fopen($bak_info,'rb');
			$bakinfo=fread($fp,200);
			fclose($fp);
			$detail=explode("\n",$bakinfo);
			$bk['name']=$file;
			$bk['version']=substr($detail[2],10);
			$bk['time']=substr($detail[3],8);
			$bk['pre']=substr($file,0,$strlen);
			$bk['num']=substr($file,$strlen,strrpos($file,'.')-$strlen);
			$filedb[]=$bk;
		}
	}
	return $filedb;
}

function bakindata($filename) {
	global $db,$charset,$PW;
	$sql=file($filename);
	$tablepre = substr($sql[4], 0, 11) == '# tablepre:' ? trim(substr($sql[4], 12)) : '';
	$query='';
	$num=0;
	$db->dbpre = 'pw_';
	foreach($sql as $key => $value){
		$value=trim($value);
		if(!$value || $value[0]=='#') continue;
		if(eregi("\;$",$value)){
			$query.=$value;
			if(eregi("^CREATE",$query)){
				$extra = substr(strrchr($query,')'),1);
				$tabtype = substr(strchr($extra,'='),1);
				$tabtype = substr($tabtype, 0, strpos($tabtype,strpos($tabtype,' ') ? ' ' : ';'));
				$query = str_replace($extra,'',$query);
				if($db->server_info() > '4.1'){
					$extra = $charset ? "ENGINE=$tabtype DEFAULT CHARSET=$charset;" : "ENGINE=$tabtype;";
				}else{
					$extra = "TYPE=$tabtype;";
				}
				$query .= $extra;
			}elseif(eregi("^INSERT",$query)){
				$query='REPLACE '.substr($query,6);
			}
			if ($tablepre && $tablepre != $PW) {
				$query = str_replace(array(" $tablepre","`$tablepre"," '$tablepre"),array(" $PW","`$PW"," '$PW"), $query);
			}
			$db->query($query);
			$query='';
		} else{
			$query.=$value;
		}
	}
	$db->dbpre = $GLOBALS['PW'];
}
function deleteDirectory($directory, $empty = false) { 
    if(substr($directory,-1) == "/") { 
        $directory = substr($directory,0,-1); 
    } 

    if(!file_exists($directory) || !is_dir($directory)) { 
        return false; 
    } elseif(!is_readable($directory)) { 
        return false; 
    } else { 
        $directoryHandle = opendir($directory); 
        
        while ($contents = readdir($directoryHandle)) { 
            if($contents != '.' && $contents != '..') { 
                $path = $directory . "/" . $contents; 
                
                if(is_dir($path)) { 
                    deleteDirectory($path); 
                } else { 
                    unlink($path); 
                } 
            } 
        } 
        
        closedir($directoryHandle); 

        if($empty == false) { 
            if(!rmdir($directory)) { 
                return false; 
            } 
        } 
        
        return true; 
    } 
} 

?>