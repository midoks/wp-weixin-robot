<?php
/**
 *	WxRobot 命令响应类
 *	
 *	@author 	midoks
 *	@category   WxRobot
 *	@package 	WxRobot/Cmd
 *  @since		5.3.0
 */

/**
 *	WxRobot_Cmd_Text 文本命令响应类
 */
class WxRobot_Cmd_Text{

	/**
	 * WxRobot_Cmd_Text Instance
	 */
	public static $_instance = null;

	/**
	 * 允许的命令列表
	 */
	public $list_cmd = array('today','n', 'h', 'r', '?');


	/**
	 * 构造函数
	 * 
	 * @return void
	 */
	public function __construct(){
		$this->wxSdk = WxRobot_SDK::instance();

		include_once(WEIXIN_ROBOT.'includes/class-wx-wp.php');
		$this->wp = WxRobot_Wp::instance();
	}

	/**
	 * WxRobot 文本消息处理类实例化
	 * 
	 * @return WxRobot_Cmd_Text instance
	 */
	public static function instance(){
		if( is_null (self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * 设置对象和关键字
	 *	
	 * @param object $obj 模板引用对象
	 * @param string $kw 关键字
	 * @return xml
	 */
	public function setValue($obj, $kw){
		$this->obj = $obj;
		$this->kw = $this->convert($kw);
	}

	/**
	 * 文本结果返回
	 *
	 * @return xml
	 */
	public function replay(){
		$kw = $this->kw;

		if($kw == '?'){
			return $this->wxSdk->toMsgText($this->obj->options['weixin_robot_helper']);//显示帮助信息
		}
	
		if($wp_cmd = $this->wordpress_cmd($kw)){
			return $wp_cmd;
		}

		if($user_cmd = $this->user_keyword_cmd($kw)){
			return $user_cmd;
		}

		if($wx_extends = $this->obj->extends->dealwith('text', $kw)){
			return $wx_extends;
		}

		return $this->wxSdk->toMsgText($this->obj->options['weixin_robot_helper']);//显示帮助信息;//显示帮助信息
	}

	/**
	 * 多个关键字提取,(建议关键字不要重叠,自己思量,ID降序)
	 *
	 * @param string $kw 请求的文本
	 * @param string $keyword 自己设定的关键子
	 * @return bool
	 */
	private function user_keyword_cmd_multi($kw, $keyword){
		$list1 = explode(',', $keyword);
		foreach($list1 as $v){
			if($kw==$v) return true;
		}
		if($kw==$keyword) return true;
		return false;
	}

	/**
	 * 外部命令回复
	 *
	 * @return bool
	 */
	private function internal_cmd($reply){
		if(in_array($reply, $this->list_cmd) || in_array(strtolower(substr($reply, 0, 1)), $this->list_cmd)){
			return true;
		}else{
			return false;
		}	
	}

	/**
	 * 用户自定义关键字回复
	 *
	 * @param $kw 关键字
	 * @return $mixed
	 */
	public function user_keyword_cmd($kw){
		$data = WxRobot_Table_Reply::instance()->weixin_get_relpy_data($kw);

		if(!empty($data)){
			$arr = array();
			foreach($data as $k=>$v){
				if($this->user_keyword_cmd_multi($kw, $v['keyword']) && 'text' == $v['type']){
					if($this->internal_cmd($v['relpy'])){
						return $this->wordpress_cmd($v['relpy']);
					}else{
						return $this->wxSdk->toMsgText($v['relpy']);
					}
				}else if($this->user_keyword_cmd_multi($kw, $v['keyword']) && 'id' == $v['type']){
					if($this->internal_cmd($v['relpy'])){
						return $this->wordpress_cmd($v['relpy']);//这是为兼容错误
					}else{
						if(count($idsc = explode(',', $v['relpy']))>1){
							$data = $this->wp->Qids($idsc);
						}else{
							$data = $this->wp->Qid(trim($v['relpy'],','));
						}
						return $data;
					}
				}else if( $this->user_keyword_cmd_multi($kw, $v['keyword']) && 'music' == $v['type']){//推荐使用插件实现..
					if(!empty($v['relpy'])){
						$mlist = explode('|', $v['relpy']);
						if('3'==count($mlist)){
							//title
							if(empty($mlist[0])){
								$title = $kw;
							}else{
								$title = $mlist[0];
							}
							//desc
							if(empty($mlist[1])){
								$desc = date('Y-m-d H:i:s');
							}else{
								$desc = $mlist[1];
							}
							//relpy
							if(empty($mlist[2])){
								return false;
							}else{
								$relpy = $mlist[2];
							}

							return $this->wxSdk->toMsgMusic($title, $desc, $relpy, $relpy);
						}
						return false;
						
					}else{
						return false;
					}
				}
			}
			return $arr;
		}
		return false;
	}

	/**
	 * WordPress 相关命令数据
	 *
	 * @return array
	 */
	public function wordpress_cmd($kw){
		$prefix = substr($kw, 0, 1);
		$suffix = substr($kw, 1);
		$result = '';
		
		$int = intval($suffix);
		if($int<1){
			$int = 5;
		}else if($int >10){
			$int = 10;
		}

		//大小写不区分
		$prefix = strtolower($prefix);
		switch($prefix){
			case 'n': $result = $this->wp->news($suffix);break;
			case 'h': $result = $this->wp->hot($suffix);break;
			case 'r': $result = $this->wp->rand($suffix);break;
			default: break;
		}

		if(!empty($result)){
			return $result;
		}
		switch($kw){
			case 'today': $result = $this->wp->today();break;
		}
		return $result;
	}

	/**
	 * 字符串半角和全角间相互转换
	 *
	 * @param 	string 	$str 待转换的字符串
	 * @param 	int 	$type TODBC:转换为半角；TOSBC，转换为全角
	 * @return 	string
	 */
	public function convert($str, $type = 'TOSBC'){
		$dbc = array('！','？','。','＠','＃','＄');
		$sbc = array('!', '?', '.', '@', '#', '$');
		if($type == 'TODBC'){
			return str_replace($sbc, $dbc, $str); //半角到全角
		}elseif($type == 'TOSBC'){
			return str_replace($dbc, $sbc, $str); //全角到半角
		}else{
			return false;
		}
	}
}
?>
