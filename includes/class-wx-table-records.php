<?php
/**
 * WxRobot Table Extends 
 * 
 * WP微信机器人 扩展表的相关功能
 *
 * @author 		midoks
 * @category 	Table
 * @package		WxRobot/Table
 * @since		5.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *	WxRobot_Table_Records 微信通信记录模型
 */
class WxRobot_Table_Records{

	/**
	 * 表前缀
	 */
	public $table_prefix = 'midoks_';

	/**
	 * Table Extends Instance
	 */
	public static $_instance = null;
	

	/**
	 * WxRobot 扩展表类实例化
	 * 
	 * @return WxRobot_Table_Extends - Table Extends instance
	 */
	public static function instance(){
		if( is_null (self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * 获取记录表名
	 *
	 * @return string
	 */
	private function get_table_name(){
		return $this->table_prefix.'weixin_robot';
	}

	/**
	 * 获取菜单表名
	 *
	 * @return string
	 */
	private function get_table_name_menu(){
		return $this->table_prefix.'weixin_robot_menu';
	}

	/**
	 * 插入记录数据
	 *
	 * return bool
	 */
	public function insert($from, $to, $msgid, $msgtype, $createtime, $content, $picurl, $location_x, $location_y,
		$scale, $label, $title, $description, $url, $event,$eventkey,$format, $recognition, $mediaid,$thumbmediaid, $response, $response_time){
		
		global $wpdb;
		$table_name = $this->get_table_name();
		
		$sql = "INSERT INTO `{$table_name}` (`id`, `from`, `to`, `msgid`, `msgtype`, `createtime`, `content`, `picurl`, `location_x`, `location_y`, `scale`, `label`, `title`, `description`, `url`, `event`, `eventkey`, `format`,`recognition`,`mediaid`, `thumbmediaid`, `response`, `response_time`) VALUES(null,'{$from}','{$to}','{$msgid}', '{$msgtype}','{$createtime}', '{$content}','{$picurl}','{$location_x}', '{$location_y}','{$scale}', '{$label}', '{$title}','{$description}', '{$url}', '{$event}','{$eventkey}','{$format}', '{$recognition}', '{$mediaid}','{$thumbmediaid}', '{$response}', '{$response_time}')";
		return $wpdb->query($sql);
	}

	/**
	 * 获取消息类型的总数
	 */
	public function weixin_get_msgtype_count($text = 'text'){
		global $wpdb;
		$table_name = $this->get_table_name();
		$sql = 'select count(`id`) as count from `'.$table_name."` where `msgtype`='{$text}'";
		$result = $wpdb->get_results($sql);
		return $result[0]->count;
	}

	/**
	 * 获取记录总数
	 *
	 * @return int
	 */
	public function weixin_get_count(){
		global $wpdb;
		$table_name = $this->get_table_name();
		$sql = "select count(id) as count from `{$table_name}`";
		$data = $wpdb->get_results($sql);
		return $data[0]->count;
	}

	/**
	 * 微信数据获取
	 * 
	 * @param uint $page_no 第几页数据
	 * @param uint $num 每页显示的数据
	 * @return array
	 */
	public function weixin_get_data($page_no = 1, $num = 20){
		global $wpdb;
		$table_name = $this->get_table_name();

		if($page_no < 1){
			$page_no = 1;
		}
		$start = ($page_no-1)*$num;
		$sql = "select `id`,`from`,`to`,`msgtype`,`createtime`,`content`,`picurl`,`location_x`,`location_y`, `scale`, `label`, `title`,"
			."`description`,`url`,`event`, `eventkey`,`format`,`recognition`,`mediaid`,`thumbmediaid`,`response`, `response_time`"
			." from `{$table_name}` order by `id` desc limit {$start},{$num}";
		$data  = $wpdb->get_results($sql);
		$newData = array();
		foreach($data as $k=>$v){
			$arr = array();
			$arr['id'] = $v->id;
			$arr['from'] = $v->from;
			$arr['to'] = $v->to;
			$arr['msgtype'] = $v->msgtype;

			//暂时显示文本消息
			switch($v->msgtype){
				case 'text':$arr['content'] = $v->content;break;
				default:$arr['content'] = $v->content;
			}

			//菜单点击事件
			if('CLICK' == $v->event){
				$data = $this->select_menu_key($v->eventkey);
				if($data){
					$arr['content'] = '菜单:'.$data;
				}else{
					$arr['content'] = '菜单:已经不存在';
				}
			}else if('subscribe' == $v->event){//订阅事件
				$arr['content'] = '订阅事件';
			}else if('unsubscribe' == $v->event){//取消订阅事件
				$arr['content'] = '取消订阅事件';
			}else if('LOCATION' == $v->event){
				$arr['content'] = '地理位置上报告事件';
			}else if('location' == $v->msgtype){
				$arr['content'] = '地理位置上报告事件';
			}else if('voice' == $v->msgtype){
				$arr['content'] = '语音事件';
			}

			$arr['createtime'] = date('Y-m-d H:i:s', $v->createtime);
			$arr['response'] = $v->response;
			$arr['response_time'] = $v->response_time;
			$newData[] = $arr;
		}
		return $newData;
	}

	/**
	 * 通过菜单key,获取菜单名字
	 */
	public function select_menu_key($key){

		global $wpdb;
		$table_name = $this->get_table_name_menu();

		$sql = "select `id`,`menu_name`, `menu_type`, `menu_key`, `menu_callback`, `pid`"
			." from `{$table_name}` where `menu_key`='{$key}' limit 1";
		$data  = $wpdb->get_results($sql);
		if(empty($data)){
			return false;
		}else{
			return $data[0]->menu_name;
		}	
		return false;
	}

	

	

}
?>
