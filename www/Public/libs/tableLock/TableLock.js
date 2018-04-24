/*
author:KenshinCui
date:2011.03.29
example:$.fn.TableLock({table:'lockTable',lockRow:1,lockColumn:2,width:'100%',height:'300px'});
*/
(function($) {
    $.extend($.fn, {
        TableLock: function(options) {
            var tl = $.extend({
                table:'lockTable',//table的id
                lockRow:1,//固定行数
                lockColumn:1,//固定列数
                width:'100%',//表格显示宽度（实质是外出div宽度）
                height:'100%',//表格显示高度（实质是外出div高度）
                lockRowCss:'lockRowBg',//锁定行的样式
                lockColumnCss:'lockColumnBg'//锁定列的样式
            }, options);

            var tableId=tl.table;
            var table=$('#'+tableId);
            var rowSpan=function(tr){

            }
            if(table){
                var box=$("<div id='divBoxing' class='divBoxing'></div>").scroll(function(){//在此处添加事件
                    var that = this;
                    setTimeout(function(){
                        $('.LockRow').css('top',that.scrollTop+'px');
                        $('.LockCell').css('left',that.scrollLeft+'px');
                    });
                });
                box.css('width',tl.width).css('height',tl.height);//设置高度和宽度
                table.wrap(box);
                table.addClass('tbLock');
                if(tl.lockRow>0){
                    var tr;
                    for(var r=0;r<tl.lockRow;++r){//添加行锁定

                        tr=table.find('tr:eq('+r+')');
                        table.find('tr:eq('+r+') td').addClass('LockRow').addClass(tl.lockRowCss);
                        table.find('tr:eq('+r+') th').addClass('LockRow').addClass(tl.lockRowCss);
                        for(var c=0;c<tl.lockColumn;++c){//设置交叉单元格样式，除了锁定单元格外还有交叉单元格自身样式
                            if(tr)
                            tr.find('td:eq('+c+')').addClass('LockCell LockCross').addClass(tl.lockRowCss);
                            tr.find('th:eq('+c+')').addClass('LockCell LockCross').addClass(tl.lockRowCss);
                        }
                    }
                }
                if(tl.lockColumn>0){
                    var rowNum=$('#'+tableId+' tr').length;
                    var tr;
                    for(var r=(tl.lockRow);r<rowNum;++r){
                        tr=table.find('tr:eq('+r+')');
                        for(var c=0;c<tl.lockColumn;++c){//添加列锁定
                            tr.find('td:eq('+c+')').addClass('LockCell').addClass(tl.lockColumnCss);
                        }
                    }
                }

                //box.live('scroll',func);
            }else{
                alert('没有找到对应的table元素，请确保table属性正确性！');
            }
        }
    });
})(jQuery);
