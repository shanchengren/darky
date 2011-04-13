<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('upload', '', false);

class AttUpload extends uploadBehavior {

	var $db;
	var $pw_attachs;
	var $post;
	var $forum;
	var $uid;

	var $ifupload;
	var $uptype;
	var $ifthumb;
	var $thumbsize;
	var $uploadmoney;
	var $uploadcredit;
	var $uploadImgNum;

	var $flashatt = array();
	var $savetoalbum;
	var $albumid;

	var $attachs = array();
	var $idrelate = array();
	var $replacedb = array();
	var $elementpic = array();

	function AttUpload($uid, $flashatt = null, $savetoalbum = 0, $albumid = 0) {
		global $db,$pwforum,$pwpost,$db_ifathumb,$db_athumbsize,$uploadmoney,$uploadcredit,$db_uploadfiletype;
		parent::uploadBehavior();

		$this->pw_attachs = L::loadDB('attachs', 'forum');
		$this->uid = $uid;
		$this->db =& $db;
		$this->forum =& $pwforum;
		$this->post =& $pwpost;

		if ($pwforum->forumset['ifthumb'] == 1) {
			$this->ifthumb	 = 1;
			$this->thumbsize = $pwforum->forumset['thumbsize'];
		} elseif ($pwforum->forumset['ifthumb'] == 2) {
			$this->ifthumb	 = 0;
			$this->thumbsize = 0;
		} else {
			$this->ifthumb	 = $db_ifathumb;
			$this->thumbsize = $db_athumbsize;
		}
		$this->uploadmoney =& $uploadmoney;
		$this->uploadcredit =& $uploadcredit;
		$this->ftype =& $db_uploadfiletype;
		$this->uploadImgNum = 0;
		$this->uptype = 'all';
		$this->setFlashAtt($flashatt, $savetoalbum, $albumid);
	}

	function check() {
		global $db_allowupload;
		if (!$db_allowupload) {
			Showmsg('upload_close');
		} elseif (!$this->forum->allowupload($this->post->user, $this->post->groupid)) {
			Showmsg('upload_forum_right');
		} elseif (!$this->forum->foruminfo['allowupload'] && $this->post->_G['allowupload'] == 0) {
			Showmsg('upload_group_right');
		}
		if ($this->post->user['uploadtime'] < $GLOBALS['tdtime']) {
			$this->post->user['uploadnum'] = 0;
		}
		if (($this->post->user['uploadnum'] + count($_FILES) + count($this->flashatt)) > $this->post->_G['allownum']) {
			Showmsg('upload_num_error');
		}
		if ($this->post->_G['allowupload'] == 1 && $this->uploadmoney) {
			global $credit;
			require_once(R_P.'require/credit.php');
			if ($this->uploadmoney < 0 && $credit->get($this->post->uid, $this->uploadcredit) < abs($this->uploadmoney)) {
				$GLOBALS['creditname'] = $credit->cType[$this->uploadcredit];
				Showmsg('upload_money_limit');
			}
		}
	}

	function execute() {
		$this->transfer();
		PwUpload::upload($this);
	}

	function setReplaceAtt($replacedb) {
		if ($replacedb && is_array($replacedb)) {
			$this->replacedb = $replacedb;
		}
	}

	function setFlashAtt($flashatt, $savetoalbum, $albumid) {
		if ($flashatt && is_array($flashatt)) {
			$this->flashatt = $flashatt;
		}
		$this->savetoalbum = $savetoalbum;
		$this->albumid = $albumid;
	}

