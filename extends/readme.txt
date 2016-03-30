WP微信机器人插件开发指南

声明:
	每个插件前前缀名为wpwx_(事件名)_(youname).php
	事件名有:
		all			所有信息
		text			文本信息
		location		地理信息
		image			图片信息
		link			连接信息
		video			视频信息
		voice			声音信息
		menu			菜单控制
		subscribe		订阅事件
提供方式:


本次更新后, 插件功能得到了极度的增强.
查看weixin-core.class.php和wp-weixin-plugins.php文件
可看实例插件
其中有服务号和订阅号的功能区别。


必须填写如下描述注释,不然后台不会显示出来
/**
 *  extend_name:find house
 *  extend_url:http://midoks.cachecha.com/
 *	author: midoks
 *	version:0.1
 *	email:midoks@163.com
 *	description: 查找房子功能模块
 */

