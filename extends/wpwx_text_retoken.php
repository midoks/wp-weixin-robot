<?php
/**
 *  extend_name:重新获取token
 *  extend_url:http://midoks.cachecha.com/
 *	author: midoks
 *	version:0.1
 *	email:midoks@163.com
 *	description: 调试必用,呵呵(回复retoken,重新获取token)
 */
class wpwx_text_retoken{

	public $obj;

	public function __construct($obj){
		$this->obj = $obj;
	}

	public function start($kw){
		if('retoken' == $kw){
			$this->obj->getReToken();
			$token = $this->obj->getToken();
			return $this->obj->toMsgText($token);
		}
	}

}