	function transfer() {
		if (!$uploaddb = PwUpload::getMutiUpload($this->flashatt, $this->post->uid, $this, $this->savetoalbum, $this->albumid)) {
			return false;
		}
		global $db_enhideset,$db_sellset,$db_ifpwcache,$timestamp;
		foreach ($uploaddb as $rt) {
			$this->attachs[$rt['aid']] = array(
				'aid'       => $rt['aid'],
				'name'      => $rt['name'],
				'type'      => $rt['type'],
				'attachurl' => $rt['fileuploadurl'],
				'needrvrc'  => 0,
				'special'	=> 0,
				'ctype'		=> '',
				'size'      => $rt['size'],
				'hits'      => $rt['hits'],
				'desc'		=> str_replace('\\','', $rt['descrip']),
				'ifthumb'	=> $rt['ifthumb']
			);
			$pwSQL = array(
				'fid' => $this->forum->fid,
				'attachurl' => $rt['fileuploadurl'],
				'descrip' => $rt['descrip'],
				'ifthumb' => $rt['ifthumb']
			);
			if ($rt['needrvrc'] > 0 && ($rt['special'] == 1 && $this->post->allowencode && in_array($rt['ctype'], $db_enhideset['type']) || $rt['special'] == 2 && $this->post->allowsell && in_array($rt['ctype'], $db_sellset['type']))) {
				$this->attachs[$rt['aid']]['needrvrc']	= $pwSQL['needrvrc']	= $rt['needrvrc'];
				$this->attachs[$rt['aid']]['special']	= $pwSQL['special']		= $rt['special'];
				$this->attachs[$rt['aid']]['ctype']		= $pwSQL['ctype']		= $rt['ctype'];
			}
			if (in_array($rt['ext'], array('gif','jpg','jpeg','png','bmp'))) {
				$this->uploadImgNum++;
			}
			$this->post->user['uploadnum']++;
			$this->post->user['uploadtime'] = $timestamp;
			$this->pw_attachs->updateById($rt['aid'], $pwSQL);
			$this->ifupload = ($rt['type'] == 'img' ? 1 : ($rt['type'] == 'txt' ? 2 : 3));
			if (($db_ifpwcache & 512) && !$rt['needrvrc'] && $rt['type'] == 'img' && !$this->elementpic) {
				$this->elementpic = array('aid' => $rt['aid'], 'attachurl' => $rt['fileuploadurl'], 'ifthumb' => $rt['ifthumb']);
			}
		}
		return true;
	}

	function allowType($key) {
		list($t) = explode('_', $key);
		return in_array($t, array('replace', 'attachment'));
	}

	function getFilePath($currUpload) {
		if ($currUpload['attname'] == 'replace' && isset($this->replacedb[$currUpload['id']])) {
			$arr = explode('/', $this->replacedb[$currUpload['id']]['attachurl']);
			$filename = array_pop($arr);
			$savedir  = $arr ? implode('/',$arr) . '/' : '';
		} else {
			global $timestamp;
			$prename  = substr(md5($timestamp . $currUpload['id'] . randstr(8)),10,15);
			$filename = $this->forum->fid . "_{$this->uid}_$prename." . preg_replace('/(php|asp|jsp|cgi|fcgi|exe|pl|phtml|dll|asa|com|scr|inf)/i', "scp_\\1", $currUpload['ext']);
			$savedir = $this->getSaveDir($currUpload['ext']);
		}
		return array($filename, $savedir);
	}

	function allowThumb() {
		return $this->ifthumb;
	}

	function getThumbInfo($filename, $dir) {
		return array(
			array($filename, 'thumb/' . $dir, $this->thumbsize),
			array($filename, 'thumb/mini/' . $dir, "200\t150\t1")
		);
	}

	function allowWaterMark() {
		return $this->forum->forumset['watermark'];
	}

	function getSaveDir($ext) {
		global $db_attachdir;
		$savedir = '';
		if ($db_attachdir) {
			if ($db_attachdir == 2) {
				$savedir = "Type_$ext/";
			} elseif ($db_attachdir == 3) {
				$savedir = 'Mon_'.date('ym').'/';
			} elseif ($db_attachdir == 4) {
				$savedir = 'Day_'.date('ymd').'/';
			} else {
				$savedir = "Fid_{$this->forum->fid}/";
			}
		}
		return $savedir;
	}

