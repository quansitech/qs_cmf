jQuery(document).ready(function($) {
    if (!(document.cookie || navigator.cookieEnabled)) {
        alert('cookie 未打开!');
    }
    
    $('.topnav a.top').on('click', function(e){
        e.preventDefault();
    });
    $('.totop').toTop();
    $('.sidebar').affix({
      offset: {
        top: function(){
            return $('.wrapper').offset().top
        },
        bottom: function () {
          return $('#footer').outerHeight(true)
        }
      }
    });
    
    //添加到页面底部，避免受到其他样式影响
    $('#showLogin').appendTo('body');

    //ajax提交form
    $('body').on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        var formValues = $(this).serialize();
        $.ajax({
            url: $(this).attr('action') ? $(this).attr('action') : document.URL,
            type: $(this).attr('method'),
            data: formValues,
            beforeSend: function() {
                $('button[type=submit]').attr('disabled', true);
                return true;
            },
            success: function(data) {
                //成功状态，下面的是服务器返回的，数据库操作的状态
                var type;
                if (data.status === 1) {
                    type = "success";
                } else {
                    //console.log(data);
                    type = "error";
                    alert(data.info);
                }
                if (type === 'success') {
                    //有提示信息，应该先显示提示信息，再跳转url
                    if(data.info){
                        alert(data.info);
                        //如有跳转网址，则跳转到返回的网址
                        if(data.url){
                            window.location = data.url;
                        }
                        else{
                            window.location.reload();
                        }
                    }
                    //无提示信息，有跳转url，则直接跳转
                    else if(data.url){
                        window.location = data.url;
                    }
                } 
                $('button[type=submit]').removeAttr('disabled');
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert($(XMLHttpRequest.responseText).find('h1').text());
                $('button[type=submit]').removeAttr('disabled');
            },
        });
    });
    
    $('.stat-meta a').on('click',function(e){
        e.preventDefault();
    });
    
    //刷新验证码
    $('a.validate-img').on('click', function() {
        //重新加载验证码
        var img = $("<img />").attr('src', $(this).children().attr('src'))
            .on('load', function() {
                if (!this.complete || typeof this.naturalWidth == "undefined" || this.naturalWidth == 0) {
                    alert('验证码图片加载出错，请刷新');
                } else {
                    $(".validate-img").html('').append(img);
                }
            });
        return false;
    });
    //验证登录
    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        var formValues = $(this).serialize();
        $.ajax({
            url: $(this).attr('action') ? $(this).attr('action') : document.URL,
            type: $(this).attr('method'),
            data: formValues,
            beforeSend: function() {
                $('button[type=submit]').attr('disabled',true);
                return true;
            },
            success: function(data) {
                //成功状态，下面的是服务器返回的，数据库操作的状态
                var type;
                if (data.status === 1) {
                    type = "success";
                } else {
                    type = "error";
                    //显示错误信息，并清空验证码字段
                    alert(data.info);
                    if ($('a.validate-img').length) {
                        $('input[name="verify"]').val('');
                    }
                }
                if (type === 'success') {
                    //成功则跳转到返回的网址
                    setTimeout(function() {
                        window.location = data.url;
                    });
                } else {
                    //如果type=error，则不能执行跳转，需重新填写，重新启用提交按钮
                    $('button[type=submit]').attr('disabled',false);
                    //如果超过三次需要验证码，而验证码域未建立，则刷新页面以显示验证码
                    if (data.verify_show) {
                        $('.validate').show();
                        $('a.validate-img').trigger('click');
                    }
//                    //若验证码域已存在，则重新载入新的验证码
//                    if ($('a.validate-img').length) {
//                        $('a.validate-img').trigger('click');
//                    }
                }
            },
            error: function() {
                alert('提交出错，请检查网络或浏览器设置');
            },
        });
    });
    
    //退出登录
    $('a.logout').on('click', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('href'),
            success: function(data) {
                setTimeout(function() {
                    window.location = data.url;
                });
            }
        });
    });

});

//只能输入数字或小数
function ValidateFloat(e, pnumber) {
    if (!/^\d+[.]?\d*$/.test(pnumber)) {
        var newValue = /^\d+[.]?\d*/.exec(e.value);
        if (newValue != null) {
            e.value = newValue;
        } else {
            e.value = "";
        }
    }
    return false;
}
//只能输入一位小数
function ValidateFloat2(e, pnumber){
    if (!/^\d+[.]?[1-9]?$/.test(pnumber))
    {
        var newValue = /\d+[.]?[1-9]?/.exec(e.value);        
        if (newValue != null)        
        {            
            e.value = newValue;       
        }     
        else    
        {         
            e.value = "";   
        }
    }
    return false;
}

//精确加法
function accAdd(arg1, arg2) {
    var r1, r2, m;
    try {
        r1 = arg1.toString().split(".")[1].length
    } catch (e) {
        r1 = 0
    }
    try {
        r2 = arg2.toString().split(".")[1].length
    } catch (e) {
        r2 = 0
    }
    m = Math.pow(10, Math.max(r1, r2));
    return (arg1 * m + arg2 * m) / m;
}

//ajax访问网址并返回信息
function ajaxlink($this, url) {

    if (typeof url == 'string') {
        $.ajax({
            url: url, //与此php页面沟通 
            beforeSend: function() {
                //禁用提交按钮，防止重复提交
                $this.attr('disabled', true);
                toastr.remove();
                toastr["info"]("操作进行中");
                return true;
            },
            //            error: function() {
            //                toastr.remove();
            //                $.bs_messagebox('错误', '提交出错，请检查网络或浏览器设置', 'ok');
            //                $this.attr('disabled', false);
            //            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(XMLHttpRequest.readyState + XMLHttpRequest.status + XMLHttpRequest.responseText);
            },
            success: function(data) {


                if (data.status === 1) {
                    type = "success";
                    toastr.remove();
                    toastr["success"](data.info);
                } else {
                    type = "error";
                    toastr.remove();
                    $.bs_messagebox('错误', data.info, 'ok');
                }
                if (type === 'success') {
                    //成功则跳转到返回的网址
                    setTimeout(function() {
                        window.location = data.url;
                    }, 300);
                } else {
                    $this.attr('disabled', false);
                }
            }
        });
    }
}

function isEmpty(obj) {
    for (var name in obj) {
        return false;
    }
    return true;
};
