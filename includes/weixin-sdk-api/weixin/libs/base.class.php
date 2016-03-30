<?php
/**
 *  weixin sdk core
 *  @time 2014-2-16
 *  @author midoks@163.com
 *	@version 1.0
 */
class Weixin_BaseCore{

	/**
 	* @func get remote data
	* @param string $url
	* @param string $json
	* @ret string $response
 	*/
	private function get($url, $json = ''){
		$go = curl_init();
		curl_setopt($go, CURLOPT_URL, $url);
		//curl_setopt($go, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($go, CURLOPT_MAXREDIRS, 30);
		curl_setopt($go, CURLOPT_HEADER, 0);
		curl_setopt($go, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($go, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($go, CURLOPT_TIMEOUT, 30);
		if(!empty($json)){//POST Data
			curl_setopt($go, CURLOPT_POST, 1);
			curl_setopt($go, CURLOPT_POSTFIELDS ,$json);
		}
		$response = curl_exec($go);
		curl_close($go);
		return $response;
	}

	public function getToken($app_id, $app_sercet){
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$app_id}&secret={$app_sercet}";
		return $this->get($url);
	}

	public function pushMsgText($token, $open_id, $msg){
		$info['touser'] = $open_id;
		$info['msgtype'] = 'text';
		$info['text']['content'] = $msg;

		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		return $this->get($url, $this->to_json($info));
	}

	public function pushMsgImage($token, $open_id, $media_id){
		$info['touser'] = $open_id;
		$info['msgtype'] = 'image';
		$info['image']['media_id'] = $media_id;
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		return $this->get($url, json_encode($info));
	}

	public function pushMsgVoice($token, $open_id, $media_id){
		$info['touser'] = $open_id;
		$info['msgtype'] = 'voice';
		$info['voice']['media_id'] = $media_id;
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		return $this->get($url, json_encode($info));
	}

	public function pushMsgVideo($token, $open_id, $media_id, $title, $desc){
		$info['touser'] = $open_id;
		$info['msgtype'] = 'video';
		$info['video']['media_id'] = $media_id;
		$info['video']['title'] = $title;
		$info['video']['description'] = $desc;
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		return $this->get($url, json_encode($info));
	}

	public function pushMsgMusic($token, $open_id, $thumb_media_id, $title, $desc, $musicurl, $hqmusicurl){
		$info['touser'] = $open_id;
		$info['msgtype'] = 'music';
		$info['music']['title'] = $title;
		$info['music']['description'] = $desc;
		$info['music']['thumb_media_id'] = $thumb_media_id;
		$info['music']['musicurl'] = $musicurl;
		$info['music']['hqmusicurl'] = $hqmusicurl;
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		return $this->get($url, json_encode($info));
	}

	public function pushMsgNews($token, $open_id, $info){
		$info['touser'] = $open_id;
		$info['msgtype'] = 'news';
		$info['news']['articles'] = $info;
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		return $this->get($url, json_encode($info));
	}


/**
 *  多客服系统接口 start 
 *	多客服系统是在的插件中使用!!!
 */
	
	/**
	 *	@func 获取客服聊天记录
	 *	@param $token		调用接口凭证
	 *	@param $open_id		普通用户的标识，对当前公众号唯一
	 *	@param $starttime	查询开始时间，UNIX时间戳
	 *	@param $endtime		查询结束时间，UNIX时间戳，每次查询不能跨日查询
	 *	@param $pagesize	每页大小，每页最多拉取1000条
	 *	@param $pageindex	查询第几页，从1开始	 
	 */
	public function getCustomServiceLog($token, $open_id, $starttime, $endtime, $pagesize=20, $pageindex=1){
		$url = "https://api.weixin.qq.com/cgi-bin/customservice/getrecord?access_token={$token}";
		$info['open_id'] = $open_id;
		$info['starttime'] = $starttime;
		$info['endtime'] = $endtime;
		$info['pagesize'] = $pagesize;
		$info['pageindex'] = $pageindex;
		return $this->get($url, json_encode($info));
	}
/* 多客服系统接口 end */

/* meun setting start */

	//菜单获取
	public function menuGet($token){
		$url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$token}";
		return $this->get($url);
	}

