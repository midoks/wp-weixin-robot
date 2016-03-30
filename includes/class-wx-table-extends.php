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
 *	WxRobot_Table_Extends 扩展表模型
 */
class WxRobot_Table_Extends{

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
	 * 获取所有可用的插件
	 * 
	 * @return array
	 */
	public function get_all_plugins(){
		$a = array();
		if($h = opendir(WEIXIN_PLUGINS)){
			while($f = readdir($h)){
				if($f =='.' || $f=='..'){
				}else if(is_file(WEIXIN_PLUGINS.$f)){
					if('php' == $this->get_file_suffix($f)){
						$d = WEIXIN_PLUGINS.$f;
						$data = $this->get_plugins_info($d);
						if(!$data){
							continue;
						}
						$a['info'][] = $data;
						$a['abspath'][] = $d;
						$a['path'][] = $f;
						$q = explode('_', $f);
						$a['type'][] = $q[1];
						$b = explode('.', $f);
						$a['classname'][] = $b[0];
					}
				}
			}
		}
		return $a;
	}

	/**
	 *	获取插件的信息
	 *	
	 *	@param string $file 插件位置
	 *	@return array
	 *	{
	 *		extend_name:扩展名称
	 *  	plugin_url:开发扩展的地址
	 *		author: 作者
	 *		version:版本信息
	 *		email:邮件地址
	 *		description: 描述信息
	 *	}
 	 */
	private function get_plugins_info($file){
		$content = file_get_contents($file);
		preg_match('/\/\*(.*?)\*\//is', $content, $info);

		if(!isset($info[1])){
			return false;
		}

		$e = trim(trim($info[1]), '*');
		$list = explode("\n", $e);
		$nString = array();

		foreach($list as $k=>$v){
			$tmp = trim(str_replace(array('*', ' '), '', $v));
			
			//分割":"、 " "
			$tmp_E = explode(' ', $tmp, 2);
			if(count($tmp_E)<2){
				$tmp_E = explode(':', $tmp, 2);
			}
			
			if(!empty($tmp_E[0])){
				$nString[strtolower($tmp_E[0])] = trim($tmp_E[1]);
			}
		}

		//扩展名称(必选)
		if(!isset($nString['extend_name'])){
			return false;
		}

		//扩展地址(必选)
		if(!isset($nString['extend_url'])){
			return false;
		}

		//作者昵称(必选)
		if(!isset($nString['author'])){
			return false;
		}

		//扩展版本信息(必选)
		if(!isset($nString['version'])){
			return false;
		}

		//扩展联系邮件地址(必选)
		if(!isset($nString['email'])){
			return false;
		}

		//扩展描述信息
		if(!isset($nString['description'])){
			return false;
		}
		return $nString;
	}

	/**
	 * 获取文件的后缀
	 *
	 * @param string $file 文件名
	 * @return string
	 */
	private function get_file_suffix($file){
		$l = explode('.', $file);
		$c = count($l);
		return $l[$c-1];
	}

	/**
	 * 获取表名
	 *
	 * @return string
	 */
	private function get_table_name(){
		return $this->table_prefix.'weixin_robot_extends';
	}


	/**
	 * 获取已经启动的扩展
	 *
	 * @return mixed
	 */
	public function select_extends(){
		global $wpdb;
		$table_name = $this->get_table_name();
		$sql = "select `id`,`ext_name`,`ext_type`,`ext_int` from `{$table_name}`";
		$data  = $wpdb->get_results($sql);
		if($data){
			$ret = array();
			foreach($data as $k=>$v){
				$a['ext_name'] = $v->ext_name;
				$b = explode('.',$v->ext_name);
				$a['ext_cn'] = $b[0];
				$a['ext_type'] = $v->ext_type;
				$ret[] = $a;
			}
			return $ret;
		}
		return false;
	}


	/**
	 * 检查扩展是否存在
	 *
	 * @param string $name 扩展名
	 * @return bool 
	 */
	public function select_extends_name($name){
		global $wpdb;
		$table_name = $this->get_table_name();
		$sql = "select `id`,`ext_name`,`ext_type`,`ext_int` from `{$table_name}` where ext_name='{$name}'";
		$result = $wpdb->query($sql);
		return $result;
	}

	/**
	 *	获取启动指定类型的扩展
	 *
	 *	@param string $type 扩展的类型
	 *  @return mixed
	 */
	public function select_extends_type($type){
		global $wpdb;
		$table_name = $this->get_table_name();
		$sql = "select `id`,`ext_name`,`ext_type`,`ext_int` from `{$table_name}` where ext_type='{$type}'";
		$data = $wpdb->get_results($sql);
		if($data){
			$ret = array();
			foreach($data as $k=>$v){
				$a['ext_name'] = $v->ext_name;
				$a['ext_type'] = $v->ext_type;
				$ret[] = $a;
			}
			return $ret;
		}
		return false;
	}

	/**
	 * 添加扩展
	 *
	 * @param string $ext_name 扩展名
	 * @param string $ext_type 扩展类型
	 * @param string $ext_int  扩展是否启动
	 * @return bool
	 */
	public function insert_extends($ext_name, $ext_type, $ext_int){
		global $wpdb;
		$table_name = $this->get_table_name();
		$sql = "INSERT INTO `{$table_name}` (`id`, `ext_name`, `ext_type`, `ext_int`)"
			." VALUES(null,'{$ext_name}','{$ext_type}','{$ext_int}')";
		return $wpdb->query($sql);
	}

	/**
	 * 在数据库删除扩展
	 *
	 * @parma stirng $name 扩展名
	 * @return bool
	 */
	public function delete_extends_name($name){
		global $wpdb;
		$table_name = $this->get_table_name();
		$sql = "delete from {$table_name} where `ext_name`='{$name}'";
		return $wpdb->query($sql);
	}

	/**
	 * 获取扩展文件名
	 *
	 * @parma string $f 扩展绝对地址
	 * @return bool
	 */
	private function _c($f){
		if(!file_exists($f)){
			$fn = basename($f);
			$this->delete_extends_name($fn);
			return false;
		}else{
			include_once($f);
			return true;
		}
	}

	/**
	 * 扩展安装
	 * 
	 * @param string $fn 扩展名
	 * @param void
	 */
	public function install($fn){
		$abspath = WEIXIN_PLUGINS.$fn;
		if($this->_c($abspath)){
			$tt = explode('.', $fn);
			$cn = $tt[0];
			if(!class_exists($cn)){
				wx_notice_msg('此文件名和类名不一致!!!');exit;
			}
			$obj = new $cn($this);
			if(method_exists($obj, 'install')){
				return $obj->install();
			}
		}
	}

	/**
	 * 扩展卸载
	 *
	 * @param string $fn 扩展名
	 * @return void
	 */
	public function uninstall($fn){
		$abspath = WEIXIN_PLUGINS.$fn;
		if($this->_c($abspath)){
			$tt = explode('.', $fn);
			$cn = $tt[0];
			$obj = new $cn($this);
			if(method_exists($obj, 'uninstall')){
				return $obj->uninstall();
			}
		}
	}
	

	

}
?>
