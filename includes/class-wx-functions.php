<?php
/**
 * WxRobot CoreFunctions
 * 
 * WP微信机器人核心方法
 *
 * @author 		midoks
 * @category	CoreFunction
 * @package		WxRobot/CoreFunctions
 * @since		5.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 把XML转化为数组
 *
 * @param string $xml XML数据
 * @return array
 */
function wx_parse_xml($xml){
	$array = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
	return (Array)$array;
}

/**
 * 随机获取大图片地址
 *
 * @return url
 */
function wx_random_big_pic(){
	return WEIXIN_ROBOT_URL.'/assets/img/640_320/'.mt_rand(1,5).'.jpg';
}

/**
 * 随机获取小图地址
 *
 * @return url
 */
function wx_random_small_pic(){
	return WEIXIN_ROBOT_URL.'/assets/img/80_80/'.mt_rand(1,10).'.jpg';
}


/**
 *	判断是否时xml数据
 *
 *	@param string $xml 检查数据
 *	@return bool
 */
function wx_is_xml($xml){
	if(substr($xml, 0, strlen('<xml>')) == '<xml>'){
		return true;
	}
	return false;
}

/**
 * 获取微信请求的XML数据
 *
 * @return xml
 */
function wx_request_xml(){

	if(!empty($GLOBALS["HTTP_RAW_POST_DATA"])){
		return $GLOBALS["HTTP_RAW_POST_DATA"];
	}else{
		return file_get_contents('php://input');
	}
}


/**
 * 检查是否是否加密 
 *
 * @return bool
 */
function wx_request_is_encode(){
	$result_xml = wx_request_xml();
	$result = wx_parse_xml($result_xml);
	if(isset($result['Encrypt'])){
		return true;
	}
	return false;
}


/**
 * 解析微信请求的数据
 *
 * @return array
 */
function wx_request_array(){
	
	$options = get_option(WEIXIN_ROBOT_OPTIONS);

	//测试地址:midoks.duapp.com/?midoks&debug=1
	if($options['weixin_robot_debug'] == 'true' && (isset($_GET['debug']) || $_GET['debug'] == '1')){
		$info['MsgType'] = 'text';//text,event,
		$info['FromUserName'] = 'userid';
		$info['ToUserName'] = 'openid';
		$info['CreateTime'] = time();
		$info['Content'] = (isset($_GET['kw']))?$_GET['kw']:'?';
		
		//事件名
		//$info['MsgType'] = 'event';//text,event,
		//$info['Event'] = 'subscribe';
		//$info['EventKey'] = 'MENU_1444226908';
		//
		//$info['Location_X'] = 'Location_X';
		//$info['Location_Y'] = 'Location_Y';
		//$info['Scale'] = 'Scale';
		//$info['Label'] = 'Label';
		return $info;
	}

	$result_xml = wx_request_xml();

	if(!wx_is_xml($result_xml)){
		return false;
	}


	if(!empty($result_xml)){
		$result = wx_parse_xml($result_xml);
		if(isset($result['Encrypt'])){
			$result = wx_request_decode($result_xml);
			return  wx_parse_xml($result);
		}
		return $result;
	}
	return false;
}


/**
 * 编码发送到微信的信息
 *
 * @param xml $text 要编码的数据
 * @return xml
 */
function wx_send_encode($text){
	$token 		= WEIXIN_TOKEN;
	$options 	= get_option(WEIXIN_ROBOT_OPTIONS);
	
	$encodingAesKey = $options['EncodingAESKey'];
	$appId = $options['ai'];
	$pc = new WXBizMsgCrypt($token, $encodingAesKey, $appId);
		
	$timeStamp = time();
	$encryptMsg = '';
	$nonce = $_GET['nonce'];
	$retCode = $pc->encryptMsg($text, $timeStamp, $nonce, $encryptMsg);
	if($retCode == 0){
		return $encryptMsg;
	}
	return $text;
}

/**
 * 解码微信发送来的信息
 *
 * @param xml $text 要解码的数据  
 * @return xml
 */
function wx_request_decode($text){
	$token 		= WEIXIN_TOKEN;
	$options 	= get_option(WEIXIN_ROBOT_OPTIONS);
	$info 		= wx_parse_xml($text);	

	$encodingAesKey = $options['EncodingAESKey'];
	$appId = $options['ai'];
	$pc = new WXBizMsgCrypt($token, $encodingAesKey, $appId);

	$timeStamp = time();
	$nonce = $_GET['nonce'];

	$sha1 = new SHA1;
	$array = $sha1->getSHA1($token, $timeStamp, $nonce, $info['Encrypt']);
	$ret = $array[0];
	if ($ret != 0) {
		return $ret;
	}
	$msg_sign = $array[1];
		
	$retCode = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $text, $msg);
	if($retCode == 0){
		return $msg;
	}
	return $text;
}

?>
