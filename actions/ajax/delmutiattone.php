<?php
!defined('P_W') && exit('Forbidden');

$aid = (int) S::getGP('aid');
S::gp(array('type'));

if ($aid <= 0) {
	echo "error";ajax_footer();
}

$delfileServer = getDelfileFactory($type);
if (!$delfileServer->delete($aid)) {
	echo "error";ajax_footer(); 
}
echo "ok";ajax_footer();

function getDelfileFactory($type) {
	if ($type == 'active') {
		return new activeDelfile();
	}
	if ($type == 'cms') {
		return new cmsDelfile();
	}
	if ($type && file_exists(R_P . "require/extents/attach/{$type}Delfile.class.php")) {
		$class = $type . 'Delfile';
		require_once S::escapePath(R_P . "require/extents/attach/{$type}Delfile.class.php");
		return new $class();
	}
	return new threadDelfile();
}

class activeDelfile {

	function delete($aid) {
		global $winduid,$db_ifftp,$db;
		$rt = $db->get_one("SELECT * FROM pw_actattachs WHERE aid=" . S::sqlEscape($aid));
		if (empty($rt)) return true;
		if ($rt['uid'] != $winduid) {
			return false;
		}
		pwDelThreadAtt($rt['attachurl'], $db_ifftp, $rt['ifthumb']);
		$db->update("DELETE FROM pw_actattachs WHERE aid=" . S::sqlEscape($aid) . " AND uid=" . S::sqlEscape($winduid) . " LIMIT 1");
		return true;
	}
}

class cmsDelfile {

	function delete($aid) {
		global $winduid,$db_ifftp,$db;
		$rt = $db->get_one("SELECT * FROM pw_cms_attach WHERE attach_id=" . S::sqlEscape($aid));
		if (empty($rt)) return true;
		$article = $db->get_one("SELECT userid FROM pw_cms_article WHERE article_id=" . S::sqlEscape($rt['article_id']));
		if ($article['userid'] != $winduid) {
			return false;
		}
		pwDelThreadAtt($rt['attachurl'], $db_ifftp, $rt['ifthumb']);
		$db->update("DELETE FROM pw_cms_attach WHERE attach_id=" . S::sqlEscape($aid) . " LIMIT 1");
		return true;
	}
}

class threadDelfile {

	function delete($aid) {
		global $winduid, $db_ifftp, $db;
		$rt = $db->get_one("SELECT aid,uid,tid,pid,attachurl,ifthumb FROM pw_attachs WHERE aid=" . S::sqlEscape($aid));
		if (empty($rt)) return true;
		if ($rt['uid'] != $winduid) {
			return false;
		}
		if (!$rt['tid'] && !$rt['pid']) {
			$rt['attachurl'] = substr($rt['attachurl'], 11);
			pwDelatt('mutiupload/' . $rt['attachurl'], $db_ifftp);
			($rt['ifthumb'] & 1) && pwDelatt('mutiupload/s1_' . $rt['attachurl'], $db_ifftp);
			($rt['ifthumb'] & 2) && pwDelatt('mutiupload/s2_' . $rt['attachurl'], $db_ifftp);
		} elseif ($rt['tid']) {
			pwDelThreadAtt($rt['attachurl'], $db_ifftp, $rt['ifthumb']);
			if ($rt['pid']) {
				$pw_posts = GetPtable('N', $rt['tid']);
				$db->update("UPDATE $pw_posts SET aid=aid-1 WHERE pid=" . S::sqlEscape($rt['pid']) . ' AND aid>0');
			} else {
				$pw_tmsgs = GetTtable($rt['tid']);
				$db->update("UPDATE $pw_tmsgs SET aid=aid-1 WHERE tid=" . S::sqlEscape($rt['tid']) . ' AND aid>0');
			}
		}
		$db->update("DELETE FROM pw_attachs WHERE aid=" . S::sqlEscape($aid) . " AND uid=" . S::sqlEscape($winduid) . " LIMIT 1");
		return true;
	}
}