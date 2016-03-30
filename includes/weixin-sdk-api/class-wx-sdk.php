<?php
/**
 *	微信机器人核心Api
 *
 *	@author		midoks
 *	@category	WxSDK
 *	@package 	WxRobot/WxSDK
 *	@since 		5.3.0
 */
class WxRobot_SDK{

	/**
	 * WxRobot_SDK Instance
	 */
	public static $_instance = null;

	public function __construct(){
		include_once('weixin/weixin.class.php');
		$this->options = get_option(WEIXIN_ROBOT_OPTIONS);
		$this->info    = wx_request_array();
		$this->obj = new WeiXin_SDK($this->options['ai'], $this->options['as']);
	}

	/**
	 * WxRobot SDK类实例化
	 * 
	 * @return WxRobot_SDK instance
	 */
	public static function instance(){
		if( is_null (self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	/**
	 * 返回文本信信息
	 *
	 * @param $Msg 信息
	 * @param $mode 是否开启截取模式(0:不开启,1:开启.默认开启)
	 * @return string xml
	 * exp:
	 * echo $this->toMsgText($contentStr);//文本地址
	 */
	public function toMsgText($Msg, $mode=1){
		if($mode){
			$c = strlen($Msg);
			if($c > 2048){
				$Msg = $this->byte_substr($Msg);
			}
		}
		return $this->obj->toMsgText($this->info['FromUserName'], $this->info['ToUserName'], $Msg);
	}

	/**
	 *	在手机客服端显示的一种(仅在客服端,有效果)
	 *	
	 *	@param array $alink
	 *	@param string $suffix (一般为"\r\n", 默认为空)
	 *	@info 由(http://weibo.com/clothand)提供, 当然我做了优化
	 *	@exp:
	 *	$alink[0]['link'] = 'midoks.cachecha.com';
	 *	$alink[0]['title'] = '你好';
	 */
	public function toMsgTextAlink($alink, $suffix = ''){
		$link_info = '';
		foreach($alink as $k=>$v){
			$_n = "<a href='{$v['link']}'>{$v['title']}</a>".$suffix;
			$ret_n = $link_info.$_n; 
			$_c = strlen($ret_n);
			if($_c > 2048){
				return $this->toMsgText($link_info);
			}else{
				$link_info .= $_n;
			}
		}
		return $this->toMsgText($link_info);
	}

	
	/**
	 * 返回图片信息(测试未成功)
	 *
 	 * @param $MediaId 图片信息
	 * @return string xml
	 * exp:
	 * echo $this->toMsgPic($MediaId);图
	 */
	public function toMsgPic($MediaId){
  		return $this->obj->toMsgPic($this->info['FromUserName'], $this->info['ToUserName'], $MediaId);
	}


	/**
	 * 返回voice xml
	 *
	 * @param MediaId
	 * @return string xml
	 * exp:
	 * echo $this->toMsgVoice($MediaId);
	 */
	public function toMsgVoice($MediaId){
		return $this->obj->toMsgVoice($this->info['FromUserName'], $this->info['ToUserName'], $MediaId);
	}

	/**
	 * 返回music xml
	 *
	 * @param string $title 标题
	 * @param string $desc 描述
	 * @param string $MusicUrl 地址
	 * @param string $HQMusicUrl 高清播放(会首先选择)
	 * @return xml
	 * exp:
	 * echo $this->toMsgVoice('声音','当男人好难！', $MusicUrl, $MusicUrl);//voice
	 */
	public function toMsgMusic($title, $desc, $MusicUrl, $HQMusicUrl, $ThumbMediaId=''){
		return $this->obj->toMsgMusic($this->info['FromUserName'], $this->info['ToUserName'], $title, $desc, $MusicUrl, $HQMusicUrl, $ThumbMediaId);
	}

	/**
	 * 返回video xml
	 * 
	 * @param 通过上传多媒体文件,得到id
	 * @param 缩图的媒体ID,通过上传多媒体文件,得到的id
	 * @return xml
	 */
	public function toMsgVideo($media_id, $thumb_media_id){
		return $this->obj->toMsgVideo($this->info['FromUserName'], $this->info['ToUserName'], $media_id, $thumb_media_id);
	}

 	/**
	 * 返回图文
	 * 
	 * @param array $info
	 * @param array $array
	 * @return string xml
	 * exp
	 * $textPic = array(
	 *		array(
	 *			'title'=> '标题',
	*			'desc'=> '描述',
	*			'pic'=> $this->bigPic(),//图片地址
	*			'link'=>$pic,//图片链接地址
	*		),//第一个图片为大图
	*		array(
	*			'title'=> '标题',
	*			'desc'=> '描述',
	*			'pic'=> $this->smallPic(),//图片地址
	*			'link'=> '',//图片链接地址
	*		),//此自以后皆为小图
	*		array(
	*			'title'=> '标题',
	*			'desc' => '描述',
	*			'pic'  => $this->smallPic(),//图片地址
	*			'link' => '',//图片链接地址
	*		),
	*		array(
	*			'title'=> '标题',
	*			'desc' => '描述',
	*			'pic'  => $this->smallPic(),//图片地址
	*			'link' => '',//图片链接地址
	*		),
	*		array(
	*			'title'=> '标题',
	*			'desc' => '描述',
	*			'pic'  => $this->smallPic(),//图片地址
	*			'link' => '',//图片链接地址
	*		),
	*		array(
	*			'title'=> '标题',
	*			'desc' => '描述',
	*			'pic'  => $this->smallPic(),//图片地址
	*			'link' => '',//图片链接地址
	*		),
	*	);
	*	echo $this->toMsgTextPic($textPic);//图文
	*/
	public function toMsgTextPic($picTextInfo){
		$fromUserName = $this->info['FromUserName'];
        $toUserName = $this->info['ToUserName'];
  		return $this->obj->toMsgNews($fromUserName, $toUserName, $picTextInfo);
	}

	/**
	 *  在客服端列表的展示形式
	 *
	 *  $list[0]['title'] = '0';
	 *	$list[0]['desc'] =  '0';
	 *	$list[0]['link'] = "http://midok.cachecha.com/";
	 */
	public function toMsgTextPicList($list){
		$info = array();
		foreach($list as $k=>$v){
			$a['title'] = $v['title'];
			$a['desc'] =  $v['desc'];
			$a['link'] = $v['link'];
			$info[] = $a;
		}
		return $this->toMsgTextPic($info);//图文
	}

	/**
	 * 重新获取token
	 *
	 * @return string
	 */
	public function getReToken(){
		$data = $this->obj->getToken();
		$data = json_decode($data, true);
		$data['expires_in'] = time() + $data['expires_in'];
		$this->options['weixin_robot_token'] = json_encode($data);
		update_option(WEIXIN_ROBOT_OPTIONS, $this->options);
		return $data['access_token']; 
	}
	
	/**
	 * 获取token
	 *
	 * @return string
	 */
	public function getToken(){
		if(empty($this->options['ai']) || empty($this->options['as'])){
			wx_notice_msg('填写服务号的信息!!!');	
		}

		if(!empty($this->options['weixin_robot_token'])){
			$data = $this->options['weixin_robot_token'];
			$data = base64_decode($data);
			$data = json_decode($data, true);
			if($data['expires_in'] <= time()){
				$_data =  $this->obj->getToken();
				$data = json_decode($_data, true);
				if(isset($data['errcode'])){//判断错误
					return($_data);
				}
				$data['expires_in'] = time() + $data['expires_in'];
				$this->options['weixin_robot_token'] = base64_encode(json_encode($data));
				update_option(WEIXIN_ROBOT_OPTIONS, $this->options);
			}
			return $data['access_token'];
		}else{
			$_data = $this->obj->getToken();
			$data = json_decode($_data, true);
			if(isset($data['errcode'])){//判断错误
				return($_data);
			}
			$data['expires_in'] = time() + $data['expires_in'];
			$this->options['weixin_robot_token'] = base64_encode(json_encode($data));
			update_option(WEIXIN_ROBOT_OPTIONS, $this->options);
		}
		return $data['access_token'];
	}

/**
 *  多客服系统接口 start 
 *	多客服系统是在的插件中使用!!!
 */
	/**
	 *	获取客服聊天记录
	 *
	 *	@param $open_id		普通用户的标识，对当前公众号唯一
	 *	@param $starttime	查询开始时间，UNIX时间戳
	 *	@param $endtime		查询结束时间，UNIX时间戳，每次查询不能跨日查询
	 *	@param $pagesize	每页大小，每页最多拉取1000条
	 *	@param $pageindex	查询第几页，从1开始
	 *	@return string json	 
	 */
	public function getCustomServiceLog($open_id, $starttime, $endtime, $pagesize=20, $pageindex=1){
		$token = $this->getToken();
		return $this->obj->getCustomServiceLog($token, $open_id, $starttime, $endtime, $pagesize, $pageindex);
	}
/* 多客服系统接口 end */

/* 菜单相关 start */
	/**
	 * 删除菜单
	 *
	 * @return json
	 */
	public function menuDel(){
		$token = $this->getToken();
		$data = $this->obj->menuDel($token);
		return $data;
	}

	/**
	 * 设置菜单
	 * 
	 * @param json $json 传递json数据
	 * @return json
	 */
	public function menuSet($json){
		$token = $this->getToken();
		return $this->obj->menuSet($token, $json);
	}

	/**
	 * 获取菜单
	 *
	 * @return json|bool
	 * @since 5.3.1
	 */
	public function menuGet(){
		$token = $this->getToken();
		$data = $this->obj->menuGet($token);
		$data = json_decode($data, true);
		if(isset($data['errcode'])){
			if('46003' == $data['errcode']){
				return true;
			}
			return false;
		}
		return $data;
	}
/* 菜单相关 start */

	/**
	 * 智能语意分析
	 * 
	 * @param string $query 	查询的句子
	 * @param string $appid 	公众号唯一标示
	 * @param string $uid 		用户唯一ID
	 * @param string $city 		城市名称			默认 北京
	 * @param string $category 	需要使用的服务类型	默认flight,hotel
	 * @param string $region 	区域名称     		默认为空
	 * @param string $lat 		纬度坐标  			默认为空
	 * @param string $long 		经度坐标			默认为空
	 * @return json
	 */
	public function getSemantic($query, $appid, $uid, $city='北京', $category='flight,hotel', $region ='', $lat = '', $long = ''){
		$token = $this->getToken();
		return $this->obj->getSemantic($token, $query, $category, $lat, $long, $city, $region, $appid, $uid);
	}

	//主动推送消息(24小时联系的人)

	/**
	 *	推送文本类型的消息
	 *
	 *	@param string $open_id
	 *	@param string $msg
	 *	@return json
	 */
	public function pushMsgText($open_id, $msg){
		$token = $this->getToken();
		return $this->obj->pushMsgText($token, $open_id, $msg);
	}

	/**
	 *	推送图片消息
	 *
	 *	@param string $open_id
	 *	@param string $media_id
	 *	@return json
	 */
	public function pushMsgImage($open_id, $media_id){
		$token = $this->getToken();
		return $this->obj->pushMsgImage($token, $open_id, $media_id);
	}

	/**
	 *	推送高清图片消息
	 *
	 *	@param string $open_id 用户open_id
	 *	@param string $file 文件绝对地址
	 *	@return json
	 */
	public function pushMsgImageAdv($open_id, $file){
		if(filesize($file) > 131072){
			return '{errcode: "file size too big"}';
		}
		$token = $this->getToken();
		return $this->obj->pushMsgImageAdv($token, $open_id, $file);
	}

	/**
	 *	推送音频消息
	 *
	 *	@param string $open_id 用户open_id
	 *	@param string $media_id 资源ID
	 *	@return json
	 */
	public function pushMsgVoice($open_id,$media_id){
		$token = $this->getToken();
		return $this->obj->pushMsgVoice($token, $open_id, $media_id);
	}

	/**
	 *	推送高清音频消息
	 *
	 *	@param string $open_id 用户open_id
	 *	@param string $media_id 资源ID
	 *	@return json
	 */
	public function pushMsgVoiceAdv($open_id, $file){
		if(filesize($file) > 262144){
			return '{errcode: "file size too big"}';
		}
		$token = $this->getToken();
		return $this->obj->pushMsgVoiceAdv($token, $open_id, $file);
	}

	/**
	 *	推送视频消息
	 *
	 *	@param string $open_id 用户open_id
	 *	@param string $media_id 资源ID
	 *	@param string $title 标题
	 *	@param string $desc 描述信息
	 *	@return json
	 */
	public function pushMsgVideo($open_id, $media_id, $title, $desc){
		$token = $this->getToken();
		return $this->obj->pushMsgVoice($token, $open_id, $media_id);
	}

	/**
	 *	推送高清视频消息
	 *
	 *	@param string $open_id 用户open_id
	 *	@param string $file 绝对地址
	 *	@param string $title 标题
	 *	@param string $desc 描述信息
	 *	@return json
	 */
	public function pushMsgVideoAdv($open_id, $file, $title, $desc){
		if(filesize($file) > 1048576){
			return '{errcode: "file size too big"}';
		}
		$token = $this->getToken();
		return $this->obj->pushMsgVoiceAdv($token, $file,$open_id, $file);
	}

	/**
	 *	推送音乐消息
	 *
	 *	@param string $open_id 用户open_id
	 *	@param string $file 文件绝对地址
	 *	@param string $title 标题
	 *	@param string $desc 描述信息
	 *	@param string $musicurl 音乐url地址
	 *	@param string $hqmusicurl 高质量url地址
	 *	@return json
	 */
	public function pushMsgMusic($open_id, $file, $title, $desc, $musicurl, $hqmusicurl){
		$token = $this->getToken();
		return $this->obj->pushMsgMusic($token, $open_id, $thumb_media_id, $title, $desc, $musicurl, $hqmusicurl);
	}

	/**
	 *	推送高清音乐消息
	 *
	 *	@param string $open_id 用户open_id
	 *	@param string $file 文件绝对地址
	 *	@param string $title 标题
	 *	@param string $desc 描述信息
	 *	@param string $musicurl 音乐url地址
	 *	@param string $hqmusicurl 高质量url地址
	 *	@return json
	 */
	public function pushMsgMusicAdv($open_id, $file, $title, $desc, $musicurl, $hqmusicurl){
		if(filesize($file) > 65536){
			return '{errcode: "file size too big"}';
		}
		$token = $this->getToken();
		return $this->obj->pushMsgMusicAdv($token, $open_id, $file, $title, $desc, $musicurl, $hqmusicurl);
	}


	/**
	 * 推送图文信息
	 *
	 * @param string $open_id 用户open_id
	 * @param array $info 图文信息
	 * @return json
	 * @exp: $info should be:
	 *		$info[]["title"] = "Happy Day";
     *      $info[]["description"]="Is Really A Happy Day";
     *      $info[]["url"] = "URL";
     *      $info[]["picurl"] = "PIC_URL";
	 */
	public function pushMsgNew($open_id, $info){
		$token = $this->getToken();
		return $this->obj->pushMsgNew($token, $open_id, $info);
	}
//END PUSH
	

	/**
	 * 	下载媒体文件
	 *
	 *	@param string $media_id 媒体文件ID
	 *	@return json
	 */
	public function download($media_id){
		$token = $this->getToken();
		return $this->obj->download($token, $media_id);
	}

	/**
	 *	上传媒体文件
	 *
 	 *	@param string $type 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
	 *	@param string $file 文件地址
	 *	@return json
	 */
	public function upload($type, $file){
		$token = $this->getToken();
		return $this->obj->upload($token, $type, $file);
	}

	/*public function uploadUrl($type, $url){
		$token = $this->getToken();
		$content = file_get_contents($url, false, stream_context_create(array('http'=> array('timeout'=> 10))));
		//var_dump($content);
		$fn = basename($url);
		$fn = "author.jpg";
		$mime = mime_content_type($fn);
		$mime = "image/jpeg";
		return $this->obj->uploadUrl($token, $type, $fn, $mime, $content);
	}*/

//user info about
	/**
	 * 	获取用户信息
	 * 	
	 *	@param string $open_id 普通用户的标识，对当前公众号唯一
	 *	@param string $lang 返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
	 *	@return json
	 */
	public function getUserInfo($open_id, $lang='zh_CN'){
		$token = $this->getToken();
		return $this->obj->getUserInfo($token, $open_id);
	}

	/**
	 * 获取用户列表
	 *
	 * @param string $next_openid 
	 * @return json
	 */
	public function getUserList($next_openid = ''){
		$token = $this->getToken();
		return $this->obj->getUserList($token, $next_openid);
	}

	/**
	 * 创建分组
	 * @param json $json 数据
	 * @return json
	 */
	public function setUserGroup($json){
		$token = $this->getToken();
		return $this->obj->setUserGroup($token, $json);
	}

	/**
	 * 查询所有组
	 * 
	 * @return json
	 */
	public function getUserGroup(){
		$token = $this->getToken();
		return $this->obj->getUserGroup($token);
	}

	/**
	 * 查询分组用户所在分组
	 *
	 * @param json $json 数据
	 * @return json
	 */
	public function getUserGroupPosition($json){
		$token = $this->getToken();
		return $this->obj->getUserGroupPosition($token, $json);
	}

	/**
	 * 修改分组名
	 * 
	 * @param json $json 数据
	 * @return json
	 */
	public function modUserGroup($json){
		$token = $this->getToken();
		return $this->obj->modUserGroup($token, $json);
	}

	/**
	 * 移动用户分组
	 * 
	 * @param json $json 数据
	 * @return json
	 */
	public function movUserGroup($json){
		$token = $this->getToken();
		return $this->obj->movUserGroup($token, $json);
	}

	/**
	 * 备注用户
	 *
	 * @param string $open_id 用户open_id
	 * @param string $remark 标记
	 * @return json
	 */
	public function updateRemark($open_id, $remark){
		$token = $this->getToken();
		return $this->obj->updateRemark($token, $open_id, $remark);
	}
//推送消息

/* 推广相关 start */
	
	/**
	 *	获取二维码的图片的图片路径
	 *
	 *	@param string $scene_id 	识别标示,默认midoks
	 * 	@param time $time 过期时间 当$type为temp类型时有效, 默认5分钟
	 *	@param string $type 二维码的类型, 默认temp, 还有 permanent 类型
	 *	@return url
	 */
	public function get_ticket_url($scene_id = 'midoks', $time = '3000', $type = 'temp'){
		$url = '';
		if('temp' == $type){
			$tmp_ticket = array(
				'expire_seconds'=> $time,
				'action_name'=>'QR_SCENE',
				'action_info'=> array('scene'=> array('scene_id'=> $scene_id)),
			);
			$data = $this->temp_ticket($tmp_ticket);
			$data = json_decode($data, true);
			$url = $this->get_ticket($data['ticket']);
		}else if('permanent' == $type){
			$tmp_ticket = array(
				'action_name'=>'QR_LIMIT_SCENE',
				'action_info'=> array('scene'=> array('scene_id'=> $scene_id)),
			);
			$data = $this->permanent_ticket($tmp_ticket);
			$data = json_decode($data, true);
			$url = $this->get_ticket($data['ticket']);
		}
		return $url;
	}

	/**
	 * 创建二维码ticket
	 *
	 * @param json $json 数据
	 * @return json
	 */
	public function temp_ticket($json){
		$token = $this->getToken();
		return $this->obj->temp_ticket($token, $json);
	}

	/**
	 * 创建永久二维码ticket
	 * 
	 * @param json $json 数据
	 * @return json
	 */
	public function permanent_ticket($json){
		$token = $this->getToken();
		return $this->obj->permanent_ticket($token, $json);
	}

	/**
	 * 获取ticket,返回路径
	 * 
	 * @
	 */
	public function get_ticket($ticket){
		return $this->obj->get_ticket($ticket);
	}

	/**
	 * 把长连接转换为短链接
	 *
	 * @param string $url URL地址
	 * @return json
	 */
	public function long2short($url){
		$token = $this->getToken();
		return $this->obj->long2short($token, $url);
	}

/* 推广相关 end */


	//不是真正意思上的群发,推荐到微信官方使用群发,本例,仅供参考
	/**
	 * 分组群发接口
	 * @param json $json 数据
	 * @return json
	 * exp:
     * 	$msg[] = array(
     *		'image' => WEIXIN_PLUGINS."/demo.jpg", 	//仅支持jpg(必须是本地的图片)
     *		'author' => "xxx",					//作者
     *		'title' => "Happy Day",				//标题
     *		'content_source_url' => "www.qq.com",//内容地址
     *		'content' => "content",				//内容
     *		'digest' => "digest",				//图文消息的描述
     *		'show_cover_pic' => "1"				//是否显示封面,0,为不显示,1,为显示
     *	),
	 */
	public function sendGroupInfo(array $json){
		//获取所有组
		$groups = $this->getUserGroup();
		$_groups = json_decode($groups, true);
		if(!isset($_groups['groups'])){
			return $_groups;
		}
		$_max = 0;
		$_id = null;

		foreach ($_groups['groups'] as $v) {
			if($v['count'] > $_max){
				$_max = $v['count'];
				$_id = $v['id'];
			}
		}

		if(is_numeric($_id)){
			$_json = array();
			foreach($json as $k=>$v){
				$image = $this->upload('thumb', $v['image']);
				$image = json_decode($image, 'true');
				if (!isset($image['thumb_media_id'])) {
					return $image;
				}

				$_insert = array();
				$_insert['thumb_media_id'] 		= $image['thumb_media_id'];
				$_insert['author'] 				= $v['author'];
				$_insert['title'] 				= $v['title'];
				$_insert['content_source_url'] 	= $v['content_source_url'];
				$_insert['digest'] 				= $v['digest'];
				$_insert['show_cover_pic'] 		= $v['show_cover_pic'];
				$_json[] = $_insert;
			}
			$imageText = $this->uploadMsgImageText($_json);
			$imageText = json_decode($imageText, 'true');
			if(!isset($imageText['media_id'])){
				return $imageText;
			}
			return $this->sendAllByGroup($_id, $imageText['media_id']);
		}
		return false;
	}
	
	/**
	 *	上传图文消息素材优化版
	 *
	 *	@param array $msg 上传消息数据
	 *	@return json
	 *
	 *  exp:
     * 	$msg[] = array(
     *		'thumb_media_id' => "qI6_Ze_6PtV7svjolgs",
     *		'author' => "xxx",
     *		'title' => "Happy Day",
     *		'content_source_url' => "www.qq.com",
     *		'content' => "content",
     *		'digest' => "digest",
     *		'show_cover_pic' => "1"
     *	),
	 */
	public function uploadMsgImageText(array $msg){
		$num = count($msg);
		if($num>10){
			$_msg = array_slice($msg, 0, 9);
		}else{
			$_msg = $msg;
		}
		$token = $this->getToken();
		return $this->obj->uploadMsgImageText($token, $_msg);
	}

	/**
	 * 通过分组进行群发
	 *
	 * @param string $group_id 分组ID
	 * @param string $media_id 资源ID
	 * @param string $msgtype 资源类型
	 * @return json
	 */
	public function sendAllByGroup($group_id, $media_id, $msgtype = 'mpnews'){
		$token = $this->getToken();
		return $this->obj->sendAllByGroup($token, $group_id, $media_id, $msgtype);
	}

	/**
	 * 根据OpenID列表群发
	 *
	 * @param array $user 发送用户列表
	 * @param string $media_id 资源ID
	 * @return json
	 */
	public function sendAll($user, $media_id, $msgtype = 'mpnews'){
		$token = $this->getToken();
		return $this->obj->sendAll($token, $user, $media_id, $msgtype);
	}

	/**
	 * 删除群发信息
	 * 
	 * @param int $id 要删除群发的ID
	 * @return json
	 */
	public function deleteSend($id){
		$token = $this->getToken();
		return $this->obj->deleteSend($token, $id);
	}

	/**
 	 *	模版接口发送消息
	 *	@param array $json 模版信息
	 *	@return json
 	 *  exp: $array = array(
	 *		"touser"=>"OPENID",
     *      "template_id"=>"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
     *      "url"=>"http://weixin.qq.com/download",
     *      "topcolor"=>"#FF0000",
     *		"data"=> array(
     *			"first"=>array(
     *			 	"value"=>"您好，您已成功消费。",
     *              "color"=>"#0A0A0A"
     *			 ),	
     *			"keynote1"=>array(
     *			 	"value"=>"巧克力",
     *              "color"=>"#CCCCCC"
     *			 ),
     *			"keynote2"=>array(
     *			 	"value"=>"39.8元",
     *              "color"=>"#CCCCCC"
     *			 ),
     *			"keynote3"=>array(
     *			 	"value"=>"2014年9月16日",
     *              "color"=>"#CCCCCC"
     *			 ),
     * 			"remark"=>array(
     *           	"value"=>"欢迎再次购买。",
     *           	"color"=>"#173177"
     *           )
     *		)
 	 *	);
	 */
	public function sendTemplateInfo(array $json){
		$token = $this->getToken();
		return $this->obj->sendTemplateInfo($token, $json);
	}

	/**
	 * 获取微信可以IP
	 *
	 * @return json
	 */
	public function getWeixinIp(){
		$token = $this->getToken();
		return $this->obj->getWeixinIp($token);
	}


///base func lib
	/**
	 * 	返回定长字节(仅utf-8)
	 *	@param string $str 截取字符传
	 *	@param int $len 字节长度(默认:2048字节)
	 *	@return string	
	 */
	public function byte_substr($str, $len = 2048){
		$ret = '';
		$c = strlen($str);
		for($i=0; $i<$c; $i++){
			if(ord(substr($str, $i, 1)) > 0xa0){
				$temp_wd = substr($str, $i, 3);
				$i += 2;

				$temp_len = strlen($ret);
				if(($temp_len+3)>$len){
					return $ret;
				}else if(($temp_len+3) == $len){
					return $ret.$temp_wd;
				}else{
					$ret .= $temp_wd;
				}
			}else{
				$temp_wd = substr($str, $i, 1);
				$temp_len = strlen($ret);

				if(($temp_len+1)>$len){
					return $ret;
				}else if(($temp_len+1) == $len){
					return $ret.$temp_wd;
				}else{
					$ret .= $temp_wd;
				}
			}
		}
		return $ret;
	}

	/**
	 * 转化为微信可识别的json
	 *
	 * @param array $arr 数组
	 * @return json
	 */
	public function to_json($arr){
		return $this->obj->to_json($arr);
	}

}
?>
