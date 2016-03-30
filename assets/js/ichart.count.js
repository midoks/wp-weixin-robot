jQuery(function($){
		var datalist = null;
		$.ajax({
			type:'POST',
			url:'admin.php',
			data:'page=weixin-robot-statistics',
			success:function(data){
				//console.log(data);
				if(!data) return; 

				datalist = eval("("+data+")");
				//console.log(datalist['text']);


	var data = [
		{name : '文本信息',value : datalist['text'], color:'#fedd74'},
		{name : '音频消息',value : datalist['voice'], color:'#82d8ef'},
		{name : '视频消息',value : datalist['video'], color:'#f76864'},
		{name : '链接消息',value : datalist['link'], color:'#80bd91'},
		{name : '事件消息',value : datalist['event'], color:'#80ee91'},
		{name : '地理消息',value : datalist['location'], color:'#70bc91'},
		{name : '图片消息',value : datalist['image'], color:'#fd8fc1'}];

	
	var chart = new iChart.Pie3D({
		render : 'canvasDiv1',
		data: data,
		title : {
			text : '微信通信记录统计',
			color : '#3e576f'
		},
		footnote : {
			text : '感谢www.ichartjs.com提供',
			color : '#486c8f',
			fontsize : 11,
			padding : '0 38'
		},
		bound_event:null,
		sub_option : {
			label : {
				background_color:null,
				sign:false,//设置禁用label的小图标
				padding:'0 4',
				border:{
					enable:false,
					color:'#be5985'
				},
				fontsize:11,
				fontweight:600,
				color : '#be5985'
			},
			border : {
				width : 2,
				color : '#ffffff'
			}
		},
		shadow : true,
		shadow_blur : 6,
		shadow_color : '#aaaaaa',
		shadow_offsetx : 0,
		shadow_offsety : 0,
		background_color:'#fefefe',
		yHeight:20,//饼图厚度
		offsetx:60,//设置向x轴负方向偏移位置60px
		offset_angle:0,//逆时针偏移120度
		mutex : true,//只允许一个扇形弹出
		showpercent:true,
		decimalsnum:2,
		width : 800,
		height : 400,
		radius:150
	});
	chart.plugin(new iChart.Custom({
			drawFn:function(){
				//计算位置
				var y = chart.get('originy'),
					w = chart.get('width');
				chart.target.textAlign('start')
				.textBaseline('middle')
				.textFont('600 16px Verdana')
				.fillText('微信统计各类型比',60,y-40,false,'#3e576f',false,20);
			}
	}));
	
	chart.draw();
			//end success
			}
		});
		
});
