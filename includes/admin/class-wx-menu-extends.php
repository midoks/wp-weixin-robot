<?php
/**
 * WxRobot Admin
 * 
 * WP微信机器人后台菜单 - 扩展功能
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
 *	WxRobot_Admin_Menu_Extends 后台扩展功能调用
 */
class WxRobot_Admin_Menu_Extends{
	/**
	 * 初始化钩子和过滤器
	 *
	 * @return void
	 */
	public function init(){	
	}

	/**
	 * 初始化菜单
	 *
	 * @return void
	 */
	public function menu(){
		add_submenu_page('weixin-robot',
			'weixin-robot',	
			'微信机器人扩展',
			'manage_options',
			'weixin-robot-extends',
			array($this, 'weixin_robot_extends'));
	}

	/**
	 * 扩展提交处理
	 *
	 * @return void
	 */
	public function extends_post(){

		if(isset($_GET['file']) && isset($_GET['type'])){
			$ext_file = trim($_GET['file']);
			$ext_type = trim($_GET['type']);
			if('del'==$ext_type){
				WxRobot_Table_Extends::instance()->uninstall($ext_file);		
				WxRobot_Table_Extends::instance()->delete_extends_name($ext_file);
				wx_notice_msg('卸载成功!!!');
			}else if(in_array($ext_type, array('all', 'subscribe', 'text', 'location', 'image', 'link', 'video','voice', 'menu'))){

				if(!WxRobot_Table_Extends::instance()->select_extends_name($ext_file)){
					WxRobot_Table_Extends::instance()->install($ext_file);
					WxRobot_Table_Extends::instance()->insert_extends($ext_file , $ext_type, '1');
					wx_notice_msg('安装成功!!!');
				}else{
					wx_notice_msg('已经安装成功!!!');
				}
			}
		}
	}

	/**
	 *	微信扩展功能页
	 *
	 *	@return void
	 */
	public function weixin_robot_extends(){
		
		$this->extends_post();

		$list = WxRobot_Table_Extends::instance()->get_all_plugins();
		echo '<div class="wrap"><h2>微信机器人扩展</h2>';

		$url = $_SERVER['REQUEST_URI'];
		$r_url = str_replace(strstr($url, '&'), '', $url);
		$thisPageUrl = 'http://'.$_SERVER['HTTP_HOST'].$r_url;

		echo '<table class="wp-list-table widefat plugins" cellspacing="0">';

		echo '<tr>';
		echo '<th scope="col" id="name" class="manage-column column-name" style="">插件</th>';
		echo '<th scope="col" id="description" class="manage-column column-description" style="">图像描述</th>';
		echo '</tr>';
		
		if(isset($list['abspath']) && !empty($list['abspath'])){
			foreach($list['abspath'] as $k=>$v){
				$pinfo = $list['info'][$k];
			
				if(WxRobot_Table_Extends::instance()->select_extends_name($list['path'][$k])){
					echo "<tr><td class=\"plugin-title\"><strong>{$pinfo['extend_name']}</strong>",
						'<div class="row-actions-visible"><span class="0"><a href="',$thisPageUrl.'&file='.$list['path'][$k].'&type=del',
						'">已启用</a></span></div></td>';
				}else{
					echo "<tr><td class=\"plugin-title\"><strong>{$pinfo['extend_name']}</strong>",
						'<div class="row-actions-visible"><span class="0"><a href="',$thisPageUrl.'&file='.$list['path'][$k].'&type='.$list['type'][$k],
						'">未启用</a></span></div></td>';
				}

				echo '<td class="column-description desc"><div class="plugin-description"><p>',
					$pinfo['description'],'</p></div><div class="active second plugin-version-author-uri">',
					$pinfo['version'],'版本 | 作者为 ',
					$pinfo['author'],' | ','事件类型:', $list['type'][$k],' | ','<a href="#" title="',
					$list['path'][$k],'" style="color:#000;">插件地址</a> | ',
					'联系邮箱:',$pinfo['email'],' | ',
					'<a href="',$pinfo['extend_url'],'" title="访问插件主页" target="_blank">访问插件主页</a></div></td>';
				echo '</tr>';
			}
		}
		echo '</table></div>';
		do_action('weixin_midoks_push');
	}
	
}

?>
