
var accessid = '';
var accesskey = '';
var host = '';
var policyBase64 = '';
var signature = '';
var callbackbody = '';
var filename = '';
var key = '';
var expire = 0;
var g_object_name = '';
var callbackvar;
var oss_meta;
var now = timestamp = Date.parse(new Date()) / 1000; 

function send_request(url)
{
    var xmlhttp = null;
    if (window.XMLHttpRequest)
    {
        xmlhttp=new XMLHttpRequest();
    }
    else if (window.ActiveXObject)
    {
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
  
    if (xmlhttp!=null)
    {
        serverUrl = url;
        xmlhttp.open( "GET", serverUrl, false );
        xmlhttp.send( null );
        return xmlhttp.responseText
    }
    else
    {
        alert("Your browser does not support XMLHTTP.");
    }
};


function get_signature(url)
{
    //可以判断当前expire是否超过了当前时间,如果超过了当前时间,就重新取一下.3s 做为缓冲
    now = timestamp = Date.parse(new Date()) / 1000; 
    body = send_request(url);
    var obj = eval ("(" + body + ")");
    console.log(obj);
    host = obj['host'];
    policyBase64 = obj['policy'];
    accessid = obj['accessid'];
    signature = obj['signature'];
    expire = parseInt(obj['expire']);
    callbackbody = obj['callback'];
    callbackvar = obj['callback_var'];
    oss_meta = obj['oss_meta'];
    key = obj['dir'];
    return true;
};

function get_suffix(filename) {
    pos = filename.lastIndexOf('.');
    suffix = '';
    if (pos != -1) {
        suffix = filename.substring(pos);
    }
    return suffix;
}

function calculate_object_name(filename)
{
    suffix = get_suffix(filename);
    g_object_name = key + suffix;
}

function set_upload_param(up, filename, ret, url)
{
    if (ret == false)
    {
        ret = get_signature(url);
    }
    g_object_name = key;
    if (filename != '') {
        suffix = get_suffix(filename)
        calculate_object_name(filename)
    }
    new_multipart_params = {
        'key' : g_object_name,
        'policy': policyBase64,
        'OSSAccessKeyId': accessid, 
        'success_action_status' : '200', //让服务端返回200,不然，默认会返回204
        'callback' : callbackbody,
        'signature': signature,
    };
    
    var var_obj = JSON.parse(callbackvar);
    for(var key in var_obj){
        new_multipart_params[key] = var_obj[key];
    }
    if(oss_meta){
        var meta_obj = JSON.parse(oss_meta);
        for(var meta in meta_obj){
            new_multipart_params[meta] = meta_obj[meta];
        }
    }
    up.setOption({
        'url': host,
        'multipart_params': new_multipart_params
    });
    up.start();
}


/* 
    * 封装图片上传初始化方法
    * browseButton:上传的按钮
    * container: browseButton的父级
    * imgBox: 图片预览的box
    * inputName: input[type="file"]的name的值
*/

// 单图上传
// initPlupload('_upload_1_selectfiles','_upload_1','_preview_1','avatar');
function initPlupload(browseButton,container,imgBox,inputName) {
     new plupload.Uploader({
        runtimes : 'html5,flash,silverlight,html4',
        browse_button : browseButton, 
        multi_selection: false,
        container: document.getElementById(container),
        flash_swf_url : '/Public/libs/plupload-2.1.2/js/Moxie.swf',
        silverlight_xap_url : '/Public/libs/plupload-2.1.2/js/Moxie.xap',
        url : 'http://oss.aliyuncs.com',

        filters: {
            // mime_types : [ //只允许上传图片
            // { title : "Image files", extensions : "jpg,gif,png,bmp,jpeg" }, 
            // ],
            prevent_duplicates : false //允许选取重复文件
        },

        init: { 
            PostInit: function() {
                $('#'+container).children('.uploadify-queue').html('');
            },

            FilesAdded: function(up, files) {
                plupload.each(files, function(file) {
                    var html = '<div id="' + file.id + '" class="uploadify-queue-item">' + 
                            '<div class="uploadify-progress" lastloaded="0">' +
                            '<div class="uploadify-progress-bar" style="width: 0%;">' +
                            '</div>' +
                            '</div>' +
                            '<span class="up_percent">0%</span>' + 
                            
                            '</div>';
                    $('#'+container).children('.uploadify-queue').html(html);
                    set_upload_param(up, file.name, false, $('#'+container).data('url'));
                });
                
            },

           // BeforeUpload: function(up, file) {
           //     check_object_radio();
           // },

            UploadProgress: function(up, file) {
                $('#' + file.id).children('.uploadify-progress').attr('lastloaded', file.loaded);
                $('#' + file.id).find('.uploadify-progress-bar').css('width', file.percent + "%");
                $('#' + file.id).children('.up_percent').html(file.percent + "%");
            },

            FileUploaded: function(up, file, info) {
                if (info.status == 200)
                {
                    var response = JSON.parse(info.response);
                    if(response.err_msg){
                        alert(response.err_msg);
                    }
                    else{
                        $('#'+imgBox).html('');
                        $('#'+imgBox).append('<input type="hidden" name="' + inputName + '" value="' + response.file_id + '">');
                        $('#'+imgBox).append('<span class="img-box"><img class="img" src="' + response.file_url + '"><i class="remove-picture"></i></span>');
                    }
                }
                else
                {
                    alert(info.response);
                } 
                $('#'+container).children('.uploadify-queue').empty();
            },

            Error: function(up, err) {
                if (err.code == -600) {
                    alert("选择的文件太大了,可以根据应用情况，在upload.js 设置一下上传的最大大小");
                }
                else if (err.code == -601) {
                    alert("选择的文件后缀不对,可以根据应用情况，在upload.js进行设置可允许的上传文件类型");
                }
                else if (err.code == -602) {
                    alert("这个文件已经上传过一遍了");
                }
                else if(err.code == -200){
                    alert('文件太大了');
                }
                else 
                {
                    console.log(err);
                    alert(err.response);
                }
            }
        }
    }).init();
    
    //删除图片
    $('#'+imgBox).on('click','.remove-picture', function(){
        $('#'+imgBox+' input').val('') //删除后覆盖原input的值为空
        $(this).closest('.img-box').remove(); //删除图片预览图
    });
}

// 多图上传
// initPluploadMulti('_upload_1_selectfiles','_upload_1','_preview_1','config[SITE_GALLERY]');
function initPluploadMulti(browseButton,container,imgBox,inputName){
     new plupload.Uploader({
        runtimes : 'html5,flash,silverlight,html4',
        browse_button : browseButton, 
        multi_selection: true,
        container: document.getElementById(container),
        flash_swf_url : '{:asset("libs/plupload-2.1.2/js/Moxie.swf")}',
        silverlight_xap_url : '{:asset("libs/plupload-2.1.2/js/Moxie.xap")}',
        url : 'http://oss.aliyuncs.com',

        filters: {
            // mime_types : [ //只允许上传图片
            // { title : "Image files", extensions : "jpg,gif,png,bmp,jpeg" }, 
            // ],
            prevent_duplicates : false //允许选取重复文件
        },

        init: { 
            PostInit: function() {
                $('#'+container).children('.uploadify-queue').html('');
            },

            FilesAdded: function(up, files) {
                plupload.each(files, function(file) {
                    var html = '<div id="' + file.id + '" class="uploadify-queue-item">' + 
                            '<div class="uploadify-progress" lastloaded="0">' +
                            '<div class="uploadify-progress-bar" style="width: 0%;">' +
                            '</div>' +
                            '</div>' +
                            '<span class="up_percent">0%</span>' + 
                            
                            '</div>';
                    $('#'+container).children('.uploadify-queue').append(html);
                });
                up.start();
            },

            BeforeUpload: function(up, file) {
                set_upload_param(up, file.name, false, $('#'+container).data('url'));
            },

            UploadProgress: function(up, file) {
                $('#' + file.id).children('.uploadify-progress').attr('lastloaded', file.loaded);
                $('#' + file.id).find('.uploadify-progress-bar').css('width', file.percent + "%");
                $('#' + file.id).children('.up_percent').html(file.percent + "%");
            },

            FileUploaded: function(up, file, info) {
                if (info.status == 200)
                {
                    var response = JSON.parse(info.response);
                    if(response.err_msg){
                        alert(response.err_msg);
                    }
                    else{
                        var file_id = $('input[name="' + inputName + '"]').val();
                        if(file_id){
                            $('input[name="' + inputName + '"]').val(file_id + ',' + response.file_id);
                        }
                        else{
                            $('input[name="' + inputName + '"]').val(response.file_id);
                        }

                        $('#'+imgBox).append('<span class="img-box"><img class="img" src="' + response.file_url + '" data-id="' + response.file_id + '"><i class="remove-picture"></i></span>');
                    }
                }
                else
                {
                    alert(info.response);
                } 
                $('#'+container).children('.uploadify-queue').find('#' + file.id).remove();
            },

            Error: function(up, err) {
                if (err.code == -600) {
                    alert("选择的文件太大了,可以根据应用情况，在upload.js 设置一下上传的最大大小");
                }
                else if (err.code == -601) {
                    alert("选择的文件后缀不对,可以根据应用情况，在upload.js进行设置可允许的上传文件类型");
                }
                else if (err.code == -602) {
                    alert("这个文件已经上传过一遍了");
                }
                else 
                {
                    alert(err.response);
                }
            }
        }
    }).init();
    
    //删除图片
    $('#'+imgBox).on('click','.remove-picture', function(){
        var id = $(this).siblings('.img').data("id");
        removeId(id);
        $(this).closest('.img-box').remove(); //删除图片预览图
    });
    
    function removeId(id) {
        var fileVal = $('input[name="' + inputName + '"]').val().split(',');
        for(var i = 0; i < fileVal.length; i++) {
            if(parseInt(id) === parseInt(fileVal[i])) {
              fileVal.splice(i, 1);
              $('input[name="' + inputName + '"]').val(fileVal.join(','));
              return i;
            }
        }
    }
}