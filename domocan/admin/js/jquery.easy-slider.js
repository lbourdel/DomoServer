/*
 * jQuery Easy Slider Plugin v0.1
 * http://www.jordisalord.com/en/idees/jquery-easy-slider/
 *
 * Copyright (c) 2011 Jordi Salord (http://www.jordisalord.com)
 * Licensed under GPL version v2.0 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * 
 * Based on a plugin skeleton by: @markdalgleish
 * Plugin skeleton URL: https://github.com/addyosmani/jquery-plugin-patterns/blob/master/jquery.highly-configurable.plugin.boilerplate.js
 */

;
(function( $, window, document, undefined ){
      // constructor
      var EasySlider = function( elem, options ){
            this.elem = elem;
            this.$elem = $(elem);
            this.options = options;
            
            this.metadata = this.$elem.data( 'easyslider-options' );
      };
      
      // default config
      var defaults = {
            style : 'default',
            imageselector : 'img',
            liselector : 'dl.gallery-item',
            aselector : 'dt.gallery-icon a',
            showloading: false,
            replacegallery: false,
            shadowcolor: '#000',
            shadowopacity:.3,
            bigimagestyle: "top:0;position:fixed;display:block;margin:0;width:auto;z-index:1500;cursor:pointer;",
            sliderleftnav: false,
            getParent:function(el) {
                  return el.parent().parent();
            },
            gallerystyle: false,
            gallerylistyle: "width:100%;margin:0;",
            galleryimgstyle: "width:auto;margin:0;"
      }
      
      // prototype
      EasySlider.prototype = {
            ready:false,
            n:0,
            c:0,
            list:new Array(),
            imglist:new Array(),
            alist:new Array(),
            active:null,
            bigimg:null,
            init: function() {
                  // merged configurations
                  this.config = $.extend({}, defaults, this.options, this.metadata);

                  if (this.config.gallerystyle == false)
                        this.config.gallerystyle = this.config.style;

                  this.loadImages();
                  return this;
            },

            loadImages: function() {
                  var $this = this;
                  $this.list = $this.$elem.find(this.config.liselector);                  
                  $this.imglist = $this.$elem.find(this.config.imageselector);         
                  $this.alist = $this.$elem.find(this.config.aselector);
                  $this.n = $this.imglist.length;
                  
                  if ($this.config.showloading) {
                        $this.showLoading();
                  }
                  
                  if ($this.config.replacegallery) {
                        $this.replaceGallery();
                  }
                  else {
                        $this.imglist.hide();
                  }
                  
                  $this.imglist.load(function(){
                        if ( (! $this.config.showloading) && (! $this.config.replacegallery) ) {
                              $this.showImage($(this));
                              $this.getReady();
                        }
                        
                        $this.c++;
                        if ($this.n == $this.c)
                              $this.loadImagesComplete();
                  }).each(function() {
                        if(this.complete) {
                              $(this).load();
                        }
                  });
            },
            
            loadImagesComplete: function() {
                  var $this = this;
                  if ( ($this.config.showloading) && (! $this.config.replacegallery) ) {
                        $this.showImages();
                        $this.getReady();
                  }
                  else if ( $this.config.replacegallery ) {
                        $this.getReady();
                  }
            },
            
            showImage: function(el) {
                  switch (this.config.style) {
                        case 'fadein':
                              el.fadeIn();
                              break;
                        case 'default':
                              el.show();
                              break;
                  }
            },
            
            showImages: function() {
                  var $this = this;
                  $this.imglist.each(function(){
                        $this.showImage($(this));
                  });
            },
            
            replaceGallery: function() {
                  var $this = this;
                  $this.list.each(function(){
                        var ha = $(this).find($this.config.aselector);
                        var newsrc = ha.attr("href");
                        $(this).find('img').attr("src",newsrc);
                  }).attr("style",$this.config.gallerylistyle).hide();
                  
                  $this.imglist.attr("style",$this.config.galleryimgstyle);
                  switch ($this.config.gallerystyle) {
                        case 'fadein':
                              $this.list.first().fadeIn();
                              break;
                        case 'default':
                              $this.list.first().show();
                              break;
                  }
            },
            
            getReady: function() {
                  var $this = this;
                  $this.ready = true;
                  
                  if ( ($this.config.showloading) ) {
                        $this.hideLoading();
                  }
                  
                  if ($this.config.replacegallery) {
                        $this.alist.unbind('click').click(function(){
                              return false;
                        });
                  }
                  else {
                        $this.alist.unbind('click').click(function(){
                              var hitem = $(this);
                              var newsh = $('<div id="wpes_shadow"></div>');
                              newsh.css({
                                    'position':'fixed',
                                    'left':0,
                                    'top':0,
                                    'width':$(document).width()+'px',
                                    'height':$(document).height()+'px',
                                    'z-index':'1000',
                                    'background':$this.config.shadowcolor,
                                    'opacity':$this.config.shadowopacity
                              });
                              $this.bigimg = $('<img id="wpes_bigimg" src="'+$(this).attr("href")+'" />');
                              $this.bigimg.attr("style",$this.config.bigimagestyle).hide();

                              $('body').append(newsh).append($this.bigimg);
                              
                              $this.bigimg.hide().each(function() {
                                    if(this.complete) {
                                          $(this).load();
                                    }
                              }).load(function(){
                                    $this.checkHeight($this.bigimg);
                                    $this.checkWidth($this.bigimg);
                                    $this.centerImage($this.bigimg);
                                    
                                    newsh.unbind('click').click(function(){
                                          $this.closeSlider();
                                          newbigimg = null;
                                          newsh = null;
                                    });
                                    
                                    switch ($this.config.style) {
                                          case 'fadein':
                                                $this.bigimg.fadeIn();
                                                break;
                                          case 'default':
                                                $this.bigimg.show();
                                                break;
                                    }
                              }).unbind('click').click(function(){
                                    $this.clickBigImg(hitem);
                              });
                              
                              return false;
                        });
                  }
            },
            
            checkHeight: function(el){
                  var iw = el.width();
                  var ih = el.height();
                  var newbigwidth = 0;
                  var newbigheight = 0;
                  
                  if (ih > $(window).height()*.8) {
                        newbigheight = $(window).height()*.8;
                        newbigwidth = iw * newbigheight / ih;
                        el.css({
                              width:newbigwidth,
                              height:newbigheight
                        });
                  }
            },
            
            checkWidth: function(el){
                  var iw = el.width();
                  var ih = el.height();
                  var newbigwidth = 0;
                  var newbigheight = 0;
                  
                  if (iw > $(window).width()*.8) {
                        newbigwidth = $(window).width()*.8;
                        newbigheight = ih * newbigwidth / iw;
                        el.css({
                              width:newbigwidth,
                              height:newbigheight
                        });
                  }
            },
            
            centerImage: function(el) {
                  var newl = $(window).width() / 2 - el.width() / 2;
                  var newt = $(window).height() / 2 - el.height() / 2;
                  el.css({
                        left:newl+'px',
                        top:newt+'px'
                  });
            },
            
            showLoading: function(){
                  
            },
            
            hideLoading: function(){
                  
            },
            
            closeSlider: function() {
                  $('#wpes_shadow').remove();
                  $('#wpes_bigimg').remove();
            },
            
            nextSlider: function(el,first) {
                  var $this = this;
                  var hp = $this.config.getParent(el);
                  var ha;
                  
                  if (first == true) {
                        ha = hp.parent().first().find($this.config.aselector);
                  }
                  else
                        ha = hp.next('.gallery-item').find($this.config.aselector);
                  
                  $this.bigimg.unbind('click').click(function(){
                        $this.clickBigImg(ha);
                  }).hide().attr("src",ha.attr("href")).each(function() {
                        if(this.complete) {
                              $(this).load();
                        }
                  });
            },
            
            clickBigImg: function(el) {
                  var $this = this;
                  
                  if ($this.config.getParent(el).next('.gallery-item').length == 0)
                        $this.nextSlider(el,true);
                  else
                        $this.nextSlider(el);
            }
      }

      $.fn.easyslider = function(options) {
            return this.each(function() {
                  new EasySlider(this, options).init();
            });
      };
})( jQuery, window , document );