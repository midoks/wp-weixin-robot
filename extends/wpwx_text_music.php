<?php
/**
 *  extend_name:音乐获取
 *  extend_url:http://midoks.cachecha.com
 *  author:midoks
 *  version:1.0
 *  email:midoks@163.com
 *  description: 回复"音乐+大海",返回大海歌曲
 *  expMethod:关键字(大海 张雨生) | 音乐+人来人往(歌名) http://box.zhangmen.baidu.com/x?op=12&count=1&title=大约在冬季$$齐秦$$$$
 */
class wpwx_text_music{
  
    private $obj = null;
  
  
    private $conf = array(
        'method' => '1', //可以自己调整 支持 1(默认),2 (就是禁用)
    );
  
    //构造函数 | init
    public function __construct($obj){
        $this->obj = $obj;
    }
  
    //开始执行
    //**
    //  如果你是文本处理 $kw 是一个用户关键
    //  如果你是不使用文本 $kw 发送过来的所有信息 (数据)
    ///
    public function start($kw){
        if('1' == $this->conf['method']){
            if('音乐' == substr($kw, 0, 6)){
                return $this->method_1($kw);
            }
            return false;
  
        }else if('2' == $this->conf['method']){
            return $this->method_2($kw);
        }
          
    }
  
    /* error ret */
    public function error_ret(){
        return $this->obj->toMsgText('对于没有返回音乐的我认为有几点原因:'.
            "\n".'1.百度音乐不给力啊!'.
            "\n".'2.你搜的什么烂歌,百度居然没有..!!~}~'.
            "\n".'3.什么狗屁程序,不给力啊!!!'.
            "\n".'综合上面的原因,你可以再试一试,又不要你钱!!!');
    }
  
    //方式1
    public function method_1($kw){
        $kw = str_replace('音乐', '', $kw);
        $Murl = $this->analysis_music($kw);
        if($Murl){
            $num = count($Murl)-1;
            if($num>1){
                return $this->obj->toMsgMusic($kw, date('Y-m-d H:i:s'),$Murl[mt_rand(1, $num)], $Murl[mt_rand(1, $num)]);//voice
            }else{
                return $this->obj->toMsgMusic($kw, date('Y-m-d H:i:s'), $Murl[0], $Murl[0]);//p2p voice
            }
        }
        return $this->error_ret();
    }
  
    /**
     *  @func 方法2
     */
    public function method_2($kw){
        $msc_arr = array('.','~','!','+','-','*','/','。');
        $music = explode(' ', $kw);
        $MN = $music[0];//歌名
        $SG = $music[1];//歌手
        if(count($music)>1){
            $Murl = $this->analysis($MN, $SG);
            if(!empty($Murl)){//成功显示
                $num = count($Murl)-1;
                if($num>1){
                    return $this->obj->toMsgVoice($kw, date('Y-m-d H:i:s').'|'.$num,$Murl[mt_rand(1, $num)], $Murl[mt_rand(1, $num)]);//voice
                }else{
                    return $this->obj->toMsgVoice($kw, date('Y-m-d H:i:s')."|".$num, $Murl[0], $Murl[0]);//p2p voice
                }   
            }
            return $this->error_ret();
        }
        return false;
    }
  
    //音乐菜单优先返回
    public function music_opt_ret($data){
        $b = strpos($data, 'baidu.com');
        if(false == $b){return false;}
        return true;
    }
  
    public function analysis_music($MusicName){
        $data = $this->get_data_music_name($MusicName);
        if($data)
            return $data;
        return false;
    }
  
    //根据歌名
    public function get_data_music_name($MN){
        $MN = urlencode($MN);//歌名
        $url = "http://box.zhangmen.baidu.com/x?op=12&count=1&title={$MN}$$";
        return $this->get_data($url);
    }
  
    /**
     *  @func 根据歌名和歌手来查找
     *  @param string $MusicName 歌名
     *  @param string $Singer 歌手
     *  ret array
     */
    public function analysis($MusicName, $Singer){
        $data = $this->get_data_music_singer($MusicName, $Singer);
        if($data)
            return $data;
        return false;
    }
  
    //返回数据 url地址['两个地址']
    public function get_data_music_singer($MusicName, $Singer){
        $mn = urlencode($MusicName);//歌名
        $sg = urlencode($Singer);   //歌手
        $url = "http://box.zhangmen.baidu.com/x?op=12&count=1&title={$mn}$${$sg}$$$$";
        return $this->get_data($url);
    }
  
    public function get_data($url){
        $content = @file_get_contents($url);        
        if(!$content) return false;
        $content = @simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
        if(!$content) return false;
  
        //没有查找到时
        $count = $content->count;
        if('0' == $count){
            return false;
        }
  
        $url_arr = array();
        //百度P2P的资源加入(优先返回)
        if($p2p = $content->p2p){
            $url_arr_p2p_data = (String)$p2p->url;
            if(empty($url_arr_p2p_data)){
            }else{
                $url_arr_p2p[] = $url_arr_p2p_data;
                return $url_arr_p2p;
            }
        }
      
        //有数据时
        for($i=0; $i<$count; $i++){
            $addr[$i] = $content->url[$i];
        }
        array_shift($addr);//百度掌门人取得(微信不显示)
        if(empty($addr)){
            echo $this->obj->toMsgText('啊!微信不给力,它不显示结果,换首歌吧!!');exit;
        }
        foreach($addr as $k=>$v){
            $encode = $v->encode;
            $decode = $v->decode;
            $mp3 = $decode;
            if(!empty($encode)){
                $url = dirname($encode).'/'.$mp3;
                $url_arr[] = $url;
            }
        }
        return $url_arr;
    }
}
?>
