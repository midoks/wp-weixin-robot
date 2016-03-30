<?php
/**
 * WxRobot 入口类
 * 
 * @author 		Midoks
 * @category 	WxRobot
 * @package		WxRobot/Cmd
 * @since		5.3.0
 */

class WxRobot_Robot{

	/**
	 * WxRobot_Robot Instance
	 */
	public static $_instance = null;

	/**
	 * 构造函数
	 */
	public function __construct(){
		include_once('class-wx-functions.php');
		include_once('class-wx-cmd.php');
	}

	/**
	 * WxRobot 入口类实例化
	 * 
	 * @return WxRobot_Robot instance
	 */
	public static function instance(){
		if( is_null (self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * 机器人功能验证和返回信息
	 * 
	 * @return void
	 */
	public function valid(){
		if(isset($_GET['debug'])){
			header('Content-type: text/html;charset=utf-8');
			$this->responseMsg();
		}else{
			if($this->checkSignature()){
				$echoStr = (isset($_GET['echostr']))?$_GET['echostr']:'';
				if(!empty($echoStr)){
					echo $echoStr;
				}else{
					$this->responseMsg();
				}
			}else{
				//echo '验证未通过!!!';
				$this->responseMsg();
			}
		}
	}

	/**
	 * 验证消息真实性
	 *
	 * @return bool
	 */
	private function checkSignature(){
        $signature = $_GET['signature'];
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];

		$token = WEIXIN_TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
      
		if($tmpStr == $signature){
			return true;
		}else{
			return false;
		}
	}

	/**
	 *	返回响应信息
	 *
	 *	@return xml
	 */
	public function responseMsg(){
		echo WxRobot_Cmd::instance()->cmd();exit;
	}	

}
?>
