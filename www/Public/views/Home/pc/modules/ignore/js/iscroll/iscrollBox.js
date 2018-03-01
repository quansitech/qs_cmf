define('modules/js/iscroll/iscrollBox', function(require, exports, module) {

  
  var IScroll = require('modules/js/iscroll/iscroll');
  
  
  function updatePosition () {
      myScroll.refresh();
      if(this.y < -400){
          $('#scrollTop').show();
      }else{
          $('#scrollTop').hide();
      }
  }
  
  function loaded () {
      if(navigator.userAgent.indexOf('UCBrowser') > -1) {
          myScroll = new IScroll('#wrapper', {mouseWheel: true, click: true,useTransform:false});
      }else{
          myScroll = new IScroll('#wrapper', {mouseWheel: true, click: true});
      }
      myScroll.on('scroll', updatePosition);
      myScroll.on('scrollEnd', updatePosition);
  }
  
  // document.addEventListener('touchmove', function (e) { e.preventDefault(); }, false);
  exports.scroll_fn = function(){
      $(function(){
          loaded();
  
          var myScroll;
  
          document.addEventListener('touchmove', function (e) { e.preventDefault(); }, false);
      })
  }

});
