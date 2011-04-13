<?php
$forum=array(
	'1' => array(
		'fid' => '1',
		'fup' => '0',
		'ifsub' => '0',
		'childid' => '1',
		'type' => 'category',
		'name' => '默认分类',
		'style' => '0',
		'f_type' => 'forum',
		'ifcms' => '0',
		'ifhide' => '1',
		'title' => '',
		'metadescrip' => '',
		'descrip' => '',
		'keywords' => '',
		'forumadmin' => '',
	),
	'2' => array(
		'fid' => '2',
		'fup' => '1',
		'ifsub' => '0',
		'childid' => '0',
		'type' => 'forum',
		'name' => '默认版块',
		'style' => '0',
		'f_type' => 'forum',
		'ifcms' => '0',
		'ifhide' => '1',
		'title' => '',
		'metadescrip' => '',
		'descrip' => '',
		'keywords' => '',
		'forumadmin' => '',
	),
);
$pwForumList=array(
	'1' => array(
		'name' => '默认分类',
		'child' => array(
			'2' => '默认版块',
		),
	),
);
$pwForumAllList = array(
);

$md_appgroups='';
$md_groups=',3,';
$md_ifapply='1';
$md_ifmsg='1';
$md_ifopen='1';


$ltitle=$lpic=$lneed=array();
/**
* default
*/
$ltitle[1]='default';		$lpic[1]='0';
$ltitle[2]='游客';		$lpic[2]='0';
$ltitle[6]='禁止发言';		$lpic[6]='0';
$ltitle[7]='未验证会员';		$lpic[7]='0';

/**
* system
*/
$ltitle[3]='管理员';		$lpic[3]='21';
$ltitle[4]='总版主';		$lpic[4]='20';
$ltitle[5]='论坛版主';		$lpic[5]='19';
$ltitle[17]='门户编辑';		$lpic[17]='18';

/**
* special
*/
$ltitle[16]='荣誉会员';		$lpic[16]='16';

/**
* member
*/
$ltitle[8]='新手上路';		$lpic[8]='1';		$lneed[8]='0';
$ltitle[9]='侠客';		$lpic[9]='2';		$lneed[9]='100';
$ltitle[10]='骑士';		$lpic[10]='3';		$lneed[10]='300';
$ltitle[11]='圣骑士';		$lpic[11]='4';		$lneed[11]='600';
$ltitle[12]='精灵王';		$lpic[12]='5';		$lneed[12]='1000';
$ltitle[13]='风云使者';		$lpic[13]='6';		$lneed[13]='5000';
$ltitle[14]='光明使者';		$lpic[14]='7';		$lneed[14]='10000';
$ltitle[15]='天使';		$lpic[15]='8';		$lneed[15]='50000';


$gp_right=array(
	'1' => array(
		'fontsize' => '',
		'imgheight' => '',
		'imgwidth' => '',
	),
	'2' => array(
		'fontsize' => '',
		'imgheight' => '',
		'imgwidth' => '',
	),
	'3' => array(
		'fontsize' => '',
		'imgheight' => '',
		'imgwidth' => '',
	),
	'4' => array(
		'fontsize' => '',
		'imgheight' => '',
		'imgwidth' => '',
	),
	'5' => array(
		'fontsize' => '',
		'imgheight' => '',
		'imgwidth' => '',
	),
	'6' => array(
		'fontsize' => '',
		'imgheight' => '',
		'imgwidth' => '',
	),
	'7' => array(
		'fontsize' => '',
		'imgheight' => '',
		'imgwidth' => '',
	),
	'8' => array(
		'fontsize' => '3',
		'imgheight' => '',
		'imgwidth' => '',
	),
	'16' => array(
		'fontsize' => '',
		'imgheight' => '',
		'imgwidth' => '',
	),
);

