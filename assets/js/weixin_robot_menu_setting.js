var JQ = null;
jQuery(function($){
	JQ = $;
	$('.weixin_robot_mv').click(function(){
		
		var content = $(this).html();
		if('修改' == content){
			$(this).html('提交修改');
			var id = $(this).parent().children(0).val();
			var p = $(this).parent().parent();
			var type = $($(p).children()[2]).html();
			var value = $($(p).children()[3]).html();
		

			var options = '<select name="weixin_robot_m" onchange="weixin_robot_m(this, this.value);" >';
			
            if('click'==type){   
				options += '<option value="click" selected="selected">点击</option>'; 
			}else{
			    options += '<option value="click">点击</option>';
			}
		   
			if('view'==type){
				options += '<option value="view" selected="selected">URL</option></select>';
			}else{
			    options += '<option value="view" >URL</option></select>'; 
			}
			$($(p).children()[2]).html(options);
			var name_string = $($(p).children()[1]).html();
			var name_p = name_string.split('─');
			//console.log(name_p);
			$($(p).children()[1]).attr('contentEditable', 'true').css('color', 'blue').css('border', '1px solid blue').html(name_p[1]);
			$($(p).children()[3]).attr('contentEditable', 'true').css('color', 'blue').css('border', '1px solid blue');
			$($(p).children()[4]).attr('contentEditable', 'true').css('color', 'blue').css('border', '1px solid blue');
		}else if('提交修改'==content){
			var _this = this;

			var id = $(this).parent().children(0).val();
			var p = $(this).parent().parent();
			var name = $($(p).children()[1]).html(); 
			var type = $($(p).children()[2]).html();
	   		var value = $($(p).children()[3]).html();
			var sort = $($(p).children()[4]).html();
            var type_value = null;
		   
			$(type).children().each(function(i){
				var type_type = $(type).children()[i];
				var type_selected = $(type_type).attr('selected');
				if('selected' == type_selected){
					type_value = $(type_type).val();
				}
			});

			$.ajax({
				type:'POST',
				url:'admin.php',
				data:'page=weixin-robot-menu-setting&method=update&id='+id+'&type='+type_value+'&value='+value+'&name='+name+'&sort='+sort,
				success:function(data){
					console.log(data);
					if('ok'==data){
						Toast('修改成功', 1000);
						$(_this).html('修改');

						/*setTimeout(function(){
							location.href =  location.href;
						}, 1000);*/

						var name_string = $($(p).children()[1]).html();

						$($(p).children()[2]).html(type_value);
						$($(p).children()[1]).removeAttr('contentEditable').css('color','').css('border','');   
                        $($(p).children()[3]).removeAttr('contentEditable').css('color','').css('border','');
						$($(p).children()[4]).removeAttr('contentEditable').css('color','').css('border','');
					}else{
					    Toast('并没有修改啊!!,请继续修改!!!');
					}
				},
				error:function(){
				  	alert('请求失败!!!');    
				}
			
			});
			//console.log(id, type_value,value, name);
		}

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


function weixin_robot_m(obj,value){
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
