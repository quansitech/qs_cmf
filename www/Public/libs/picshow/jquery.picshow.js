(function($){
  'use strict';

  $.fn.picshow = function(){
    $(this).each(function(){
      var picshow = new Picshow($(this));
      picshow.init();
      picshow.bindEvents();
    });
  };

  function Picshow(container){
    this.container = container;
    this.imgs = [];
    this.mask = null;
    this.close = null;
    this.slide = null;
    this.pinchs = [];

    this.mySwipe = null;

    this.pinch_init_flag = false;
  };

  Picshow.prototype.init = function(){
    this.imgs = this.container.children('img');
    this.createDom();
  };

  Picshow.prototype.createDom = function(){
    this.mask = $('<div class="picshow-mask"></div>');
    var close_container = $('<div class="close-container"></div>');
    this.close = $('<span class="close"></span>');
    this.close.text('X');
    close_container.append(this.close);
    this.slide = $('<div class="slide"></div>');
    var ul = $('<ul></ul>');
    for(var i=0;i<this.imgs.length;i++){
        $(this.imgs[i]).attr('data-index', i);
        var li = $('<li></li>');
        var pinch = $('<div class="pinch-zoom"><img class="pinch-img" src="' + $(this.imgs[i]).attr('src') + '" /></div>');
        this.pinchs.push(pinch);
        li.append(pinch);
        ul.append(li);
    }
    this.slide.append(ul);
    this.mask.append(close_container);
    this.mask.append(this.slide);
    this.container.append(this.mask);
  };

  Picshow.prototype.initPinchZoom = function(){
    if(this.pinch_init_flag === false){
      for(var i=0;i<this.pinchs.length;i++){
        new RTP.PinchZoom(this.pinchs[i], {});
      }
      this.pinch_init_flag = true;
    }
  };

  Picshow.prototype.initSwipe = function(index){
    if(this.mySwipe === null){
      this.mySwipe = new Swipe(this.slide[0], {speed: 400, startSlide: index});
    }
    else{
      this.mySwipe.slide(index);
    }

  }


  Picshow.prototype.bindEvents = function(){
    var picshow = this;
    this.imgs.each(function(){
      $(this).on('click', function(){
        picshow.mask.show();
        picshow.initPinchZoom();
        picshow.initSwipe($(this).data('index'));


        $('html').css('overflow','hidden');
        $('body').css('overflow','hidden');
      });
    });

    this.close.on('click', function(){
      picshow.mask.hide();

      $('html').css('overflow','');
      $('body').css('overflow','');
    });
  };

}(jQuery));
