/** 
 * https://github.com/blueimp/jQuery-File-Upload/wiki/Client-side-Image-Resizing 
 * https://github.com/blueimp/jQuery-File-Upload/wiki
 * opts {}
 * url * 上传地址
 * name * 上传name
 * ele * 触发上传的元素 jq对象
 */

'use strict';
function Uploader(opts) {
  // 默认参数
  this.opts = {
    name: 'file',
    dataType: 'json'
  };
  // 传递的参数和默认参数合并
  $.extend(this.opts, opts);
  // 检测传递的参数
  this.init();
  this.insertInput();
}

Uploader.readFile = function (file, cb) {
  if (!/image\/\w+/.test(file.type)) {
    alert("文件必须为图片！");
    return false;
  }
  var reader = new FileReader();
  reader.readAsDataURL(file);
  reader.onload = function (e) {
    cb && cb(e.target.result);
  };
};

Uploader.prototype = {
  init: function init() {
    if (!this.opts.url) throw new Error('上传url必填');
    if (!this.opts.name) throw new Error('name必填');
    if (!this.opts.ele) throw new Error('触发上传的元素必填');
  },
  insertInput: function insertInput() {
    // 生成随机参数
    this.id = 'uploader' + new Date().getTime();
    // 创建粗发input
    var input = '<input style="visibility: hidden;width:0;height:0;" id=' + this.id + ' type="file"' + 'name=' + this.opts.name + ' />';
    $('body').append(input);
    this.inputEle = $('#' + this.id);
    this.handleClick();
  },
  handleClick: function handleClick() {
    var This = this;
    this.opts.ele.on('click', function () {
      This.handleUpload();
      This.inputEle.trigger('click');
    });
  },
  handleUpload: function handleUpload() {
    this.inputEle.fileupload(this.opts);
  },
  cancel: function cancel() {
    this.jqXHR && this.jqXHR.abort();
  }
};

// 操作DOM & 进度条
function addUploadEl(el, imgUrl) {
  var htmlEL = $('<div class="dash-border dash-border-d"><img src=' + imgUrl + ' alt=""><span class="progress"></span><span class="filedelete">×</span></div>');
  el.parent().append(htmlEL);
  return htmlEL.children('.progress');
}
function progressFn(el, progress) {
  el.css('width', progress + '%');
  if (progress >= 100) {
    setTimeout(function () {
      el.fadeOut();
    }, 300);
  }
}

function fileIdIndex(id) {
  var fileVal = $('input[name="letter_ids"]').val().split(',');
  for(var i = 0; i < fileVal.length; i++) {
    if(parseInt(id) === parseInt(fileVal[i])) {
      fileVal.splice(i, 1);
      $('input[name="letter_ids"]').val(fileVal.join(','));
      return i;
    }
  }
}

function deleteFile() {
  $('.upload-box').on('click', '.filedelete', function() {
    fileIdIndex($(this).parent().data('fileid'));
    $(this).parent().remove();
  })
}
  deleteFile();
