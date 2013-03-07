$(function(){
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
		'reset_dash_size':true,
		'callback':list_sort
	});
	$('.card_drag').ysdsort({
		'dashDiv':'<div  class="card card_dash"></div>',
		'dragClass':'card_draging',
		'reset_dash_size':true,
		'callback':card_sort,
		'dragbox':'.list',
		'dragbox_inner':'.card_list',
		'outer':'#board_main'
	});

	$('.card').hover(function(){
		$(this).find('.todo_status').show();
	},function(){
		$(this).find('.todo_status').hide();
	});

	$('.todo_status').click(function(e){
		//显示弹出层
		var tid=$(this).parent().attr('tid');
		$('<div id="todo_status_pop" class="nav-collapse open"><ul class="dropdown-menu"><li><a href="javascript:todo_status(\'pause\','+tid+');">todo</a></li><li><a href="javascript:todo_status(\'start\','+tid+')">doing</a></li><li><a href="javascript:todo_status_done('+tid+')">done</a></li></ul></div>').appendTo('body').css("position","absolute").offset({top:($(this).offset().top+20),left:$(this).offset().left});;
		e.stopPropagation();
	});
	$(document).click(function(){
		$('#todo_status_pop').remove();
	});

});

function todo_status(type,tid){
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
	$('#list_head_'+id).hide();
	$('#list_edit_head_'+id).show();
}
function list_edit_cancel(id){
	$('#list_edit_head_'+id).hide();
	$('#list_head_'+id).show();
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

