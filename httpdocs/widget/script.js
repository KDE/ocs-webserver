/* jshint browser: true */
(function(window, document) {"use strict";  /* Wrap code in an IIFE */

var jQuery, $; // Localize jQuery variables
//var opendesktopwigeturl = 'http://pling.local/';
var opendesktopwigeturl = 'http://pling.cc/';
//var opendesktopwigeturl = 'https://opendesktop.org/';
 let indicator = '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>';            

function loadScript(url, callback) {
  /* Load script from url and calls callback once it's loaded */
  var scriptTag = document.createElement('script');
  scriptTag.setAttribute("type", "text/javascript");
  scriptTag.setAttribute("src", url);
  if (typeof callback !== "undefined") {
    if (scriptTag.readyState) {
      /* For old versions of IE */
      scriptTag.onreadystatechange = function () { 
        if (this.readyState === 'complete' || this.readyState === 'loaded') {
          callback();
        }
      };
    } else {
      scriptTag.onload = callback;
    }
  }
  (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(scriptTag);
}


function opendesktopFilterReviews(filterBtn){
    //init
    let reContainer = $('#opendesktopwidget-reviews');
    reContainer.find('.opendesktopwidget-reviews-rows').show();   

    let inactive =  reContainer.find('.opendesktopwidget-reviews-rows-inactive');
    let active = reContainer.find('.opendesktopwidget-reviews-rows-active');
    let up =  reContainer.find('.opendesktopwidget-reviews-rows-clsUpvotes');
    let down = reContainer.find('.opendesktopwidget-reviews-rows-clsDownvotes');

    if($(filterBtn).attr('id')=='opendesktopwidget-reviews-filters-all'){                                         
          inactive.show();
          active.show();                
    } else  if($(filterBtn).attr('id')=='opendesktopwidget-reviews-filters-active'){
          inactive.hide();
          active.show();                         
    } else  if($(filterBtn).attr('id')=='opendesktopwidget-reviews-filters-likes'){               
          up.show();
          down.hide();
          inactive.hide();                           
    } else if($(filterBtn).attr('id')=='opendesktopwidget-reviews-filters-hates'){                                            
          up.hide();
          down.show();
          inactive.hide();
    }
    $('html, body').animate({
           scrollTop: reContainer.offset().top-150
       }, 1);  
}

function opendesktoptoggleDetail(thisrow){
                let listcontainer = $('#opendesktopwidget-main');
                let detailcontainer = $('#opendesktopwidget-main-detail-container');

                let detailpopup = '<div id="opendesktopwidget-main-detail-modal" class="modal-wrapper">'
                                            +'<div class="modal"><div class="modal-header">'
                                            +'<a class="close" data-opendesktop-dismiss="modal" href="#">×</a>'
                                            +'</div>'
                                            +'<div class="modal-body">'
                                            +'<div id="opendesktopwidget-main-detail-container"></div>'
                                            +'</div></div></div>';

                let modal = $('#opendesktopwidget-main-detail-modal');
                if(detailcontainer.length==0){
                      listcontainer.parent().append($(detailpopup));
                      detailcontainer = $('#opendesktopwidget-main-detail-container');
                      modal.show();                                                 
                }else{                                            
                     modal.show();
                      detailcontainer.html('');                     
                }         
                
                $('a[data-opendesktop-dismiss]').on('click',function(){
                      $('#opendesktopwidget-main-detail-modal').hide();                       
                });             

                let projectid = $(thisrow).attr('data-project-id').trim();
                let container  = $('<div class="opendesktopwidget-main-detail-container-body"></div>'); 
                container.append(indicator);    
                detailcontainer.append(container);      
                let jsonp_url_projectdetail = opendesktopwigeturl+"embed/v1/project/"+projectid+"?&callback=?"; 
                $.getJSON(jsonp_url_projectdetail, function(data) {
                    container.html(data.html);
                    
                    // images carousel                    
                    let imgs = $('#opendesktopwidget-main-detail-carousel').find('img');                    
                    if(imgs.length>1){

                           let btnNext = $('<button class="gonext"><i class="fa fa-chevron-circle-right" aria-hidden="true"></i></button>');
                           btnNext.on('click',function(){                           
                                  $("#opendesktopwidget-main-detail-carousel").slick('slickNext');                                
                            });

                           let btnPrev = $('<button class="goprev"><i class="fa fa-chevron-circle-left" aria-hidden="true"></i></button>');
                           btnPrev.on('click',function(){                           
                                  $("#opendesktopwidget-main-detail-carousel").slick('slickPrev');                                
                            });

                            $('#opendesktopwidget-main-detail-carousel').slick({
                                    dots: true,
                                    arrows:false
                                });       
                            $('#opendesktopwidget-main-detail-carousel').append(btnPrev);
                            $('#opendesktopwidget-main-detail-carousel').append(btnNext);
                            /*
                            $('button.slick-next').on('click',function(){
                              console.log('------------test----------------');

                                  $("#opendesktopwidget-main-detail-carousel").slick('slickNext');                                
                            });
                            $('button.slick-prev').on('click',function(){
                                  $("#opendesktopwidget-main-detail-carousel").slick('slickPrev');                                
                            });
                            */

                    }
                                                          
                    // tabs onclick
                    let lis = container.find('#opendesktopwidget-content-tabs').find('li');
                    lis.each(function(index) {
                        $(this).on("click", function(){                      
                            $(this).addClass('active').siblings().removeClass('active');  
                            let tabcontainerid = $(this).find('a').attr('data-wiget-target');                            
                            $(tabcontainerid).addClass('active').siblings().removeClass('active') ;                                              
                             
                        });
                    });
                    
                    // project comments paging
                    let spans = container.find('.opendesktopwidgetcomments').find('ul.opendesktopwidgetpager').find('span');
                    spans.each(function(index) {
                        $(this).on("click", function(){                      
                            $(this).parent().addClass('active').siblings().removeClass('active');                      
                          
                           $(indicator).insertBefore(container.find('.opendesktopwidgetcomments').find('ul.opendesktopwidgetpager'));
                            let jsonp_url_nopage = opendesktopwigeturl+"embed/v1/comments/"+projectid+"?nopage=1&page="+$(this).html()+"&callback=?";     
                            $.getJSON(jsonp_url_nopage, function(data) {
                                container.find('i.fa-spinner').remove();
                                let ct = container.find('#opendesktopwidget-main-container-comments');  
                                ct.html(data.html);
                                                                                 
                            });

                        });
                    });

                    // reviews filter
                    let reviewsfilters = container.find('#opendesktopwidget-reviews').find('.opendesktop-widget-reviews-filters-btn');
                    reviewsfilters.each(function(index){
                          $(this).on("click", function(){ 
                                $(this).addClass('opendesktopwidget-reviews-activeRating').siblings().removeClass('opendesktopwidget-reviews-activeRating'); 
                                opendesktopFilterReviews(this);
                          });
                    });
              
           
                });                 
        }

      


function main() { 
   
        /******* Load CSS *******/                

        let opendesktopwidgetcss_link = $("<link>", { rel: "stylesheet", type: "text/css", href: opendesktopwigeturl+"widget/style.css" });
        let fontawsomecss_link =$("<link>", { rel: "stylesheet", type: "text/css", href:"http://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" });
        let slick_link =$("<link>", { rel: "stylesheet", type: "text/css", href:"https://cdn.jsdelivr.net/jquery.slick/1.6.0/slick.css" });
        let slick_theme_link =$("<link>", { rel: "stylesheet", type: "text/css", href:"https://cdn.jsdelivr.net/jquery.slick/1.6.0/slick-theme.css" });

        opendesktopwidgetcss_link.appendTo('head');          
        fontawsomecss_link.appendTo('head'); 
        slick_link.appendTo('head'); 
        slick_theme_link.appendTo('head'); 
       
        /******* Load HTML *******/       
      let this_js_script = $('#opendesktopwiget');
      let memberid = this_js_script.attr('data-memberid');   
      let countperpage = this_js_script.attr('data-countperpage'); 
      let catids = this_js_script.attr('data-catids');       
      let query = '';
      if(countperpage){
           query = 'pagelimit='+countperpage+'&';
      }
      if(catids){
           query =query+'catids='+catids+'&';
      }

       if (typeof memberid === "undefined" ) {
          alert('Please set data-memberid in your script.');
          return;
       }

       $('#opendesktopwiget').after('<div id="opendesktop-widget-container">'+indicator+'</div>');
       let jsonp_url = opendesktopwigeturl+"embed/v1/member/"+memberid+"?"+query+"callback=?";       
        $.getJSON(jsonp_url, function(data) {
              $('#opendesktop-widget-container').html(data.html);             
              let spans = $('.opendesktopwidgetpager').find('span');
              spans.each(function(index) {
                  $(this).on("click", function(){                      
                      $(this).parent().addClass('active').siblings().removeClass('active');                                                              
                      $(indicator).insertBefore($('#opendesktop-widget-container').find('ul.opendesktopwidgetpager'));                      
                      let jsonp_url_nopage = opendesktopwigeturl+"embed/v1/member/"+memberid+"?"+query+"nopage=1&page="+$(this).html()+"&callback=?";     
                      $.getJSON(jsonp_url_nopage, function(data) {
                          $('#opendesktop-widget-container').find('i.fa-spinner').remove();
                          $('#opendesktopwidget-main-container').html(data.html);    
                          let rows =$('#opendesktop-widget-container').find('.opendesktopwidgetrow');
                               rows.each(function(index) {
                                   $(this).on("click", function(){                                                       
                                        opendesktoptoggleDetail(this);
                                   });
                               });
                      });

                  });
              });

              let rows =$('#opendesktop-widget-container').find('.opendesktopwidgetrow');
              rows.each(function(index) {
                  $(this).on("click", function(){ 
                        opendesktoptoggleDetail(this);                    
                  });
              });
              
        });
}

/* Load jQuery */
loadScript("https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js", function() {

      loadScript("https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/3.0.0/jquery-migrate.min.js", function() {

        


                /* Restore $ and window.jQuery to their previous values and store the
                   new jQuery in our local jQuery variables. */
                  //$ = jQuery = window.jQuery.noConflict(false);
                  $ = jQuery = window.jQuery.noConflict();

                //$.noConflict();
                //$.noConflict();
                /* Load jQuery plugin and execute the main logic of our widget once the
                   plugin is loaded is loaded */
               
                loadScript("https://cdn.jsdelivr.net/jquery.slick/1.6.0/slick.min.js", function() {

                  main();
                });

      });        
});

}(window, document)); /* end IIFE */