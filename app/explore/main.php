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
		$rule_action['rule_type'] = "white"; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查

		if ($this->user_info['permission']['visit_explore'] AND $this->user_info['permission']['visit_site'])
		{
			$rule_action['actions'][] = 'index';
		}

		return $rule_action;
	}
	
	public function setup()
	{
		if (is_mobile() AND !$_GET['ignore_ua_check'])
		{
			switch ($_GET['app'])
			{
				default:
					HTTP::redirect('/m/');
				break;
			}
		}
	}

	public function index_action()
	{
		if (is_mobile())
		{
			HTTP::redirect('/m/explore/' . $_GET['id']);
		}

		if ($this->user_id)
		{
			$this->crumb(AWS_APP::lang()->_t('发现'), '/explore');

			if (! $this->user_info['email'])
			{
				HTTP::redirect('/account/complete_profile/');
			}
		}

		if ($_GET['category'])
		{
			if (is_digits($_GET['category']))
			{
				$category_info = $this->model('system')->get_category_info($_GET['category']);
			}
			else
			{
				$category_info = $this->model('system')->get_category_info_by_url_token($_GET['category']);
			}
		}

		if ($category_info)
		{
			TPL::assign('category_info', $category_info);

			$this->crumb($category_info['title'], '/category-' . $category_info['id']);

			$meta_description = $category_info['title'];

			if ($category_info['description'])
			{
				$meta_description .= ' - ' . $category_info['description'];
			}

			TPL::set_meta('description', $meta_description);
		}

		// 导航
		if (TPL::is_output('block/content_nav_menu.tpl.htm', 'explore/index'))
		{
			TPL::assign('content_nav_menu', $this->model('menu')->get_nav_menu_list('explore'));
		}

		// 边栏可能感兴趣的人
		if (TPL::is_output('block/sidebar_recommend_users_topics.tpl.htm', 'explore/index'))
		{
			TPL::assign('sidebar_recommend_users_topics', $this->model('module')->recommend_users_topics($this->user_id));
		}

		// 边栏热门用户
		if (TPL::is_output('block/sidebar_hot_users.tpl.htm', 'explore/index'))
		{
			TPL::assign('sidebar_hot_users', $this->model('module')->sidebar_hot_users($this->user_id, 5));
		}
		
		// 顶部活跃用户
		if (TPL::is_output('block/top_hot_users.tpl.htm', 'explore/index'))
		{
			TPL::assign('users_count', $this->model('system')->count('users'));
			TPL::assign('users_valid_email_count', $this->model('system')->count('users', 'valid_email = 1'));
			TPL::assign('question_count', $this->model('system')->count('question'));
			TPL::assign('article_count', $this->model('system')->count('article'));
			if(!$top_hot_users= AWS_APP::cache()->get('top_hot_users')){
				$top_hot_users=$this->model('module')->top_hot_users($this->user_id, 1, FOX_users_num, FOX_Bangs_num);
				AWS_APP::cache()->set('top_hot_users', $top_hot_users, 3600);
			}
			TPL::assign('top_hot_users', $top_hot_users);
		}
		
		// 认证用户
		if (TPL::is_output('block/sidebar_verified_users.tpl.htm', 'explore/index'))
		{
			TPL::assign('top_verified_users', $this->model('module')->top_verified_users($this->user_id, 0, 3));
		}
		
		// 他们说
		if (TPL::is_output('block/sidebar_users_say.tpl.htm', 'explore/index'))
		{
			TPL::assign('side_users_say', $this->model('foxs')->foxbox_users_say($this->user_id, 15));
		}		

		// 边栏热门话题
		if (TPL::is_output('block/sidebar_hot_topics.tpl.htm', 'explore/index'))
		{
			TPL::assign('sidebar_hot_topics', $this->model('module')->sidebar_hot_topics($category_info['id']));
		}

		// 边栏专题
		if (TPL::is_output('block/sidebar_feature.tpl.htm', 'explore/index'))
		{
			TPL::assign('feature_list', $this->model('module')->feature_list());
		}

		if (! $_GET['sort_type'] AND !$_GET['is_recommend'])
		{
			$_GET['sort_type'] = 'new';
			//$_GET['is_recommend'] = 'is_recommend-1';
			
		}

		if ($_GET['sort_type'] == 'hot')
		{
			$posts_list = $this->model('posts')->get_hot_posts(null, $category_info['id'], null, $_GET['day'], $_GET['page'], get_setting('contents_per_page'));
		}
		else
		{
			$posts_list = $this->model('posts')->get_posts_list(null, $_GET['page'], get_setting('contents_per_page'), $_GET['sort_type'], null, $category_info['id'], $_GET['answer_count'], $_GET['day'], $_GET['is_recommend']);
		}

		if ($posts_list)
		{
			foreach ($posts_list AS $key => $val)
			{
				if ($val['answer_count'])
				{
					$posts_list[$key]['answer_users'] = $this->model('foxs')->get_answer_users_by_id($val['question_id'], FOX_Hfren_num, $val['published_uid'],'question');
				}else{
					$posts_list[$key]['answer_users'] = $this->model('foxs')->get_answer_users_by_id($val['id'], FOX_Hfren_num, $val['published_uid'],'article');
				}
				//这里新增
				if($val['question_id']){
					$posts_list[$key]['topics'] = $this->model('topic')->get_topics_by_item_id($val['question_id'], 'question');
					$posts_list[$key]['zans'] = $this->model('foxs')->get_zans_by_item_id($val['question_id'], 'question');
				}elseif($val['id']){
					$posts_list[$key]['topics'] = $this->model('topic')->get_topics_by_item_id($val['id'], 'article');
					$posts_list[$key]['zans'] = $this->model('foxs')->get_zans_by_item_id($val['id'], 'article');
				}
				$article_ids[] = $val['id'];
				$question_ids[] = $val['question_id'];//这里新增
			}
			//这里新增
			$article_attachs = $this->model('publish')->get_attachs('article', $article_ids, 'min');
			$question_attachs = $this->model('publish')->get_attachs('question', $question_ids, 'min');

			foreach ($posts_list AS $key => $val)
			{
				if ($val['has_attach']&&$val['id'])
				{
					$posts_list[$key]['attachs'] = $article_attachs[$val['id']];
				}
				if ($val['has_attach']&&$val['question_id'])
				{
					$posts_list[$key]['attachs'] = $question_attachs[$val['question_id']];
				}
			}
			//新增结束

		}

		TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
			'base_url' => get_js_url('/sort_type-' . preg_replace("/[\(\)\.;']/", '', $_GET['sort_type']) . '__category-' . $category_info['id'] . '__day-' . intval($_GET['day']) . '__is_recommend-' . intval($_GET['is_recommend'])),
			'total_rows' => $this->model('posts')->get_posts_list_total(),
			'per_page' => get_setting('contents_per_page')
		))->create_links());

		TPL::assign('posts_list', $posts_list);
		TPL::assign('posts_list_bit', TPL::output('explore/ajax/list', false));

		TPL::output('explore/index');
	}
}