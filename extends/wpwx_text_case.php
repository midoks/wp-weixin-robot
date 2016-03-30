<?php
/**
 *  extend_name: 插件开发例子
 *  extend_url:http://midoks.cachecha.com/
 *	author: midoks
 *	version:0.1
 *	email:midoks@163.com
 *	description: 开发你自己的扩展吧
 */
class wpwx_text_case{

	public $obj;

	public function __construct($obj){
		$this->obj = $obj;
	}

	public function start($kw){
		if('ok' == $kw){
			return $this->obj->toMsgText('case ok!!!');
		}
	}

	/**
	 * 后台控制
	 */
	public function admin(){
		$this->obj->admin_menu($this, 'wx_case', '插件开发例子', 'wx_case');
	}

	public function wx_case(){
		echo 'case ok!!';exit;
	}

	/**
	 * 前端显示
	 */
	public function frontend(){
		
		//var_dump($this->obj->getAppSelect());exit;
		//echo 'frontend case';
	}

}
