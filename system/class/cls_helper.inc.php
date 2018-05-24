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

class H
{
	public static function get_file_ext($file_name, $merge_type = true)
	{
		$file_ext = end(explode('.', $file_name));

		if ($merge_type)
		{
			if ($file_ext == 'jpeg' or $file_ext == 'jpe')
			{
				$file_ext = 'jpg';
			}

			if ($file_ext == 'htm')
			{
				$file_ext = 'html';
			}
		}

		return $file_ext;
	}

	/**
	 * 数组JSON返回
	 *
	 * @param  $array
	 */
	public static function ajax_json_output($array)
	{
		//HTTP::no_cache_header('text/javascript');

		echo str_replace(array("\r", "\n", "\t"), '', json_encode(H::sensitive_words($array)));
		exit;
	}

	public static function valid_email($email)
	{
		return Zend_Validate::is($email, 'EmailAddress');

		//return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)) ? FALSE : TRUE;
	}

	public static function redirect_msg($message, $url = NULL, $interval = 5)
	{
		TPL::assign('message', $message);
		TPL::assign('url_bit', HTTP::parse_redirect_url($url));
		TPL::assign('interval', $interval);

		TPL::output('global/show_message');
		die;
	}

	/**
	 * 加密 hash，生成发送给用户的 hash 字符串
	 *
	 * @param array $hash_data
	 * @param string $hash_key
	 * @return string
	 */
	public static function encode_hash($hash_data, $hash_key = null)
	{
		if (!$hash_data)
		{
			return false;
		}

		foreach ($hash_data as $key => $value)
		{
			$hash_string .= $key . "^]+" . $value . "!;-";
		}

		$hash_string = substr($hash_string, 0, - 3);

		// 加密干扰码，加密解密时需要用到的 key
		if (! $hash_key)
		{
			$hash_key = G_COOKIE_HASH_KEY;
		}

		// 加密过程
		for ($i = 1; $i <= strlen($hash_string); $i ++)
		{
			$char = substr($hash_string, $i - 1, 1);
			$keychar = substr($hash_key, ($i % strlen($hash_key)) - 2, 1);
			$char = chr(ord($char) + ord($keychar));
			$tmp_str .= $char;
		}

		$hash_string = base64_encode($tmp_str);

		$hash_string = str_replace(array(
			'+',
			'/',
			'='
		), array(
			'-',
			'_',
			'.'
		), $hash_string);

		return $hash_string;
	}

	/**
	 * 解密 hash，从用户回链的 hash 字符串解密出里面的内容
	 *
	 * @param string $hash_string
	 * @return array
	 */
	public static function decode_hash($hash_string, $hash_key = null)
	{
		if (!$hash_string)
		{
			return false;
		}

		// 加密干扰码，加密解密时需要用到的 Key
		if (! $hash_key)
		{
			$hash_key = G_COOKIE_HASH_KEY;
		}

		// 解密过程
		if (strpos($hash_string, '-') OR strpos($hash_string, '_') OR strpos($hash_string, '.'))
		{
			$hash_string = str_replace(array(
				'-',
				'_',
				'.'
			), array(
				'+',
				'/',
				'='
			), $hash_string);
		}

		$hash_string = base64_decode($hash_string);

		for ($i = 1; $i <= strlen($hash_string); $i ++)
		{
			$char = substr($hash_string, $i - 1, 1);
			$keychar = substr($hash_key, ($i % strlen($hash_key)) - 2, 1);
			$char = chr(ord($char) - ord($keychar));
			$tmp_str .= $char;
		}

		$hash_data = array();

		$arr = explode('!;-', $tmp_str);

		foreach ($arr as $value)
		{
			list($k, $v) = explode('^]+', $value);

			if ($k)
			{
				$hash_data[$k] = $v;
			}
		}

		return $hash_data;
	}

	/** 生成 Options **/
	public static function display_options($param, $default = '_DEFAULT_', $default_key = 'key')
	{
		if (is_array($param))
		{
			$keyindex = 0;

			foreach ($param as $key => $value)
			{
				if ($default_key == 'value')
				{
					$output .= '<option value="' . $key . '"' . (($value == $default) ? '  selected' : '') . '>' . $value . '</option>';
				}
				else
				{
					$output .= '<option value="' . $key . '"' . (($key == $default) ? '  selected' : '') . '>' . $value . '</option>';
				}
			}

		}

		return $output;
	}

