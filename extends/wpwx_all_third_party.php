<?php
/**
 *  extend_name:第三方托管(实例,测试)
 *  extend_url:http://midoks.cachecha.com/
 *	author: midoks
 *	version:1.0
 *	email:midoks@163.com
 *	description: 第三方托管(以我这个插件基础)
 */
class wpwx_all_third_party{

	private $obj = null;

	//构造函数
	public function __construct($obj){
		$this->obj = $obj;
		$this->url = 'http://www.weiphp.cn/index.php?s=/home/weixin/index.html';
	}

	//开始执行
	public function start($args){
		if($this->cmd_filters($args)){
			$data = $this->get_res_data();
			$url = $this->get_url();

			$e = $this->deliver($url, $data);
			if(!$e){
				return false;
				//return $this->obj->toMsgText('请求失败!!!');
			}
			return $e;
		}
		return false;
	}

	/**
	 * @func 命令过滤
	 * @param string $args 参数
	 * @ret bool
	 */
	public function cmd_filters($args){
		$kw = $args['Content'];
		$pkw = substr($kw, 0, 1);
		if(in_array($pkw, array('$', '#', '@', '?','p'))){
			return false;
		}

		if(in_array($kw, array('today','p?'))){
			return false;
		}

		if(in_array($pkw, array('n', 'h', 'r'))){
			$skw = substr($kw, 1);
			$skw = (int)$skw;
			if($skw>0 && $skw <=10){
				return false;
			}
		}
		//其他过滤
		return true;
	}

	/**
	 * @func 推送原来的数据
	 * @param string $url 传送数据
	 * @param string $data 传送数据
	 * @ret 返回数据 
	 */
	public function deliver($url, $data){
		$go = curl_init();
		curl_setopt($go, CURLOPT_URL, $url);
		curl_setopt($go, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($go, CURLOPT_MAXREDIRS, 5);
		curl_setopt($go, CURLOPT_HEADER, 0);
		curl_setopt($go, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($go, CURLOPT_TIMEOUT, 3);
		if(!empty($data)){//POST Data
			curl_setopt($go, CURLOPT_POST, 1);
			curl_setopt($go, CURLOPT_POSTFIELDS ,$data);
		}
		$response = curl_exec($go);
		curl_close($go);
		return $response;
	}


	public function default_url(){
		$url = $this->url.
			'&signature=786f054ada120e459426757946d5c24d36adc1d7&timestamp=1405333563&nonce=193938084';
		return $url;
	}


	public function get_url(){
		$url = $this->url;
		$url .= '&web=true';

		$sign = false;
		if(isset($_GET['signature'])){
			$url .= '&signature='.$_GET['signature'];
			$sign = true;
		}

		if(isset($_GET['echostr'])){
			$url .= '&echostr='.$_GET['echostr'];
		}

		if(isset($_GET['timestamp'])){
			$url .= '&timestamp='.$_GET['timestamp'];
			$sign = true;
		}

		if(isset($_GET['nonce'])){
			$url .= '&nonce='.$_GET['nonce'];
			$sign = true;
		}

		if(!$sign){
			return $this->default_url();
		}
		return $url;
	}

	//请注意这个函数!!!
	public function get_res_data(){
		if($data = file_get_contents('php://input')){
			return $data;
		}elseif(isset($GLOBALS['HTTP_RAW_POST_DATA'])){
			return $GLOBALS['HTTP_RAW_POST_DATA'];//POST数据
		}else{

$name = isset($_GET['kw'])? $_GET['kw']:'测试';
$ttt = <<<EOT
<xml><ToUserName><![CDATA[gh_aa1df1b1f411]]></ToUserName>
<FromUserName><![CDATA[oTGh6jlvP2S56YW2-7GJSl8UCo_0]]></FromUserName>
<CreateTime>1405322624</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[$name]]></Content>
<MsgId>6035814710609440344</MsgId>
</xml>
EOT;
			return $ttt;
		}
	}
}
?>
