String.prototype.trim=function(){
    return this.replace(/(^\s*)|(\s*$)/g, "");
}
String.prototype.ltrim=function(){
    return this.replace(/(^\s*)/g,"");
}
String.prototype.rtrim=function(){
   return this.replace(/(\s*$)/g,"");
}

jQuery(document).ready(function($) {
    //ajax提交form
    $('body').on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        var formValues = $(this).serialize();
        $.ajax({
            url: $(this).attr('action') ? $(this).attr('action') : document.URL,
            type: $(this).attr('method'),
            data: formValues,
            beforeSend: function() {
                return true;
            },
            success: function(data) {
                //成功状态，下面的是服务器返回的，数据库操作的状态
                console.log(data);
                var type;
                if (data.status === 1) {
                    type = "success";
                } else {
                    type = "error";
                    alert(data.info);
                }
                if (type === 'success') {
                    //有提示信息，应该先显示提示信息，再跳转url
                    if(data.info){
                        alert(data.info);
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
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log('error2');
                alert($(XMLHttpRequest.responseText).find('h1').text());
                $('button[type=submit]').button('reset').removeAttr('disabled');
            },
        });
    });
    
    $('.ajax-link').on('click', function(){
        var url = $(this).data('url');
        ajaxlink($(this), url);
    });

    if ($('.file_upload').length) {
        $('.file_upload').fileupload({
            dataType: 'json',
            done: function(e, data) {
                var result = data.result;
                $parentWrapper = $(this).parents('.upload-wrapper');
                if (result.Status == 1) {
                    if (result.Data.url)
                        $parentWrapper.find('.file_name').html('<a href="' + result.Data.url + '">' + result.Data.title + '</a>');
                    $parentWrapper.find('.file_id').val(result.Data.file_id);
                } else {
                    $parentWrapper.find('p.upload_error').text(result.info);
                }
            },
            progress: function(e, data) {
                $(this).parents('.upload-wrapper').find('p.upload_error').text('');
                $progress = $(this).parents('.upload-wrapper').find('.progress');
                if ($progress.not('.in')) {
                    $progress.addClass('in');
                }
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $progress.children('.progress-bar').css(
                    'width',
                    progress + '%'
                );
                if (progress === 100) {
                    progress = 0;
                    setTimeout(function() {
                        $progress.removeClass('in').children().css(
                            'width',
                            progress + '%'
                        );
                    }, 1000);
                };
            }
        });
    }
    if ($('.image_upload').length) {
        $('.image_upload').fileupload({
            dataType: 'json',
            done: function(e, data) {
                var result = data.result;
                console.log(result);
                $parentWrapper = $(this).parents('.upload-wrapper');
                if (result.Status == 1) {
                    if (result.Data.url)
                        $parentWrapper.find('.image_pic').attr('src', result.Data.url);
                    $parentWrapper.find('.image_id').val(result.Data.file_id);
                } else {
                    $parentWrapper.find('p.upload_error').text(result.info);
                }
            },
            progress: function(e, data) {
                $(this).parents('.upload-wrapper').find('p.upload_error').text('');
                $progress = $(this).parents('.upload-wrapper').find('.progress');
                if ($progress.not('.in')) {
                    $progress.addClass('in');
                }
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $progress.children('.progress-bar').css(
                    'width',
                    progress + '%'
                );
                if (progress === 100) {
                    progress = 0;
                    setTimeout(function() {
                        $progress.removeClass('in').children().css(
                            'width',
                            progress + '%'
                        );
                    }, 1000);
                };
            }
        });
    }

});

function ajaxlink($this, url) {

    if (typeof url == 'string') {
        $.ajax({
            url: url, //与此php页面沟通 
            beforeSend: function() {
                //禁用提交按钮，防止重复提交
                $this.attr('disabled', true);
                return true;
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(XMLHttpRequest.readyState + XMLHttpRequest.status + XMLHttpRequest.responseText);
            },
            success: function(data) {
                console.log(data);
                if (data.status === 1) {
                    type = "success";
                    alert(data.info);
                } else {
                    type = "error";
                    alert(data.info);
                    
                    if(data.url.length > 0){
                        window.location = data.url;
                    }
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

function ajaxlinkNoRefresh($this, url) {

    if (typeof url == 'string') {
        $.ajax({
            url: url, //与此php页面沟通 
            beforeSend: function() {
                //禁用提交按钮，防止重复提交
                $this.attr('disabled', true);
                return true;
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(XMLHttpRequest.readyState + XMLHttpRequest.status + XMLHttpRequest.responseText);
            },
            success: function(data) {
                if (data.status === 1) {
                    type = "success";
                    alert(data.info);
                } else {
                    type = "error";
                    alert(data.info);
                }
                if (type === 'success') {

                } else {
                    $this.attr('disabled', false);
                }
            }
        });
    }
}