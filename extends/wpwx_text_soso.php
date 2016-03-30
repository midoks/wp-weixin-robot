<?php
/**
 *  extend_name:关键字搜索功能
 *  extend_url:http://midoks.cachecha.com/
 *	author: midoks
 *	version:0.1
 *	email:midoks@163.com
 *	description: 把以前的功能移动过来,但是我只想实现了一部分。
 */
class wpwx_text_soso{

	public $obj;

	public function __construct($obj){
		$this->obj = $obj;
	}

	public function start($kw){
		if(!empty($kw)){
			$result = $this->keySoso($kw);

			if(!empty($result)){

				$info = array();
				foreach($result as $k=>$v){

					if($k==0){
						$a['desc'] 	= WxRobot_Wp::instance()->head_one_line($v->post_content);
						$a['pic'] 	= WxRobot_Wp::instance()->get_opt_pic_big($v->post_content);
					}else{
						$a['desc'] = $v->post_title;
						$a['pic'] 	= WxRobot_Wp::instance()->get_opt_pic_small($v->post_content);
					}

					$a['title'] = $v->post_title;
					$a['link'] 	= $v->guid;
					
					$info[] = $a;
					$a = array();
				}
				return $this->obj->toMsgTextPic($info);
			}


			return false;
		}
	}


	/**
	 * 对每个第一条消息,进行处理
	 *
	 * @param string $c 内容
	 * @return string 
	 */
	public function head_one_line($c){
		$c = html_entity_decode($c, ENT_NOQUOTES, 'utf-8');
		$c = strip_tags($c);
		$c = mb_substr($c, 0, 50, 'utf-8').'...';
		return $c;
	}

	/**
	 *	@func 关键查询
	 *	@param string $k 关键字
	 *	@ret array
	 */
	public function keySoso($kw){
		global $wpdb;
	
		$sql = "SELECT p.ID,p.post_title,p.guid,p.post_content from {$wpdb->posts} p ".
			"where p.post_status='publish' ".
			"and 1=1 ".
			//关键字处
			"and ((p.post_title like '%{$kw}%') ".
			"or (p.post_content like '%{$kw}%'))".
			"order by p.id desc limit 10";

		$result = $wpdb->get_results($sql);
		return $result;
	}

}
