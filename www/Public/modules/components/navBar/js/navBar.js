define('modules/components/navBar/js/navBar', function(require, exports, module) {

  exports.fclick = function() {
    $('.nav-bar').on('click', function(){
      alert($(this).html());
      })  
  }
  
  

});
