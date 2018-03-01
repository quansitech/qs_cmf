define('modules/ignore/utils', function(require, exports, module) {

  require('modules/ignore/fileupload/jquery.ui.widget');
  require('modules/ignore/fileupload/jquery.iframe-transport');
  require('modules/ignore/fileupload/jquery.fileupload');
  module.exports = {
      //JQ-fileupload插件上传文件
        fileupload: function(fileImgBox) {
            var fileBtn = fileImgBox.find('.fileupload-file');
            var parent = $(fileBtn).parent();
            var progressBox = fileImgBox.children('.progress');
            var fileProgress = fileImgBox.children('.progress').children('.progress-bar');
            var fileHidden = fileImgBox.find('.files-hidden');
            var valArr = [];
            var fileBase64 = [];
            var url = fileBtn.attr('data-url');
            var fileId;
    
    
            fileBtn.change(function() {
                fileBase64.length = 0;
                for (var i = 0, l = this.files.length; i < l; i++) {
                    reader = new FileReader();
                    reader.readAsDataURL(this.files[i]);
                    reader.onload = function(e) {
                        // box.prepend('<img src='+ this.result +'>');  
                        fileBase64.push(this.result)
                    }
                }
    
            })
  
            var closeBtn = fileImgBox.find('.file-img-box a');
            closeBtn.unbind('click').one('click', function(e) {
                valArr.splice(valArr.indexOf($(this).prev('img').attr('data-id')), 1);
                fileHidden.val(valArr.join(','));
                if (fileImgBox.find('.file-img-box').length == 1) {
                    parent.show();
                }
                $(this).parent().remove();
            
            })
            // console.log(fileHidden.val().split(','));
            // t
            
            try{
              if(fileHidden.val() != 0){
                valArr=valArr.concat(fileHidden.val().split(','))
              }
          }catch(e) {
  
            }
  
  
           console.log(valArr);
  
  
            fileBtn.fileupload({
                    url: url,
                    dataType: 'json',
                    multipart: true,
                    done: function(e, data) {
                        $.each(data.result.files, function(index, file) {
    
                        });
    
                    },
                    beforeSend: function() {
                        fileProgress.css(
                            'width',
                            0 + '%'
                        );
                        progressBox.css('visibility', 'visible');
                        if (!fileBtn.attr('multiple')) {
                            parent.hide();
                        }
                    },
                    success: function(data) {
                        if (data.Status != 1) {
                            alert(data.info);
                            parent.show();
                            return;
                        }
                        valArr.push(data.Data.file_id);
                        fileHidden.val(valArr.join(','));
                        fileId = data.Data.file_id;
                        $.each(fileBase64, function(index, item) {
                            fileImgBox.prepend('<span class="file-img-box"><img src = "' + item + '" data-id = "' + fileId + '"/><a href="javascript:void(0);">×</a></span>');
                            var closeBtn = fileImgBox.find('.file-img-box a');
                            closeBtn.unbind('click').one('click', function(e) {
                                valArr.splice(valArr.indexOf($(this).prev('img').attr('data-id')), 1);
                                fileHidden.val(valArr.join(','));
                                if (fileImgBox.find('.file-img-box').length == 1) {
                                    parent.show();
                                }
                                $(this).parent().remove();
    
    
                            })
                        })
                        fileBase64.length = 0;
                    },
                    progressall: function(e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        fileProgress.animate({
                                'width': progress + '%'
                            })
                            // console.log(progress);
                        if (progress == 100) {
                            setTimeout(function() {
                                progressBox.css('visibility', 'hidden');
                            }, 1000);
                        }
                    }
                }).prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? undefined : 'disabled');
        }
//      pageTab: function(tab_btn, tab_box) { 
//          tab_btn.click(function() {
//              var _this = this;
//              tab_btn.each(function(index) {
//                  if (tab_btn.get(index) == _this) {
//                      tab_box.eq(index).fadeIn(300);
//                      tab_btn.eq(index).addClass('on')
//                  } else {
//                      tab_box.eq(index).hide();
//                      tab_btn.eq(index).removeClass('on')
//                  }
//              })
//          })
//      }
  }

});
