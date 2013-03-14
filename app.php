<?php
/*** 
TeamToy extenstion info block
##name  看板
##folder_name board
##author luofei614
##email upfy@qq.com
##reversion 1
##desp 看板是开会是必备的工具，能将开会内容整理为TODO分派给不同的人，而且能给TODO进行清晰的归类。 
##update_url http://tt2net.sinaapp.com/?c=plugin&a=update_package&name=board 
##reverison_url http://tt2net.sinaapp.com/?c=plugin&a=latest_reversion&name=board
***/

add_action( 'UI_NAVLIST_LAST' , 'board_icon' );
function board_icon()
{
	?><li <?php if( g('c') == 'plugin' && g('a') == 'board' ): ?>class="active"<?php endif; ?>><a href="?c=plugin&a=board" title="看板" >
	<div><img src="plugin/board/kanban.png"/></div></a>
	</li>
	<?php
}
add_action('PLUGIN_BOARD','board_display');
function board_display(){
	//智能跳转
	$board_id=isset($_GET['id'])?intval(v('id')):1;
	if(!isset($_GET['id']) && isset($_SESSION['board_id']) && 1!=$_SESSION['board_id']){
		header('location:?c=plugin&a=board&id='.$_SESSION['board_id']);
		exit();	
	}
	$_SESSION['board_id']=$board_id;
	//初始化数据库
	if(!get_data("SHOW TABLES LIKE 'board'")){
		$sql=<<<SQL
CREATE TABLE IF NOT EXISTS `board` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `name` char(50) NOT NULL,
 `visible` enum('all','group','private') NOT NULL DEFAULT 'all',
 `visible_value` char(20) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

SQL;
		run_sql($sql);
		$sql=<<<SQL
SQL;
		run_sql("INSERT INTO `board` (`id`, `name`, `visible`, `visible_value`) VALUES (1, '团队看板', 'all', '')");
		$sql=<<<SQL
CREATE TABLE IF NOT EXISTS `board_list` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `name` char(50) NOT NULL,
 `board_id` int(11) unsigned NOT NULL,
 `todos` char(100) DEFAULT NULL,
 `sort` tinyint(4) unsigned NOT NULL DEFAULT '99',
 PRIMARY KEY (`id`),
 KEY `board_id` (`board_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
SQL;
		run_sql($sql);
	}
	$data=array();
	$data['top_title']='看板';
	$board_data=json_decode(send_request('board_data',array(),token()),true);
	if(0!=$board_data['err_code']){
		$msg=6001==$board_data['err_code']?'看板不存在':'您无权限访问此看版';
		info_page($msg);
	}
	$data['board']=$board_data['data'];
	$data['id']=$board_id;
	$board_list=json_decode(send_request('board_list',array(),token()),true);
	$data['board_list']=$board_list['data'];
	render($data,'web','plugin','board');
}
//添加看板界面
add_action('PLUGIN_BOARD_CREATE','board_create');
function board_create(){
	include dirname(__FILE__).'/view/board_create.tpl.html';
}
//添加看板处理方法
add_action('PLUGIN_BOARD_ADD','plugin_board_add');
function plugin_board_add(){
	$result=json_decode(send_request('board_add',array(),token()),true);
	if(0!=$result['err_code']){
		info_page('添加失败');
	}
	header("location:?c=plugin&a=board&id=".$result['data']);
}
//修改看板
add_action('PLUGIN_BOARD_UPDATE','plugin_board_update');
function plugin_board_update(){
	$id=empty($_GET['id'])?1:intval(v('id'));
	$board=get_line("select * from board where id='{$id}'");
	include dirname(__FILE__).'/view/board_update.tpl.html';
}
//修改处理代码
add_action('PLUGIN_BOARD_SAVE','plugin_board_save');
function plugin_board_save(){
	$result=json_decode(send_request('board_update',array(),token()),true);
	if(0!=$result.err_code){
		info_page('修改失败');
	}else{
		header("location:?c=plugin&a=board&id=".intval(v('id')));	
	}
}
//删除看板
add_action('PLUGIN_BOARD_DELETE','plugin_board_delete');
function plugin_board_delete(){
	$result=json_decode(send_request('board_delete',array(),token()),true);	
	if(0!=$result['err_code']){
		$msg=6005==$result['err_code']?'不能删除默认看板':'删除失败';
		info_page($msg);
	}else{
		header('location:?c=plugin&a=board&id=1');
	}
}
//添加列表
add_action('PLUGIN_BOARD_LIST_ADD','plugin_board_list_add');
function plugin_board_list_add(){
	include dirname(__FILE__).'/view/board_list_add.tpl.html';
}
//添加列表处理
add_action('PLUGIN_BOARD_LIST_INSERT','plugin_board_list_insert');
function plugin_board_list_insert(){
	$result=json_decode(send_request('board_list_add',array(),token()),true);
	if(0!=$result['err_code']){
		$msg=6007==$result['err_code']?'你没有权限添加这个看板的列表':'列表添加失败';
		info_page($msg);
	}else{
		header('location:?c=plugin&a=board&id='.intval(v('board_id')));
	}
}

add_action('PLUGIN_BOARD_LIST_DELETE','plugin_board_list_delete');
function plugin_board_list_delete(){
	$result=json_decode(send_request('board_list_delete',array(),token()),true);
	if(0!=$result['err_code']){
		info_page('删除失败');
	}else{
		header('location:'.$_SERVER['HTTP_REFERER']);
	}	
}

add_action('PLUGIN_BOARD_LIST_UPDATE','plugin_board_list_update');
function plugin_board_list_update(){
	$result=json_decode(send_request('board_list_update',array(),token()),true);
	if(0!=$result['err_code']){
		info_page('更新失败');
	}else{
		header('location:'.$_SERVER['HTTP_REFERER']);
	}
}

add_action('PLUGIN_BOARD_LIST_SORT','plugin_board_list_sort');
function plugin_board_list_sort(){
	exit(send_request('board_list_sort',array(),token()));
}
add_action('PLUGIN_BOARD_TODO_ADD','plugin_board_todo_add');
function plugin_board_todo_add(){
 include dirname(__FILE__).'/view/board_todo_add.tpl.html';
}
add_action('PLUGIN_BOARD_TODO_INSERT','plugin_board_todo_insert');
function plugin_board_todo_insert(){
	$result=json_decode(send_request('board_todo_batch_add',array(),token()),true);
	if(0!=$result['err_code']){
		info_page('TODU添加失败,'.$result['err_msg']);
	}else{
		header('location:'.$_SERVER['HTTP_REFERER']);
	}
}
add_action('PLUGIN_BOARD_TODO_SORT','plugin_board_todo_sort');
function plugin_board_todo_sort(){
	exit(send_request('board_todo_sort',array(),token()));
}
add_action('PLUGIN_BOARD_TODO_IMPORT','plugin_board_todo_import');
function plugin_board_todo_import(){
	//读取用户列表
    $users=get_data('SELECT `id`,`name` FROM `user` WHERE `is_closed` = 0 AND `level` > 0 ');	
	$todos=plugin_board_get_todo_by_uid($users[0]['id']);
	//读取用户TODO列表
	include dirname(__FILE__).'/view/board_todo_import.tpl.html';
}
add_action('PLUGIN_BOARD_GET_TODO_BY_UID','plugin_board_get_todo_by_uid');
function plugin_board_get_todo_by_uid($uid=null){
	$sql_uid=empty($uid)?intval(v('uid')):$uid;
	$is_public=$sql_uid==uid()?'':" and todo_user.is_public='1'";//屏蔽其他人的似有TODO
	$data=get_data("select distinct id,content from todo left join todo_user on todo.id=todo_user.tid where todo.owner_uid='{$sql_uid}' and todo_user.status!=3{$is_public}");
	if(empty($uid)) exit(json_encode($data));
	return $data;
}
add_action('PLUGIN_BOARD_TODO_IMPORT_SAVE','plugin_board_todo_import_save');
function plugin_board_todo_import_save(){
	$result=json_decode(send_request('board_todo_import',array(),token()),true);
	if(0!=$result['err_code']){
		info_page('TODO导入失败');
	}else{
		header('location:'.$_SERVER['HTTP_REFERER']);
	}
}
//API

//读取所有有权限可见的看版列表
add_action('API_BOARD_LIST','board_list');
function board_list(){
	$boards=get_data("select id,name,visible,visible_value from board");
	$result=array();
	foreach($boards as $board){
		if(has_board_permission(uid(),$board['visible'],$board['visible_value']))
			$result[$board['id']]=$board['name'];
	}
	return apiController::send_result($result);
}

add_action('API_BOARD_ADD','board_add');
function board_add(){
	$name=s(t(z(v('name'))));
	$visible=s(t(z(v('visible'))));
	if('all'==$visible){
		$visible_value='';
	}elseif('private'==$visible){
		$visible_value=uid();
	}else{
		$visible_value=s(t(z(v('visible_value'))));
	}
	if(!run_sql("insert into board(name,visible,visible_value) values('{$name}','{$visible}','{$visible_value}')")){
		return apiController::send_error(6003,'db insert error');	
	}
	return apiController::send_result(last_id());

}

add_action('API_BOARD_UPDATE','board_update');
function board_update(){
	//TODO, 修改权限,创建者才能修改
	$id=intval(v('id'));
	$name=s(z(t(v('name'))));
	$visible=s(z(t(v('visible'))));
	if(1==$id) $visible='all';//不能修改默认模板的权限
	if('all'==$visible){
		$visible_value='';
	}elseif('private'==$visible){
		$visible_value=uid();
	}else{
		$visible_value=s(z(t(v('visible_value'))));
	}
	if(!run_sql("update board set name='{$name}',visible='{$visible}',visible_value='{$visible_value}' where id='{$id}'")){
		return	apiController::send_error(6004,'update board failed');
	}else{
		return apiController::send_result('success');
	}
}
//删除看板
add_action('API_BOARD_DELETE','board_delete');
function board_delete(){
	//TODO,只有创建者才能删除
	$id=empty($_GET['id'])?1:intval(v('id'));
	if(1==$id)
		return apiController::send_error(6005,'can not delete default board');
	if(!run_sql("delete from board where id='{$id}'")){
		return apiController::send_error(6006,'delete board failed');
	}else{
		return apiController::send_result('success');
	}
}

//获得某一个看板的数据
add_action('API_BOARD_DATA','board_data');
function board_data(){
	$board_id=isset($_GET['id'])?intval(v('id')):1;
	//读取board的数据
	if(!$result=get_line("select * from board where id='{$board_id}'")){
		return apiController::send_error(6001,'board not exists');
	}
	//判断访问权限
	if(!has_board_permission(uid(),$result['visible'],$result['visible_value'])){
		return apiController::send_error(6002,'no permission to visit this board');
	}
	$result['lists']=array();
	//读取list
	if($list=get_data("select * from board_list where board_id='{$result['id']}' order by sort asc,id asc")){
		//读取list下的TODO
		foreach($list as $arr){
			$todos=trim($arr['todos'],',');
			$arr['todo_lists']=empty($todos)?array():get_data("select distinct id,content,status from todo left join todo_user on todo_user.tid=todo.id where id in({$todos}) order by find_in_set(id,'{$todos}')");
			$result['lists'][]=$arr;
		}
	}
	return apiController::send_result($result);
}

//判断是否有访问指定看版的权限
function has_board_permission($uid,$visible,$visible_value){
	if('all'==$visible) return true;
	if('private'==$visible) return $visible_value==$uid?true:false;
	if('group'==$visible){
		return get_var("select 1 from user where id='{$uid}' and is_closed='0' and groups like '%|".strtoupper($visible_value)."|%'")?true:false;
	}
	return false;
}
function has_board_permission_by_id($board_id,$uid=null){
	if(is_null($uid)) $uid=uid();
	$board=get_line("select visible,visible_value from board where id='{$board_id}'");
	if(!has_board_permission($uid,$board['visible'],$board['visible_value'])){
		return false;	
	}
	return true;
}

add_action('API_BOARD_LIST_ADD','board_list_add');
function board_list_add(){
	$board_id=intval(v('board_id'));
	$name=s(z(t(v('name'))));
	if(!has_board_permission_by_id($board_id)){
		return apiController::send_error(6007,'no permission');
	}	
	if(!run_sql("insert into board_list(name,board_id) values('{$name}','{$board_id}')")){
		return apiController::send_error(6008,'add list failed');
	}else{
		return apiController::send_result(last_id());
	}
	
}

add_action('API_BOARD_LIST_UPDATE','board_list_update');

function board_list_update(){
	$list_id=intval(v('id'));
	$name=s(t(z(v('name'))));
	if(!$board_id=get_var("select board_id from board_list where id='{$list_id}'")){
		return apiController::send_error(6011,'list not exists');
	}
	if(!has_board_permission_by_id($board_id)){
		return apiController::send_error(6012,'no permission');
	}
	if(!run_sql("update board_list set name='{$name}' where id='{$list_id}'")){
		return apiController::send_error(6013,'list update failed');
	}else{
		return apiController::send_result('success');
	}

}

add_action('API_BOARD_LIST_DELETE','board_list_delete');
function board_list_delete(){
	$list_id=intval(v('id'));
	if(!$board_id=get_var("select board_id from board_list where id='{$list_id}'")){
		return apiController::send_error(6009,'list not exists');
	}
	if(!has_board_permission_by_id($board_id)){
		return apiController::send_error(6010,'no permission');
	}
	if(!run_sql("delete from board_list where id='{$list_id}'")){
		return apiController::send_error('list delete failed');
	}else{
		return apiController::send_result('success');
	}
}

add_action('API_BOARD_LIST_SORT','board_list_sort');
function board_list_sort(){
	$ids=explode(',',v('ids'));
	foreach($ids as $sort=>$id){
		run_sql("update board_list set sort='{$sort}' where id='".intval($id)."'");
	}
	return apiController::send_result('success');
}
add_action('API_BOARD_TODO_BATCH_ADD','board_todo_batch_add');

function board_todo_batch_add(){
	$board_id=intval(v('board_id'));
	$list_id=intval(v('list_id'));
	$todos=explode("\r\n",v('todos'));
	//读取board信息
	if(!$board=get_line("select * from board where id='{$board_id}'")){
		return apiController::send_error(6014,'board not exists');
	}
	//判断操作权限
	if(!has_board_permission(uid(),$board['visible'],$board['visible_value'])){
		return apiController::send_error(6015,'no permission');
	}
	$is_public='private'==$board['visible']?0:1;
	$tids=array();
	foreach($todos as $todo){
		$uids=array();
		if(!empty($todo)){
			//分析@
			if($ats=find_at($todo)){
				//循环分析@取UID
				foreach($ats as $at){
					$wsql=array();
					if( mb_strlen($at, 'UTF-8') >= 2 )
						$wsql[] = " `name` = '" . s(t($at)) . "' ";
					if( c('at_short_name') )
						if( mb_strlen($at, 'UTF-8') == 2 )
							$wsql[] = " `name` LIKE '_" . s($at) . "' ";
					if(!empty($wsql)){
						if($get_uid=get_var("SELECT `id` FROM `user` WHERE (`level` > 0 AND `is_closed` != 1 ) AND ( ".join(' OR ',$wsql)." ) ")){
							$uids[]=$get_uid;
						}			
					}
				}

			}
			//如果TODO以#号结尾，则为公有TODO，
			$is_this_public='#'==substr($todo,-1)?1:$is_public;
			//用户UID
			if(empty($uids))
				$uids=array(uid());
			else
				$is_this_public=1;// 如果@了人，固定为公有的TODO
			//添加todu
			foreach($uids as $uid){
				$result=json_decode(send_request('todo_add',array('text'=>$todo,'is_public'=>$is_this_public,'uid'=>$uid),token()),true);
			}
			if(0==$result['err_code']){
			 	$tids[]=$result['data']['tid'];
			}else{
				return apiController::send_error(6016,'todo['.$todo.'] add failed,'.$result['err_msg']);
			}
		}
	}
	if(!empty($tids)){
	  run_sql("update board_list set todos=concat_ws(',',todos,'".implode(',',$tids)."') where id='{$list_id}'");
	}
	return apiController::send_result('success');

}
add_action('API_BOARD_TODO_IMPORT','board_todo_import');
function board_todo_import(){
	$todos=v('todos');
	$board_id=intval(v('board_id'));
	$list_id=intval(v('list_id'));
	if(is_array($todos)) $todos=implode(',',$todos);
	if(!has_board_permission_by_id($board_id)){
		return apiController::send_error(6018,'no permission');
	}
	if(!empty($todos)){
		if(!run_sql("update board_list set todos=concat_ws(',',todos,'".s($todos)."') where id='{$list_id}'")){
			return apiController::send_error(6019,'todo add failed');	
		}else{
			return apiController::send_result('success');
		}
	}else{
			return apiController::send_error(6020,'todos empty');
	}
}


add_action('API_BOARD_TODO_SORT','board_todo_sort');
function board_todo_sort(){
	$list_id=intval(v('list_id'));
	$todos=s(z(t(v('todos'))));
	if(!run_sql("update board_list set todos='{$todos}' where id='{$list_id}'")){
		return apiController::send_error(6017,'todo sort failed');
	}else{
		return apiController::send_result('success');
	}
}

