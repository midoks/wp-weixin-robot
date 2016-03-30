<?php
/*
 * Plugin Name: WP微信机器人
 * Plugin URI: http://midoks.cachecha.com/
 * Description: 微信连接WordPress，使你传播信息更快。
 * Version: 5.3.4
 * Author: Midoks
 * Author URI: http://midoks.cachecha.com/
 */

if( ! defined('ABSPATH')){
	exit; //不能直接进入
}

if( ! class_exists('WxRobot') ) :

/**
 * Main WxRobot Class
 * 
 * @class WxRobot
 * @version 5.3.0
 */

final class WxRobot{
	
	/**
	 * 版本信息
	 */	
	public $version = '5.3.4';

	/**
	 * WxRobot单例类
	 */
	protected static $_instance = null;


	/**
	 * WxRobot 实例化
	 *
	 * 保证只有一个WxRobot类实例化
	 * 
	 * @return WxRobot - Main instance
	 */
	public static function instance(){
		if( is_null (self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * WxRobot 构造器
	 */
	public function __construct(){
		$this->define_constants();
		$this->init_hooks();
		$this->includes();

		do_action('weixin_robot_loaded');
	}


	/**
	 * 定义WxRobot需要的常量
	 */
	public function define_constants(){
		$this->define('WEIXIN_ROBOT', str_replace('\\', '/', dirname(__FILE__)).'/');
		$this->define('WEIXIN_PLUGINS', WEIXIN_ROBOT.'extends/');
		$this->define('WEIXIN_ROBOT_URL', plugins_url('', __FILE__));
		$this->define('WEIXIN_ROBOT_POS' , __FILE__);
		$this->define('WEIXIN_ROBOT_DOCUMENT', 'http://midoks.duapp.com/api-docs/index.html');
		$this->define('WEIXIN_ROBOT_OPTIONS', 'weixin_robot_options');
	}

	/**
	 * 初始化钩子和过滤
	 */
	public function init_hooks(){

		register_activation_hook(WEIXIN_ROBOT_POS, array($this, 'weixin_robot_install'));
		register_deactivation_hook(WEIXIN_ROBOT_POS, array($this, 'weixin_robot_uninstall'));

		add_action('init', array($this, 'init'), 0);
		add_action('init', array($this, 'weixin_robot_start'), 1);
	}

	/**
	 * 插件安装
	 */
	public function weixin_robot_install(){
		include_once('includes/class-wx-install.php');
	}

	/**
	 * 插件卸载
	 */
	public function weixin_robot_uninstall(){
		include_once('includes/class-wx-uninstall.php');
	}

	/**
	 * 微信机器人入口
	 */
	public function weixin_robot_start(){

		include_once(WEIXIN_ROBOT.'includes/class-wx-table-extends.php');
		include_once(WEIXIN_ROBOT.'includes/class-wx-table-menu.php');
		include_once(WEIXIN_ROBOT.'includes/class-wx-table-records.php');
		include_once(WEIXIN_ROBOT.'includes/class-wx-table-reply.php');	
		include_once(WEIXIN_ROBOT.'includes/class-wx-extends.php');
		include_once(WEIXIN_ROBOT.'includes/class-wx-functions.php');
		include_once(WEIXIN_ROBOT.'includes/weixin-sdk-api/class-wx-sdk.php');

		$options = get_option(WEIXIN_ROBOT_OPTIONS);

		if(!empty($options['token_url'])){
			$token_url = $options['token_url'];
			if(!empty($options['token'])){
				$this->define('WEIXIN_TOKEN', $options['token']);
			}

			include_once(WEIXIN_ROBOT.'includes/class-wx-robot.php');
			$wxrobot = WxRobot_Robot::instance();

			if ( isset( $_GET[$token_url] ) ){
				$wxrobot->valid();exit;
			}

			if ( $this->is_request( 'frontend' ) ) {
				$this->frontend();
			}
		}
	}


	/**
	 * 前台调用 - 调用插件的前台功能
	 *
	 * @return void
	 */
	public function frontend(){
		$list = WxRobot_Table_Extends::instance()->select_extends();
		if(!empty($list)){
			foreach($list as $k=>$v){
				WxRobot_Extends::instance()->frontend($v['ext_cn']);
			}
		}
	}

	/**
	 * 根据不同的地点,加载需要的文件
	 */
	public function includes(){
		
		if ( $this->is_request( 'admin' ) ) {
			include_once(WEIXIN_ROBOT.'includes/admin/class-wx-admin.php');
		}
	}

	/**
	 * 如果尚未设置,则定义常量
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * 请求的类型
	 * string $type ajax, frontend or admin
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * 获取插件URL.
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * 获取插件地址.
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * 当WordPress初始化,WxRobot也初始化
	 */
	public function init(){
		do_action('before_weixin_robot');		
		do_action('weixin_robot_loaded');
	}
}

endif;

function WX(){
	return WxRobot::instance();
}

/**
 * 启动微信机器人
 */
WX();

/**
 * 提前响应
 */
if(isset($_GET['echostr'])){
	WX()->weixin_robot_start();
}

?>
