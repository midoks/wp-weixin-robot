<?php
/**
 * WxRobot_Wp WordPress文章查询相关功能
 *
 * @author 		midoks
 * @category 	WxRobot
 * @package		WxRobot/Table
 * @since		5.3.0
 */

/**
 * WxRobot_Wp WordPress文章查询相关功能
 */
class WxRobot_Wp{


	public $opt_big_show = array();
	public $opt_small_show = array();


	/**
	 * 构造函数
	 */
	public function __construct(){
		//最优图片选择是否开启
		$this->options 	= get_option(WEIXIN_ROBOT_OPTIONS);
		$this->obj 		= WxRobot_SDK::instance();
		
		if($this->options['opt_pic_show']){
			$this->opt_pic_sign = true;
			$this->option_pic_to_array();

		}else{
			$this->opt_pic_sign = false;
		}
	}

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
	 * 把配置中图片变成变为数据处理
	 * 
	 * @return void
	 */
	public function option_pic_to_array(){
		//小图
		$small = $this->options['opt_small_show'];
		if(!empty($small)){
			$this->opt_small_show = false;
		}
		$s_arr = explode("\r\n", $small);
		$tmp = array();
		foreach($s_arr as $k=>$v){
			$tmp[] = trim($v);
		}
		$this->opt_small_show = $tmp;
		//大图
		$big = $this->options['opt_big_show'];
		if(!empty($big)){
			$this->opt_big_show = false;
		}
		$s_arr = explode("\r\n", $big);
		$tmp = array();
		foreach($s_arr as $k=>$v){
			$tmp[] = trim($v);
		}
		$this->opt_big_show = $tmp;
	}

	/**
	 * 对中文名的图片路径进行urlencode编码
	 *
	 * @return string $thumb 图片路径
	 * @return string
	 */
	public function path_url_encode($thumb){
		$pos = strrpos($thumb,'/');
		return substr($thumb, 0,$pos+1).urlencode(substr($thumb, $pos+1));
	}

	/**
	 * 获取文章中的图片
	 *
	 * @param string $c 文章内容
	 * @param string $type 图片类型
	 * @return string
	 */
	public function get_opt_pic($c, $type){
		$u2 = '/(<img[^>]+src\s*=\s*\"?([^>\"\s]+)\"?[^>]*>)/im';
		//echo $u2;
		$p_sign = preg_match($u2 ,$c, $match);
		if($p_sign){
			return $this->path_url_encode($match[2]);
		}

		//上面执行过,选择默认自定义的图片
		if('small' == $type){
			$num = count($this->opt_small_show);
			$t = $num - 1;
			$mt = mt_rand(0, $t);
			if($num){
				return $this->opt_small_show[$mt];
			}
		}else if('big' == $type){
			$num = count($this->opt_big_show);
			$t = $num - 1;
			$mt = mt_rand(0, $t);
			if($num){
				return $this->opt_big_show[$mt];
			}
		}
		return false;
	}


	/**
	 *	获取最优图片地址
	 *
	 *	@param string $content 文章内容
	 *	@return string
	 */
	public function get_opt_pic_small($content = ''){
		if($this->opt_pic_sign){
			$pic = $this->get_opt_pic($content, 'small');
			if(!empty($pic)){
				return $pic;
			}
		}
		return wx_random_small_pic();
	}

	/**
	 *	获取最优图片地址
	 *
	 *	@param string $content 文章内容
	 *	@return string
	 */
	public function get_opt_pic_big($content = ''){
		if($this->opt_pic_sign){
			$pic = $this->get_opt_pic($content, 'big');
			if(!empty($pic)){
				return $pic;
			}
		}
		return wx_random_big_pic();
	}

	/**
	 * 对每个第一条消息,进行处理字符截取
	 * 
	 * @param string $c
	 * @return string
	 */
	public function head_one_line($c){	
		$c = html_entity_decode($c, ENT_NOQUOTES, 'utf-8');
		$c = strip_tags($c);
		$c = mb_substr($c, 0, 50, 'utf-8').'...';
		return $c;
	}


	/**
	 * 指定文章回复
	 *
	 * @param int $id 文章的ID
	 * @return array
	 */
	public function Qid($id){
		query_posts('p='.$id);
		$info = array();
		$i = 0;
		while(have_posts()){the_post();
			++$i;
			if($i==1){
				$a['title'] = get_the_title();
				$a['desc'] = $this->head_one_line(get_the_content());
				$a['pic'] = $this->get_opt_pic_big(get_the_content());
				$a['link'] = get_permalink();
			}else{
				$a['title'] = get_the_title();
				$a['desc'] = get_the_title();
				$a['pic'] = $this->get_opt_pic_small(get_the_content());
				$a['link'] = get_permalink();
			}
			$info[] = $a;
		}
		if(!empty($info)){
			return $this->obj->toMsgTextPic($info);//图文
		}else{
			return false;
		}
	}

