var opendesktop_widget = (function() {
// Localize jQuery variable
var jQuery;
//var opendesktopwigeturl = 'http://pling.local/';
var opendesktopwigeturl = 'http://pling.cc/';
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

var showHelloWorld = function () {
    alert("Hello world!");
};



function main() { 
    jQuery(document).ready(function($) { 
        /******* Load CSS *******/        
        let opendesktoploadingindicator = '<img src="'+opendesktopwigeturl+'theme/flatui/img/ajax-loader.gif"/>';
        opendesktoptoggleRow = function(thisrow){
                let prefix = 'opendesktopwidgetrow_';
                let prefex_detail = 'opendesktopwidgetrowdetail_';                                                                                          
                let detailcontainer = $('#'+$(thisrow).attr('id').replace(prefix,prefex_detail));    
                if(detailcontainer.css("display")=='none'){                              
                         let filecontainer = detailcontainer.find('div[data-ppload-collection-id]');
                         let collectionid = filecontainer.attr('data-ppload-collection-id');
                         // load pploadfiles if collection_id exist
                         if(collectionid.trim()!=''){
                               let jsonp_url_pploadfiles = opendesktopwigeturl+"embed/v1/ppload/"+collectionid+"?&callback=?"; 
                               filecontainer.html(opendesktoploadingindicator);
                               $.getJSON(jsonp_url_pploadfiles, function(data) {
                                   filecontainer.html(data.html);
                               });  
                             }                                
                };
                detailcontainer.slideToggle();
        }

        let opendesktopwidgetcss_link = $("<link>", { rel: "stylesheet", type: "text/css", href: opendesktopwigeturl+"widget/style.css" });
        opendesktopwidgetcss_link.appendTo('head');          

       $('#opendesktopwiget').after('<div class="opendesktopwidgetloader">'+opendesktoploadingindicator+'</div> ');
        /******* Load HTML *******/       
      let this_js_script = $('#opendesktopwiget');
      let memberid = this_js_script.attr('data-memberid');   
       if (typeof memberid === "undefined" ) {
          alert('Please set data-memberid in your script.');
          return;
       }
       let jsonp_url = opendesktopwigeturl+"embed/v1/member/"+memberid+"?callback=?";       
        $.getJSON(jsonp_url, function(data) {
              $('.opendesktopwidgetloader').remove();
              $('#opendesktopwiget').after('<div id="opendesktop-widget-container">loading...</div>');
              $('#opendesktop-widget-container').html(data.html);             
              let spans = $('.opendesktopwidgetpager').find('span');
              spans.each(function(index) {
                  $(this).on("click", function(){                      
                      $(this).parent().addClass('active').siblings().removeClass('active');                      
                      $('#opendesktopwidget-main-container').html(opendesktoploadingindicator);                          
                      let jsonp_url_nopage = opendesktopwigeturl+"embed/v1/member/"+memberid+"?nopage=1&page="+$(this).html()+"&callback=?";     
                      $.getJSON(jsonp_url_nopage, function(data) {
                          $('#opendesktopwidget-main-container').html(data.html);    
                                    let rows =$('#opendesktop-widget-container').find('.opendesktopwidgetrow');
                                         rows.each(function(index) {
                                             $(this).on("click", function(){                                                       
                                                  opendesktoptoggleRow(this);
                                             });
                                         });
                      });

                  });
              });

              let rows =$('#opendesktop-widget-container').find('.opendesktopwidgetrow');
              rows.each(function(index) {
                  $(this).on("click", function(){ 
                        opendesktoptoggleRow(this);                    
                  });
              });
              
        });
    });
}

})(); 