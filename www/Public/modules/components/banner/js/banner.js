define('modules/components/banner/js/banner', function(require, exports, module) {

  var Swiper = require('modules/js/swiper/swiper.min');
  exports.banner_fn = function() {
    var swiper = new Swiper('.banner', {
        pagination: '.banner-pagination',
        paginationClickable: true
    });  
  }
  
  

});
