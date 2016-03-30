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
 * WxRobot_Cmd_Event_User 自定义事件响应类
 */
class WxRobot_Cmd_Event_User{	
	
	/**
	 * WxRobot_Cmd_Text Instance
	 */
	public static $_instance = null;

	public $callback = array('today','n', 'h', 'r', '?');

	/**
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct(){
		$this->extends = WxRobot_Extends::instance();
		$this->wxSdk   = WxRobot_SDK::instance();
	}

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
	 * 设置对象
	 *	
	 * @param object $obj 模板引用对象
	 * @return xml
	 */
	public function setValue($obj){
		$this->obj = $obj;
	}

	/**
	 * 事件响应
	 *
	 * @param string $key 键值
	 * @return mixed
	 */
	public function go($key){
		$data = WxRobot_Table_Menu::instance()->weixin_get_menu_data();

		if($data){
			foreach($data as $k=>$v){
				if($key == $v['menu_key']){
					return $this->choose($v['menu_callback'], $v['menu_name']);
				}
			}
		}

		return  $this->obj->help_info('key:'.$key."\n".'用户自定菜单响应未定义?');
	}	

	/**
	 * 事件选择
	 * 
	 * @param string $case 时间的回调值
	 * @param string $name 菜单名
	 * @return xml
	 */
	public function choose($case, $name){
		if($wp_plugins = $this->extends->dealwith('menu', $name)){
			return $wp_plugins;
		}

		include_once('class-wx-cmd-text.php');
		if(in_array($case, $this->callback) || in_array(substr($case, 0, 1), $this->callback)){//预定义
			$text = WxRobot_Cmd_Text::instance();
			$text->setValue($this->obj, $case);
			$case = $text->replay();
			return $case;
		}else{
			$reply =  $this->wxSdk->toMsgText($case);
			return $reply;
		}
	}
}
?>
