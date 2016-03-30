<?php
/**
 * WxRobot Admin
 * 
 * WP微信机器人后台菜单 - 微信菜单设置
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
 * WxRobot_Admin_Menu_Menu 微信菜单控制功能
 */
class WxRobot_Admin_Menu_Menu{

	/**
	 * 初始化钩子和过滤
	 *
	 * @return void
	 */
	public function init(){
		add_action('init', array($this, 'menu_ajax'), 10);
		add_action('admin_head', array(&$this, 'weixin_robot_menu_js'), 10);
	
	}

	/**
	 * Ajax请求
	 *
	 * @return void
	 */
	public function menu_ajax(){
		if(!empty($_POST['page']) && 'weixin-robot-menu-setting' == $_POST['page']){
			if($_POST['method'] == 'update'){
				echo($this->setting_ajax_update());
			}
		}
	}

	/**
	 * 菜单更新
	 *
	 * @return string
	 */
	public function setting_ajax_update(){
		$id = $_POST['id'];
		$type = $_POST['type'];
		$name = $_POST['name'];
		$value = strip_tags($_POST['value']);
		$sort  = $_POST['sort']; 
		$res = WxRobot_Table_Menu::instance()->update_menu($id, $name, $type, $value, $sort);
		if($res){
			$this->weixin_robot_ab_menu();
			return 'ok';
		}else{
			return 'fail';
		}
	}

	/**
	 * 加载需要的js
	 *
	 * @return void
	 */
	public function weixin_robot_menu_js(){
		$url = WEIXIN_ROBOT_URL;
		if(!empty($_GET['page']) && 'weixin-robot-menu-setting' == $_GET['page']){
			echo '<link type="text/css" rel="stylesheet" href="'.$url.'/assets/css/hover.css" />';
			echo '<link type="text/css" rel="stylesheet" href="'.$url.'/assets/css/weixin_robot_menu_setting.css" />';
			echo '<script type="text/javascript" src="'.$url.'/assets/js/weixin_robot_menu_setting.js"></script>';
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
			'微信机器人菜单',
			'manage_options',
			'weixin-robot-menu-setting',
			array($this, 'menu_setting'));
	}
	

	/**
	 * 组装menu菜单
	 *
	 * @return json
	 */
	public function weixin_robot_ab_menu(){
		if($data = WxRobot_Table_Menu::instance()->weixin_get_menu_p_data()){
			$menu = array();
			foreach($data as $k=>$v){
				if($data2 = WxRobot_Table_Menu::instance()->weixin_get_menu_p_data_id($v['id'])){
					$list['name'] = $v['menu_name'];
					foreach($data2 as $k1=>$v2){
						$list2['type'] = $v2['menu_type'];
						$list2['name'] = $v2['menu_name'];
						if('view' == $v2['menu_type']){
							$list2['url'] = $v2['menu_callback'];
						}else{
							$list2['key'] = $v2['menu_key'];
						}
						$list['sub_button'][] = $list2;
						$list2 = array();
					}
					
					$menu[] = $list;
					$list = array();
				}else{
					$list['type'] = $v['menu_type'];
					$list['name'] = $v['menu_name'];

					if('view' == $v['menu_type']){
						$list['url'] = $v['menu_callback'];
					}else{
						$list['key'] = $v['menu_key'];
					}
					$menu[] = $list;
					$list = array();
				}
			}
			$M['button'] = $menu;
			$menu =  WxRobot_SDK::instance()->to_json($M);
			$data = WxRobot_SDK::instance()->menuSet($menu);
			$_data = json_decode($data, true);
			if($_data['errmsg'] == 'ok'){
				return true;
			}
		}
		return false;
	}

	/**
	 * 随机key菜单值
	 * 
	 * @return string
	 */
	public function weixin_robot_rand_menu(){
		return 'MENU_'.time();
	}

	/**
	 *	检查是否是正确的服务好
	 *
	 *	@return bool
	 */
	public function check_right_server(){
		$data = WxRobot_SDK::instance()->getToken();
		if(empty($data)){
			return false;
		}

		$data = WxRobot_SDK::instance()->menuGet();

		if(!$data){
			return false;
		}


		return true;
	}

