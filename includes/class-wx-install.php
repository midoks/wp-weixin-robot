<?php
/**
 * WxRobot Install
 * 
 * WP微信机器人安装准备
 *
 * @author 		midoks
 * @category 	Install
 * @package		WxRobot/Install
 * @since		5.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (! class_exists('WxRobot_Install')) :

/**
 * WxRobot_Install 安装类
 */
class WxRobot_Install{

	/**
	 * 表前缀
	 */
	public static $table_prefix = 'midoks_';


	/**
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct(){
		$this->create_options();
		$this->create_tables();
	}

	/**
	 * 初始化帮助信息
	 *
	 * @return string
	 */
	public function weixin_init_help(){
		$text = "提供的方式:\n?(提供帮助)\nn5(最新文章五篇)\nh5(热门文章五篇)\nr5(随机文章五篇)";
		return $text;
	}

	/**
	 * 保存基本配置信息
	 *
	 * @return void
	 */
	public function create_options(){

		//订阅时,给用户的提示信息
		$weixin_robot_options['subscribe'] = '欢迎订阅,回复?提供帮助信息';
		
		//文章最优处理
		$weixin_robot_options['opt_pic_show'] = 'false';
		$weixin_robot_options['opt_big_show'] = '';
		$weixin_robot_options['opt_small_show'] = '';

		//测试模式
		$weixin_robot_options['weixin_robot_debug'] = 'true';
		//是否开启数据库记录,默认开启
		$weixin_robot_options['weixin_robot_record'] = 'true';
		//定义帮助的信息
		$weixin_robot_options['weixin_robot_helper'] = $this->weixin_init_help();
		//定义是否无此命令,回复帮助信息
		$weixin_robot_options['weixin_robot_helper_is'] = 'false';
		//推送今日文章
		$weixin_robot_options['weixin_robot_push_today'] = '';

		//服务号配置
		$weixin_robot_options['ai'] = '';
		$weixin_robot_options['as'] = '';

		//TOKEN相关(URL验证)
		$weixin_robot_options['token'] = 'midoks';
		$weixin_robot_options['token_url'] = 'midoks';
		
		//token
		$weixin_robot_options['weixin_robot_token'] = '';	

		add_option(WEIXIN_ROBOT_OPTIONS, $weixin_robot_options);
	}

	/**
	 * 创建表
	 *
	 * @return void
	 */
	public function create_tables(){
		global $wpdb;
		$wpdb->hide_errors();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$wpdb->query(self::get_schema_weixin_robot());
		$wpdb->query(self::get_schema_weixin_robot_reply());
		$wpdb->query(self::get_schema_weixin_robot_menu());
		$wpdb->query(self::get_schema_weixin_robot_extends());
		

		//此方法未生效
		//dbDelta();
	}

	/**
	 * 获取表属性
	 */
	public static function get_schema_attr(){
		global $wpdb;
		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$collate .= " DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}
		return $collate;
	}

	/**
	 * 获取创建微信通信的SQL
	 *
	 * @return sql
	 */
	public static function get_schema_weixin_robot(){
		
		$prefix = self::$table_prefix;
		$collate = self::get_schema_attr();
		
		return "
create table if not exists {$prefix}weixin_robot (
	`id` bigint(20) not null auto_increment,
	`from` varchar(64) not null,
	`to` varchar(32) not null,
	`msgid` char(64) not null,
	`msgtype` varchar(10) not null,
	`createtime` varchar(13) not null,
	`content` varchar(100) not null, 
	`picurl` varchar(100) not null,
	`location_x` double(10,6) not null,
	`location_y` double(10,6) not null,
	`scale` double(10,6) not null,
	`label` varchar(255) not null,
	`title` text not null,
	`description` longtext not null,
	`url` varchar(255) not null,
	`event` varchar(255) not null,
	`eventkey` varchar(255) not null,
	`format` varchar(255) not null,
	`recognition` varchar(255) not null,
	`mediaid` varchar(255) not null,
	`thumbmediaid` varchar(255) not null,
	`response` varchar(255) not null,
	`response_time` double(10,6) not null,
	primary key(`id`)
) $collate;
	";
	}

	/**
	 * 获取创建微信自定义回复的SQL
	 *
	 * @return sql
	 */
	public static function get_schema_weixin_robot_reply(){
		
		$prefix = self::$table_prefix;
		$collate = self::get_schema_attr();
		
		return "
create table if not exists {$prefix}weixin_robot_reply (
	`id` bigint(20) not null auto_increment,
	`keyword` varchar(255) not null,
	`relpy` text not null,
	`status` char(64) not null,
	`time` datetime not null,
	`type` varchar(100) not null,
	`sort` int(10) not null default 0,
	primary key(`id`)
) $collate;
	";
	}

	/**
	 * 获取创建微信菜单设置的SQL
	 *
	 * @return sql
	 */
	public static function get_schema_weixin_robot_menu(){
		
		$prefix = self::$table_prefix;
		$collate = self::get_schema_attr();
		
		return "
create table if not exists {$prefix}weixin_robot_menu (
	`id` bigint(20) not null auto_increment,
	`menu_name` varchar(255) not null,
	`menu_type` varchar(100) not null,
	`menu_key` text not null,
	`menu_callback` varchar(180) not null,
	`menu_sort` int(10) not null default 0,
	`pid` int(10) not null,
	primary key(`id`)
) $collate;
	";
	}

	/**
	 * 获取创建扩展的SQL
	 *
	 * @return sql
	 */
	public static function get_schema_weixin_robot_extends(){
		
		$prefix = self::$table_prefix;
		$collate = self::get_schema_attr();
		
		return "
create table if not exists {$prefix}weixin_robot_extends (
	`id` bigint(20) not null auto_increment,
	`ext_name` varchar(180) not null,
	`ext_type` varchar(100) not null,
	`ext_sort` int(10) default 0,
	`ext_int` int not null,
	primary key(`id`),
	UNIQUE KEY `ext_name` (`ext_name`)
) $collate;
	";
	}

}

endif;

return new WxRobot_Install();
?>
