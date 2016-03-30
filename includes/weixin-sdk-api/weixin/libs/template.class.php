<?php

class Weixin_Template{

	public $time;

	public function __construct(){
		$this->time = time();
	}

	public function toMsgText($fromUserName, $toUserName, $Msg){
		$text = <<<EOF
<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[text]]></MsgType>
	<Content><![CDATA[%s]]></Content>
</xml>
EOF;
		$resultStr = sprintf($text, $fromUserName, $toUserName, $this->time, $Msg);
		return $resultStr;
	}

	public function toMsgImage($fromUserName, $toUserName, $MediaId){
		$text = <<<EOF
<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[image]]></MsgType>
	<Image>
		<MediaId><![CDATA[%s]]></MediaId>
	</Image>
</xml>
EOF;
		$resultStr = sprintf($text, $fromUserName, $toUserName, $this->time, $MediaId);
		return $resultStr;
	}

	public function toMsgVoice($fromUserName, $toUserName, $MediaId){
	
		$text = <<<EOF
<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[voice]]></MsgType>
	<Voice>
		<MediaId><![CDATA[%s]]></MediaId>
	</Voice>
</xml>
EOF;
		$resultStr = sprintf($text, $fromUserName, $toUserName, $this->time, $MediaId);
		return $resultStr;
	}

	public function toMsgVideo($fromUserName, $toUserName, $MediaId, $Title, $Description){
		$text = <<<EOF
<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[video]]></MsgType>
	<Video>
		<MediaId><![CDATA[%s]]></MediaId>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
	</Video> 
</xml>
EOF;
		$resultStr = sprintf($text, $fromUserName, $toUserName, $this->time, $MediaId, $Title, $Description);
		return $resultStr;
	}

	public function toMsgMusic($fromUserName, $toUserName, $Title, $Description, $MusicUrl, $HQMusicUrl, $ThumbMediaId){
		$text = <<<EOF
<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[music]]></MsgType>
	<Music>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		<MusicUrl><![CDATA[%s]]></MusicUrl>
		<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
	</Music>
</xml>
EOF;
		$resultStr = sprintf($text, $fromUserName, $toUserName, $this->time, $Title, $Description, $MusicUrl, $HQMusicUrl, $ThumbMediaId);
		return $resultStr;
	}


	public function toMsgMusicId($fromUserName, $toUserName, $Title, $Description, $MusicUrl, $HQMusicUrl, $ThumbMediaId){
		$text = <<<EOF
<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[music]]></MsgType>
	<Music>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		<MusicUrl><![CDATA[%s]]></MusicUrl>
		<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
		<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
	</Music>
</xml>
EOF;
		$resultStr = sprintf($text, $fromUserName, $toUserName, $this->time, $Title, $Description, $MusicUrl, $HQMusicUrl, $ThumbMediaId);
		return $resultStr;
	}

	public function toMsgNews($fromUserName, $toUserName, $News){
		if(empty($News))
			exit('send news message not null!!!');

		$item = <<<EOF
		<item>
			<Title><![CDATA[%s]]></Title>
			<Description><![CDATA[%s]]></Description>
			<PicUrl><![CDATA[%s]]></PicUrl>
			<Url><![CDATA[%s]]></Url>
		</item>
EOF;
		$items = '';
		foreach($News as $k=>$v){
			$items .= sprintf($item, $v['title'], $v['desc'], $v['pic'], $v['link'])."\r\n"; 
		}

		$new = <<<EOF
<ArticleCount>%s</ArticleCount>
	<Articles>
%s
	</Articles>
EOF;
		$num = count($News);
		$new = sprintf($new, $num, $items);

		$text = <<<EOF
<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[news]]></MsgType>
	%s
</xml>
EOF;
		$resultStr = sprintf($text, $fromUserName, $toUserName, $this->time, $new);
		return $resultStr;
	}
}