	//设置菜单哪
	public function menuSet($token, $json){
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$token}";
		return $this->get($url, $json);
	}

	//删除菜单哪
	public function menuDel($token){
		$url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$token}";
		return $this->get($url);
	}
/* meun setting end */

/* 智能接口 start */

	/**
     *  @func 智能语意接口
     *	@ret string json
	 */ 
	public function getSemantic($token, $query, $category, $lat, $long, $city, $region, $appid, $uid){
		$url = "https://api.weixin.qq.com/semantic/semproxy/search?access_token={$token}";
		$info['query'] = $query;
		$info['category'] = $category;
		$info['lat'] = $lat;
		$info['long'] = $long;
		$info['city'] = $city;
		$info['region'] = $region;
		$info['appid'] = $appid;
		$info['uid'] = $uid;
		return $this->get($url, json_encode($info));
	}
	

/* 智能接口 end */


	//upload and download
	public function download($token, $media_id){
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$token}&media_id={$media_id}";
		return $this->get($url);
	}

	public function upload($token, $type, $file){
		$info['media'] = '@'.$file;
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token={$token}&type={$type}";
		//$url = "http://127.0.0.1/hello.php";
		return $this->get($url, $info);
	}

	public function uploadUrl($token, $type, $fn, $mime, $content){
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token={$token}&type={$type}";
		return $this->uploadContents($url, $fn, $mime, $content, $token, $type);
	}


	public function uploadContents($url, $fn, $mime, $content, $token, $type){
		$boundary = substr(md5(rand(0,32000)), 0, 10);
		$boundary = '--WebKitFormBoundary'.$boundary;
	
		$data .= "--$boundary\n";
		$data .= "Content-Disposition: form-data; name=\"media\"; filename=\"{$fn}\";\r\n";
		$data .= 'Content-Type: '.$mime."\r\n";
		$data .= $content."\r\n\r\n";
		$data .= "--$boundary--\r\n";
		
		return	$this->get($url, $data);
		
	}

	public function uploadContent5($url, $fn, $mime, $content, $token, $type){
		$boundary = substr(md5(rand(0,32000)), 0, 10);
		$boundary = '--WebKitFormBoundary'.$boundary;

		//$data .= "--$boundary\n";
		//$data .= "Content-Disposition: form-data; name=\"media\"\n";
		//$data .= "media\n";
		
		$data .= "--$boundary\n";
		$data .= "Content-Disposition: form-data; name=\"media\"; filename=\"{$fn}\";\r\n";
		$data .= 'Content-Type: '.$mime."\r\n\r\n\r\n\r\n";
		$data .= $content."\r\n\r\n";
		$data .= "--$boundary--\r\n";

		//$fp = fsockopen('127.0.0.1', 80, $errno, $errstr, 10);
		$fp = fsockopen('file.api.weixin.qq.com', 80, $errno, $errstr, 10);

		$postStr = "POST /cgi-bin/media/upload?access_token={$token}&type={$type} HTTP/1.1\r\n";
		//$postStr = "POST /hello.php HTTP/1.1\r\n";
		//$postStr .= "Host: 127.0.0.1\r\n";
		$postStr .= "Host: file.api.weixin.qq.com\r\n";
		$postStr .= "User-Agent: {$_SERVER['HTTP_USER_AGENT']}\r\n";
		$postStr .= "Content-Length: ".strlen(trim($data))."\r\n";
		$postStr .= "Content-Type: multipart/form-data; boundary={$boundary}\r\n";
		$postStr .= "Accept-Encoding: gzip,deflate,sdch;\r\n";
		

		/*foreach($_COOKIE as $k=>$v){
			$cookiestr .= "{$k}:{$v};";
		}

		$postStr .= "Cookie: {$cookiestr}\r\n";*/
		$postStr .= "\r\n\r\n";

		$postStr .= $data;

		echo '<pre>';
		echo $postStr;
		echo "</pre>";

		if($fp){
			fwrite($fp, $postStr);
			while (!feof($fp)) {
        		echo fgets($fp, 128);
    		}			
		}else{
			return false;
		}
		
	}

	private function uploadContents4($url, $fn, $mime, $content){
		$boundary = substr(md5(rand(0,32000)), 0, 10);
		  
		//$data .= "--$boundary\n";
		//$data .= "Content-Disposition: form-data; name=\"media\"\n";
		//$data .= "media\n";
		  
		$data .= "--$boundary\n";
		$data .= "Content-Disposition: form-data; name=\"media\"; filename=\"{$fn}\"\r\n";
		$data .= 'Content-Type: '.$mime."\r\n";    
		$data .= 'Content-Transfer-Encoding: binary'."\r\n\r\n\r\n";
		$data .= ($content)."\r\n";
		$data .= "--$boundary--\r\n";
		
		echo '<pre>';
		echo $data;
		echo '</pre>';

		$context = stream_context_create(array(
			'http'=> array(
				'method' => 'POST',
				'timeout'=> 10,
				'user_agent'=>$_SERVER['HTTP_USER_AGENT'],
				'header' =>"Content-Type: multipart/form-data; boundary={$boundary}".
					"\r\nContent-Length: ".strlen($content).
					"\r\nReferer: http://mp.weixin.qq.com/".
					"\r\n\r\n",
				'content'=> $data
			)
		));
		$ret = file_get_contents($url, false, $context);
		return $ret;
	}

	private function uploadContents1($url, $fn, $mime, $content){

		$info['media'] = '@'.$content;
		$temp_headers = array(
			"Content-Disposition: attachment; form-data; name=\"media\";filename='{$fn}'",
			'Content-Type: application/x-www-form-urlencoded',
			'Content-Length: '.strlen($content),
		);

		$go = curl_init();
		curl_setopt($go, CURLOPT_URL, $url);
		curl_setopt($go, CURLOPT_HTTPHEADER, $temp_headers);
		curl_setopt($go, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($go, CURLOPT_MAXREDIRS, 30);
		curl_setopt($go, CURLOPT_HEADER, 0);
		curl_setopt($go, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($go, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($go, CURLOPT_TIMEOUT, 30);

		//curl_setopt($go, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($go, CURLOPT_POST, 1);
		curl_setopt($go, CURLOPT_POSTFIELDS, $content);
		$response = curl_exec($go);
		curl_close($go);
		return $response;
	}

	private function uploadContents3($url, $fn, $mime, $content){
		$boundary = substr(md5(rand(0,32000)), 0, 10);
		  
		$data .= "--$boundary\n";
		$data .= "Content-Disposition: form-data; name=\"media\"\n\n";
		$data .= "media\n";
		  
		$data .= "--$boundary\n";
		$data .= "Content-Disposition: form-data; name=\"media\";filename=\"{$fn}\"\n";
		$data .= 'Content-Type: '.$mime."\n";    
		$data .= 'Content-Transfer-Encoding: binary'."\n\n";
		$data .= $content."\n";
		$data .= "--$boundary--\n";

		$go = curl_init();
		curl_setopt($go, CURLOPT_URL, $url);
		curl_setopt($go, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data; boundary=".$boundary,
			'Referer: https://mp.weixin.qq.com'));
		curl_setopt($go, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($go, CURLOPT_MAXREDIRS, 30);
		curl_setopt($go, CURLOPT_HEADER, 0);
		curl_setopt($go, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($go, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($go, CURLOPT_TIMEOUT, 30);

		//curl_setopt($go, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($go, CURLOPT_POST, 1);
		curl_setopt($go, CURLOPT_POSTFIELDS, $data);
		$response = curl_exec($go);
		curl_close($go);
		return $response;
	}

/* user info about  start */
	public function getUserInfo($token, $open_id, $lang='zh_CN'){
	
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$token}&openid={$open_id}lang={$lang}";
		return $this->get($url);
	}

	public function getUserList($token, $next_openid){
		if(empty($open_id)){
			$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$token}";
		}else{
			$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$token}&next_openid={$next_openid}";
		}
		return $this->get($url);
	}

	public function setUserGroup($token, $json){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token={$token}";
		return $this->get($url, $json);
	}

	public function getUserGroup($token){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token={$token}";
		return $this->get($url);
	}

	public function getUserGroupPosition($token, $json){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/getid?access_token={$token}";
		return $this->get($url, $json);
	}

	public function modUserGroup($token, $json){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/update?access_token={$token}";
		return $this->get($url, $json);
	}

	public function movUserGroup($token, $json){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token={$token}";
		return $this->get($url, $json);
	}

	public function updateRemark($token, $open_id, $remark){
		$url = "https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token={$remark}";
		$a['openid'] = $open_id;
		$a['remark'] = $remark;
		return $this->get($url, $this->to_json($a));
	}


/* user info about  start */

/* 推广支持 start */

	//创建临时二维码ticket
	public function temp_ticket($token, $json){
		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$token}";
		return $this->get($url, $this->to_json($json));
	}

	//创建永久二维码ticket
	public function permanent_ticket($token, $json){
		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$token}";
		return $this->get($url, $this->to_json($json));
	}

	//通过ticket换取二维码
	public function get_ticket($ticket){
		$url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
		return $url;
	}

	//把长连接转换为短链接
	public function long2short($token, $url){
		$array['access_token'] = $token;
		$array['long_url'] = $url;
		$array['action'] = 'long2short';
		return $this->get("https://api.weixin.qq.com/cgi-bin/shorturl?access_token={$token}", $array);
	}

