<?php
/**
 * 微信SDK
 *
 * @author		midoks
 * @category 	WxSDK
 * @package 	
 */

define('WEIXIN_SDK', str_replace('\\', '/', dirname(__FILE__)).'/');
defined('WEIXIN_DEBUG') or define('WEIXIN_DEBUG', true);//默认关闭

class WeiXin_SDK{

	public $template;
	public $base;
	public $app_id;
	public $app_sercet;

	/**
	 * WxRobot_SDK Instance
	 */
	public static $_instance = null;


	/**
	 * WxRobot SDK类实例化
	 * 
	 * @return WxRobot_SDK instance
	 */
	public static function instance(){
		if( is_null (self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	//构造函数
	public function __construct($AppId='', $AppSecret=''){

		$this->app_id = $AppId;
		$this->app_sercet = $AppSecret;

		include_once(WEIXIN_SDK.'libs/base.class.php');
		include_once(WEIXIN_SDK.'libs/template.class.php');

		$this->template = new Weixin_Template();
		$this->base = new Weixin_BaseCore();
	}

//no appkey and appsercet

	// response message (text)	
	public function toMsgText($fromUserName, $toUserName, $Msg){
		return $this->template->toMsgText($fromUserName, $toUserName, $Msg);
	}

	// response message (image)	
	public function toMsgImage($fromUserName, $toUserName, $MediaId){
		return $this->template->toMsgImage($fromUserName, $toUserName, $MediaId);
	}

	// response message (voice)	
	public function toMsgVoice($fromUserName, $toUserName, $MediaId){
		return $this->template->toMsgVoice($fromUserName, $toUserName, $MediaId);
	}

	// response message (music)	
	public function toMsgMusic($fromUserName, $toUserName, $Title, $Description, $MusicUrl, $HQMusicUrl, $ThumbMediaId=''){
		if(empty($ThumbMediaId)){
			return $this->template->toMsgMusic($fromUserName, $toUserName, $Title, $Description, $MusicUrl, $HQMusicUrl, $ThumbMediaId);
		}else{
			return $this->template->toMsgMusicId($fromUserName, $toUserName, $Title, $Description, $MusicUrl, $HQMusicUrl, $ThumbMediaId);
		}
	}

	// response message (video)	
	public function toMsgVideo($fromUserName, $toUserName, $MediaId, $Title, $Description){
		return $this->template->toMsgVideo($toUserName, $fromUserName, $MediaId, $Title, $Description);
	}

	// response message (news)	
	public function toMsgNews($fromUserName, $toUserName, $News){
			return $this->template->toMsgNews($fromUserName, $toUserName, $News);
	}
//END TO MSG


//have appkey and appsercet
	/**
	 *	get token
	 *  @ret json
	 */
	public function getToken(){
		$app_id = $this->app_id;
		$app_sercet = $this->app_sercet;
		return $this->base->getToken($app_id, $app_sercet);
	}

	public function pushMsgText($token, $open_id, $msg){
		return $this->base->pushMsgText($token, $open_id, $msg);
	}

	public function pushMsgImage($token, $open_id, $media_id){
		return $this->base->pushMsgImage($token, $open_id, $media_id);
	}

	public function pushMsgImageAdv($token, $open_id, $file){
		$ret = $this->upload($token, 'image', $file);
		if(!$ret){
			return '{errcode: "network imeout"}';
		}
		$data = json_decode($ret, true);
		if(isset($data['errcode'])){
			return $ret;
		}
		$ret = $this->pushMsgImage($token, $open_id, $data['media_id']);
		return $ret;
	}

	public function pushMsgVoice($token, $open_id, $media_id){
		return $this->base->pushMsgVoice($token, $open_id, $media_id);
	}

	public function pushMsgVoiceAdv($token, $open_id, $file){
		$ret = $this->upload($token, 'voice', $file);
		if(!$ret){
			return '{errcode: "network imeout"}';
		}
		$data = json_decode($ret, true);
		if(isset($data['errcode'])){
			return $ret;
		}
		$ret = $this->pushMsgVoiceAdv($token, $open_id, $data['media_id']);
		return $ret;
	}

	public function pushMsgVideo($token, $open_id, $media_id, $title, $desc){
		return $this->base->pushMsgVoice($token, $open_id, $media_id, $title, $desc);
	}

	public function pushMsgVideoAdv($token, $open_id, $file, $title, $desc){
		$ret = $this->upload($token, 'voice', $file);
		if(!$ret){
			return '{errcode: "network imeout"}';
		}
		$data = json_decode($ret, true);
		if(isset($data['errcode'])){
			return $ret;
		}
		$ret = $this->pushMsgVoice($token, $open_id, $data['media_id'], $title, $desc);
		return $ret;
	}

	public function pushMsgMusic($token, $open_id, $thumb_media_id, $title, $desc, $musicurl, $hqmusicurl){
		return $this->base->pushMsgMusic($token, $open_id, $thumb_media_id, $title, $desc, $musicurl, $hqmusicurl);
	}

	public function pushMsgMusicAdv($token, $open_id, $file, $title, $desc, $musicurl, $hqmusicurl){
		$ret = $this->upload($token, 'voice', $file);
		if(!$ret){
			return '{errcode: "network imeout"}';
		}
		$data = json_decode($ret, true);
		if(isset($data['errcode'])){
			return $ret;
		}
		$ret = $this->pushMsgVoice($token, $open_id, $data['media_id'], $title, $desc, $musicurl, $hqmusicurl);
		return $ret;
	}

	/**
	 * @exp: $info should be:
	 *		$info[]["title"] = "Happy Day";
     *      $info[]["description"]="Is Really A Happy Day";
     *      $info[]["url"] = "URL";
     *      $info[]["picurl"] = "PIC_URL";
	 */
	public function pushMsgNews($token, $open_id, $info){
		return $this->base->pushMsgNews($token, $open_id, $info);
	}
///END PUSH



/**
 *  多客服系统接口 start 
 *	多客服系统是在的插件中使用!!!
 */
	
	/**
	 *	@func 获取客服聊天记录
	 *	@param $token		调用接口凭证
	 *	@param $open_id		普通用户的标识，对当前公众号唯一
	 *	@param $starttime	查询开始时间，UNIX时间戳
	 *	@param $endtime		查询结束时间，UNIX时间戳，每次查询不能跨日查询
	 *	@param $pagesize	每页大小，每页最多拉取1000条
	 *	@param $pageindex	查询第几页，从1开始	 
	 */
	public function getCustomServiceLog($token, $open_id, $starttime, $endtime, $pagesize=20, $pageindex=1){
		return $this->base->getCustomServiceLog($token, $open_id, $starttime, $endtime, $pagesize, $pageindex);
	}
/* 多客服系统接口 end */


//END PUSH ADV

	//menu setting
	public function menuGet($token){
		return $this->base->menuGet($token);
	}

	public function menuSet($token, $json){
		return $this->base->menuSet($token, $json);
	}

	public function menuDel($token){
		return $this->base->menuDel($token);
	}

//

//智能接口

	//智能接口分析
	public function getSemantic($token, $query, $category, $lat, $long, $city, $region, $appid, $uid){
		return $this->base->getSemantic($token, $query, $category, $lat, $long, $city, $region, $appid, $uid);
	}

//uplaod and download

	public function download($token, $media_id){
		return $this->base->download($token, $media_id);
	}

	public function upload($token, $type, $file){
		if(in_array($type, array('image', 'voice', 'video', 'thumb'))){
			return $this->base->upload($token, $type, $file);
		}else{
			return $this->notice('upload: invalid media type', __LINE__);
		}	
	}

	public function uploadUrl($token, $type, $fn, $mime, $content){
		if(in_array($type, array('image', 'voice', 'video', 'thumb'))){
			return $this->base->uploadUrl($token, $type, $fn, $mime, $content);
		}else{
			return $this->notice('upload: invalid media type', __LINE__);
		}
	}

//user info 
	public function getUserInfo($token, $open_id, $lang='zh_CN'){
		return $this->base->getUserInfo($token, $open_id, $lang);
	}

	public function getUserList($token, $next_openid){
		return $this->base->getUserList($token, $next_openid);
	}

	public function setUserGroup($token, $json){
		return $this->base->setUserGroup($token, $json);
	}

	public function getUserGroup($token){
		return $this->base->getUserGroup($token);
	}

	public function getUserGroupPosition($token, $json){
		return $this->base->getUserGroupPosition($token, $json);
	}

	public function modUserGroup($token, $json){
		return $this->base->modUserGroup($token, $json);
	}

	public function movUserGroup($token, $json){
		return $this->base->movUserGroup($token, $json);
	}

	public function updateRemark($token, $open_id, $remark){
		return $this->base->updateRemark($token, $open_id, $remark);
	}

//推送消息

/* 推广相关 start */

	//创建二维码ticket
	public function temp_ticket($token, $json){
		return $this->base->temp_ticket($token, $json);	
	}

	//创建永久二维码ticket
	public function permanent_ticket($token, $json){
		return $this->base->permanent_ticket($token, $json);
	}

	//返回路径
	public function get_ticket($ticket){
		return $this->base->get_ticket($ticket);
	}

	//把长连接转换为短链接
	public function long2short($token, $url){
		return $this->base->long2short($token, $url);
	}


/* 推广相关 end */
	
	//上传图文消息素材	
	public function uploadMsgImageText($token, $msg){
		return $this->base->uploadMsgImageText($token, $msg);
	}

	//通过分组进行群发
	public function sendAllByGroup($token, $group_id, $media_id, $msgtype){
		return $this->base->sendAllByGroup($token, $group_id, $media_id, $msgtype);
	}

	//根据OpenID列表群发
	public function sendAll($token, $user, $media_id, $msgtype){
		return $this->base->sendAll($token, $user, $media_id, $msgtype);
	}

	//删除群发信息
	public function deleteSend($token, $id){
		return $this->base->deleteSend($token, $id);
	}

	//发送模版消息
	public function sendTemplateInfo($token, $json){
		return $this->base->sendTemplateInfo($token, $json);
	}

	//获取微信地址
	public function getWeixinIp($token){
		return $this->base->getWeixinIp($token);
	}

	//处理后的JSON
	public function to_json($arr){
		return $this->base->to_json($arr);
	}

	//提示
	public function notice($msg, $line){
		if(WEIXIN_DEBUG){
			$this->logs($msg, $line);
		}else{
			trigger_error($msg);
		}
		return $msg;
	}

	public function logs($msg, $line){
		$fn = WEIXIN_SDK.'logs/'.date('Y-m-d').'.log';
		$handler = fopen($fn, 'a');
		fwrite($handler, 'error: in '.__FILE__.' at '.$line.' line '.$msg."\t".date('Y-m-d H:i:s')."\r\n");
		fclose($handler);
	}
}
?>
