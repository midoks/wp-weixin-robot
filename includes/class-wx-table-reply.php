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
 *	WxRobot_Table_Reply 微信关键子设置模型
 */
class WxRobot_Table_Reply{

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
	 * 获取表名
	 *
	 * @return string
	 */
	private function get_table_name(){
		return $this->table_prefix.'weixin_robot_reply';
	}

	/**
	 * 插入数据
	 *
	 * @param string $keyword 关键字
	 * @param string $reply   回复内容
	 * @param string $status  状态
	 * @param int 	 $type    类型
	 * @param int 	 $sort 	  排序
	 * return bool
	 */
	public function insert_relpy($keyword, $relpy, $status, $type, $sort=1){

		global $wpdb;
		$table_name = $this->get_table_name();
		$time = date('Y-m-d H:i:s');

		$sql = 	" INSERT INTO `{$table_name}` (`id`, `keyword`, `relpy`, `status`, `time`, `type`, `sort`)".
				" VALUES(null,'{$keyword}','{$relpy}','{$status}', '{$time}', '{$type}', '{$sort}') ";

		return $wpdb->query($sql);
	}

	/**
	 * 获取回复数据
	 *
	 * @param string $kw 关键字
	 * @return mixed
	 */
	public function weixin_get_relpy_data($kw=''){
		global $wpdb;
		$table_name = $this->get_table_name();
		if(!empty($kw)){
			$sql = "select `id`,`keyword`,`relpy`,`status`,`time`,`type`"
				." from `{$table_name}` where `status`='1' and `keyword` like '%{$kw}%' order by `id` desc";
		}else{
			$sql = "select `id`,`keyword`,`relpy`,`status`,`time`,`type`"
				." from `{$table_name}` where `status`='1' order by `id` desc";
		}
		$data  = $wpdb->get_results($sql);
		if(empty($data)){
			return false;
		}else{
			$arrs = array();
			foreach($data as $k=>$v){
				$arr['id'] = $v->id;
				$arr['keyword'] = $v->keyword;
				$arr['relpy'] = $v->relpy;
				$arr['status'] = $v->status;
				$arr['time'] = $v->time;
				$arr['type'] = $v->type;
				$arrs[] = $arr;
			}
			return $arrs;
		}		
	}


	/**
	 * 删除关键字数据
	 * 
	 * @param int $id ID
	 * @return bool
	 */
	public function delete_relpy_id($id){
		global $wpdb;
		$table_name = $this->get_table_name();
		$sql = 'delete from `'.$table_name."` where `id`='{$id}'";
		return $wpdb->query($sql);
	}

	/**
	 * 改变status状态
	 *
	 * @param int $id ID
	 * @param int $status 状态值
	 * @return bool
	 */
	public function change_relpy_status($id, $status){
		global $wpdb;
		$table_name = $this->get_table_name();
		$sql = "UPDATE `{$table_name}` SET `status`='{$status}' WHERE `id`='{$id}'";
		return $wpdb->query($sql);
	}

	/**
	 * 更改关键字回复内容
	 *
	 * @param int $id ID
	 * @param string $keyword 关键字
	 * @param string $reply  回复内容
	 * @param string $type   类型
	 */
	public function change_reply($id, $keyword, $relpy, $type){
		global $wpdb;
		$table_name = $this->get_table_name();
		$sql = "UPDATE `{$table_name}` SET `keyword`='{$keyword}',`relpy`='{$relpy}',`type`='{$type}' WHERE `id`='{$id}'";
		return $wpdb->query($sql);
	}

}
?>