	function update($uploaddb) {
		global $db_enhideset,$db_sellset,$timestamp,$db_ifpwcache;
		foreach ($uploaddb as $value) {
			$value['name'] = addslashes($value['name']);
			if ($value['attname'] == 'replace' && isset($this->replacedb[$value['id']])) {
				$aid = $value['id'];
				$value['needrvrc']	= $this->replacedb[$aid]['needrvrc'];
				$value['special']	= $this->replacedb[$aid]['special'];
				$value['ctype']		= $this->replacedb[$aid]['ctype'];
				$value['descrip']	= $this->replacedb[$aid]['desc'];
				$this->pw_attachs->updateById($aid, array(
					'name'		=> $value['name'],			'type'		=> $value['type'],
					'size'		=> $value['size'],			'attachurl'	=> $value['fileuploadurl'],
					'needrvrc'	=> $value['needrvrc'],		'special'	=> $value['special'],
					'ctype'		=> $value['ctype'],			'uploadtime'=> $timestamp,
					'descrip'	=> $value['descrip'],		'ifthumb'	=> $value['ifthumb']
				));
				$this->replacedb[$aid]['name'] = $value['name'];
				$this->replacedb[$aid]['type'] = $value['type'];
				$this->replacedb[$aid]['size'] = $value['size'];
				$this->replacedb[$aid]['ifthumb'] = $value['ifthumb'];
			} else {
				$value['descrip']	= S::escapeChar(S::getGP('atc_desc'.$value['id'], 'P'));
				$value['needrvrc']	= intval(S::getGP('atc_needrvrc'.$value['id'], 'P'));
				$value['special']	= intval(S::getGP('att_special'.$value['id'], 'P'));
				$value['ctype']		= S::getGP('att_ctype'.$value['id'], 'P');
				if ($value['needrvrc'] > 0 && ($value['special'] == 1 && $this->post->allowencode && in_array($value['ctype'], $db_enhideset['type']) || $value['special'] == 2 && $this->post->allowsell && in_array($value['ctype'],$db_sellset['type']))) {

				} else {
					$value['needrvrc'] = $value['special'] = 0;
					$value['ctype'] = '';
				}
				$aid = $this->pw_attachs->add(array(
					'fid'		=> $this->forum->fid,			'uid'		=> $this->post->uid,
					'hits'		=> 0,							'name'		=> $value['name'],
					'type'		=> $value['type'],				'size'		=> $value['size'],
					'attachurl'	=> $value['fileuploadurl'],		'needrvrc'	=> $value['needrvrc'],
					'special'	=> $value['special'],			'ctype'		=> $value['ctype'],
					'uploadtime'=> $timestamp,					'descrip'	=> $value['descrip'],
					'ifthumb'	=> $value['ifthumb']
				));
				$this->attachs[$aid] = array(
					'aid'       => $aid,
					'name'      => stripslashes($value['name']),
					'type'      => $value['type'],
					'attachurl' => $value['fileuploadurl'],
					'needrvrc'  => $value['needrvrc'],
					'special'	=> $value['special'],
					'ctype'		=> $value['ctype'],
					'size'      => $value['size'],
					'hits'      => 0,
					'desc'		=> str_replace('\\','',$value['descrip']),
					'ifthumb'	=> $value['ifthumb']
				);
				$this->idrelate[$aid] = $value['id'];
				$this->post->user['uploadnum']++;
				$this->post->user['uploadtime'] = $timestamp;
			}
			if ($value['type'] == 'img') {
				$this->ifupload = 1;
				$this->uploadImgNum++;
			} else {
				$this->ifupload = ($value['type'] == 'txt') ? 2 : 3;
			}
			//Start elementupdate
			if (($db_ifpwcache & 512) && $value['type'] == 'img' && !$value['needrvrc'] && !$this->elementpic) {
				$this->elementpic = array('aid' => $aid, 'attachurl' => $value['fileuploadurl'], 'ifthumb' => $value['ifthumb']);
			}
			//End elementupdate
		}
		$this->addCredit();
		return true;
	}

	function addCredit() {
		if ($this->attachs && $this->post->_G['allowupload'] == 1 && $this->uploadmoney) {
			global $credit,$onlineip;
			$credit->addLog('topic_upload', array($this->uploadcredit => $this->uploadmoney), array(
				'uid'		=> $this->post->uid,
				'username'	=> $this->post->username,
				'ip'		=> $onlineip,
				'fname'		=> $this->forum->name
			));
			if (!$credit->set($this->post->uid, $this->uploadcredit, $this->uploadmoney, false)) {
				require_once(R_P.'require/updateforum.php');
				delete_att($this->attachs);
				Showmsg('undefined_action');
			}
		}
	}

	function getIdRelate() {
		return $this->idrelate;
	}

	function getAttachs() {
		return $this->attachs;
	}

	function getImages($num) {
		$imgs = array();
		foreach ($this->attachs as $key => $value) {
			if ($value['type'] == 'img') {
				$imgs[] = array('attachurl' => $value['attachurl'], 'ifthumb' => $value['ifthumb']);
				$num = $num - 1;
				if (empty($num)) break;
			}
		}
		return $imgs;
	}

	function getUploadImgNum() {
		return $this->uploadImgNum;
	}

	function getAids() {
		return array_keys($this->attachs);
	}

	function getAttachNum() {
		return count($this->attachs);
	}
}
?>