	/**
	 * 菜单提交处理
	 *
	 * @return void
	 */
	public function menu_setting_post(){
		//自定义菜单设置
		$opts = get_option(WEIXIN_ROBOT_OPTIONS);


		if(!$this->check_right_server()){
			wx_notice_msg('你填写服务号信息,有问题。请重新填写');exit;	
		}


		if(isset($_POST['submit_menu'])){
			switch($_POST['submit_menu']){
			case '提交菜单':
					$data = $_POST['weixin_robot_menu'];
					if(empty($data['name']) || empty($data['value'])){
						if($this->weixin_robot_ab_menu()){
							wx_notice_msg('菜单成功更新微信服务器上了!!!');
						}else{
							wx_notice_msg('菜单更新失败!!!');
						}
						wx_notice_msg('请填写号内容!!!');
					}else{
						//判断是否为1级菜单
						if(isset($data['child']) && 'true' == $data['child'] && $data['parent'] != 'false'){//子菜单
							if(WxRobot_Table_Menu::instance()->weixin_get_menu_c_count($data['parent']) < 5){
								$data = WxRobot_Table_Menu::instance()
									->insert_menu($data['name'], $data['type'], $this->weixin_robot_rand_menu(), $data['value'], $data['parent']);

								if($this->weixin_robot_ab_menu()){
									wx_notice_msg('菜单成功更新微信服务器上了!!!');
								}else{
									wx_notice_msg('菜单更新失败!!!');
								}
							}else{
								wx_notice_msg('二级菜单不能再添加了!!!');
							}
						}else{//一级菜单
							if(WxRobot_Table_Menu::instance()->weixin_get_menu_p_count() < 3){
								$data = WxRobot_Table_Menu::instance()
									->insert_menu($data['name'], $data['type'],  $this->weixin_robot_rand_menu(), $data['value'], 0);
								if($this->weixin_robot_ab_menu()){
									wx_notice_msg('菜单成功更新微信服务器上了!!!');
								}else{
									wx_notice_msg('菜单更新失败!!!');
								}
							}else{
								wx_notice_msg('一级菜单不能再添加了!!!');
							}
						}
					}
				break;
			case '清空菜单':
				WxRobot_Table_Menu::instance()->clear_menu();
				$data = WxRobot_SDK::instance()->menuDel();
				$_data = json_decode($data, true);
				if('ok' == $_data['errmsg']){
					wx_notice_msg('清空菜单成功!!!');
				}else{
					wx_notice_msg('清空菜单失败!!!');
				}
				break;
			case '删除':
				if(isset($_POST['id'])){
					if($data = WxRobot_Table_Menu::instance()->delete_menu_id($_POST['id'])){
						wx_notice_msg('ok!!!');
						if($this->weixin_robot_ab_menu()){
							wx_notice_msg('菜单成功更新微信服务器上了!!!');
						}else{
							wx_notice_msg('菜单更新失败!!!');
						}
					}else{
						wx_notice_msg('fail!!!');
					}
				}
				break;
			}
		}
	}