/* 推广支持 end */

	//上传图文消息素材
	public function uploadMsgImageText($token, $msg){
		$url = 'https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token='.$token;
		$info['articles'] = $msg;
		return $this->get($url, $this->to_json($info));
	}

	//通过分组进行群发
	public function sendAllByGroup($token, $group_id, $media_id, $msgtype = 'mpnews'){
		$msg['filter']['group_id'] = $group_id;
		$msg['mpnews']['media_id'] = $media_id;
		$msg['msgtype'] = $msgtype;
		$url = 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token='.$token;
		return $this->get($url, $this->to_json($msg));
	}

	//根据OpenID列表群发
	public function sendAll($token, $user, $media_id, $msgtype = 'mpnews'){
		$msg['touser'] = $user;
		$msg['mpnews']['media_id'] = $media_id;
		$msg['msgtype'] = $msgtype;
		$url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$token;
		return $this->get($url, $this->to_json($msg));
	}

	//删除群发信息
	public function deleteSend($token, $id){
		$msg['msgid'] = $id;
		$url = 'https://api.weixin.qq.com/cgi-bin/message/mass/delete?access_token='.$token;
		return $this->get($url, $this->to_json($msg));
	}

	//发送模版消息
	public function sendTemplateInfo($token, $json){
		$url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$token;
		return $this->get($url, $this->to_json($json));
	}

	//获取微信ip地址
	public function getWeixinIp($token){
		$url = 'https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token='.$token;
		return $this->get($url);
	}

	//转换json的数据
	public function to_json($array){
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
	}

	/**************************************************************
     *
     *  使用特定function对数组中所有元素做处理
     *  @param  string  &$array     要处理的字符串
     *  @param  string  $function   要执行的函数
     *  @return boolean $apply_to_keys_also     是否也应用到key上
     *  @access public
     *
     *************************************************************/
    public function arrayRecursive(&$array, $function, $apply_to_keys_also = false){
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }
            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }
}
?>
