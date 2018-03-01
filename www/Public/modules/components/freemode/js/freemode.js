define('modules/components/freemode/js/freemode', function(require, exports, module) {

  var Swiper = require('modules/js/swiper/swiper.min');
  exports.freemode_fn = function() {
      var swiper = new Swiper('.freemode-container', {
          pagination: '.freemode-pagination',
          slidesPerView: 3,
          paginationClickable: false,
          spaceBetween: 5,
          freeMode: false
      }); 
  }
  
  

});
