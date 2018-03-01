define('modules/js/utils', function(require, exports, module) {

  
  var $ = require('modules/js/jquery-3.0.0/jquery-3.0.0.min');
  require('modules/js/fileupload/jquery.ui.widget');
  require('modules/js/fileupload/jquery.iframe-transport');
  require('modules/js/fileupload/jquery.fileupload');
  module.exports = {
      fontRem: function() {
          var designW = 640;
          var html = $('html');
          var winW = html.width();
          html.css('font-size',(winW / designW) * 100 +'px');
      },
      uploadFile: function(obj,box,len){
          /**
           * html5上传文件
           */
          var len = len ? true : false;
          if(box.find('img').length > 1 && len){
              alert('只能选择一张图片');
              return;
          }
          function html5Reader(obj,box){
              // var len = len ? '' : 1;
              for(var i = 0 , l = obj.files.length; i < l ; i++){ 
                      reader = new FileReader();
                      reader.readAsDataURL( obj.files[i]);
                      reader.onload = function(e){
                          box.prepend('<img src='+ this.result +'>');         
                      }
              }
       
          }
          var ext=obj.value.substring(obj.value.lastIndexOf('.')+1).toLowerCase();
           if(ext!='png'&&ext!='jpg'&&ext!='jpeg'){
               alert("图片的格式必须为png或者jpg或者jpeg格式！"); 
               return;
           }
           html5Reader(obj,box);
      },
      //JQ-fileupload插件上传文件
        fileupload:function(fileImgBox){
            var fileBtn = fileImgBox.find('.fileupload-file');
            var parent = $(fileBtn).parent();
            var progressBox = fileImgBox.children('.progress');
            var fileProgress = fileImgBox.children('.progress').children('.progress-bar');
            var fileHidden = fileImgBox.find('.files-hidden');
            var valArr = [];
            var url = fileImgBox.attr('data-url');
               fileBtn.fileupload({
                        url: url,
                        dataType: 'json',
                        multipart:true,
                        done: function(e, data) {
                            $.each(data.result.files, function(index, file) {
                            });
                        },
                        beforeSend:function(){
                          fileProgress.css(
                              'width',
                              0 + '%'
                          );
                          progressBox.css('visibility','visible');
                        },
                        success: function(data) {
                            console.log(data);
                          valArr.push(data.Data.file_id);
                          fileImgBox.prepend('<img src = "' + data.Data.url + '"/>');
                          fileHidden.val(valArr.join(','));
                            if(!fileBtn.attr('multiple')){
                                    parent.hide();
                              }
                        
                        },
                        progressall: function(e, data) {
                            var progress = parseInt(data.loaded / data.total * 100, 10);
                            fileProgress.animate({
                            'width':progress + '%'
                            })
                            // console.log(progress);
                            if(progress == 100){
                              setTimeout(function(){
                                  progressBox.css('visibility','hidden');
                              },1000)
                            }
                        }
                    }).prop('disabled', !$.support.fileInput)
                    .parent().addClass($.support.fileInput ? undefined : 'disabled');
        }
  }

});
