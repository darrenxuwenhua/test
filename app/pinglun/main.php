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
			HTTP::redirect('/m/pinglu/' . $_GET['id']);
		}
		
		$this->crumb(AWS_APP::lang()->_t('牛评'), '/pinglu');

		if ($this->user_id)
		{

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


		if (! $_GET['sort_type'] AND !$_GET['is_recommend'])
		{
			$_GET['sort_type'] = 'new';
			//$_GET['is_recommend'] = 'is_recommend-1';
			
		}
		
		$_GET['answer_count']=2;

		if ($_GET['sort_type'] == 'hot')
		{
			$posts_list = $this->model('posts')->get_hot_posts(null, $category_info['id'], null, $_GET['day'], $_GET['page'], 8);
		}
		else
		{
			$posts_list = $this->model('posts')->get_posts_list(null, $_GET['page'], 8, $_GET['sort_type'], null, $category_info['id'], $_GET['answer_count'], $_GET['day'], $_GET['is_recommend']);
		}

		if ($posts_list)
		{
			foreach ($posts_list AS $key => $val)
			{
				if ($val['answer_count'])
				{
					$posts_list[$key]['answer_users'] = $this->model('question')->get_answer_users_by_question_id($val['question_id'], 2, $val['published_uid']);
					$posts_list[$key]['answer_lists'] = $this->model('answer')->get_answer_list_by_question_id($val['question_id'], 3);//新增
				}else{
					$posts_list[$key]['answer_lists'] = $this->model('article')->get_comments($val['id'], 1, 3);
				}
				$article_ids[] = $val['id'];//这里新增
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
			'base_url' => get_js_url('/pinglun/sort_type-' . preg_replace("/[\(\)\.;']/", '', $_GET['sort_type']) . '__category-' . $category_info['id'] . '__day-' . intval($_GET['day']) . '__is_recommend-' . intval($_GET['is_recommend'])),
			'total_rows' => $this->model('posts')->get_posts_list_total(),
			'per_page' => 8
		))->create_links());

		TPL::assign('posts_list', $posts_list);
		TPL::assign('posts_list_bit', TPL::output('pinglun/ajax/list', false));

		TPL::output('pinglun/index');
	}
}