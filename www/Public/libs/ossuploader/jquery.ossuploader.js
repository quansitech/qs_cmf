(function($){
    function guid() {
        function S4() {
           return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
        }
        return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
    }
  
    function Crop(opt, browse_button, plupload){
       this.options = opt;
       this.browse_button = browse_button;
       this.plupload = plupload;
  
       this.createDom();
       this.bindEvent();
    };
  
    Crop.prototype.bindEvent = function(){
        var crop = this;
        var browse_button = this.browse_button;
        $('body').on('click', '#' + browse_button, function(){
            crop.fileInput.trigger('click');
        });
        this.close.on('click', function(){
            crop.hideModal();
        });
        this.fileInput.on('click', function(e){
            this.value = '';
        });
        this.fileInput.on('change', function(e){
            crop.loadUploadImg(e.target.files[0]);
            crop.showModal();
        });
        this.cropButton.on('click', function(){
            var canvas = crop.cropper.getCroppedCanvas();
            canvas.toBlob(function(blob){
                crop.plupload.addFile(blob);
                crop.hideModal();
            },'image/jpeg');
        });
    }
  
    Crop.prototype.createDom = function(){
      this.fileInput = $('<input type="file" />');
      this.mask = $('<div class="ossuploader-mask"></div>');
      this.mask.attr('id', guid());
      var close_container = $('<div class="close-container"></div>');
      this.cropButton = $('<button class="crop-btn">截取</button>');
      this.close = $('<span class="close"></span>');
      this.close.text('X');
      close_container.append(this.cropButton);
      close_container.append(this.close);
      this.image = $('<div class="image"><img class="ori-img" /></div>');
      this.mask.append(close_container);
      this.mask.append(this.image);
      $('body').append(this.mask);
    };
  
    Crop.prototype.showModal = function(){
       $(this.mask[0]).show();
    };
  
    Crop.prototype.hideModal = function(){
       $(this.mask[0]).hide();
       this.closeMask();
    };
  
    Crop.prototype.closeMask = function(){
        this.image.children('img').removeAttr('src');
        this.cropper.destroy();
    }
  
    Crop.prototype.loadUploadImg = function(file){
        if($.inArray(file.type, ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/bmp']) == -1){
            alert('请选择图片类型文件');
            throw '请选择图片类型文件';
        }
        var crop = this;
        var reader = new FileReader();
        reader.onload = function(e){
            crop.image.children('img').attr('src', e.target.result);
            crop.initCropper();
        }
        reader.readAsDataURL(file);
    }
  
    Crop.prototype.initCropper = function(){
        if(this.cropper){
          this.cropper.destroy();
        }
        this.cropper = new Cropper(this.image.children('img')[0], this.options);
  
    }
  
       $.fn.ossuploader = function(option){
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
  
           var defaultSetting = { multi_selection:false};
           var setting = $.extend(defaultSetting,option);
  
           var send_request = function(url){
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
           }
  
           var get_signature = function(url){
               //可以判断当前expire是否超过了当前时间,如果超过了当前时间,就重新取一下.3s 做为缓冲
               now = timestamp = Date.parse(new Date()) / 1000;
               body = send_request(url);
               var obj = eval ("(" + body + ")");
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
           }
  
           var get_suffix = function(filename){
               pos = filename.lastIndexOf('.');
               suffix = '';
               if (pos != -1) {
                   suffix = filename.substring(pos);
               }
               return suffix;
           }
  
           var calculate_object_name = function(filename){
               suffix = get_suffix(filename);
               g_object_name = key + suffix;
           }
  
           var set_upload_param = function(up, filename, ret, url){
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
  
           //upload_flag true 为文件上传流程
           //upload_flag false 数据读取流程
           var add_img = function(parent_div, id, src, upload_flag, file_id){
             var htmlEL = $('<div class="ossuploader-dash-border" id="' + id + '"><img src="' + src + '" alt=""><span class="ossuploader-progress"></span><span class="ossuploader-filedelete">×</span></div>');
             if(upload_flag == true && !setting.multi_selection){
               $(parent_div).children('.ossuploader-dash-border').remove();
             }
  
             if(upload_flag == false && file_id){
               htmlEL.attr('data-fileid', file_id);
             }
             $(parent_div).append(htmlEL);
           }
  
  
           var init = function(o){
              var files_length = 0;
              var file_count = 0;
              var parent = o.parentNode;
              var clone_o = o.cloneNode();
              var div = document.createElement('div');
  
              div.className = 'ossuploader-upload-box';
              div.id = "ossuploader_upload_box_" + guid();
  
              var div_add = document.createElement('div');
              div_add.className = 'ossuploader-add';
              div_add.id = 'ossuploader_upload_' + guid();
  
              setting.browse_button = div_add.id;
              if(setting.crop){
                  var div_add1 = document.createElement('div');
                  div_add1.id = 'ossuploader_upload1_' + guid();
                  setting.browse_button = div_add1.id;
                  if(setting.multi_selection) setting.uploader_multi_selection = false;
                  div.appendChild(div_add1);
              }

              if(typeof setting.uploader_multi_selection === 'undefined') setting.uploader_multi_selection = setting.multi_selection;
  
              var i = document.createElement('i');
              var p = document.createElement('p');
  
              p.innerText = '添加图片';
  
              div_add.appendChild(i);
              div_add.appendChild(p);
  
              div.appendChild(div_add);
              div.appendChild(clone_o);
  
              var values,srcs;
              values = o.value.split(',');
              if(values && o.dataset && o.dataset.srcjson){
                srcs = JSON.parse(o.dataset.srcjson);
  
                for(var n = 0; n < values.length; n++){
  
                  var id = new Date().getTime();
                  id = id + values[n];
                  add_img(div, id, srcs[n], false, values[n]);
                }
                clone_o.removeAttribute("data-srcjson");
              }
              parent.replaceChild(div, o);
              o = null;
  
              var pluploaduploader = new plupload.Uploader({
                 runtimes : 'html5,flash,silverlight,html4',
                 browse_button : setting.browse_button,
                 multi_selection: setting.uploader_multi_selection,
                 container: document.getElementById(div.id),
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
                         //$('#'+container).children('.uploadify-queue').html('');
                     },
  
                     FilesAdded: function(up, files) {
                         if(setting.beforeUpload && typeof setting.beforeUpload == 'function'){
                             if(setting.beforeUpload() === false){
                                return;
                             }
                         }
                         files_length += files.length;
                         plupload.each(files, function(file) {
                             var reader = new FileReader();
                             reader.readAsDataURL(file.getNative());
                             reader.onload = function (e) {
                               add_img(div, file.id, e.target.result, true);
                             };
  
                         });
                         up.start();
                     },
  
                     BeforeUpload: function(up, file) {
                         if(setting.oss == true){
                             set_upload_param(up, file.name, false, setting.url);
                         }
                         else{
                             up.setOption({
                                 'url': setting.url
                             });
                             up.start();
                         }
                     },
  
                     UploadProgress: function(up, file) {
                         var el = $('#' + file.id).children('.ossuploader-progress');
                         el.css('width', file.percent + '%');
                     },
  
                     FileUploaded: function(up, file, info) {
                         if (info.status == 200)
                         {
                             var response = JSON.parse(info.response);
                             if(response.err_msg){
                                 alert(response.err_msg);
                             }
                             else{
                                 if(!setting.multi_selection){
                                     $(clone_o).val(response.file_id);
                                 }
                                 else{
                                     var file_id = $(clone_o).val();
                                     if(file_id){
                                         $(clone_o).val(file_id + ',' + response.file_id);
                                     }
                                     else{
                                         $(clone_o).val(response.file_id);
                                     }
                                 }
                                 $('#' + file.id).attr('data-fileid', response.file_id);
                             }
                         }
                         else
                         {
                             alert(info.response);
                         }
  
                         file_count++;
                         if(files_length === file_count && setting.uploadCompleted && typeof setting.uploadCompleted == "function"){
                             setting.uploadCompleted();
                         }
                     },

                     Error: function(up, err) {
                         // if (err.code == -600) {
                         //     alert("选择的文件太大了,可以根据应用情况，在upload.js 设置一下上传的最大大小");
                         // }
                         // else if (err.code == -601) {
                         //     alert("选择的文件后缀不对,可以根据应用情况，在upload.js进行设置可允许的上传文件类型");
                         // }
                         // else if (err.code == -602) {
                         //     alert("这个文件已经上传过一遍了");
                         // }
                         // else if(err.code == -200){
                         //     alert('文件太大了');
                         // }
                         // else
                         // {
                             alert(err.response);
                         // }
                     }
                 }
             });
  
             pluploaduploader.init();
  
             if(setting.crop){
                 var crop = new Crop(setting.crop, div_add.id, pluploaduploader);
             }
  
               $(div).on('click', '.ossuploader-filedelete', function() {
                   var box = $(this).parents('.ossuploader-upload-box');
                   var input = box.children('input[type=hidden]');
                   var fileVal = input.val().split(',');
                   var id = $(this).parent().data('fileid');
                   for(var i = 0; i < fileVal.length; i++) {
                       if(parseInt(id) === parseInt(fileVal[i])) {
                           fileVal.splice(i, 1);
                           input.val(fileVal.join(','));
                           break;
                       }
                   }
                   $(this).parent().remove();
               });
           };
  
           (function(o){
             $(o).each(function(){
                init(this);
             });
  
  
          }(this));
  
       }
  
  }(jQuery));
  