jQuery(document).ready(function($){
	$('.totop').toTop();
	$('.sidebar').affix({
	  offset: {
	    top: function(){
	    	return $('.wrapper').offset().top
	    },
	    bottom: function () {
	      return $('#footer').outerHeight(true)
	    }
	  }
	});
});