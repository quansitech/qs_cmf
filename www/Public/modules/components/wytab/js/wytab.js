define('modules/components/wytab/js/wytab', function(require, exports, module) {

  var TouchSlide = require('modules/js/TouchSlide/TouchSlide.1.1').TouchSlide;
  exports.wytab = function() {
      TouchSlide({
          slideCell: "#leftTabBox",
  
          endFun: function(i) { //高度自适应
              var bd = document.getElementById("tabBox1-bd");
              bd.parentNode.style.height = bd.children[i].children[0].offsetHeight + "px";
              if (i > 0) bd.parentNode.style.transition = "200ms"; //添加动画效果
          }
  
      });
  }

});
