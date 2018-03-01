define('modules/main', function(require, exports, module) {

  var utils = require('modules/ignore/utils');
  // SuperSlide
  require('modules/ignore/SuperSlide/jquery.SuperSlide.2.1.1');
  // 数字滚动
  var CountUp = require('modules/ignore/int/countUp.min');
  require('modules/ignore/magnific/jquery.magnific-popup');
  $(function(){ 
      // banner
      jQuery(".banner").slide({ titCell: ".hd ul", mainCell: ".bd ul", effect: "fold", autoPlay: true, autoPage: true, trigger: "click" });
      jQuery(".scrollBox_36").slide({ titCell:".list li", mainCell:".piclist", effect:"left",vis:6,scroll:1,delayTime:400,trigger:"click",easing:"easeOutCirc"});
      //数字滚动
      (function(){
          var options = {  
              useEasing: true,
                useGrouping: true,
                separator: ',',
                decimal: '.',
                prefix: '',
                suffix: ''
          };
          try{
              var myTargetElement = new CountUp("myTargetElement", 0, $('#myTargetElement').text(), 2, 2.5, options);
              var myTargetElement2 = new CountUp("myTargetElement2", 0, $('#myTargetElement2').text(), 2, 2.5, options);
              var myTargetElement3 = new CountUp("myTargetElement3", 0, $('#myTargetElement3').text(), 2, 2.5, options);
              var myTargetElement4 = new CountUp("myTargetElement4", 0, $('#myTargetElement4').text(), 2, 2.5, options);
              $(window).scroll(function() { 
                  if ($(window).scrollTop() >= 80) {
                      myTargetElement.start();
                      myTargetElement2.start();
                      myTargetElement3.start();
                      myTargetElement4.start();
                  }
              })
          }catch(e){
  
          }
      })()
  
      // 视频
      if ($('.video').length) {
          $('.video').magnificPopup({
              type: 'iframe'
          });
      }
  })
  
  // utils.fileupload($('#files-1'));
  
  $('div[id^="files-"]').each(function(){
      utils.fileupload($(this));
  
  });
  
  utils.pageTab($('.nav_js li'),$('.content_js'));
  utils.pageTab($('.nav_js2 li'),$('.content_js2'));

});
