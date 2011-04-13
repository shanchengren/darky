<?php
!defined('P_W') && exit('Forbidden');

header("Content-type: text/html; charset=$db_charset");

S::gp(array('pid'), 'G');

$attachs = array();
require_once(R_P.'require/credit.php');
$alltype = '';
foreach ($credit->cType as $key => $value) {
	$alltype .= "<option value=\"$key\">" . $value . "</option>";
}

if ($tid && $pid) {
	
	//$pw_attachs = L::loadDB('attachs', 'forum');
	//$attachs = $pw_attachs->getByTid($tid, $pid == 'tpc' ? 0 : intval($pid));
	/*
	$attach = '';
	if ($attachs = $pw_attachs->getByTid($tid, $pid == 'tpc' ? 0 : intval($pid))) {
		foreach ($attachs as $key => $value) {
			list($value['attachurl']) = geturl($value['attachurl'], 'lf');
			$attach .= "'$key' : ['$value[name]', '$value[size]', '$value[attachurl]', '$value[type]', '$value[special]', '$value[needrvrc]', '$value[ctype]', '$value[descrip]'],";
		}
		$attach = rtrim($attach,',');
	}
	*/
}
/*
$_G['uploadtype'] && $db_uploadfiletype = $_G['uploadtype'];
$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();

$uploadfilesize = ' ';
foreach ($db_uploadfiletype as $key => $value) {
	$uploadfilesize .= $key.':'.$value.'KB; ';
}
*/

require_once PrintEot('wysiwyg_editor_attach');
define('AJAX', 1);
pwOutPut();
exit;