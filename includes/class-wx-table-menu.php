<?php
/**
 * WxRobot Table Extends 
 * 
 * WP微信机器人 菜单的相关功能
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
 *	WxRobot_Table_Menu 菜单表模型
 */
class WxRobot_Table_Menu{

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
		return $this->table_prefix.'weixin_robot_menu';
	}


	/**
	 * 清空数据
	 * 
	 * @return bool
	 */
	public function clear_menu(){
		global $wpdb;
		$table_name = $this->get_table_name();
		$sql = 'truncate '.$table_name;
		return $wpdb->query($sql);
	}

	/**
	 *	更新菜单到数据库中
	 *
	 *	@param int $id ID
	 *	@param string $name 菜单名
	 *	@param string $type 菜单类型
	 *	@param string $value 菜单值
	 *	@param int 	$sort  排序值
	 *	@return bool
	 */
	public function update_menu($id, $name, $type, $value, $sort){
		global $wpdb;
		$table_name = $this->get_table_name();

		$sql = "update {$table_name} set menu_name='{$name}', menu_type='{$type}', menu_callback='{$value}', menu_sort='{$sort}' where id='{$id}'";
		return $wpdb->query($sql);
	}

	/**
	 * 插入菜单
	 *
	 * @param string $menu_name 菜单名
	 * @param string $menu_type 菜单类型
	 * @param string $menu_key  菜单key值
	 * @param string $menu_callback 菜单回复值
	 * @param int 	 $pid 菜单父ID
	 * @return bool
	 */
	public function insert_menu($menu_name, $menu_type, $menu_key, $menu_callback, $pid){
		global $wpdb;
		$table_name = $this->get_table_name();

		$sql = "INSERT INTO `{$table_name}` (`id`, `menu_name`, `menu_type`, `menu_key`, `menu_callback`, `pid`)"
			." VALUES(null,'{$menu_name}','{$menu_type}','{$menu_key}', '{$menu_callback}', '{$pid}')";
		return $wpdb->query($sql);
	}

	/**
	 * 删除菜单
	 *
	 * @param int $id ID
	 * @return bool
	 */
	public function delete_menu_id($id){
		global $wpdb;
		$table_name = $this->get_table_name();

		$sql = "delete from {$table_name} where `id`='{$id}'";
		$this->delete_menu_g_id($id);
		return $wpdb->query($sql);
	}

	/**
	 * 删除相应的子菜单
	 *
	 * @param int $pid 父ID
	 * @return bool
	 */
	public function delete_menu_g_id($pid){
		global $wpdb;
		$table_name = $this->get_table_name();
		
		$sql = "delete from {$table_name} where `pid`='{$pid}'";
		return $wpdb->query($sql);
	}


	/**
	 * 获取菜单数据
	 *
	 * @return array
	 */
	public function weixin_get_menu_data(){

		global $wpdb;
		$table_name = $this->get_table_name();

		$sql = "select `id`,`menu_name`,`menu_type`,`menu_key`, `menu_callback`, `menu_sort`,`pid`"
			." from {$table_name} order by `menu_sort` desc";
		$data  = $wpdb->get_results($sql);
		if(empty($data)){
			return false;
		}else{
			$arrs = array();
			foreach($data as $k=>$v){
				$arr['id'] = $v->id;
				$arr['menu_name'] = $v->menu_name;
				$arr['menu_type'] = $v->menu_type;
				$arr['menu_key'] = $v->menu_key;
				$arr['menu_callback'] = $v->menu_callback;
				$arr['menu_sort'] = $v->menu_sort;
				$arr['pid'] = $v->pid;
				$arrs[] = $arr;
			}
			return $arrs;
		}		
	}

	/**
	 * 获取一级菜单列表
	 *
	 * @return array
	 */
	public function weixin_get_menu_p_data(){
		global $wpdb;
		$table_name = $this->get_table_name();

		$sql = "select `id`,`menu_name`, `menu_type`, `menu_key`, `menu_callback`, `menu_sort`, `pid`"
			." from `{$table_name}` where `pid`='0' order by `menu_sort` desc";
		$data  = $wpdb->get_results($sql);
		if(empty($data)){
			return false;
		}else{
			$arrs = array();
			foreach($data as $k=>$v){
				$arr['id'] = $v->id;
				$arr['menu_name'] = $v->menu_name;
				$arr['menu_type'] = $v->menu_type;
				$arr['menu_key'] = $v->menu_key;
				$arr['menu_callback'] = $v->menu_callback;
				$arr['menu_sort'] = $v->menu_sort;
				$arr['pid'] = $v->pid;
				$arrs[] = $arr;
			}
			return $arrs;
		}		
	}

	/**
	 * 获取一级菜单下的列表
	 *
	 * @param int $id ID
	 * @return array
	 */
	public function weixin_get_menu_p_data_id($id){
		global $wpdb;
		$table_name = $this->get_table_name();

		$sql = "select `id`,`menu_name`, `menu_type`, `menu_key`, `menu_callback`,`menu_sort`, `pid`"
			." from {$table_name} where `pid`='{$id}' order by menu_sort desc";
		$data  = $wpdb->get_results($sql);
		if(empty($data)){
			return false;
		}else{
			$arrs = array();
			foreach($data as $k=>$v){
				$arr['id'] = $v->id;
				$arr['menu_name'] = $v->menu_name;
				$arr['menu_type'] = $v->menu_type;
				$arr['menu_key'] = $v->menu_key;
				$arr['menu_callback'] = $v->menu_callback;
				$arr['menu_sort'] = $v->menu_sort;
				$arr['pid'] = $v->pid;
				$arrs[] = $arr;
			}
			return $arrs;
		}		
	}

	/**
	 * 获取一级菜单总数
	 *
	 * @return int
	 */
	public function weixin_get_menu_p_count(){
		global $wpdb;
		$table_name = $this->get_table_name();
		
		$sql = "select count(id) as count from {$table_name} where `pid`='0'";
		$data = $wpdb->get_results($sql);
		return $data[0]->count;
	}

	/**
	 * 获取二级菜单总数
	 *
	 * @param int $id ID
	 * @return int
	 */
	public function weixin_get_menu_c_count($id){
		global $wpdb;
		$table_name = $this->get_table_name();
		
		$sql = "select count(id) as count from {$table_name} where `pid`='{$id}'";
		$data = $wpdb->get_results($sql);
		return $data[0]->count;
	}
	

}
?>