	/**
	 * 指定文章回复
	 *
	 * @param int $id 文章的ID
	 * @return array
	 */
	public function QidResult($id){
		$wp = new WP_query('p='.$id);
		$info = array();
		
		while($wp->have_posts()){$wp->the_post();
			$a['title'] = get_the_title();
			$a['desc'] = get_the_content();
			$a['pic'] = get_the_content();
			$a['link'] = get_permalink();
			$info = $a;
		}
		return $info;
	}


	/**
	 * 指定文章回复
	 *
	 * @param array $id 文章的ID
	 * @return array
	 */
	public function Qids($id){
		$string = array();
		$i = 0;
		foreach($id as $k){
			$res = $this->QidResult($k);
			if($res){
				++$i;
				if(1 == $i){
					$a['title'] = $res['title'];
					$a['desc'] = $this->head_one_line($res['desc']);
					$a['pic'] = $this->get_opt_pic_big($res['desc']);
					$a['link'] = $res['link'];
				}else{
					$a['title'] = $res['title'];
					$a['desc'] = $res['desc'];
					$a['pic'] = $this->get_opt_pic_small($res['desc']);
					$a['link'] = $res['link'];
				}
			}
			$string[] = $a;
		}
		return $this->obj->toMsgTextPic($string);//图文
	}

	/**
	 * 获取今日发布的文章
	 *
	 * @return xml
	 */
	public function today(){
		$sql = 'showposts=10'.'&year='.date('Y').'&monthnum='.date('m').'&day='.date('d');
		$wp = new WP_query($sql);
		$info = array();
		$i = 0;
		while($wp->have_posts()){$wp->the_post();
			++$i;
			if($i==1){
				$a['title'] = get_the_title();
				$a['desc'] = $this->head_one_line(get_the_content());
				$a['pic'] = $this->get_opt_pic_big(get_the_content());
				$a['link'] = get_permalink();
			}else{
				$a['title'] = get_the_title();
				$a['desc'] = get_the_title();
				$a['pic'] = $this->get_opt_pic_small(get_the_content());
				$a['link'] = get_permalink();
			}
			$info[] = $a;
		}
		if(empty($info)){
			return $this->obj->toMsgText('今日暂未发表文章!!!');
		}
		return $this->obj->toMsgTextPic($info);//图文
	}

	/**
	 * 获取最热文章
	 * 
	 * @param int $int 数量
	 * @return xml
	 */
	public function hot($int){
		$wp = new WP_query(array(
			'post_status' => 'publish',		//选择公开的文章
			'post_not_in' => array(),		//排除当前文章
			'ignore_sticky_posts'=> 1,		//排除顶置文章
			'orderby' => 'comment_count', 	//依据评论排序
			'showposts' => $int,			//调用的数量
		));
		$info = array();
		$i = 0;
		while($wp->have_posts()){$wp->the_post();
			++$i;
			if($i==1){
				$a['title'] = get_the_title();
				$a['desc'] = $this->head_one_line(get_the_content());
				$a['pic'] = $this->get_opt_pic_big(get_the_content());
				$a['link'] = get_permalink();
			}else{
				$a['title'] = get_the_title();
				$a['desc'] = get_the_title();
				$a['pic'] = $this->get_opt_pic_small(get_the_content());
				$a['link'] = get_permalink();
			}
			$info[] = $a;
		}
		return $this->obj->toMsgTextPic($info);//图文
	}

	
	/**
	 * 获取最新文章
	 * 
	 * @param int $int 数量
	 * @return xml
	 */
	public function news($int){
		$wp = new WP_query('showposts='.$int);
		$info = array();
		$i = 0;
		while($wp->have_posts()){$wp->the_post();
			++$i;
			if($i==1){
				$a['title'] = get_the_title();
				$a['desc'] = $this->head_one_line(get_the_content());
				$a['pic'] = $this->get_opt_pic_big(get_the_content());
				$a['link'] = get_permalink();
			}else{
				$a['title'] = get_the_title();
				$a['desc'] = get_the_title();
				$a['pic'] = $this->get_opt_pic_small(get_the_content());
				$a['link'] = get_permalink();
			}
			$info[] = $a;
		}
		return $this->obj->toMsgTextPic($info);//图文
	}

	/**
	 * 获取随机文章
	 * 
	 * @param int $int 数量
	 * @return xml
	 */
	public function rand($int){
		$wp = new WP_query("showposts={$int}&orderby=rand");
		$info = array();
		$i = 0;
		while($wp->have_posts()){$wp->the_post();
			++$i;
			if($i==1){
				$a['title'] = get_the_title();
				$a['desc'] = $this->head_one_line(get_the_content());
				$a['pic'] = $this->get_opt_pic_big(get_the_content());
				$a['link'] = get_permalink();
			}else{
				$a['title'] = get_the_title();
				$a['desc'] = get_the_title();
				$a['pic'] = $this->get_opt_pic_small(get_the_content());
				$a['link'] = get_permalink();
			}
			$info[] = $a;
		}
		return $this->obj->toMsgTextPic($info);//图文
	}


}
?>
