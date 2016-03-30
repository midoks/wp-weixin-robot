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

/**
 *	WxRobot_Admin_Menu_Reply 关键子回复类
 */
class WxRobot_Admin_Menu_Reply{

	/**
	 *	初始化钩子和过滤
	 *
	 *	@reutrn void
	 */
	public function init(){
		add_action('init', array($this, 'relpy_ajax'), 9);
		add_action('admin_head', array(&$this, 'weixin_robot_reply_js'), 9);
	}

	/**
	 * 加载需要的js
	 *
	 * @return void
	 */
	public function weixin_robot_reply_js(){
		$url = WEIXIN_ROBOT_URL;
		//使用ichatjs开源项目 http://www.ichartjs.com/
		if(!empty($_GET['page']) && 'weixin-robot-setting-keyword-relpy' == $_GET['page']){
			echo '<link type="text/css" rel="stylesheet" href="'.$url.'/assets/css/hover.css" />';
			echo '<script type="text/javascript" src="'.$url.'/assets/js/weixin_robot_setting_keyword_relpy.js"></script>';
		}
	
	}

	/**
	 * Ajax设置关键字
	 *
	 * @return json
	 */
	public function relpy_ajax(){
		if(!empty($_POST['page']) && $_POST['page'] == 'weixin_robot_setting_keyword_relpy'){
			if($_POST['method'] == 'update'){
				echo($this->relpy_ajax_update());
			}
		}
	}

	/**
	 * Ajax更新关键字数据
	 *
	 * @return string
	 */
	public function relpy_ajax_update(){
		$id = $_POST['id'];
		$keyword = $_POST['keyword'];
		$reply = strip_tags($_POST['reply']);
		$type = $_POST['type'];
		$res = WxRobot_Table_Reply::instance()->change_reply($id, $keyword, $reply, $type);
		if($res){
			return 'ok';
		}else{
			return 'fail';
		}
	}

	/**
	 * 菜单初始化
	 *
	 * @return void
	 */
	public function menu(){
		add_submenu_page('weixin-robot',
			'weixin-robot',	
			'微信机器人回复',
			'manage_options',
			'weixin-robot-setting-keyword-relpy',
			array($this, 'setting_keyword_relpy'));
	}

	/**
	 * 提交添加内容
	 *
	 * @return void
	 */
	public function reply_post(){
		if(isset($_POST['submit_key'])){
			switch($_POST['submit_key']){
			case '启用':
				$id = $_POST['id'];
				$data = WxRobot_Table_Reply::instance()->change_relpy_status($id, '1');
				wx_notice_msg('启用成功!!!');
				break;
			case '禁用':
				$id = $_POST['id'];
				$data = WxRobot_Table_Reply::instance()->change_relpy_status($id, '0');
				wx_notice_msg('禁用成功!!!');
				break;
			case '删除':
				$id = $_POST['id'];
				$data = WxRobot_Table_Reply::instance()->delete_relpy_id($id);
				wx_notice_msg('删除成功!!!');
				break;
			case '提交数据':
				$type = $_POST['option']['check'];
				$key = $_POST['option']['key'];
				$relpy = $_POST['option']['word'];

				if(empty($type) || empty($key) || empty($relpy)){
					wx_notice_msg('信息不能为空!!!');
				}else{
					$result = WxRobot_Table_Reply::instance()->insert_relpy($key, $relpy, $status=1, $type);
					wx_notice_msg('设置成功!!!');
				}
				
				if(!$result){
					wx_notice_msg('设置失败!!!');
				}	
				break;
			}	
		}
	}


	/**
	 * 设置关键字回复页
	 *
	 * @return void
	 */
	public function setting_keyword_relpy(){
		
		$this->reply_post();
	
		echo 	'<div class="wrap"><h2>微信机器人关键字自定义回复设置</h2></div>',
				'<div class="wrap"><div class="metabox-holder">',
				'<div class="postbox">',
				'<table class="form-table" style="width:700px;border:2px;border-color:#21759b;">';
	
		$trTpl = "<tr class='wp_weixin_robot_table_head_tr'>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;width:160px' scope='col'>%s</td></tr>";
		$tableHeadTpl = sprintf($trTpl, '序号ID', '关键字', '回复内容','类型', '操作');
		echo $tableHeadTpl;
		
		$data = WxRobot_Table_Reply::instance()->weixin_get_relpy_data();
		if($data){
			foreach($data as $k=>$v){
				$trTpl = "<tr>
				<td style='text-align:center;' scope='col'>{$v['id']}</td>
				<td style='text-align:center;' scope='col'>{$v['keyword']}</td>
				<td style='text-align:center;' scope='col'>{$v['relpy']}</td>
				<td style='text-align:center;' scope='col'>{$v['type']}</td>
				<td style='width:200px;text-align:center;' scope='col'>";

				$trTpl .= '<input type="hidden" name="id" value="'.$v['id'].'" />';
				$trTpl .= '<input name="submit_key" class="button" type="submit"  value="';
				if($v['status']){
					$trTpl .= '禁用';
				}else{
					$trTpl .= '启用';
				}
				$trTpl .= '" />';
				$trTpl .=" | ";
				$trTpl .= '<input name="submit_key" class="button" type="submit" value="删除" />';
				$trTpl .= '|<span class="weixin_robot_mv button wobble-to-top-right">修改</span>';

				$trTpl .= "</td></tr>";
				echo '<form  method="POST">';
				echo  $trTpl;
				echo '</form>';
			}
		}else{
			echo '<tr>',
				'<td class="wp_weixin_robot_table_head_td" style="color:#21759b;width:100px;text-align:center;" scope="col" colspan="4">没有设置keyword</td>',
				'</tr>';
		}

		echo '</table>';

		echo '</div></div>';
		echo '<div><div>';
		echo '<form  method="POST">';
		echo '<table class="form-table">';
		echo '<tr><td></td></tr>';
		//数据开启数据记录
		echo '<tr  valign="top"><th scope="row">类型选择</th>';
		echo '<td>';
		echo '<select name="option[check]" id="method" />';
		
		echo '<option value="text" selected="selected">文本回复</option>';
		echo '<option value="id">图文ID回复</option>';
		echo '<option value="music">音乐回复</option>';
		echo '</select><p></p>';

		echo '<td></tr>',
			//keyword
			'<tr valign="top"><th scope="row">关键字</th>',
			'<td><textarea name="option[key]" style="width:350px;height:50px;" class="regular-text code"></textarea><br /></td></tr>',
			//replay
		 	'<tr valign="top"><th scope="row">回复信息</th>',
			'<td><textarea name="option[word]" style="width:350px;height:50px;" class="regular-text code"></textarea><br />',
			'<p>如果选择"图文ID"选项,应该填写文章ID: 1,4,8(图文最多显示10个信息)</p>',
			'<p>如果选择"文本回复"选项,可以是使用today(今日发布),n(1-10)最新信息, ',
			'h(1-10)热门信息, r(1-10)随机信息, ?(帮助信息)等内置命令!!!</p>',
			'<p>如果选择音乐回复,则填写如下格式:</p>',
			'<p style="color:red;">音乐名称|音乐描述|音乐地址</p>',
			'<p>不满足上面的话,则会返回文本信息</p>',
			'</td></tr>',
			'<input type="hidden" name="weixin_robot_keyword_relpy" value="true" /></table>',
			'<p style="margin-left:20px;" class="submit">',
			'<input name="submit_key" type="submit" class="button-primary" value="提交数据" />',
			'</p></form></div></div></div>';

		do_action('wx_admin_reply');

		do_action('weixin_midoks_push');
	}

	
}
?>
