/*
云商店拖动排序插件
此插件在新浪云商店（yunshangdian.com）中使用, 如果你成为云商店供应商可以见到这个插件的使用实例
@author  luofei614(www.3g4k.com)
*/
;(function($){
	$.fn.extend({
		'ysdsort':function(options){
			var defaults={
				'handler':false,//拖动层,默认拖动层就是移动层
				'dashDiv':'<div  class="dash"></div>',//虚框元素内容
				'dragClass':'main_dash',//正在拖动层的样式,这个样式的position要设在为absolute
				'direction':'y', //元素排列方向：x或y
				'condition':'xy',//判断条件，默认x轴和y轴都判断及元素中心点如果在其他元素内则会移动虚框层，如果拖动元素大小不一样，判断中心可能不合适，这时候可以指定之判断一个条件，x或y。
				'reset_dash_size':false,//是否设置虚框的大小（如果每个拖动层大小不一样，设置虚框大小或当前拖动层大小,如果设置为wh表示宽高都要设置，w表示只设置宽，h表示只设置高）
				'auto_size':false,//拖动层大小为100%, 没有设置大小时设置此为true
				'callback':false, //拖动后的回调函数
				'dragbox':false, //可放拖到层的容器，如果需要想一个空层中放拖动层，需要设置此项。
				'dragbox_inner':false,//把拖动层指定放到容器里面的层
				'outer':false,//指定一个层，如果拖动层移动到这个层以外，拖动层将被删除。 
				'outer_dash_class':'dash_delete'
			};
			options=$.extend(defaults,options);
			var selector=$(this).selector;
			var selectorClass=selector.substr(1);
			var $handler=options.handler?$(this).find(options.handler):$(this);
			//取消图片的默认拖动行为
			$handler.find('img').each(function(){
				this.ondragstart=function(){ return false;}
			});
			var readymovetimeout=null;
			//禁止拖动层文字被选中
			$handler.bind('selectstart',function(e){
				e.preventDefault();
			});
			$handler.css('user-select','none');
			$handler.live('mousedown',function(e){
			    var $this_div=options.handler?$(this).parents(selector):$(this);
				var readymove=function(){
			        //获得鼠标在元素内部的位置
			        var insertX=e.pageX-$this_div.offset().left;
			        var insertY=e.pageY-$this_div.offset().top;
			        var width=$this_div.width();
			        var height=$this_div.height();
			        var halfWidth=width/2;
			        var halfHeight=height/2;
					if(options.auto_size) $this_div.css('width',$this_div.width()+'px');
			        //设置虚框
			        var $tempDiv=$(options.dashDiv).insertBefore($this_div);
					if(options.reset_dash_size){
						 if('wh'==options.reset_dash_size || 'w'==options.reset_dash_size) $tempDiv.width($this_div.width());
						 if('wh'==options.reset_dash_size || 'h'==options.reset_dash_size) $tempDiv.height($this_div.height());
					}
			        $this_div.addClass(options.dragClass);
			        $this_div.removeClass(selectorClass);//暂时去掉，为了避免下面判断位置判断到自己
			        var drag_move=function(e){
			            var moveToX=e.pageX-insertX;
			            var moveToY=e.pageY-insertY;
			            var centerX=moveToX+halfWidth;//元素中心点X抽坐标
			            var centerY=moveToY+halfHeight;//元素中心点Y抽坐标
			            $this_div.offset({'top':moveToY,'left':moveToX});
						//拖动到一个容器层
						if(options.dragbox){
							$(options.dragbox).each(function(index,elem){
							  var minX=$(this).offset().left;
			                  var maxX=minX+$(this).width();
			                  var minY=$(this).offset().top;
			                  var maxY=minY+$(this).height();	
							  if(centerX>=minX&&centerX<=maxX&&centerY>=minY&&centerY<=maxY){
								var to=options.dragbox_inner?$(this).find(options.dragbox_inner):this;
							  	$tempDiv.appendTo(to);
							  }
							});	
						}
			            //计算虚框变化位置， 第一个元素是放置到before，其他元素放置到after
			            $(selector).each(function(index,elem){
			                //计算拖动元素中心点位置是否在当前循环元素内
			                var minX=$(this).offset().left;
			                var maxX=minX+$(this).width();
			                var minY=$(this).offset().top;
			                var maxY=minY+$(this).height();
							var move_temp_div=false;
							if('xy'==options.condition && centerX>=minX&&centerX<=maxX&&centerY>=minY&&centerY<=maxY){
								move_temp_div=true;
							}else if('y'==options.condition && centerY>=minY&&centerY<=maxY){
								move_temp_div=true;
							}else if('x'==options.condition && centerX>=minX&&centerX<=maxX){
								move_temp_div=true;
							}
			                if(move_temp_div){
			                	if((options.direction=='x' && centerX<=minX+halfWidth) || (options.direction=='y' && centerY<=minY+halfHeight)){
			                		var  before=true;//是否向前插入
			                	}else{
			                		var before=false;
			                	}
			                    if(before){
			                        //如果位置靠前则向前插入
			                        $tempDiv.insertBefore(this);
			                    }else{
			                        $tempDiv.insertAfter(this);
			                    }
			                }
			            });
						
						//如果拖动移动到层外，删除拖动层
						if(options.outer){
							var minX=$(options.outer).offset().left;
							var maxX=minX+$(options.outer).width();
							var minY=$(options.outer).offset().top;
							var maxY=minY+$(options.outer).height();
							if(centerX<minX || centerX>maxX || centerY<minY || centerY>maxY){
								$tempDiv.addClass(options.outer_dash_class);
							}else{
								$tempDiv.removeClass(options.outer_dash_class);
							}
						}
	
			        }
					var selectstart=function(e){
						e.preventDefault();
					}
			        var drag_stop=function(e){
						if(options.outer && $tempDiv.is('.'+options.outer_dash_class)){
							//删除拖动层
							$tempDiv.remove();
							$this_div.remove();
						}else{
							//放置元素
							$tempDiv.replaceWith($this_div);
							$this_div.removeClass(options.dragClass);
							$this_div.addClass(selectorClass)
							if(options.auto_size) $this_div.css('width','');
						}
							$(document).unbind('mouseup',drag_stop);
							$(document).unbind('mousemove',drag_move);
							$('body').css('user-select','auto');
							$(document).unbind('selectstart',selectstart);
						//执行回调函数
						if($.isFunction(options.callback)) options.callback();
			        }
					$('body').css('user-select','none');//拖动时不会选择文字
					$(document).bind('selectstart',selectstart);//拖动时不会选择文字，兼容IE
			        $(document).mousemove(drag_move).mouseup(drag_stop);
			};// end readymove

				readymovetimeout=setTimeout(readymove,200);//延迟拖动, 可似地拖动层内部元素的click事件得到监听
    }).live('mouseup',function(e){
				clearTimeout(readymovetimeout);
	});

		}
	});
})(jQuery);
