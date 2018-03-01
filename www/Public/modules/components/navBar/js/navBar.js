define('modules/components/navBar/js/navBar', function(require, exports, module) {

  exports.fclick = function() {
    $('.nav-bar').click(function(){
      alert($(this).html());
      })  
  }
  
  

});