$customfield=array(
	'0' => array(
		'id' => '8',
		'category' => 'contact',
		'title' => 'QQ',
		'maxlen' => '12',
		'vieworder' => '1',
		'type' => '1',
		'state' => '1',
		'required' => '0',
		'viewinread' => '0',
		'editable' => '1',
		'descrip' => '',
		'viewright' => '',
		'options' => '',
		'complement' => '2',
		'ifsys' => '1',
		'fieldname' => 'oicq',
	),
	'1' => array(
		'id' => '9',
		'category' => 'contact',
		'title' => '阿里旺旺',
		'maxlen' => '30',
		'vieworder' => '1',
		'type' => '1',
		'state' => '1',
		'required' => '0',
		'viewinread' => '0',
		'editable' => '1',
		'descrip' => '',
		'viewright' => '',
		'options' => '',
		'complement' => '0',
		'ifsys' => '1',
		'fieldname' => 'aliww',
	),
	'2' => array(
		'id' => '10',
		'category' => 'contact',
		'title' => 'Yahoo',
		'maxlen' => '35',
		'vieworder' => '1',
		'type' => '1',
		'state' => '1',
		'required' => '0',
		'viewinread' => '0',
		'editable' => '1',
		'descrip' => '',
		'viewright' => '',
		'options' => '',
		'complement' => '0',
		'ifsys' => '1',
		'fieldname' => 'yahoo',
	),
	'3' => array(
		'id' => '11',
		'category' => 'contact',
		'title' => 'Msn',
		'maxlen' => '35',
		'vieworder' => '1',
		'type' => '1',
		'state' => '1',
		'required' => '0',
		'viewinread' => '0',
		'editable' => '1',
		'descrip' => '',
		'viewright' => '',
		'options' => '',
		'complement' => '0',
		'ifsys' => '1',
		'fieldname' => 'msn',
	),
	'4' => array(
		'id' => '1',
		'category' => 'basic',
		'title' => '性别',
		'maxlen' => '0',
		'vieworder' => '2',
		'type' => '3',
		'state' => '1',
		'required' => '0',
		'viewinread' => '0',
		'editable' => '1',
		'descrip' => '',
		'viewright' => '',
		'options' => '0=保密
1=男
2=女',
		'complement' => '2',
		'ifsys' => '1',
		'fieldname' => 'gender',
	),
	'5' => array(
		'id' => '2',
		'category' => 'basic',
		'title' => '生日',
		'maxlen' => '0',
		'vieworder' => '3',
		'type' => '6',
		'state' => '1',
		'required' => '0',
		'viewinread' => '0',
		'editable' => '1',
		'descrip' => '',
		'viewright' => '',
		'options' => 'a:2:{s:9:"startdate";s:4:"1912";s:7:"enddate";s:4:"2011";}',
		'complement' => '2',
		'ifsys' => '1',
		'fieldname' => 'bday',
	),
	'6' => array(
		'id' => '3',
		'category' => 'basic',
		'title' => '现居住地',
		'maxlen' => '0',
		'vieworder' => '4',
		'type' => '7',
		'state' => '1',
		'required' => '1',
		'viewinread' => '0',
		'editable' => '1',
		'descrip' => '',
		'viewright' => '',
		'options' => '',
		'complement' => '1',
		'ifsys' => '1',
		'fieldname' => 'apartment',
	),
	'7' => array(
		'id' => '4',
		'category' => 'basic',
		'title' => '家乡',
		'maxlen' => '0',
		'vieworder' => '4',
		'type' => '7',
		'state' => '1',
		'required' => '0',
		'viewinread' => '0',
		'editable' => '1',
		'descrip' => '',
		'viewright' => '',
		'options' => '',
		'complement' => '2',
		'ifsys' => '1',
		'fieldname' => 'home',
	),
	'8' => array(
		'id' => '7',
		'category' => 'education',
		'title' => '工作经历',
		'maxlen' => '0',
		'vieworder' => '5',
		'type' => '9',
		'state' => '1',
		'required' => '0',
		'viewinread' => '0',
		'editable' => '1',
		'descrip' => '',
		'viewright' => '',
		'options' => '',
		'complement' => '2',
		'ifsys' => '1',
		'fieldname' => 'career',
	),
	'9' => array(
		'id' => '5',
		'category' => 'basic',
		'title' => '支付宝账号',
		'maxlen' => '60',
		'vieworder' => '6',
		'type' => '1',
		'state' => '1',
		'required' => '0',
		'viewinread' => '0',
		'editable' => '1',
		'descrip' => '',
		'viewright' => '',
		'options' => '',
		'complement' => '0',
		'ifsys' => '1',
		'fieldname' => 'alipay',
	),
	'10' => array(
		'id' => '6',
		'category' => 'education',
		'title' => '教育经历',
		'maxlen' => '0',
		'vieworder' => '6',
		'type' => '8',
		'state' => '1',
		'required' => '0',
		'viewinread' => '0',
		'editable' => '1',
		'descrip' => '',
		'viewright' => '',
		'options' => '',
		'complement' => '2',
		'ifsys' => '1',
		'fieldname' => 'education',
	),
);

$_MEDALDB=array(
	'1' => array(
		'id' => '1',
		'name' => '终身成就奖',
		'intro' => '谢谢您为社区发展做出的不可磨灭的贡献!!',
		'picurl' => '1.gif',
	),
	'2' => array(
		'id' => '2',
		'name' => '优秀斑竹奖',
		'intro' => '辛劳地为论坛付出劳动，收获快乐，感谢您!',
		'picurl' => '2.gif',
	),
	'3' => array(
		'id' => '3',
		'name' => '宣传大使奖',
		'intro' => '谢谢您为社区积极宣传,特颁发此奖!',
		'picurl' => '3.gif',
	),
	'4' => array(
		'id' => '4',
		'name' => '特殊贡献奖',
		'intro' => '您为论坛做出了特殊贡献,谢谢您!',
		'picurl' => '4.gif',
	),
	'5' => array(
		'id' => '5',
		'name' => '金点子奖',
		'intro' => '为社区提出建设性的建议被采纳,特颁发此奖!',
		'picurl' => '5.gif',
	),
	'6' => array(
		'id' => '6',
		'name' => '原创先锋奖',
		'intro' => '谢谢您积极发表原创作品,特颁发此奖!',
		'picurl' => '6.gif',
	),
	'7' => array(
		'id' => '7',
		'name' => '贴图大师奖',
		'intro' => '帖图高手,堪称大师!',
		'picurl' => '7.gif',
	),
	'8' => array(
		'id' => '8',
		'name' => '灌水天才奖',
		'intro' => '能够长期提供优质的社区水资源者,可得这个奖章!',
		'picurl' => '8.gif',
	),
	'9' => array(
		'id' => '9',
		'name' => '新人进步奖',
		'intro' => '新人有很大的进步可以得到这个奖章!',
		'picurl' => '9.gif',
	),
	'10' => array(
		'id' => '10',
		'name' => '幽默大师奖',
		'intro' => '您总是能给别人带来欢乐,谢谢您!',
		'picurl' => '10.gif',
	),
);

?>