define('modules/components/scrollTop/js/scrollTop', function(require, exports, module) {

  var IScroll = require('modules/js/iscroll/iscroll');
  function onscroll(){
      return myScroll.y;
  }
  exports.scrollTop = function(){
      $('#scrollTop').click(function(){
          $(this).hide();
          new IScroll('#wrapper', {y: 0});
      });
  }
  
  

});
