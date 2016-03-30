<?php

include('../weixin.class.php');




$obj = new weixin('wx56903169114ffd54', 'fe781e168d419d0bbd905f3eb87d423e');

//echo $obj->toMsgImage('ad','dd','as');
//echo $obj->toMsgImage('ad','dd','as');
//echo $obj->toMsgVoice('ad','dd','as');
//echo $obj->toMsgVideo('ad','dd','as', '1', '22');
//echo $obj->toMsgMusic('ad','dd','as', '1', '22','d', 'd');

$textPic = array(
			array(
				'title'=> '标题',
				'desc'=> '描述',
				'pic'=> '11',//图片地址
				'link'=>'11',//图片链接地址
			),//第一个图片为大图
			array(
				'title'=> '标题',
				'desc'=> '描述',
				'pic'=> '11',//图片地址
				'link'=> '11',//图片链接地址
			),//此自以后皆为小图	
		);

//echo $obj->toMsgNews('1','1',$textPic);
//
$data = $obj->pushMsgText('a','a','d');
var_dump($data);
?>
