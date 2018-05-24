<?php
if (!defined('IN_ANWSION'))
{
	die;
}

class foxs_class extends AWS_MODEL
{
	public function foxbox_users_say($uid = 0, $limit = 15)
	{
		 if ($users_say = $this->fetch_all('answer', 'uid <> ' . intval($uid) . '', 'add_time DESC', ($limit * 2)))
		 {
			 foreach($users_say as $key => $val){
				if ($val['uid']>0)
				{
					$users_say[$key]['user_box'] = $this->fetch_row('users', "uid = " . $val['uid']);
					foreach($users_say[$key]['user_box'] as $v){
						if (!$users_say[$key]['user_box']['url_token'] AND $users_say[$key]['user_box']['user_name'])
							{
								$users_say[$key]['user_box']['url_token'] = urlencode($users_say[$key]['user_box']['user_name']);
							}
					}
				}
			 }
		 }
		shuffle($users_say);
		return array_slice($users_say, 0, $limit);
	}
	
	
	public function get_zans_by_item_id($id,$type)
	{
		if($type=='article'){
			if($list=$this->fetch_all('article', 'id = ' . intval($id))){
				return $list[0]['votes'];
			}else{
				return 0;
			}			
		}elseif($type=='question'){
			if($list = $this->query_all("SELECT agree_count FROM " . $this->get_table('answer') . " WHERE question_id = " . intval($id) . " ORDER BY agree_count Desc")){
				return $list[0]['agree_count'];
			}else{
				return 0;
			}
		}
	}
	
	public function get_answer_users_by_id($question_id, $limit = 5, $published_uid = null,$type='question')
	{
		if($type=='question'){
			if ($result = AWS_APP::cache()->get('answer_users_by_question_id_f' . md5($question_id . $limit . $published_uid)))
			{
				return $result;
			}

			if (!$published_uid)
			{
				if (!$question_info = $this->model('question')->get_question_info_by_id($question_id))
				{
					return false;
				}

				$published_uid = $question_info['published_uid'];
			}

			if ($answer_users = $this->query_all("SELECT DISTINCT uid FROM " . get_table('answer') . " WHERE question_id = " . intval($question_id) . " AND uid <> " . intval($published_uid) . " AND anonymous = 0 ORDER BY add_time DESC LIMIT " . intval($limit)))
			{
				foreach ($answer_users AS $key => $val)
				{
					$answer_uids[] = $val['uid'];
				}

				$result = $this->model('account')->get_user_info_by_uids($answer_uids);

				AWS_APP::cache()->set('answer_users_by_question_id_f' . md5($question_id . $limit . $published_uid), $result, get_setting('cache_level_normal'));
			}

			return $result;
		}
		if($type=='article'){
			if ($result = AWS_APP::cache()->get('answer_users_by_article_id_f' . md5($question_id . $limit . $published_uid)))
			{
				return $result;
			}

			if (!$published_uid)
			{
				if (!$question_info = $this->model('article')->get_article_info_by_id($question_id))
				{
					return false;
				}

				$published_uid = $question_info['published_uid'];
			}

			if ($answer_users = $this->query_all("SELECT DISTINCT uid FROM " . get_table('article_comments') . " WHERE article_id = " . intval($question_id) . " AND uid <> " . intval($published_uid) . " ORDER BY add_time DESC LIMIT " . intval($limit)))
			{
				foreach ($answer_users AS $key => $val)
				{
					$answer_uids[] = $val['uid'];
				}

				$result = $this->model('account')->get_user_info_by_uids($answer_uids);

				AWS_APP::cache()->set('answer_users_by_article_id_f' . md5($question_id . $limit . $published_uid), $result, get_setting('cache_level_normal'));
			}

			return $result;
		}
		
	}
	
	//新增加认证用户
	function top_verified_users($uid = 0, $avatra = 0, $limit = 5) {
		$avar='';
		if($avatra<>0){$avar='avatar_file <>"" AND ';}
		if ($users_list = $this->fetch_all('users', ''.$avar.' uid<>1 AND (verified = "enterprise" OR verified = "personal") AND last_active > ' . (time() - (60 * 60 * 24 * 7)), 'answer_count DESC', ($limit * 4)))
		{
			foreach($users_list as $key => $val)
			{
				if (!$val['url_token'])
				{
					$users_list[$key]['url_token'] = urlencode($val['user_name']);
				}
			}
		}

		shuffle($users_list);

		return array_slice($users_list, 0, $limit);
	}

	
}
