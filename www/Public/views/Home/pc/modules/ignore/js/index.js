define('modules/js/index', function(require, exports, module) {

  require('modules/js/app');
  var utils = require('modules/js/utils');
  require('modules/components/banner/js/banner').banner_fn();
  require('modules/components/wytab/js/wytab').wytab();
  require('modules/components/freemode/js/freemode').freemode_fn();
  

});
