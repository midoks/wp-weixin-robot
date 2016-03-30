<?php
/**
 *	WxRobot 命令响应类
 *	
 *	@author 	midoks
 *	@category   WxRobot
 *	@package 	WxRobot/Cmd
 *  @since		5.3.0
 */

/**
 * WxRobot 命令响应类
 */
class WxRobot_Cmd{

	/**
	 * WxRobot_Cmd Instance
	 */
	public static $_instance = null;

	/**
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct(){

		include_once('weixin-sdk-api/weixin_crypt/wxBizMsgCrypt.php');

		$this->options 	= get_option(WEIXIN_ROBOT_OPTIONS);
		$this->extends 	= WxRobot_Extends::instance();
		$this->info 	= wx_request_array();
	}

	/**
	 * WxRobot 命令响应实例化
	 * 
	 * @return WxRobot_Cmd instance
	 */
	public static function instance(){
		if( is_null (self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 *	命令分析
	 *	
	 *	@return xml
	 */
	public function cmd(){

		//插件接口调用
		if($wp_plugins = $this->extends->dealwith('all', $this->info)){
			$this->result =  $wp_plugins;
		}else{
			$this->result = $this->cmd_choose();
		}

		//开启数据库记录判断
		if($this->options['weixin_robot_record']){
			$this->weixin_robot_insert();
		}

		//是否加密通信
		if(!empty($this->options['EncodingAESKey'])  && !empty($_GET['encrypt_type'])){
			$this->result = wx_send_encode($this->result);
		}

		return $this->result;
	}

	/**
	 * 微信记录插入到数据中
	 *
	 * @return bool
	 */
	public function weixin_robot_insert(){
		$info = $this->info;
			
		$from = $info['FromUserName'];
		$to = $info['ToUserName'];
	   	$msgid = isset($info['MsgId']) ? $info['MsgId']: '';
		$msgtype = $info['MsgType'];
		$createtime = $info['CreateTime'];

		//文本内容
		$content = isset($info['Content']) ? $info['Content']: '';

		//图片资源
		$picurl = isset($info['PicUrl']) ? $info['PicUrl']: '';

		//地理位置上传
		$location_x = isset($info['Location_X']) ? $info['Location_X']: '0.00';
		$location_y = isset($info['Location_Y']) ? $info['Location_Y']: '0.00';
		$scale = isset($info['Scale']) ? $info['Scale']: '0.00';
		$label = isset($info['Label']) ? $info['Label']: '';

		//link分享
	   	$title= isset($info['Title']) ? $info['Title']: '';
	   	$description = isset($info['Description']) ? $info['Description']: '';
		$url = isset($info['Url']) ? $info['Url']: '';

		//事件
		$event = isset($info['Event']) ? $info['Event']: '';

		if(!empty($event)){
			//事件中的特殊操作
			if('TEMPLATESENDJOBFINISH'==$info['Event']){
				$content = '模版发送返回消息'.$info['Status'];
			}else if('MASSSENDJOBFINISH' == $info['Event']){
				$content = '事件推送群发结果:'.$info['Status'].
				'成功发送粉丝:'.$info['SentCount'].',失败发送粉丝:'.$info['ErrorCount'];
			}
		}

		$eventkey = isset($info['EventKey']) ? $info['EventKey']: '';

		//语音识别
		$format = isset($info['Format']) ? $info['Format']: '';
		$recognition = isset($info['Recognition']) ? $info['Recognition']: '';

		//资源ID
		$mediaid = isset($info['MediaId']) ? $info['MediaId']: '';
		$thumbmediaid = isset($info['ThumbMediaId']) ? $info['ThumbMediaId']: '';

		//回复
		$response = $this->ret_reply_type();

		//反应时间,本来是还有数据库插入的耗时(此时可以忽略不计)
		$response_time = timer_stop(0);
		$result =  WxRobot_Table_Records::instance()->insert($from, $to, $msgid, $msgtype, $createtime, $content, 
			$picurl, $location_x, $location_y,$scale, $label, $title, $description, 
			$url, $event,$eventkey,$format, $recognition, $mediaid, $thumbmediaid, $response, $response_time);

		return $result;
	}

	/**
	 * 回复的类型
	 * 
	 * @return string
	 */
	public function ret_reply_type(){
		$result = wx_parse_xml($this->result);
		if(isset($result['MsgType']) && !empty($result['MsgType']) ){
			switch($result['MsgType']){
				case 'text'		:	return '文本回复';
				case 'voice'	:	return '声音回复';
				case 'music'	:   return '音乐回复';
				case 'video'	:	return '视频回复';
				case 'link'		:	return '声音回复';
				case 'event'	:	return '事件回复';
				case 'news'		:	return '图文回复';
				case 'image'	:	return '图片回复';
				case 'location'	:	return '地址回复';
			}
		}
		return '无回复';
	}


	/**
	 * @func 类型选择
	 */
	public function cmd_choose(){
		switch($this->info['MsgType']){
			//文本消息	
			case 'text':return $this->textReply();break;
			//图片消息
			case 'image':return $this->imageReply();break;
			//语音消息
			case 'voice':return $this->voiceReply();break;
			//视频消息
			case 'video':return $this->videoReply();break;
			//事件消息
			case 'event':return $this->eventReply();break;
			//地理位置
			case 'location':return $this->locationReply();break;
			//连接信息
			case 'link':return $this->linkReply;break;
			//默认消息
			default:return $this->textReply();break;
		}
	}

	//文本消息回复
	public function textReply(){
		$kw = $this->info['Content'];//关键字
		include_once('weixin-robot/class-wx-cmd-text.php');
		
		WxRobot_Cmd_Text::instance()->setValue($this, $kw);
		return WxRobot_Cmd_Text::instance()->replay();
	}

	//图片消息回复
	public function imageReply(){

		$info['PicUrl'] = $this->info['PicUrl'];
		$info['MediaId'] = $this->info['MediaId'];

		//插件接口调用
		if($wp_plugins = $this->extends->dealwith('image', $info)){
			return $wp_plugins;
		}
	}

	//语音消息回复(腾讯普通开发者未开启),使用时,请注意
	public function voiceReply(){

		$info['MediaId'] = $this->info['MediaId'];
		$info['Format'] = $this->info['Format'];
		$info['Recognition'] = $this->info['Recognition'];
		
		//插件接口调用
		if($wp_plugins = $this->extends->dealwith('voice', $info)){
			return $wp_plugins;
		}
	}

	//视频消息回复
	public function videoReply(){
		$info['MediaId'] = $this->info['MediaId'];
		$info['ThumbMediaId'] = $this->info['ThumbMediaId'];

		//插件接口调用
		if($wp_plugins = $this->extends->dealwith('video', $info)){
			return $wp_plugins;
		}
	}


	//事件消息回复
	public function eventReply(){
		$type = $this->info['Event'];
		if($type == 'CLICK'){//自定义菜单事件
			include_once('weixin-robot/class-wx-event-user.php');
			$key = $this->info['EventKey'];
			if(!empty($key)){
				$weixin_robot_event_user = WxRobot_Cmd_Event_User::instance();
				$weixin_robot_event_user->setValue($this);
				return $weixin_robot_event_user->go($key);
			}
		}else{
			include_once('weixin-robot/class-wx-event.php');
			$weixin_robot_event = WxRobot_Cmd_Event::instance();
			$type = $type.'Event';
			return $weixin_robot_event->$type();
		}
	}

	//地理位置回复
	public function locationReply(){
	
		$info['Location_X'] = $this->info['Location_X'];
		$info['Location_Y'] = $this->info['Location_Y'];
		$info['Scale'] = $this->info['Scale'];
		$info['Label'] = $this->info['Label'];

		//插件接口调用
		if($wp_plugins = $this->extends->dealwith('location', $info)){
			return $wp_plugins;
		}
	}

	//分享链接信息
	public function linkReply(){

		$info['Title'] = $this->info['Title'];
		$info['Description'] = $this->info['Description'];
		$info['Url'] = $this->info['Url'];

		//插件接口调用
		if($wp_plugins = $this->extends->dealwith('link', $info)){
			return $wp_plugins;
		}
	}

	/**
	 * 返回帮助信息
	 *
	 * @param string $info 帮助信息
	 * @return xml
	 */
	public function help_info($info = ''){
		if($this->options['weixin_robot_helper_is'] != 'true'){
			$text = $this->options['weixin_robot_helper'];
			if(!empty($info)){
				return $this->toMsgText($info."\n".$text);//文本
			}else{
				return $this->toMsgText($text);//文本
			}
		}
	}
	
}
?>