	/**
	 * 敏感词替换
	 * @param unknown_type $content
	 * @param unknown_type $replace
	 * @return mixed
	 */
	public static function sensitive_words($content, $replace = '*')
	{
		if (!$content or !get_setting('sensitive_words'))
		{
			return $content;
		}

		if (is_array($content) && $content)
		{
			foreach($content as $key => $val)
			{
				$content[$key] = self::sensitive_words($val, $replace);
			}

			return $content;
		}

		$sensitive_words = explode("\n", get_setting('sensitive_words'));

		foreach($sensitive_words as $word)
		{
			$word = trim($word);

			if (!$word)
			{
				continue;
			}

			$replace_str = '';

			$word_length = cjk_strlen($word);

			for($i = 0; $i < $word_length; $i++)
			{
				$replace_str .=  $replace;
			}

			$content = str_replace($word, $replace_str, $content);
		}

		return $content;
	}

	/**
	 * 是否包含敏感词
	 * @param unknown_type $content
	 * @param unknown_type $replace
	 * @return mixed
	 */
	public static function sensitive_word_exists($content)
	{
		if (!$content or !get_setting('sensitive_words'))
		{
			return false;
		}

		if (is_array($content))
		{
			foreach($content as $key => $val)
			{
				if(self::sensitive_word_exists($val))
				{
					return true;
				}
			}

			return false;
		}

		$sensitive_words = explode("\n", get_setting('sensitive_words'));

		foreach($sensitive_words as $word)
		{
			$word = trim($word);

			if (!$word)
			{
				continue;
			}

			if (strstr($content, $word))
			{
				return true;
			}
		}

		return false;
	}
	
