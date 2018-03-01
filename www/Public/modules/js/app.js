define('modules/js/app', function(require, exports, module) {

  // require('./zepto.min/zepto.min');
  var $ = require('modules/js/jquery-3.0.0/jquery-3.0.0.min');
  // 工具函数
  var utils = require('modules/js/utils');
  // 滚动条
  var iscrollBox = require('modules/js/iscroll/iscrollBox').scroll_fn();
  // 点击返回顶部
  require('modules/components/scrollTop/js/scrollTop').scrollTop();
  
  $(function() {
      //根据屏幕大小动态改变根节点字体大小
      // utils.fontRem();
      //根据屏幕大小动态改变根节点字体大小
      // $(window).resize(function(){
      // utils.fontRem();
      // })
  
      /*上传图片*/
      // $('#file1').change(function(){
      //     utils.uploadFile(this,$('.file-box1'),true);
      // });
      // $('#file2').change(function(){
      //     utils.uploadFile(this,$('.file-box2'));
      // });
      // $('#file3').change(function(){
      //     utils.uploadFile(this,$('.file-box3'));
      // });
      // $('#file3').change(function(){
      //     utils.uploadFile(this,$('.user-img-file')); 
      // });
      // 
  
  
      // var url = window.location.hostname === 'blueimp.github.io' ?
      //     '//jquery-file-upload.appspot.com/' : 'servers/php/';
  
      // var url = 'http://www.t4tstudio.com/api/upload/uploadImage'
      utils.fileupload($('#files-1'));
      utils.fileupload($('#files-2'));
  
  
  
  
  
      /*登录切换*/
      $('.login-register-nav a').click(function() {
          $('.logn-tab').hide().eq($(this).index()).show();
          $('.login-register-nav a').removeClass('on').eq($(this).index()).addClass('on');
          // 从新计算高度
          myScroll.refresh();
      });
  
      $('.t-administration .text').click(function() {
          $(this).prev('input').removeAttr('readonly').focus().one('blur', function() {
              $(this).attr('readonly', 'readonly')
          });
      })
  
  
      // 搜索
  
      $('.search-btn>a').on('click', function() {
          $('.search-page').addClass('on');
      });
  
      $('.search-cancel').on('click', function() {
          $('.search-page').removeClass('on');
      });
  
      //解决表单焦点无法失去
      $(document.body).click(function(e) {
          if (e.target.tagName.toLowerCase() == "input" || e.target.tagName.toLowerCase() == "textarea") {
              if ($(e.target.tagName).attr("type") == "text") {
                  return false;
              }
          } else {
              $('input,textarea').blur();
  
          }
      })
  
  
  
  
  })

});
