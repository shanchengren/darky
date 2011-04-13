<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class ArticleUpload extends uploadBehavior {
	
	var $db;
	var $attachs;
	var $replacedb = array();

	function ArticleUpload() {
		global $db,$db_ifathumb,$db_athumbsize,$db_uploadfiletype,$winduid;
		parent::uploadBehavior();
		$this->uid = $winduid;
		$this->db =& $db;
		$this->ifthumb =& $db_ifathumb;
		$this->thumbsize =& $db_athumbsize;
		$this->ftype = !is_array($db_uploadfiletype) ? unserialize($db_uploadfiletype) : $db_uploadfiletype;
	}

	function transfer($flashatt, $savetoalbum = 0, $albumid = 0) {
		if (!$uploaddb = PwUpload::getMutiUpload($flashatt, $this->uid, $this, $savetoalbum, $albumid)) {
			return false;
		}
		foreach ($uploaddb as $rt) {
			$this->attachs[] = array(
				'id'		=> $rt['aid'],
				'attname'	=> 'attachment',
				'name'      => $rt['name'],
				'type'      => $rt['type'],
				'fileuploadurl' => $rt['fileuploadurl'],
				'size'      => $rt['size'],
				'descrip'	=> str_replace('\\','', $rt['descrip']),
				'ifthumb'	=> $rt['ifthumb']
			);
		}
		$pw_attachs = L::loadDB('attachs', 'forum');
		$pw_attachs->delete(array_keys($uploaddb));
	}

	function allowType($key) {
		list($t) = explode('_', $key);
		return in_array($t, array('replace', 'attachment'));
	}

	function getFilePath($currUpload) {
		global $timestamp;
		$prename  = substr(md5($timestamp . $currUpload['id'] . randstr(8)),10,15);
		$filename = "{$this->uid}_$prename." . preg_replace('/(php|asp|jsp|cgi|fcgi|exe|pl|phtml|dll|asa|com|scr|inf)/i', "scp_\\1", $currUpload['ext']);
		$savedir = $this->getSaveDir($currUpload['ext']);
		return array($filename, $savedir);
	}

	function update($uploaddb) {
		foreach ($uploaddb as $value) {
			$value['descrip']	= S::escapeChar(S::getGP('atc_desc'.$value['id'], 'P'));
			$value['name']      = stripslashes($value['name']);
			$this->attachs[] = $value;
		}
		return $uploaddb;
	}

	function getSaveDir($ext) {
		global $db_attachdir;
		$savedir = 'cms_article/';
		if ($db_attachdir) {
			if ($db_attachdir == 2) {
				$savedir .= "Type_$ext/";
			} elseif ($db_attachdir == 3) {
				$savedir .= 'Mon_'.date('ym').'/';
			} elseif ($db_attachdir == 4) {
				$savedir .= 'Day_'.date('ymd').'/';
			}
		}
		return $savedir;
	}

	function allowThumb() {
		return (int) $this->ifthumb;
	}

	function allowWaterMark() {
		global $db_watermark;
		return (int)$db_watermark;
	}

	function getThumbInfo($filename, $dir) {
		return array(
			array($filename, 'thumb/' . $dir, $this->thumbsize)
		);
	}

	function getAttachs() {
		return $this->attachs;
	}

	function getAids() {
		return array_keys($this->attachs);
	}

	function getAttNum() {
		return count($this->attachs);
	}
}

class ArticleModify extends uploadBehavior {

	var $db;
	var $attach;
	var $attachs;

	function ArticleModify($aid) {
		global $db,$db_ifathumb,$db_athumbsize,$db_uploadfiletype,$_G;
		parent::uploadBehavior();

		$this->db =& $db;
		$this->attach = $this->db->get_one("SELECT * FROM pw_cms_attach a LEFT JOIN pw_cms_article at USING(article_id) WHERE a.attach_id=" . S::sqlEscape($aid));
		$this->ifthumb =& $db_ifathumb;
		$this->thumbsize =& $db_athumbsize;
		!is_array($db_uploadfiletype) && $db_uploadfiletype = unserialize($db_uploadfiletype);
		$this->ftype =& $db_uploadfiletype;
	}

	function check() {
		global $db_allowupload, $winddb, $groupid, $_G, $windid, $winduid, $manager;
		if (empty($this->attach)) {
			return 'job_attach_error';
		}
		if (!$db_allowupload) {
			return 'upload_close';
		}
		if (!($winduid == $this->attach['userid'] || S::inArray($windid, $manager))) {
			return 'modify_noper';
		}
		return true;
	}

	function allowType($key) {
		list(, $t) = explode('_', $key);
		return $t == $this->attach['attach_id'];
	}

	function getFilePath($currUpload) {
		$arr = explode('/', $this->attach['attachurl']);
		$filename = array_pop($arr);
		$savedir  = $arr ? implode('/',$arr) . '/' : '';
		
		return array($filename, $savedir);
	}

	function allowThumb() {
		return $this->ifthumb;
	}

	function allowWaterMark() {
		global $db_watermark;
		return (int)$db_watermark;
	}
	
	function getThumbInfo($filename, $dir) {
		return array(
			array($filename, 'thumb/' . $dir, $this->thumbsize)
		);
	}

	function update($uploaddb) {
		global $timestamp;
		foreach ($uploaddb as $value) {
			$value['name'] = addslashes($value['name']);
			$aid = $value['id'];
			pwQuery::update('pw_cms_attach', 'attach_id=:attach_id', array($aid), array(
				'name'		=> $value['name'],			'type'		=> $value['type'],
				'size'		=> $value['size'],			'attachurl'	=> $value['fileuploadurl'],
				'uploadtime'=> $timestamp,				'ifthumb'	=> $value['ifthumb']
			));
		}
		$this->attachs = $uploaddb;
		return true;
	}

	function getAttachName() {
		$array = current($this->attachs);
		return $array['name'];
	}
}
?>