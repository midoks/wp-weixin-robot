<?php
/**
 * WxRobot Admin
 * 
 * WP微信机器人后台菜单 - 配置页
 *
 * @author 		midoks
 * @category 	Admin
 * @package		WxRobot/Admin
 * @since		5.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('WxRobot_Admin_Menu_Setting') ):

/**
 * WxRobot_Admin_Menu_Setting 插件功能配置类
 */
class WxRobot_Admin_Menu_Setting{


	/**
	 * 菜单初始化
	 *
	 * @return void
	 */
	public function menu(){
		add_submenu_page('weixin-robot',
			'weixin-robot',	
			'微信机器人设置',
			'manage_options',
			'weixin-robot-setting',
			array($this, 'weixin_robot_setting'));
	}

	/**
	 * 显示配置选项
	 *
	 * @return void
	 */
	public function weixin_robot_setting(){

		$this->weixin_robot_setting_post();

		echo '<div class="wrap"><div class="narrow">';
		echo '<form  method="POST">';
		echo '<h2>微信机器人配置</h2>';
		echo '<table class="form-table">';

		$this->weixin_robot_setting_table();

		echo '<input type="hidden" name="weixin_robot_setting" value="true" />';
		echo '</table>';
		echo '<p class="submit"><input name="submit" type="submit" class="button-primary" value="保存设置" /></p>';
		echo '</form></div></div>';
		
		do_action('weixin_midoks_push');
	}

	/**
	 * 更新配置信息
	 *
	 * @return void
	 */
	public function weixin_robot_setting_post(){
		
		if( isset($_POST['submit']) && isset($_POST['weixin_robot_setting'])){

			$newp = $_POST['weixin_robot_options'];
			$this->options['ai'] 						= $newp['ai'];
			$this->options['as'] 						= $newp['as'];
			$this->options['subscribe'] 				= $newp['subscribe'];
			$this->options['token_url'] 				= empty($newp['token_url']) ? 'midoks' : $newp['token_url'];
			$this->options['token'] 					= empty($newp['token']) ? 'midoks' : $newp['token'];
			$this->options['opt_pic_show'] 				= empty($newp['opt_pic_show']) ? '' : $newp['opt_pic_show'];
			$this->options['opt_big_show'] 				= empty($newp['opt_big_show']) ? '' : $newp['opt_big_show'];
			$this->options['opt_small_show'] 			= $newp['opt_small_show'];
			$this->options['weixin_robot_debug'] 		= $newp['weixin_robot_debug'];
			$this->options['weixin_robot_record'] 		= $newp['weixin_robot_record'];
			$this->options['weixin_robot_helper'] 		= trim($newp['weixin_robot_helper']);
			$this->options['weixin_robot_helper_is'] 	= $newp['weixin_robot_helper_is'];
			$this->options['EncodingAESKey'] 			= $newp['EncodingAESKey'];
			update_option(WEIXIN_ROBOT_OPTIONS, $this->options);

			wx_notice_msg('配置更新成功!!!');
		}
	}

