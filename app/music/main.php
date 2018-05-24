<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/


if (!defined('IN_ANWSION'))
{
	die;
}

class main extends AWS_CONTROLLER
{

	public function get_access_rule()
	{
		$rule_action['rule_type'] = "white";

		if ($this->user_info['permission']['search_avail'] AND $this->user_info['permission']['visit_site'])
		{
			$rule_action['rule_type'] = "black"; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		}

		$rule_action['actions'] = array();

		return $rule_action;
	}

	public function setup()
	{
		HTTP::no_cache_header();

		$this->crumb(AWS_APP::lang()->_t('音乐'), '/music/');
	}

	public function index_action()
	{
		TPL::output('music/index');
	}
}