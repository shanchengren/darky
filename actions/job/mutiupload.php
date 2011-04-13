<?php
!defined('P_W') && exit('Forbidden');

if (empty($_POST['step'])) {
	
	$_G['uploadtype'] && $db_uploadfiletype = $_G['uploadtype'];
	$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();
	$filetype = '';
	foreach ($db_uploadfiletype as $key => $value) {
		$filetype .= ($filetype ? ',' : '') . $key . ':' . $value;
	}
	$pwServer['HTTP_USER_AGENT'] = 'Shockwave Flash';
	$swfhash = GetVerify($winduid);
	
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><swf><filetype>{$filetype}</filetype><uid>{$winduid}</uid><step>2</step><verify>{$swfhash}</verify></swf>";

} else {
	
	define('AJAX', 1);
	S::gp(array(
		'uid',
		'type',
		'verify'
	), 'P');
	S::gp(array('type'));

	$uid = intval($uid);
	$pwServer['HTTP_USER_AGENT'] = 'Shockwave Flash';
	$swfhash = GetVerify($uid);
	checkVerify('swfhash');

	if (!$db_allowupload) {
		Showmsg('upload_close');
	}
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$rt = $userService->get($uid);//groupid,memberid
	(!$rt) && Showmsg('not_login');
	$groupid = $rt['groupid'] == '-1' ? $rt['memberid'] : $rt['groupid'];
	if (file_exists(D_P . "data/groupdb/group_$groupid.php")) {
		//* require_once pwCache::getPath(S::escapePath(D_P . "data/groupdb/group_$groupid.php"));
		pwCache::getData(S::escapePath(D_P . "data/groupdb/group_$groupid.php"));
	} else {
		//* require_once pwCache::getPath(D_P . 'data/groupdb/group_1.php');
		pwCache::getData(D_P . 'data/groupdb/group_1.php');
	}
	if ($_G['allowupload'] == 0) {
		Showmsg('upload_group_right');
	}

	if ($type == 'diary') {
		$db_uploadfiletype = L::config('o_uploadsize', 'o_config');
	} else {
		$_G['uploadtype'] && $db_uploadfiletype = $_G['uploadtype'];
	}
	$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();

	L::loadClass('mutiupload', 'upload', false);
	$mutiupload = new MutiUpload($uid);
	PwUpload::upload($mutiupload);
	echo pwJsonEncode($mutiupload->getAttachInfo());
	ajax_footer();
}

function showExtraMsg($msg) {
	echo $msg;
	ajax_footer();
}