	public static function isImage($filename){
		$types = '.gif|.jpeg|.jpg|.png|.bmp';//定义检查的图片类型
		if(file_exists($filename)){
			$info = getimagesize($filename);
			$ext = image_type_to_extension($info['2']);
			return stripos($types,$ext);
		}else{
			return false;
		}
	}

/*
* 功能:PHP图片水印 (水印支持图片或文字)
* 参数:
* $groundImage 背景图片,即需要加水印的图片,暂只支持GIF,JPG,PNG格式;
* $waterPos 水印位置,有10种状态,0为随机位置;
* 1为顶端居左,2为顶端居中,3为顶端居右;
* 4为中部居左,5为中部居中,6为中部居右;
* 7为底端居左,8为底端居中,9为底端居右;
* $waterImage 图片水印,即作为水印的图片,暂只支持GIF,JPG,PNG格式;
* $waterText 文字水印,即把文字作为为水印,支持ASCII码,不支持中文;
* $textFont 文字大小,值为1、2、3、4或5,默认为5;
* $textColor 文字颜色,值为十六进制颜色值,默认为#FF0000(红色);
*
* 注意:Support GD 2.0,Support FreeType、GIF Read、GIF Create、JPG 、PNG
* $waterImage 和 $waterText 最好不要同时使用,选其中之一即可,优先使用 $waterImage.
* 当$waterImage有效时,参数$waterString、$stringFont、$stringColor均不生效.
* 加水印后的图片的文件名和 $groundImage 一样.

*/	
	public static function FoximageWaterMark($groundImage,$waterPos=0,$waterImage="",$waterText="",$textFont=5,$textColor="#FF0000")
	{
	$isWaterImage = FALSE;
	$formatMsg = "暂不支持该文件格式,请用图片处理软件将图片转换为GIF、JPG、PNG格式.";
	//读取水印文件
	if(!empty($waterImage) && file_exists($waterImage))	{
		$isWaterImage = TRUE;
		$water_info = getimagesize($waterImage);
		$water_w = $water_info[0];//取得水印图片的宽
		$water_h = $water_info[1];//取得水印图片的高
		switch($water_info[2])//取得水印图片的格式
		{
		case 1:$water_im = imagecreatefromgif($waterImage);break;
		case 2:$water_im = imagecreatefromjpeg($waterImage);break;
		case 3:$water_im = imagecreatefrompng($waterImage);break;
		default:die($formatMsg);
		}
	}
	//读取背景图片
	if(!empty($groundImage) && file_exists($groundImage)){
		$ground_info = getimagesize($groundImage);
		$ground_w = $ground_info[0];//取得背景图片的宽
		$ground_h = $ground_info[1];//取得背景图片的高
		switch($ground_info[2])//取得背景图片的格式
		{
		case 1:$ground_im = imagecreatefromgif($groundImage);break;
		case 2:$ground_im = imagecreatefromjpeg($groundImage);break;
		case 3:$ground_im = imagecreatefrompng($groundImage);break;
		default:die($formatMsg);
		}
	}
	else{
		die("需要加水印的图片不存在!");
	}
	//水印位置
	if($isWaterImage)//图片水印
	{
	$w = $water_w;
	$h = $water_h;
	$label = "图片的";
	}
	else//文字水印
	{
	$temp = imagettfbbox(ceil($textFont*5.5),0,"/static/font/1.ttf",$waterText);//取得使用 TrueType 字体的文本的范围
	$w = $temp[2] - $temp[6];
	$h = $temp[3] - $temp[7];
	unset($temp);
	$label = "文字区域";
	}
	if( ($ground_w<$w) || ($ground_h<$h) )
	{
	echo "需要加水印的图片的长度或宽度比水印".$label."还小,无法生成水印!";
	return;
	}
	switch($waterPos)
	{
	case 0://随机
	$posX = rand(0,($ground_w - $w));
	$posY = rand(0,($ground_h - $h));
	break;
	case 1://1为顶端居左
	$posX = 0;
	$posY = 0;
	break;
	case 2://2为顶端居中
	$posX = ($ground_w - $w) / 2;
	$posY = 0;
	break;
	case 3://3为顶端居右
	$posX = $ground_w - $w;
	$posY = 0;
	break;
	case 4://4为中部居左
	$posX = 0;
	$posY = ($ground_h - $h) / 2;
	break;
	case 5://5为中部居中
	$posX = ($ground_w - $w) / 2;
	$posY = ($ground_h - $h) / 2;
	break;
	case 6://6为中部居右
	$posX = $ground_w - $w;
	$posY = ($ground_h - $h) / 2;
	break;
	case 7://7为底端居左
	$posX = 0;
	$posY = $ground_h - $h;
	break;
	case 8://8为底端居中
	$posX = ($ground_w - $w) / 2;
	$posY = $ground_h - $h;
	break;
	case 9://9为底端居右
	$posX = $ground_w - $w;
	$posY = $ground_h - $h;
	break;
	default://随机
	$posX = rand(0,($ground_w - $w));
	$posY = rand(0,($ground_h - $h));
	break;
	}
	//设定图像的混色模式
	imagealphablending($ground_im, true);
	if($isWaterImage)//图片水印
	{
	imagecopy($ground_im, $water_im, $posX, $posY, 0, 0, $water_w,$water_h);//拷贝水印到目标文件
	}
	else//文字水印
	{
	if( !empty($textColor) && (strlen($textColor)==7) )
	{
	$R = hexdec(substr($textColor,1,2));
	$G = hexdec(substr($textColor,3,2));
	$B = hexdec(substr($textColor,5));
	}
	else
	{
	die("水印文字颜色格式不正确!");
	}
	imagestring ( $ground_im, $textFont, $posX, $posY, $waterText, imagecolorallocate($ground_im, $R, $G, $B));
	}
	//生成水印后的图片
	@unlink($groundImage);
	switch($ground_info[2])//取得背景图片的格式
	{
	case 1:imagegif($ground_im,$groundImage);break;
	case 2:imagejpeg($ground_im,$groundImage);break;
	case 3:imagepng($ground_im,$groundImage);break;
	default:die($errorMsg);
	}
	//释放内存
	if(isset($water_info)) unset($water_info);
	if(isset($water_im)) imagedestroy($water_im);
	unset($ground_info);
	imagedestroy($ground_im);
	}
	
	function isAnimatedGif($filename) {
		if (!file_exists($filepath)) {
        $fp=fopen($filename,'rb');
        $filecontent=fread($fp,filesize($filename));
        fclose($fp);
        return strpos($filecontent,chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0')===FALSE?0:1;
		}
	}
	
}