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
 * 消息提示
 * 
 * @param string $msg 消息内容
 * @param string $type 消息类型,默认更新
 * @return void
 */
function wx_notice_msg($msg, $type='updated'){
	if(!empty($msg)){
		?><div class="<?php echo($type);?>"><p><?php echo($msg); ?></p></div><?php
	}
}

/**
 * 日志测试
 *
 * @param string $text 日志内容
 * @return bool
 */
function wx_admin_log($text = 'test'){
	$file = WEIXIN_ROBOT.'log-'.date('Y-m-d').'.log';
	return file_put_contents($file, $text);
}
?>
