<?php
/**
 * WxRobot Admin
 * 
 * WP微信机器人后台菜单 - 统计页
 *
 * @author 		midoks
 * @category 	Admin
 * @package		WxRobot/Admin
 * @since		5.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *	WxRobot_Admin_Menu_Statistics 通信记录统计类
 */
class WxRobot_Admin_Menu_Statistics{

	/**
	 * 初始化钩子和过滤
	 *
	 * return void
	 */
	public function init(){
		add_action('init', array($this, 'statistics_ajax'), 10);
		add_action('admin_head', array(&$this, 'weixin_robot_menu_js'), 10);
	}

	/**
	 * 菜单初始化
	 *
	 * @return void
	 */
	public function menu(){
		add_submenu_page('weixin-robot',
			'weixin-robot',	
			'微信机器人统计',
			'manage_options',
			'weixin-robot-statistics',
			array($this, 'weixin_robot_statistics'));
	}

	/**
	 * 传递ajax请求
	 * 
	 * @return echo json
	 */
	public function statistics_ajax(){
		if(!empty($_POST['page']) && $_POST['page'] == 'weixin-robot-statistics'){
			echo($this->weixin_robot_count_ajax());
		}
	}

	/**
	 * 通过ajax获取各类型总数
	 *
	 * @return json
	 */
	public function weixin_robot_count_ajax(){
		$text 		= WxRobot_Table_Records::instance()->weixin_get_msgtype_count('text');
		$voice 		= WxRobot_Table_Records::instance()->weixin_get_msgtype_count('voice');
		$video 		= WxRobot_Table_Records::instance()->weixin_get_msgtype_count('video');
		$link 		= WxRobot_Table_Records::instance()->weixin_get_msgtype_count('link');
		$event 		= WxRobot_Table_Records::instance()->weixin_get_msgtype_count('event');
		$image 		= WxRobot_Table_Records::instance()->weixin_get_msgtype_count('image');
		$location 	= WxRobot_Table_Records::instance()->weixin_get_msgtype_count('location');

		$list['text'] 		= $text;
		$list['voice'] 		= $voice;
		$list['video'] 		= $video;
		$list['link'] 		= $link;
		$list['event'] 		= $event;
		$list['image'] 		= $image;
		$list['location'] 	= $location;
		return json_encode($list);
	}

	/**
	 * 加载需要的js
	 *
	 * @return void
	 */
	public function weixin_robot_menu_js(){
		$url = WEIXIN_ROBOT_URL;
		if(!empty($_GET['page']) && 'weixin-robot-statistics' == $_GET['page']){
			echo '<script type="text/javascript" src="'.$url.'/assets/js/ichart.min.js"></script>';
			echo '<script type="text/javascript" src="'.$url.'/assets/js/ichart.count.js"></script>';
		}
	
	}

	/**
	 * 微信机器人统计页
	 */
	public function weixin_robot_statistics(){
		echo '<div class="wrap"><div class="metabox-holder">',
			'<div class="postbox"><h3>微信通信记录统计分析</h3><div id="canvasDiv1"></div></div></div></div>';
		do_action('weixin_midoks_push');
	}	
}

?>
