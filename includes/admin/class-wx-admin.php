<?php
/**
 * WxRobot Admin
 * 
 * WP微信机器人后台
 *
 * @author 		midoks
 * @category 	Admin
 * @package		WxRobot/Admin
 * @since		5.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('WxRobot_Admin') ):

/**
 * WxRobot_Admin 后台控制类
 */
class WxRobot_Admin{

	/**
	 * WxRobot Admin Instance
	 */
	public static $_instance = null;
	

	/**
	 * WxRobot 后台类实例化
	 * 
	 * @return WxRobot Admin instance
	 */
	public static function instance(){
		if( is_null (self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct(){

		include_once('class-wx-menu-functions.php');

		add_filter( 'plugin_action_links', array($this, 'weixin_robot_action_links'), 10, 2);
		add_filter( 'plugin_row_meta', array($this, 'weixin_robot_row_meta'), 10, 2 );
		add_action( "current_screen", array( $this, 'add_tabs' ), 50 );

		add_action( 'admin_menu', array($this, 'weixin_robot_menu'), 1);	
		add_action( 'admin_footer', array(&$this, 'weixin_robot_footer'));

		add_filter( 'manage_posts_columns', array($this, 'posts_columns_id'), 1);
    	add_action( 'manage_posts_custom_column', array($this, 'posts_custom_id_columns'), 1, 2);
    	add_filter( 'manage_pages_columns', array($this, 'posts_columns_id'), 1);
		add_action( 'manage_pages_custom_column', array($this,'posts_custom_id_columns'), 1, 2);

		//add_action( 'publish_post', array($this, 'pull_new_pubish_post'));
		//add_action( 'pre_post_update', array($this, 'pull_new_pubish_post'));
		add_action( 'weixin_midoks_push', array($this, 'weixin_midoks_push'));

		$this->weixin_menu_cache('init');
		
		add_action( 'admin_menu', array($this, 'wx_extends_admin'), 3);

	}

	/**
	 * 前台调用 - 调用插件的前台功能
	 *
	 * @return void
	 */
	public function wx_extends_admin(){
		$list = WxRobot_Table_Extends::instance()->select_extends();
		if(!empty($list)){
			foreach($list as $k=>$v){
				WxRobot_Extends::instance()->admin($v['ext_cn']);
			}
		}
	}

	/**
  	 * 插件右侧过滤功能
	 */
	public function weixin_robot_row_meta($input, $file){
		$file_arr = explode('/', $file);
		if('wp-weixin-robot' == $file_arr[0]){
			array_push($input, '<a href="'.WEIXIN_ROBOT_DOCUMENT.'" target="_blank">API文档</a>');
			array_push($input, '<a href="https://wordpress.org/plugins/wp-weixin-robot/developers/" target="_blank">代码版本</a>');
		}
		return $input;
	}

	/**
	 * 过滤设置功能插件功能显示
	 */
	public function weixin_robot_action_links($links, $file){
		if ( basename($file) != basename(plugin_basename(WEIXIN_ROBOT_POS))){
			return $links;
		}
    	$settings_link = '<a href="admin.php?page=weixin-robot-setting">设置</a>';
    	array_unshift($links, $settings_link);
    	return $links;
	}


	/**
	 * 添加帮助信息
	 */
	public function add_tabs(){
		$screen = get_current_screen();
		$screen->add_help_tab( array(
			'id'        => 'wxrobot_support_tab',
			'title'     => 'API支持',
			'content'   =>
				'<h2>感谢</h2>' .
				'<p>非常感谢你使用WP微信机器人,这次更新,主要是进行了代码处理。让代码更加易读</p>' .
				'<p>文档地址:<a href="'.WEIXIN_ROBOT_DOCUMENT.'" target="_blank">'.WEIXIN_ROBOT_DOCUMENT.'</a></p>'
		) );
	}

	/**
	 * 类缓存
	 * 
	 * @param 	string $file 		加载类文件
	 * @param 	string $className 类名
	 * @param 	string $method 	调用的方法
	 * @return 	void
	 */
	private function weixin_class_cache($file, $className, $method = ''){
		static $_instance = array();
		$md5_name = md5($file);
		if(!isset($_instance[$md5_name])){
			include_once($file);
			$_instance[$md5_name] = new $className();
		}
		
		if( !empty($method) && method_exists($_instance[$md5_name], $method)){
			$_instance[$md5_name]->$method();
		}
	}

	/**
	 * 后台菜单缓存
	 *
	 * @param	string $method	调用方法
	 * @void
	 */
	private function weixin_menu_cache($method = ''){
	
		$options = get_option(WEIXIN_ROBOT_OPTIONS);

		$this->weixin_class_cache('class-wx-menu-instro.php', 'WxRobot_Admin_Menu_Instro', $method);
		$this->weixin_class_cache('class-wx-menu-setting.php', 'WxRobot_Admin_Menu_Setting', $method);
		$this->weixin_class_cache('class-wx-menu-records.php', 'WxRobot_Admin_Menu_Records', $method);
		$this->weixin_class_cache('class-wx-menu-reply.php', 'WxRobot_Admin_Menu_Reply', $method);
		$this->weixin_class_cache('class-wx-menu-statistics.php', 'WxRobot_Admin_Menu_Statistics', $method);

		if(!empty($options['ai']) && !empty($options['as'])){
			$this->weixin_class_cache('class-wx-menu-menu.php', 'WxRobot_Admin_Menu_Menu', $method);
		}
		$this->weixin_class_cache('class-wx-menu-extends.php', 'WxRobot_Admin_Menu_Extends', $method);
	}

	/**
	 * 微信机器人后台菜单
	 */
	public function weixin_robot_menu(){
		$this->weixin_menu_cache('menu');
	}

	/**
	 * 显示文章ID选项
	 */
	public function posts_columns_id($defaults){
    	$defaults['wps_post_id'] = __('ID');
    		return $defaults;
	}

	/**
	 * 显示文章ID值
	 */
	public function posts_custom_id_columns($column_name, $id){
        if($column_name === 'wps_post_id'){
            echo $id;
    	}
	}

	/**
	 * 推送更新文章
	 */
	public function pull_update_pubish_post($id){
	}

	/**
	 *	推送最新文章
	 */
	public function pull_new_pubish_post($id){
		$t['time'] = time(); 
		$t['id'] = $id;
		$this->options['weixin_robot_push_today'] = json_encode($t);
		update_option('weixin_robot_options', $this->options);
	}

	/**
	 * 显示我推广信息
	 */
	public function weixin_midoks_push(){
		?>
		<hr />
		<p>请关注我的博客:<a href="http://midoks.cachecha.com/" target="_blank">midoks.cachecha.com</a></p>
		<p><img src="<?php echo WEIXIN_ROBOT_URL; ?>/assets/img/mini_alipay.png" title="支付宝扫描,即可为我捐助。" alt="支付宝扫描,即可为我捐助。"></p>
		<p>能为你服务,我感到无限的兴奋</p><?php
	}

	/**
	 * 加入统计信息
	 */
	public function weixin_robot_footer(){
		echo '<script language="javascript" type="text/javascript" src="http://js.users.51.la/16589822.js"></script>';
		$t = <<<EOT
var h51Time=window.setInterval(hidden51la,100);function hidden51la(){var t={a:'ajiang',a2:'51.la'};for(i=0;i<document.getElementsByTagName("a").length;i++){var temObj=document.getElementsByTagName("a")[i];if(temObj.href.indexOf(t.a)>=0){temObj.style.display="none"}if(temObj.href.indexOf(t.a2)>=0){temObj.style.display="none";clearInterval(h51Time)}}}
EOT;
		echo '<script> '.$t.' </script>';
	}
	
}

endif;

return WxRobot_Admin::instance();



?>
