var JQ = null;
jQuery(function($){
	JQ = $;

	$('.weixin_robot_mv').click(function(){
		var content = $(this).html();
		if('修改' == content){
			$(this).html("提交修改");


		
			var id = $(this).parent().children(0).val();
			var p = $(this).parent().parent();
			var keyword = $($(p).children()[1]).html();
			var key_content = $($(p).children()[2]).html();
			var key_type = $($(p).children()[3]).html();
			//console.log(id,keyword,key_content,key_type);
		
			var options = '<select name="weixin_robot_m" onchange="weixin_robot_m(this, this.value);" >';

			if('id' == key_type){
				options += '<option value="id" selected="selected">图文ID回复</option>';
			}else{
				options += '<option value="id">图文ID回复</option>';
			}

			if('text' == key_type){
				options += '<option value="text" selected="selected">文本回复</option>';
			}else{
				options += '<option value="text">文本回复</option>';
			}

			if('music' == key_type){
				options += '<option value="music" selected="selected">音乐回复</option>';
			}else{
				options += '<option value="music">音乐回复</option></select>';
			}



			$($(p).children()[3]).html(options);
			$($(p).children()[1]).attr('contentEditable', 'true').css('color', 'blue').css('border', '1px solid blue');
			$($(p).children()[2]).attr('contentEditable', 'true').css('color', 'blue').css('border', '1px solid blue');
		}else if('提交修改' == content){
			var _this = this;
			
			var id = $(this).parent().children(0).val();
			var p = $(this).parent().parent();
			var keyword = $($(p).children()[1]).html();
			var key_content = $($(p).children()[2]).html();
			var type = $($(p).children()[3]).html();

			var type_value = null;
		   
			$(type).children().each(function(i){
				var type_type = $(type).children()[i];
				var type_selected = $(type_type).attr('selected');
				if('selected' == type_selected){
					type_value = $(type_type).val();
				}
			});
		

			//console.log(id, keyword, key_content, type_value);
			$.ajax({
				type:'POST',
				url:'admin.php',
				data:'page=weixin_robot_setting_keyword_relpy&method=update&id='+id+'&keyword='+keyword+'&reply='+key_content+'&type='+type_value,
				success:function(data){
					if('ok' == data){
						Toast('修改成功', 1000);
						$(_this).html('修改');

						$($(p).children()[3]).html(type_value);
						$($(p).children()[1]).removeAttr('contentEditable').css('color','').css('border','');   
                        $($(p).children()[2]).removeAttr('contentEditable').css('color','').css('border','');
					}else{
						Toast('并没有修改啊!!,请继续修改!!!');
						console.log(data);
					}
				},
				error:function(){
				  	alert('请求失败!!!');    
				}
			
			});
		}
		//end
	});



	function Toast(info, time){
		if(typeof time == 'undefined'){
			var time = 3000;
		}

		var div =  document.createElement('div');
		div.id = 'midoks_toast_'+((new Date()).getTime());

		var t = (parseInt($('body').height())/2)+'px';
		var l = (parseInt($('body').width())/2)+'px';

		$('body').append(div);
		$('#'+ div.id).attr('id', 'midoks_toast').addClass('button-primary').
			css('position', 'fixed').css('top', t).css('left', l).
			fadeIn(1000,function(){//淡入
 			}).fadeOut(time, function(){
				$(this).remove();
			}).text(info);
	}

});

function weixin_robot_m(obj, value){
	JQ(obj).children().each(function(i){
		//var opt = JQ(this)[i];
		//opt_value = JQ(this).text();
		opt_value = JQ(this).val();
		if(value == opt_value){
			JQ(this).attr('selected', 'selected');	
		}else{
			JQ(this).removeAttr('selected');
		}
	});
}