	/**
	 * 菜单设置页
	 */
	public function menu_setting(){

		$this->menu_setting_post();

		echo '<div class="wrap">';
		echo '<h2>微信菜单设置</h2>';
		////////////////////////////////////////////////////////////////////////下面设置菜单
		echo '<div class="metabox-holder">';
		echo '<div class="postbox">';
		
		echo '<table class="form-table" style="width:700px;border:2px;border-color:#21759b;">';

		$trTpl = "<tr class='wp_weixin_robot_table_head_tr'>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='width:180px;text-align:center;color:#21759b;' scope='col'>%s</td></tr>";
		$tableHeadTpl = sprintf($trTpl, '序号ID', '菜单名', '菜单类型', 'key/url', '排序','操作');
		echo $tableHeadTpl;


		//一级菜单
		$data = WxRobot_Table_Menu::instance()->weixin_get_menu_p_data();
		
		if($data){
			foreach($data as $k=>$v){
				$trTpl = "<tr><td style='text-align:center;' scope='col'>{$v['id']}</td>
				<td style='text-align:left;' scope='col'>─{$v['menu_name']}</td>
				<td style='text-align:center;' scope='col'>{$v['menu_type']}</td>
				<td style='text-align:center;' scope='col'>{$v['menu_callback']}</td>
				<td style='text-align:center;' scope='col'>{$v['menu_sort']}</td>
				<td style='text-align:center;' scope='col'>";
				$trTpl .= '<input type="hidden" name="id" value="'.$v['id'].'" />';
				$trTpl .= '<input class="button" name="submit_menu" type="submit" value="删除" />';
				$trTpl .= '|<span class="weixin_robot_mv button wobble-to-top-right">修改</span>';
				$trTpl .= "</td></tr>";
				echo '<form  method="POST">';
				echo $trTpl;
				echo '</form>';
				//二级菜单
				if($data2 = WxRobot_Table_Menu::instance()->weixin_get_menu_p_data_id($v['id'])){
					foreach($data2 as $k=>$v){
						$trTpl = "<tr><td style='width:40px;text-align:center;' scope='col'>{$v['id']}</td>
						<td style='width:100px;text-align:left;' scope='col'>└─{$v['menu_name']}</td>
						<td style='text-align:center;' scope='col'>{$v['menu_type']}</td>
						<td style='text-align:center;' scope='col'>{$v['menu_callback']}</td>
						<td style='text-align:center;' scope='col'>{$v['menu_sort']}</td>
						<td style='width:100px;text-align:center;' scope='col'>";
						$trTpl .= '<input type="hidden" name="id" value="'.$v['id'].'" />';
						$trTpl .= '<input class="button" name="submit_menu" type="submit" value="删除" />';
						$trTpl .= '|<span class="weixin_robot_mv button wobble-to-top-right">修改</span>';
						$trTpl .= "</td></tr>";

						echo '<form  method="POST">';
						echo $trTpl;
						echo '</form>';
					}
				}
			}
		}else{
			echo '<tr>';
			echo "<td class='wp_weixin_robot_table_head_td' style='color:#21759b;width:100px;text-align:center;' scope='col' colspan='6'>没有设置相应菜单</td>";
			echo '</tr>';
		}
		echo '</table></div></div>';

		echo '<div><div>';
		echo '<table class="form-table">';
		echo '<form action="" method="POST">';
		//菜单名称
		echo '<tr valign="top"><th scope="row">菜单名称</th>';
		echo '<td><input type="text" name="weixin_robot_menu[name]" value="" size="35"></input></td></tr>';

		//事件选择
		echo '<tr  valign="top"><th scope="row">事件类型选择</th><td>'
			,'<select name="weixin_robot_menu[type]" id="method">'
			,'<option value="click" selected="selected">点击</option>'
			,'<option value="view" >URL</option>'
			,'</select><p></p><td></tr>';

		//菜单key/url
		echo '<tr valign="top"><th scope="row">key/url</th>';
		echo '<td><input type="text" name="weixin_robot_menu[value]" value="" size="35"></input><br />';
		echo '<p>如果选择"URL"选项,应该填写网址: http://midoks.cachecha.com/</p>';
		echo '<p>如果选择"点击"选项,可以是使用today(今日发布),n(1-10)最新信息, h(1-10)热门信息, r(1-10)随机信息, ?(帮助信息)等内置命令!!</p>';
		echo '<p style="color:red">如果回复内容在关键字设置了, 就会返回关键字的回复信息!</p>';
		echo '<p>不满足上面的话,则会返回文本信息</p>';
		echo '</td></tr>';

		//是否为菜单
		echo '<tr valign="top"><th scope="row">是否为子菜单</th>';
		echo '<td><input type="checkbox" name="weixin_robot_menu[child]"  value="true"/>';
		echo '<br />为子菜单时,请一定选择</td></tr>';

		//选择父级菜单
		echo '<tr valign="top"><th scope="row">父级菜单选择</th><td>';
		echo '<select name="weixin_robot_menu[parent]" id="method" />';
		$data = WxRobot_Table_Menu::instance()->weixin_get_menu_p_data();
		if($data){
			foreach($data as $k=>$v){
				echo "<option value='{$v['id']}' selected='selected'>{$v['menu_name']}</option>";
			}
		}else{
			echo '<option value="false" selected="selected">无顶级菜单,请先创建</option>';
		}	
		echo '</select><td></tr></table>'
			,'<p class="submit">'
			,'<input name="submit_menu" type="submit" class="button-primary" value="提交菜单" title="提交本地的数据库中..."/>'
			,'<input style="margin-left:10px" name="submit_menu" type="submit" class="button-primary" value="清空菜单" title="删除本地数据菜单相关数据" />'	
			,'</p></form></div></div>';

		do_action('weixin_midoks_push');
	}
	
}
?>