	/**
	 * 配置选项
	 *
	 * @return void
	 */
	public function weixin_robot_setting_table(){
		global $wp;
		$options = get_option(WEIXIN_ROBOT_OPTIONS);
		$current_url = home_url(add_query_arg(array(),$wp->request));

		//关注
		echo '<tr  valign="top"><th scope="row">订阅事件提示(subscribe)</th>';
		echo '<td><textarea name="weixin_robot_options[subscribe]" style="width:350px;height:50px;" class="regular-text code">'
			.$options['subscribe'].'</textarea><br />当用户关注时,发送的消息</td></tr>';

		//Token URL
		echo '<tr  valign="top"><th scope="row">URL(服务器地址)</th>';
		echo '<td><input id="weixin_token_url" url="'.$current_url.'" type="text" name="weixin_robot_options[token_url]" value="';
		if(!empty($options['token_url'])){ echo($options['token_url']); }
		echo '" size="35"></input><br /><span><b id="weixin_token_url_exp">'.$current_url.'/?'.$options['token_url'].'</b><br/>URL(服务器地址)</span></td></tr>';
		echo('<script>jQuery(function($){  $("#weixin_token_url").keyup(function(){
			var u = $(this).attr("url");
			var t = $(this).val();
			$("#weixin_token_url_exp").text(u+"/?"+t);
		});  });</script>');

		//Token
		echo '<tr  valign="top"><th scope="row">Token(令牌)</th>';
		echo '<td><input type="text" name="weixin_robot_options[token]" value="';
		if(!empty($options['token'])){ echo($options['token']); }
		echo '" size="35"></input><br />Token(令牌)</td></tr>';

		

		//图片最优显示
		echo '<tr  valign="top"><th scope="row">图片最优显示</th>';
		echo '<td><input type="checkbox" name="weixin_robot_options[opt_pic_show]"  value="true" ';
		if( $options['opt_pic_show'] == 'true' ){ echo ' checked="checked"'; }
		echo '/>
			<br/>是否开启最优图片获取.
			<br/>1.开启后会在文章中匹配第一个张图片(如果有多张图片).
			<br/>2.如果没有找到,返回你的下面默认大小图片地址
			<br/>3.如过默认大小也没有设置,会返会本插件自带图片
			<br/><span style="color:red">note:开启图片防盗链的话,还是不要开启为好.覆盖原来的图片就很好!</span></td></tr>';

		//大图地址
		echo '<tr valign="top"><th scope="row">大图显示地址</th>';
		echo '<td><textarea name="weixin_robot_options[opt_big_show]" style="width:350px;height:50px;" class="regular-text code">'
			.$options['opt_big_show'].'</textarea><br/>多个图片地址,回车换行来区分|官方建议大图为:360*200</td></tr>';

		//小图地址
		echo '<tr valign="top"><th scope="row">小图显示地址</th>';
		echo '<td><textarea name="weixin_robot_options[opt_small_show]" style="width:350px;height:50px;" class="regular-text code">'
			.$options['opt_small_show'].'</textarea><br/>多个图片地址,回车换行来区分|官方建议大图为:200*200</td></tr>';


		//数据开启数据记录
		echo '<tr  valign="top"><th scope="row">是否开启数据记录</th>';
		echo '<td><input type="checkbox" name="weixin_robot_options[weixin_robot_record]"  value="true" ';
		if( $options['weixin_robot_record'] == 'true' ){ echo ' checked="checked"'; }
		echo '/><td></tr>';

		//是否开启测试模式
		echo '<tr  valign="top"><th scope="row">是否开启测试模式</th>';
		echo '<td><input type="checkbox" name="weixin_robot_options[weixin_robot_debug]"  value="true"';
		if( $options['weixin_robot_debug'] == 'true' ){ echo ' checked="checked"'; }
		echo '/></td></tr>';

		//帮助信息
		echo '<tr valign="top"><th scope="row">帮助信息</th>';
		echo '<td><textarea name="weixin_robot_options[weixin_robot_helper]" style="width:350px;height:100px;" class="regular-text code">'
			.$options['weixin_robot_helper'].'</textarea><br/><span style="color:red;">帮助信息(note:微信一行12字左右)</span></td></tr>';

		//是否启动无此命令不回复选项(设置后台,无匹配关键字将不返回任何信息)
		echo '<tr  valign="top"><th scope="row">是否启动无此匹配命令不回复</th>';
		echo '<td><input type="checkbox" name="weixin_robot_options[weixin_robot_helper_is]"  value="true"';
		if( $options['weixin_robot_helper_is'] == 'true' ){ echo ' checked="checked"'; }
		echo '/><br/>开启后,只有<span style="color:red;">?</span>回复帮助信息</td>';

		//服务号设置(公司相关)
		//ai
		echo '<tr valign="top"><td scope="row" colspan="2"><h2>服务号设置</h2><br/>说明:如果你不是服务号,请不要设置</td></tr>';
		echo '<tr valign="top"><th scope="row">appID</th>';
		echo '<td><input type="text" name="weixin_robot_options[ai]" value="'
			.$options['ai'].'" size="35"></input><br />微信公众平台开发者ID(第三方用户唯一凭证)</td></tr>';
		
		//as
		echo '<tr valign="top"><th scope="row">appsecret</th>';
		echo '<td><input type="text" name="weixin_robot_options[as]" value="'
			.$options['as'].'" size="35"></input><br />appsecret(第三方用户唯一凭证密钥)</td></tr>';
		
		//EncodingAESKey
		echo '<tr valign="top"><th scope="row">EncodingAESKey(密钥)</th>';
		echo '<td><input type="text" name="weixin_robot_options[EncodingAESKey]" value="';
		if(!empty($options['EncodingAESKey'])){ echo($options['EncodingAESKey']); }
		echo '" size="35"></input><br />EncodingAESKey(消息加解密密钥)[安全和兼容模式必须填写]</td></tr>';

		do_action('wx_admin_setting');
	}
	
}

endif;
?>
