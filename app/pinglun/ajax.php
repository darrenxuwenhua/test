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

class ajax extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'white';

		if ($this->user_info['permission']['visit_explore'])
		{
			$rule_action['actions'] = array(
				'list',
				'list_read'
			);
		}

		return $rule_action;
	}

	public function list_action()
	{
		if ($_GET['feature_id'])
		{
			$topic_ids = $this->model('feature')->get_topics_by_feature_id($_GET['feature_id']);
		}
		else
		{
			$topic_ids = explode(',', $_GET['topic_id']);
		}

		if ($_GET['per_page'])
		{
			$per_page = 6;
		}
		else
		{
			$per_page = 6;
		}
		
		$_GET['answer_count']=2;

		if ($_GET['sort_type'] == 'hot')
		{
			$posts_list = $this->model('posts')->get_hot_posts($_GET['post_type'], $_GET['category'], $topic_ids, $_GET['day'], $_GET['page'], $per_page);
		}
		else
		{
			$posts_list = $this->model('posts')->get_posts_list($_GET['post_type'], $_GET['page'], $per_page, $_GET['sort_type'], $topic_ids, $_GET['category'], $_GET['answer_count'], $_GET['day'], $_GET['is_recommend']);
		}

		if (!is_mobile() AND $posts_list)
		{
			foreach ($posts_list AS $key => $val)
			{
				if ($val['answer_count'])
				{
					$posts_list[$key]['answer_users'] = $this->model('question')->get_answer_users_by_question_id($val['question_id'], 2, $val['published_uid']);
				}
			}
		}
		//这里新增
		if (is_mobile() AND $posts_list)
		{
			foreach ($posts_list AS $key => $val)
			{
				if ($val['answer_count'])
				{
					$posts_list[$key]['answer_users'] = $this->model('question')->get_answer_users_by_question_id($val['question_id'], 2, $val['published_uid']);
					$posts_list[$key]['answer_lists'] = $this->model('answer')->get_answer_list_by_question_id($val['question_id'], 3);//新增
				}else{
					$posts_list[$key]['answer_listst'] = $this->model('article')->get_comments($val['id'], 1, 3);
				}
				$article_ids[] = $val['id'];
				$question_ids[] = $val['question_id'];
			}

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

		TPL::assign('posts_list', $posts_list);

		if (is_mobile())
		{
			TPL::output('m/ajax/explore_list');
		}
		else
		{
			TPL::output('explore/ajax/list');
		}
	}
	
	//新增
	public function list_read_action()
	{
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
		TPL::assign('posts_list', $posts_list);
		if($posts_list){
			TPL::output('pinglun/ajax/list');			
		}
	}
	//结束
}