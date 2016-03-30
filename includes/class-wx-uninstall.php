<?php
/**
 * WxRobot Uninstall
 * 
 * WP微信机器人卸载程序
 *
 * @author 		midoks
 * @category 	Uninstall
 * @package		WxRobot/Uninstall
 * @since		5.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (! class_exists('WxRobot_Uninstall')) :

/**
 * WxRobot_Uninstall 插件卸载类
 */
class WxRobot_Uninstall{

	/**
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct(){
		$this->delete_options();
		$this->delete_tables();
	}

	/**
	 * 删除配置内容
	 *
	 * @return void
	 */
	public function delete_options(){
		delete_option(WEIXIN_ROBOT_OPTIONS);
	}

	/**
	 * 删除表
	 *
	 * @return void
	 */
	public function delete_tables(){
		global $wpdb;

		$sqls[] = "DROP TABLE midoks_weixin_robot;";
		$sqls[] = "DROP TABLE midoks_weixin_robot_reply;";
		$sqls[] = "DROP TABLE midoks_weixin_robot_menu;";
		$sqls[] = "DROP TABLE midoks_weixin_robot_extends;";	
		
		foreach($sqls as $sql){
			$wpdb->query($sql);
		}
	}
}

endif;

return new WxRobot_Uninstall();
?>
