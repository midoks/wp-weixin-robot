<?php
/**
 * WxRobot Admin
 * 
 * WP微信机器人后台菜单 - 通信记录页
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
 * WxRobot_Admin_Menu_Records 通信记录
 */
class WxRobot_Admin_Menu_Records{	
	
	/**
	 * 初始化钩子和过滤
	 *
	 * @return void
	 */
	public function init(){}


	/**
	 * 菜单初始化
	 *
	 * @return void
	 */
	public function menu(){
		add_submenu_page('weixin-robot',
			'weixin-robot',	
			'微信机器人记录',
			'manage_options',
			'weixin-robot-records',
			array($this, 'weixin_robot_records'));
	}

	/**
	 * @func 微信机器人记录
	 */
	public function weixin_robot_records(){
		//当前页
		$paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
		//每页显示多少数据
		$pageNum = 20;
		$c = WxRobot_Table_Records::instance()->weixin_get_count();
		$pagePos = ceil($c/$pageNum);
		if($paged > $c){
			$paged = $c;
		}
		if($paged < 1){
			$page = 1;
		}

		echo '<h2>微信机器人记录</h2>';

		do_action('weixin_robot_records_footer');
		
		$trTpl = "<tr class='wp_weixin_robot_table_head_tr'>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;color:#21759b;' scope='col'>%s</td></tr>";
		$tableHeadTpl = sprintf($trTpl, '序号ID', '开发者ID', '用户ID',
			'消息类型', '消息内容', '消息时间', '回复', '响应时间');


		$tableTrTpl = "<tr class='in_out_event'>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;width:40px;'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;width:100px;'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;width:180px;'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;width:50px;'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;width:130px'>%s</td>
			<td class='wp_weixin_robot_table_head_td' style='text-align:center;width:100px'>%s</td>
			<td title='超过5s,则代表失败!!!' style='text-align:center;width:100px'>%s</td></tr>";
		$tableBodyTpl = '';
		$data = WxRobot_Table_Records::instance()->weixin_get_data($paged);

		foreach($data as $k=>$v){
			//var_dump($v);
			$tableHeadTpl .= sprintf($tableTrTpl,   $v['id'], $v['to'], $v['from'],
				$this->type_replace($v['msgtype']), $v['content'], $v['createtime'], $v['response'], $v['response_time']);
		}

		//echo($tableTpl);
		echo '<div class="metabox-holder"><div class="wrap">';
		echo '<table class="wp-list-table widefat fixed" id="user_info">';
		echo '<thead>';
		echo($tableHeadTpl);
		echo '</thead>';
		
		echo '<tbody>';
		echo($tableBodyTpl);
		echo '</tbody>';

		echo '<tfoot>';
		//分页显示
		echo '<tr><td colspan="8" class="wp_weixin_robot_table_head_td">';
		echo($this->weixin_info_page($c, $paged, $pageNum));
		echo '</td></tr></tfoot></table></div></div>';

		do_action('weixin_robot_records_footer');
		do_action('weixin_midoks_push');
	}

	/**
	 * 消息类型转换
	 *
	 * @param string $type 类型
	 * @return string
	 */
	public function type_replace($type){
		switch($type){
			//文本消息	
			case 'text':return '文本';break;
			//图片消息
			case 'image':return '图片';break;
			//语音消息
			case 'voice':return '语音';break;
			//视频消息
			case 'video':return '视频';break;
			//事件消息
			case 'event':return '事件';break;
			//地理位置
			case 'location': return '地理';break;
			case 'link':return '连接';break;
			//默认消息
			default:return '文本';break;
		}
		return '你傻了吧';
	}

	/**
	 * 分页功能 path版
	 *
	 * @param $total 	共多少数据
	 * @param $position 在第几页
	 * @param $page 	每页的数量
	 * @param $show  	显示多少li
	 * @return void
	 */
	public function weixin_info_page($total, $position, $page=5, $show=7){
		$prev = $position-1;//前页
		$next = $position+1;//下页
		//$showitems = 3;//显示多少li
		$big = ceil($show/2);
		$small = floor($show/2);//$show最好为奇数 
		$total_page = ceil($total/$page);//总页数
		//if($prev < 1){$prev = 1;}
		if($next > $total_page){$next = $total_page;}
		if($position > $total_page){$position = $total_page;}
		if(0 != $total_page){
			echo "<div>";
			echo("<span>总共{$total}条数据/当前第{$position}页<span>");
			/////////////////////////////////////////////
			echo("<span style='margin-left:30px'><a href='".get_pagenum_link(1)."#' class='fixed'>首页</a></span>");
			echo("<span style='margin-left:30px'><a class='p_prev' href='".get_pagenum_link($prev)."#'><<</a></span>");
			$j=0;
			for($i=1;$i<=$total_page;$i++){
				$url = get_pagenum_link($i);
				if($position==$i)
					$strli = "<span style='margin-left:30px'><a href='".$url."#' class='current' >".$i.'</a></span>';
				else
					$strli =  "<span style='margin-left:30px'><a href='".$url."#' class='inactive' >".$i.'</a></span>';
				if($total_page<=$show){echo $strli;}
				if(($position+$small)>=$total_page){
					//也是对的,下面为简化版
					//if(($j<$show) && ($total_page>$show) && ($i>=($position-($small+($position+$small-$total_page))))){echo($strli);++$j;}
					if(($j<$show) && ($total_page>$show) && ($i>=($total_page-(2*$small)))){echo($strli);++$j;}
				}else{if(($j<$show) && ($total_page>$show) && ($i>=($position-$small))){echo($strli);++$j;}}
			}
			echo("<span style='margin-left:30px'><a class='p_next' href='".get_pagenum_link($next)."#'>>></a></span>");
			echo("<span style='margin-left:30px'><a href='".get_pagenum_link($total_page)."#'>尾页</a></span>");
			//////////////////////////////////////////////
			echo '</div>';
		}
	}
	
}

?>
