<?php
/**
 *	WxRobot - 事件处理类
 *
 *	@author		midoks
 *	@category	WxRobot
 *	@package	WxRobot/Cmd
 *	@since 		5.3.0
 */

/**
 *	WxRobot_Cmd_Event 事件命令处理类
 */
class WxRobot_Cmd_Event{

	/**
	 * WxRobot_Cmd_Text Instance
	 */
	public static $_instance = null;

	/**
	 * WxRobot 文本消息处理类实例化
	 * 
	 * @return WxRobot_Cmd_Text instance
	 */
	public static function instance(){
		if( is_null (self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * 构造函数
	 */
	public function __construct(){
		$this->options = get_option(WEIXIN_ROBOT_OPTIONS);
		$this->wxSdk   = WxRobot_SDK::instance();
		$this->extends = WxRobot_Extends::instance();
		$this->info	   = wx_request_array();
	}

	/**
	 * 订阅事件
	 *
	 * @return xml|bool
	 */
	public function subscribeEvent(){
		if($wp_plugins = $this->extends->dealwith('subscribe', $this->info)){
			return $wp_plugins;
		}

		$s = $this->options['subscribe'];
		if(!empty($s)){
			return $this->wxSdk->toMsgText($s);
		}

		return false;
	}

	/**
	 * 取消订阅时间
	 * 
	 * @return mixed
	 */
	public function unsubscribeEvent(){
		return $this->wxSdk->toMsgText('谢谢你的使用!!!');
	}

	/**
	 * 上报地址事件(服务号开启后,会每隔5分钟回复一次)
	 * 
	 * @return mixed
	 */
	public function LOCATIONEvent(){
		//基本数据
		$info['Latitude'] = $this->info['Latitude'];
		$info['Longitude'] = $this->info['Longitude'];
		$info['Precision'] = $this->info['Precision'];

		if($wp_plugins = $this->extends->dealwith('location', $info)){
			return $wp_plugins;
		}
	}

	/**
	 * 用户已关注时的事件推送
	 *
	 * @return xml
	 */
	public function scanEvent(){
		if($wp_plugins = $this->extends->dealwith('scan', $this->$info)){
			return $wp_plugins;
		}
	}

	/**
	 * 模版消息返回信息(暂时不处理)
	 *
	 * @return void
	 */
	public function TEMPLATESENDJOBFINISHEvent(){}

	/**
	 * 事件推送群发结果(暂时不处理)
	 *
	 * @return void
	 */
	public function MASSSENDJOBFINISHEvent(){}
}
?>
