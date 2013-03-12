$(function(){
	var dragging=false;
	$('#board_head').hover(function(){
		$('.board_top_ctrl').show();
	},function(){
		$('.board_top_ctrl').hide();
	});
	if($('.list').size()>4){
		$('#board_wrap').width(($('.list').outerWidth(true)*$('.list').size())+10);
	}
	$('.list_head').hover(function(){
		$(this).find('.list_remove').show();
	},function(){
		$(this).find('.list_remove').hide();
	});

	$('.list_drag').ysdsort({
		'handler':'.list_head',
		'direction':'x',
		'condition':'x',
		'dashDiv':'<div  class="span list_dash"></div>',
		'dragClass':'list_draging',
		'reset_dash_size':'wh',
		'callback':list_sort
	});
	$('.card_drag').ysdsort({
		'dashDiv':'<div  class="card card_dash"></div>',
		'dragClass':'card_draging',
		'reset_dash_size':'h',
		'auto_size':true,
		'callback':card_sort,
		'dragbox':'.list',
		'dragbox_inner':'.card_list',
		'outer':'#board_main'
	});

	$('.card').hover(function(){
		if(!$(this).is('.card_done')) $(this).find('.todo_status').css('visibility','visible');
	},function(){
		$(this).find('.todo_status').css('visibility','hidden');
	});

	$('.todo_status').click(function(e){
		$('#todo_status_pop').remove();
		if($(this).is('.open')){
			$(this).removeClass('open');
		}else{
			//显示弹出层
			var tid=$(this).parent().attr('tid');
			var status_str=$(this).parent().is('.card_doing')?'暂停':'进行中';
			$('<div id="todo_status_pop" class="nav-collapse open"><ul class="dropdown-menu"><li><a href="javascript:todo_status('+tid+')">'+status_str+'</a></li><li><a href="javascript:todo_status_done('+tid+')">标记为完成</a></li></ul></div>').appendTo('body').css("position","absolute").offset({top:($(this).offset().top+20),left:$(this).offset().left});;
			$(this).addClass('open');
		}
		e.stopPropagation();
	});
	$(document).click(function(){
		$('#todo_status_pop').remove();
		$('.todo_status').removeClass('open');
	});

});

function todo_status(tid){
	var type=$('.card[tid="'+tid+'"]').is('.card_doing')?'pause':'start';
	$.get('?c=dashboard&a=todo_start&tid='+tid+'&type='+type,function(d,x,s){
		var ret=$.parseJSON(d);
		if(!ret || 0!=ret.err_code){
			alert('操作失败');
			return;
		}
		$('.card[tid="'+tid+'"]').removeClass('card_done');	
		if('start'==type){
			$('.card[tid="'+tid+'"]').addClass('card_doing');
		}else{
			$('.card[tid="'+tid+'"]').removeClass('card_doing');
		}		
	});
}

function todo_status_done(tid){
	$.post('?c=dashboard&a=todo_done','tid='+tid,function(d,x,s){
		var ret=$.parseJSON(d);
		if(!ret || 0!=ret.err_code){
			alert('操作失败');
			return;
		}
		$('.card[tid="'+tid+'"]').removeClass('card_doing');
		$('.card[tid="'+tid+'"]').addClass('card_done');	
			
	})
}

function card_sort(){
	dragging=true;
	setTimeout(function(){
		dragging=false;
	},300);
	$('.list').each(function(){
		var $this_list=$(this);
		var old_todos=$this_list.attr('tods');
		var new_todos_arr=[];
		$this_list.find('.card').each(function(){
			new_todos_arr.push($(this).attr('tid'));
		});
		var new_todos=new_todos_arr.join(',');
		if(old_todos!=new_todos){
			$.post('?c=plugin&a=board_todo_sort','list_id='+$this_list.attr('lid')+'&todos='+new_todos,function(d,x,s){
				var ret=$.parseJSON(d);
				if(!ret || 0!=ret.err_code){
					alert('todo排序失败');
					window.location.reload();
				}else{
					$this_list.attr('todos',new_todos);	
				}

			})
		}
	});
}

function list_sort(){
	dragging=true;
	setTimeout(function(){
		dragging=false;
	},300);
	var list_ids=[];
	$('.list').each(function(){
		list_ids.push($(this).attr('lid'));
	});
	$.get('?c=plugin&a=board_list_sort&ids='+list_ids.join(','),function(ret,x,s){
		var obj=$.parseJSON(ret);
		if(!obj || 0!=obj.err_code){
			alert('列表排序失败');
			window.location.reload();
		}
	});
}

function list_edit(id){
	if(dragging) return ;//解决firefox19  拖动会触发点击的问题
	$('#list_head_'+id).hide();
	$('#list_edit_head_'+id).show();
}
function list_edit_cancel(id){
	$('#list_edit_head_'+id).hide();
	$('#list_head_'+id).show();
}
function board_show_todo_detail_center(id){
	if(dragging) return;//解决firefox19 拖动会触发点击的问题
	show_todo_detail_center(id);

}

function todo_batch_add(board_id,list_id,visible){
	$('#float_box').off('show');
	$('#float_box').on('show', function () 
	{
  		$('#float_box_title').text('批量添加TODO');
  		$('#float_box .modal-body').load('?c=plugin&a=board_todo_add&board_id='+board_id+'&list_id='+list_id+'&visible='+visible,function(){
			enable_at('todo_batch_add_input');
		});
	})

	$('#float_box .modal-body').html('<div class="muted"><center>Loading</center>');
	$('#float_box').modal({ 'show':true });

}
function board_get_todos_by_uid(uid){
	$.get('?c=plugin&a=board_get_todo_by_uid&uid='+uid,function(d,s,x){
		var data=$.parseJSON(d);
		$('#import_todos').empty();
		$.each(data,function(index,todo){
			$('<option value="'+todo.id+'">'+todo.content+'</option>').appendTo('#import_todos');
		})
	});
}

