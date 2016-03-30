<?php
/**
 *	WxRobot - 扩展类
 *
 *	@author		midoks
 *	@category	WxRobot
 *	@package	WxRobot/Exends
 *	@since 		5.3.0
 */


class WxRobot_Extends{

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
	
		$this->options = get_option(WEIXIN_ROBOT_OPTIONS);
		$this->obj	   = WxRobot_SDK::instance();
	}

	/**
	 * WxRobot 实例化
	 * 
	 * @return WxRobot_Extends instance
	 */
	public static function instance(){
		if( is_null (self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * 处理分离的功能
	 *
	 * @param string $func 功能名
	 * @param string $args 其他参数
	 * @return bool
	 */
	public function dealwith($func, $args){
		$res = '';
		switch($func){
			case 'all'		:	$res = $this->p_all($args);break;
			case 'subscribe':	$res = $this->p_subscribe('');break;
			case 'scan'		:	$res = $this->p_scan($args);break;
			case 'text'		:	$res = $this->p_text($args);break;
			case 'image'	:	$res = $this->p_image($args);break;
			case 'voice'	:	$res = $this->p_voice($args);break;
			case 'video'	:	$res = $this->p_video($args);break;
			case 'location'	:	$res = $this->p_location($args);break;
			case 'link'		: 	$res = $this->p_link($args);break;
			case 'menu'		:	$res = $this->p_menu($args);break;
			default			:	$res = $this->p_text('');break;
		}
		if(empty($res)){
			return false;
		}
		return $res;
	}


	/**
	 * 所有信息处理
	 *
	 * @param array $args 所有数据
	 * @return mixed
	 */ 
	private function p_all($args){
		if(empty($args)){return false;}
		if($data = $this->extends_start('all', $args)){
			return $data;
		}
		return false;
	}

	/**
	 * 订阅事件处理
	 *
	 * @return mixed
	 */
	private function p_subscribe($args){
		if($data = $this->extends_start('subscribe', $args)){
			return $data;
		}
		return false;
	}

	/**
	 * 扫描处理
	 *
	 * @return mixed
	 */
	private function p_scan($args){
		if($data = $this->extends_start('scan', $args)){
			return $data;
		}
		return false;
	}

	/**
	 *	文本关键回复
	 *	
	 *	@param string $kw 关键字
	 *	@return mixed
	 */
	private function p_text($kw){
		if(empty($kw)){return false;}
		if($data = $this->extends_start('text', $kw)){
			return $data;
		}
		return false;
	}

	/**
	 *	图片事件处理
	 *
	 *	@param array $info 图片消息
	 *	@return mixed
	 */
	private function p_image($info){
		if(empty($info)){return false;}
		if($data = $this->extends_start('image', $info)){
			return $data;
		}
		return false;
	}

	/**
	 *	声音信息处理
	 *
	 *	@param array $info 图片消息
	 *	@return mixed
	 */
	private function p_voice($info){
		if(empty($info)){return false;}
		if($data = $this->extends_start('voice', $info)){
			return $data;
		}
		return false;
	}

	/**
	 *	视频信息处理
	 *
	 *	@param array $info 图片消息
	 *	@return mixed
	 */
	private function p_video($info){
		if(empty($info)){return false;}
		if($data = $this->extends_start('video', $info)){
			return $data;
		}
		return false;
	}

	/**
	 *	地理信息处理
	 *
	 *	@param array $info 图片消息
	 *	@return mixed
	 */
	private function p_location($info){
		if(empty($info)){return false;}
		if($data = $this->extends_start('location', $info)){
			return $data;
		}
		return false;
	}

	/**
	 *	分享链接信息处理
	 *
	 *	@param array 图片消息
	 *	@return mixed
	 */
	private function p_link(){
		if(empty($info)){return false;}
		if($data = $this->extends_start('link', $info)){
			return $data;
		}
		return false;
	}

	/**
	 * 分离出菜单控制(本插件功能并不能做到100%,提供次接口,让你自己控制)
	 *
	 * @param menu_name 菜单名字
	 * @return mixed
	 */
	private function p_menu($menu_name){
		if(empty($menu_name)){return false;}
		if($data = $this->extends_start('menu', $menu_name)){
			return $data;
		}
		return false;
	}

	/**
	 * 插件启用
	 *
	 * @param string $name 插件名
	 * @param array  $args 参数
	 * @return mixed
	 */
	private function extends_start($name, $args){
		$flist = WxRobot_Table_Extends::instance()->select_extends_type($name);
		if(!$flist) return false;
		foreach($flist as $k=>$v){
			if($name == $v['ext_type']){
				$abspath = WEIXIN_PLUGINS.$v['ext_name'];
				if(!file_exists($abspath)){
					WxRobot_Table_Extends::instance()->delete_extends_name($v['ext_name']);
				}else{
					include_once($abspath);
					$tt = explode('.', $v['ext_name']);
					$cn = $tt[0];
					$obj = new $cn($this);
					if(method_exists($obj, 'start')){
						$data = $obj->start($args);
						if( $data )	return $data;
					}
				}	
			}
		}
		return false;
	}	


	/**
	 * 获取扩展文件名
	 *
	 * @parma string $f 扩展绝对地址
	 * @return bool
	 */
	private function _c($f){
		if(!file_exists($f)){
			$fn = basename($f);
			WxRobot_Table_Extends::instance()->delete_extends_name($fn);
			return false;
		}else{
			include_once($f);
			return true;
		}
	}

	/**
	 * 后台功能控制
	 * 
	 * @param string $fn 插件名
	 * @return void
	 */
	public function admin($fn){
		$abspath = WEIXIN_PLUGINS.$fn.'.php';
		if($this->_c($abspath)){
			$obj = new $fn($this);
			if(method_exists($obj, 'admin')){	
				$obj->admin();
			}
		}
	}

	/**
	 * 前端页面调用
	 *
	 * @param string $fn 插件名
	 * @return void
	 */
	public function frontend($fn){
		$abspath = WEIXIN_PLUGINS.$fn.'.php';
		if($this->_c($abspath)){
			$obj = new $fn($this);
			if(method_exists($obj, 'frontend')){
				$obj->frontend();
			}
			
		}
	}


	/**
	 * 在WP微信机器人后台控制(添加子菜单)
	 *
	 * @param object $object 对象
	 * @param string $method 方法
	 * @param string $titleName 标题名
	 * @param string $linkName URL连接名字
	 * @return void
	 */
	public function admin_menu($object, $method, $titleName, $linkName){
		add_submenu_page('weixin_robot',
			'weixin_robot',	
			$titleName,
			'manage_options',
			$linkName,
			array($object, $method));
	}


	/**
	 * 微信SDK功能的使用
	 *
	 * @param string $method 方法
	 * @param array $args 参数
	 * @retutn void
	 */
	public function __call($method, $args){
		if(!empty($args)){
			return call_user_func_array(array($this->obj, $method), $args);
		}else{
			return call_user_func(array($this->obj, $method));
		}
	}

	/**
	 *	获取接受的所有信息
	 *
	 *	@return array 返回所有的信息
	 */
	public function getAcceptInfo(){
		return $this->info;
	}

	/**
	 * 	获取本插件的所有配置信息
	 *
	 *	@return array 返回数组
	 */
	public function getConfigInfo(){
		return $this->options;
	}

	/**
	 * 用户OpenID
	 *
	 * @return string
	 */
	public function getUserOpenID(){
		return $this->info['FromUserName'];
	}

	/**
	 * 获取开发AppID
	 *
	 * @return string
	 */
	public function getAppID(){
		return $this->options['ai'];
	}

	/**
	 * 获取开发AppSelect
	 *
	 * @return string
	 */
	public function getAppSelect(){
		return $this->options['as'];
	}
}
?>
