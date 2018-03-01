(function($) {
    $.donateCart = {
        init: function() {
            //初始化捐赠栏，从cookie读取信息，计算总额
            var initdata = {},current_time='';
            if ($.cookie('gy_donate') != null && $.cookie('gy_donate') != '{}' && $.cookie('gy_donate') != '' ) {
                initdata = jQuery.parseJSON($.cookie('gy_donate'));
                $.getJSON("/Api/Time/getServerTime",function(result){
                    //获取当前时间，以判断捐赠活动是否过期
                    current_time = result.current_time;
                    //初始化捐赠栏
                    $.each(initdata, function(index, value) {
                        console.log(index);
                        console.log(value);
                        if(value.endDate!=='undefined' && value.endDate < current_time){
                            //捐赠活动已过期，删除捐赠栏的项目
                            $.donateCart.delItem(value.name);
                        }
                        else{
                            //添加到捐赠栏
                            $.donateCart.addItem(index, value, false);
                        }
                    });
                    //计算总值
                    $.donateCart.sum();
                });
            }
            //更新捐赠按钮状态
            $.donateCart.changeButtonState();
        },
        sum: function() {
            //计算捐赠栏总额
            var donateTotalSum = 0;
            $('.donate-table input.donate-money').each(function() {
                donateTotalSum = accAdd(donateTotalSum, parseFloat($(this).val() ? $(this).val() : 0));
            });
            //更新捐赠栏总额
            $('#tools .donate-sum span').text('￥' + donateTotalSum);
            //更新捐赠栏数量
            $('#tools span.cart-number').text($('#tools .donate-table tr').length);
            if ($('#tools .donate-table tr').length === 0) {
                $('#tools span.cart-number').hide();
            } else {
                $('#tools span.cart-number').show();
            }
        },
        addItem: function(donateName, donateObj, refreshCookie) {
            //添加捐赠栏项
            //捐赠名称、捐赠信息对象、是否更新cookie
            var $donateItem = $('.donate-table tr#' + donateName);
            if ($donateItem.length) {
                //已存在捐赠项，修改值
                var $donateMoneyItem = $donateItem.find('.donate-money');
                var currentSum = parseFloat($donateMoneyItem.val());
                $donateMoneyItem.val(accAdd(currentSum, parseFloat(donateObj.sum)));
            } else {
                //不存在捐赠项，新增行
                var donateData = '<tr id="' + donateName + '"><td class="donate-name" width="40%">' + donateObj.name + '</td><td><input type="hidden" class="donate-type" value="' + donateObj.type + '"><input type="hidden" class="donate-id" value="' + donateObj.id + '"><input type="hidden" class="donate-end-date" value="' + donateObj.endDate + '"><input type="text" class="input-sm form-control donate-money" value="' + donateObj.sum + '"></td><td nowrap width="30%" class="text-right"><a href="#" class="donate-del">删除</a></td></tr>';
                //添加到表格
                $('.donate-table').append(donateData);
            }
            //计算总值
            $.donateCart.sum();
            //更新cookie
            if (refreshCookie) {
                $.donateCart.refreshCookie();
            }
            //更新捐赠按钮状态
            $.donateCart.changeButtonState();
        },
        delItem: function(donateName) {
            //删除捐赠栏指定项
            var $donateItem = $('.donate-table tr#' + donateName);
            if ($donateItem.length) {
                $donateItem.remove();
            }
            //计算总值
            $.donateCart.sum();
            //更新cookie
            $.donateCart.refreshCookie();
            //更新捐赠按钮状态
            $.donateCart.changeButtonState();
        },
        modifyItem: function(donateName, sum) {
            //修改捐赠栏指定项
            $('.donate-table tr#' + donateName).find('.donate-money').val(sum);
            //计算总值
            $.donateCart.sum();
            //更新cookie
            $.donateCart.refreshCookie();
        },
        refreshCookie: function() {
            //更新cookie
            var cookieData = {};
            $('.donate-table tr').each(function() {
                var donateType = $(this).find('input.donate-type').val();
                var donateId = $(this).find('input.donate-id').val();
                var donateName = $(this).find('.donate-name').text();
                var donateSum = $(this).find('input.donate-money').val();
                var donateEndDate = $(this).find('input.donate-end-date').val();
                var itemName = donateType + '_' + donateId;
                var donateObj = {
                    'id': donateId,
                    'type': donateType,
                    'name': donateName,
                    'sum': donateSum,
                    'endDate': donateEndDate
                };
                cookieData[itemName] = donateObj;
            });
            $.cookie('gy_donate', JSON.stringify(cookieData), {
                expires: 30,
                path: '/'
            });
        },
        buildOrderTable: function() {
            //通过cookie构建捐赠订单表格
            var data = {};
            if ($.cookie('gy_donate') != null && $.cookie('gy_donate') != '') {
                data = jQuery.parseJSON($.cookie('gy_donate'));
                //初始化捐赠栏
                $.each(data, function(index, value) {
                    var dataItem = '<tr><td>' + value.name + '</td><td class="orderSum">' + value.sum + '</td></tr>'
                    $('.donateOrderTable tbody').append(dataItem);
                });
                //计算总值
                var donateTotalSum = 0;
                $('.donateOrderTable td.orderSum').each(function() {
                    donateTotalSum = accAdd(donateTotalSum, parseFloat($(this).text() ? $(this).text() : 0));
                });
                $('.donateOrderSum span').text('￥' + donateTotalSum);
            }
        },
        addCart: function(event, img, endFunction) {
            //事件、飞入捐赠栏图片、回调函数（一般执行写入cookie操作）
            var offset = $("#tools .tool-button").offset();
            var flyer = $('<img class="u-flyer" src="' + img + '">');
            flyer.fly({
                start: {
                    left: event.pageX - $(window).scrollLeft(),
                    top: event.pageY - $(window).scrollTop()
                },
                end: {
                    left: offset.left - $(window).scrollLeft(),
                    top: offset.top - $(window).scrollTop(),
                    width: 0,
                    height: 0
                },
                speed: 1,
                vertex_Rtop: 200,
                onEnd: function() {
                    this.destory();
                    //执行写入cookie函数
                    endFunction();
                }
            });
        },
        changeButtonState: function(){
            if($('.donate-table').find('tr').length){
                $('#donateNow').removeClass('disabled');
            }
            else{
                $('#donateNow').addClass('disabled');
            }
        }
    }
})(jQuery);
