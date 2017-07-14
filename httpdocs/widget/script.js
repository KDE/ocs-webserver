var opendesktop_widget = (function() {
// Localize jQuery variable
var jQuery;
var opendesktopwigeturl = 'http://pling.cc/';
//var opendesktopwigeturl = 'http://pling.local/';
//var opendesktopwigeturl = 'http://mylocal.com/';
/******** Load jQuery if not present *********/
if (window.jQuery === undefined || window.jQuery.fn.jquery !== '3.2.1') {
    var script_tag = document.createElement('script');
    script_tag.setAttribute("type","text/javascript");
   // script_tag.setAttribute("src", "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js");
   script_tag.setAttribute("src", "https://code.jquery.com/jquery-3.2.1.min.js");   
    if (script_tag.readyState) {
      script_tag.onreadystatechange = function () { // For old versions of IE
          if (this.readyState == 'complete' || this.readyState == 'loaded') {
              scriptLoadHandler();
          }
      };
    } else { // Other browsers
      script_tag.onload = scriptLoadHandler;
    }
    // Try to find the head, otherwise default to the documentElement
    (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
} else {
    // The jQuery version on the window is the one we want to use
    jQuery = window.jQuery;
    main();
}




/******** Called once jQuery has loaded ******/
function scriptLoadHandler() {
    // Restore $ and window.jQuery to their previous values and store the
    // new jQuery in our local jQuery variable
    jQuery = window.jQuery.noConflict(true);
    // Call our main function
    main(); 
}




function main() { 
    jQuery(document).ready(function($) { 
        /******* Load CSS *******/                
        let indicator = '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>';            
      opendesktopFilterReviews = function(filterBtn){
          //init
          $('#opendesktopwidget-reviews').find('.opendesktopwidget-reviews-rows').show();    
          if($(filterBtn).attr('id')=='opendesktopwidget-reviews-filters-all'){
                $('#opendesktopwidget-reviews').find('.opendesktopwidget-reviews-rows-inactive').show();
                $('#opendesktopwidget-reviews').find('.opendesktopwidget-reviews-rows-active').show();                                       
          } else  
          if($(filterBtn).attr('id')=='opendesktopwidget-reviews-filters-active'){
                $('#opendesktopwidget-reviews').find('.opendesktopwidget-reviews-rows-inactive').hide();
                $('#opendesktopwidget-reviews').find('.opendesktopwidget-reviews-rows-active').show();                                       
          } else                                                               
          if($(filterBtn).attr('id')=='opendesktopwidget-reviews-filters-likes'){
                $('#opendesktopwidget-reviews').find('.opendesktopwidget-reviews-rows-clsUpvotes').show();
                $('#opendesktopwidget-reviews').find('.opendesktopwidget-reviews-rows-clsDownvotes').hide();
                $('#opendesktopwidget-reviews').find('.opendesktopwidget-reviews-rows-inactive').hide();                                       
          } else
          if($(filterBtn).attr('id')=='opendesktopwidget-reviews-filters-hates'){
                $('#opendesktopwidget-reviews').find('.opendesktopwidget-reviews-rows-clsUpvotes').hide();
                $('#opendesktopwidget-reviews').find('.opendesktopwidget-reviews-rows-clsDownvotes').show();    
                $('#opendesktopwidget-reviews').find('.opendesktopwidget-reviews-rows-inactive').hide();                                   
          }
          $('html, body').animate({
                 scrollTop: $('#opendesktopwidget-reviews').offset().top-150
             }, 1);  
      }
        opendesktoptoggleDetail = function(thisrow){
                let listcontainer = $('#opendesktopwidget-main');
                let detailcontainer = $('#opendesktopwidget-main-detail-container');
                if(detailcontainer.length==0){
                      detailcontainer = $('<div id="opendesktopwidget-main-detail-container"></div>');                      
                      listcontainer.parent().append(detailcontainer);
                      listcontainer.hide();                                          
                }else{                      
                      detailcontainer.show();
                      detailcontainer.html('');
                      listcontainer.hide();
                }                
                
                
                let naviback = '<i class="fa fa-angle-double-left" aria-hidden="true"></i>';
                let navi = $('<div class="opendesktopwidget-subnavi"><a class="backtolist">'+ naviback +'Back to List</a></div>');
                navi.on('click',function(){
                       detailcontainer.hide();
                       listcontainer.show();
                });
                detailcontainer.append(navi);

                let projectid = $(thisrow).attr('data-project-id');
                let container  = $('<div class="opendesktopwidget-main-detail-container-body"></div>'); 
                container.append(indicator);    
                detailcontainer.append(container);      
                let jsonp_url_projectdetail = opendesktopwigeturl+"embed/v1/project/"+projectid+"?&callback=?"; 
                $.getJSON(jsonp_url_projectdetail, function(data) {
                    container.html(data.html);
                    
                    // images carousel
                    let imgs = $('#opendesktopwidget-main-detail-carousel').find('img');                    
                    if(imgs.length>1){
                            $.getScript('https://cdnjs.cloudflare.com/ajax/libs/simple-slider/1.0.0/simpleslider.min.js',function( data, textStatus, jqxhr ) {                            
                                    let slider = simpleslider.getSlider({
                                          container: document.getElementById('opendesktopwidget-main-detail-carousel'),
                                          paused:true
                                    });                                   
                                    $('.opendesktopwidget-imgs').find('.prev').on('click',function(){
                                          slider.prev();
                                    });
                                    $('.opendesktopwidget-imgs').find('.next').on('click',function(){
                                          slider.next();
                                    });                                  
                            });
                    }
                                                          
                    // tabs onclick
                    let lis = container.find('#opendesktopwidget-content-tabs').find('li');
                    lis.each(function(index) {
                        $(this).on("click", function(){                      
                            $(this).addClass('active').siblings().removeClass('active');  
                            let tabcontainerid = $(this).find('a').attr('data-wiget-target');                            
                            $(tabcontainerid).addClass('active').siblings().removeClass('active') ;                                              
                            $('html, body').animate({
                                   scrollTop: $(tabcontainerid).offset().top-150
                               }, 1);             
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
                                $('html, body').animate({
                                       scrollTop: ct.offset().top-150
                                   }, 1);                                                       
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

      

        let opendesktopwidgetcss_link = $("<link>", { rel: "stylesheet", type: "text/css", href: opendesktopwigeturl+"widget/style.css" });
        let fontawsomecss_link =$("<link>", { rel: "stylesheet", type: "text/css", href:"http://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" });

        opendesktopwidgetcss_link.appendTo('head');          
        fontawsomecss_link.appendTo('head'); 
       
        /******* Load HTML *******/       
      let this_js_script = $('#opendesktopwiget');
      let memberid = this_js_script.attr('data-memberid');   
       if (typeof memberid === "undefined" ) {
          alert('Please set data-memberid in your script.');
          return;
       }

       $('#opendesktopwiget').after('<div id="opendesktop-widget-container">'+indicator+'</div>');
       let jsonp_url = opendesktopwigeturl+"embed/v1/member/"+memberid+"?callback=?";       
        $.getJSON(jsonp_url, function(data) {
              $('#opendesktop-widget-container').html(data.html);             
              let spans = $('.opendesktopwidgetpager').find('span');
              spans.each(function(index) {
                  $(this).on("click", function(){                      
                      $(this).parent().addClass('active').siblings().removeClass('active');                      
                      //$('#opendesktopwidget-main-container').html(indicator);                          
                      $(indicator).insertBefore($('#opendesktop-widget-container').find('ul.opendesktopwidgetpager'));                      
                      let jsonp_url_nopage = opendesktopwigeturl+"embed/v1/member/"+memberid+"?nopage=1&page="+$(this).html()+"&callback=?";     
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
    });
}

})(); 
