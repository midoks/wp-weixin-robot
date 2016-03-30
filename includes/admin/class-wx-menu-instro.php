<?php
/**
 * WxRobot Admin
 * 
 * WP微信机器人后台菜单 - 插件功能介绍
 *
 * @author 		midoks
 * @category 	Admin
 * @package		WxRobot/Admin
 * @since		5.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('WxRobot_Admin_Menu_Instro') ):

/**
 * WxRobot_Admin_Menu_Instro 插件功能介绍
 */
class WxRobot_Admin_Menu_Instro{

	/**
	 * 菜单初始化
	 * 
	 * @return void
	 */
	public function menu(){
		add_menu_page('微信机器人',
			_('微信机器人'),
			'manage_options',
			'weixin-robot',
			array(&$this, 'weixin_robot_instro'),
			WEIXIN_ROBOT_URL.'/weixin_robot.png');
	}

	/**
	 * WP微信机器人介绍
	 *
	 * @return void
	 */
	public function weixin_robot_instro(){
		echo file_get_contents(WEIXIN_ROBOT.'assets/weixin_robot_instro.html');
		do_action('weixin_midoks_push');	
	}

	
}

endif;
?>
