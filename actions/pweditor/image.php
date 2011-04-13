<?php
!defined('P_W') && exit('Forbidden');

header("Content-type: text/html; charset=$db_charset");
S::gp(array('job', 'tabs'));

$photoEditor = ($db_phopen && $winduid);
if (!$photoEditor && $job) {
	Showmsg('undefined_action');
}
extract(pwCache::getData(D_P . 'data/bbscache/o_config.php', false));

if ($tabs) {
	$tabs = explode(',', $tabs);
} else {
	$tabs = array('local', 'album', 'network');
}
$photoEditor || $tabs = array_diff($tabs, array('album'));
($db_allowupload && $_G['allowupload']) || $tabs = array_diff($tabs, array('local'));

if ($job == 'listphotos') {
	/* ajax 请求获取相片列表 */
	//define('AJAX', 1);
	S::gp(array('aid'));
	
	require_once(R_P . 'u/require/core.php');
	L::loadClass('photo', 'colony', false);
	$photoService = new PW_Photo($winduid, 0, 1, 0);
	$photoService->setPerpage($photoService->getAlbumNumByUid());

	$ajaxPhotos = array();
	$result = $photoService->getPhotoListByAid($aid,false,false);
	list(,$photos) = $result;//$albumInfo,$photos
	if (S::isArray($photos)) {
		foreach ($photos as $photo) {
			$lastpos = strrpos($photo['path'],'/');
			$ajaxPhotos[] = array(
				'pid' => $photo['pid'],
				'thumbpath' => $photo['path'],
				'ifthumb' => $photo['ifthumb'],
				//'path' => dirname($photo['path']).'/'.substr($photo['path'],$lastpos+3),
				'path' => $photo['path'],
				'pintro' => $photo['pintro']
			);
		}
		$ajaxPhotos = pwJsonEncode($ajaxPhotos);
		echo "success\t{$ajaxPhotos}";
	} else {
		Showmsg('data_error');
	}
	//ajax_footer();
	exit;
}

$albums = $photos = array();
if (in_array('local', $tabs) || $photoEditor) {
	require_once(R_P . 'u/require/core.php');
	L::loadClass('photo', 'colony', false);
	$photoService = new PW_Photo($winduid, 0, 1, 0);
	$photoService->setPerpage($photoService->getAlbumNumByUid());
	$result = $photoService->getAlbumBrowseList();
	list(,$albums) = $result;
	if (in_array('album', $tabs)) {
		$aid = $albums[0]['aid'];
		$aid > 0 && list(,$photos) = $photoService->getPhotoListByAid($aid, false);
	}
}
$currentCss = array('local' => '', 'album' => '', 'network' => '');
$display = array('local' => 'none', 'album' => 'none', 'network' => 'none');

$currentTab = current($tabs);
$currentCss[$currentTab] = ' current';
$display[$currentTab] = '';

require_once PrintEot('wysiwyg_editor_photos');
define('AJAX', 1);
pwOutPut();exit;