/**
 *  ocs-webserver
 *
 *  Copyright 2016 by pling GmbH.
 *
 *    This file is part of ocs-webserver.
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/

var newProductPage = (function () {

    return {
        setup: function () {

            function adjustScrollableContentHeight() {
                var pling_box_height = $('#pling-it-box').height();
                $('.scrollable-content').height(pling_box_height);
                $('.scrollable-content').jScrollPane({
                    mouseWheelSpeed: 30
                });
            }

            function adjustSupportersHeight() {
                var comments_height = $('#donations-panel').find('#comments').height();
                var supporters_height = $('#donations-panel').find('#supporters').height();
                if (comments_height > supporters_height) {
                    $('#donations-panel').find('#supporters').height(comments_height);
                }
            }

            $(document).ready(function () {
                adjustScrollableContentHeight();
                adjustSupportersHeight();
                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    adjustSupportersHeight();
                });
            });
        }
    }

})();

// only instantiate when needed to instantiate:
var ImagePreview =  {
        hasError: false,
        setup: function () {
            this.initProductPicture();
            this.initTitlePicture();
            this.initProfilePicture();
            this.initProfilePictureBackground();
        },
        previewImage: function (input, img_id) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                var image = new Image();
                var file = input.files[0];

                reader.readAsDataURL(input.files[0]);
                reader.onload = function (_image) {

                    var image_element = $('#' + img_id);

                    image.src = _image.target.result;              // url.createObjectURL(file);
                    image.onload = function () {
                        var w = this.width,
                            h = this.height,
                            t = file.type,                           // ext only: // file.type.split('/')[1],
                            n = file.name,
                            s = ~~(file.size / 1024); // + 'KB'
                        ImagePreview.hasError = false;

                        $('#product-picture-container div.image-error').remove();

                        if (w > 1000 || w < 20 || h > 1000 || h < 20) {
                            $('#product-picture-preview').attr('src', '').hide().parent()
                                .append('<div class="image-error">Wrong image dimensions</div>')
                            ;
                            $('#image_small_upload').val(null);
                            ImagePreview.hasError = true;
                        }
                        if (s > 2000) {
                            $('#product-picture-preview').attr('src', '').hide().parent()
                                .append('<div class="image-error">File too large</div>')
                            ;
                            $('#image_small_upload').val(null);
                            ImagePreview.hasError = true;
                        }
                        if (false == ImagePreview.hasError) {
                            ImagePreview.hasError = false;
                            image_element.attr('src', _image.target.result);
                            image_element.show();
                        }
                    };
                    image.onerror = function () {
                        $('#product-picture-preview').parent().append('Invalid file type: ' + file.type);
                    };

                    //image_element.attr('src', _image.target.result);
                    //image_element.show();
                    if (img_id == 'product-picture-preview') {
                        $('button#add-product-picture').text('CHANGE LOGO');
                    } else if (img_id == 'title-picture-preview') {
                        $('button#add-title-picture').text('CHANGE BANNER');
                    } else if (img_id == 'profile-picture-preview') {
                        $('button#add-profile-picture').text('CHANGE PICTURE');
                        $('input#profile_img_src').val('local');
                    }else if (img_id == 'profile-picture-bg-preview') {
                        $('button#add-profile-picture-background').text('CHANGE PICTURE');                       
                    }
                };
            }
        },
        previewImageMember: function (input, img_id) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                var image = new Image();
                var file = input.files[0];

                reader.readAsDataURL(input.files[0]);
                reader.onload = function (_image) {

                    var image_element = $('#' + img_id);

                    image.src = _image.target.result;              // url.createObjectURL(file);
                    image.onload = function () {                       
                        ImagePreview.hasError = false;
                        
                        image_element.parent().find('.image-error').remove();
                        
                        if (false == ImagePreview.hasError) {                            
                            image_element.attr('src', _image.target.result);
                            image_element.show();
                        }
                    };

                    image.onerror = function () {
                        image_element.parent().append('<div class="image-error">Invalid file type</div>');
                    };
                  
                    if (img_id == 'profile-picture-background-preview') {
                        $('button#add-profile-picture-background').text('CHANGE PICTURE');                       
                    }
                };
            }
        },
        readImage: function (file) {

            var reader = new FileReader();
            var image = new Image();

            reader.readAsDataURL(file);
            reader.onload = function (_file) {
                image.src = _file.target.result;              // url.createObjectURL(file);
                image.onload = function () {
                    var w = this.width,
                        h = this.height,
                        t = file.type,                           // ext only: // file.type.split('/')[1],
                        n = file.name,
                        s = ~~(file.size / 1024) + 'KB';
                    $('#uploadPreview').append('<img src="' + this.src + '"> ' + w + 'x' + h + ' ' + s + ' ' + t + ' ' + n + '<br>');
                };
                image.onerror = function () {
                    alert('Invalid file type: ' + file.type);
                };
            };

        },
        initProductPicture: function () {
            if ($('#image_small').length == 0) {
                return;
            }
            if ($('#image_small').attr('value').length == 0) {
                return;
            }
            var imageTarget = $('#image_small').data('target');
            $(imageTarget).attr('src', 'https://cn.pling.com/cache/200x200-2/img/' + $('#image_small').attr('value'));
            $(imageTarget).show();
            $('button#add-product-picture').text('CHANGE LOGO');
        },
        initTitlePicture: function () {
            if ($('#image_big').length == 0) {
                return;
            }
            if ($('#image_big').attr('value').length == 0) {
                return;
            }
            var imageTarget = $('#image_big').data('target');
            $(imageTarget).attr('src', 'https://cn.pling.com/cache/200x200-2/img/' + $('#image_big').attr('value'));
            $(imageTarget).show();
            $('button#add-title-picture').text('CHANGE BANNER');
        },
        initProfilePicture: function () {
            if ($('#profile_image_url').length == 0) {
                return;
            }
            if ($('#profile_image_url').attr('value').length == 0) {
                return;
            }
            var imageTarget = $('#profile_image_url').data('target');
            $(imageTarget).attr('src', $('#profile_image_url').attr('value'));
            $('#profile-picture').attr('src', $('#profile_image_url').attr('value'));
            $(imageTarget).show();
            $('button#add-profile-picture').text('CHANGE PICTURE');
        },
        initProfilePictureBackground: function () {
            if ($('#profile_image_url_bg').length == 0) {
                return;
            }
            if ($('#profile_image_url_bg').attr('value').length == 0) {
                return;
            }            
            var imageTarget = $('#profile_image_url_bg').data('target');
            $(imageTarget).attr('src', $('#profile_image_url_bg').attr('value'));           
            $('#profile-picture-background-preview').attr('src', $('#profile_image_url_bg').attr('value'));
            $(imageTarget).show();
            $('button#add-profile-picture-background').text('CHANGE PICTURE');
        }
    };

var MenuHover = (function () {
    return {

        setup: function () {
            $('body').on('click', 'a#login-dropdown', function (event) {
                event.stopPropagation();
                $('.header-login-container').toggleClass('active');
            }).on('click', '.header-login-container', function (event) {
                event.stopPropagation();
            }).on('click', 'a.menu-trigger', function (event) {
                event.stopPropagation();
                var html_menu_element = '.' + $(this).attr('rel');
                $(html_menu_element).toggleClass('active');
            }).on('mouseup', function (event) {
                var container = $('ul.profile-menu');
                var trigger = $('li.profile-menu-container a.menu-trigger');

                if (trigger.is(event.target)) {
                    return;
                }

                if (!container.is(event.target) // if the target of the click isn't the container...
                    && container.has(event.target).length === 0) // ... nor a descendant of the container
                {
                    container.removeClass('active');
                }
            }).on('mouseup', function (event) {
                container = $('div.header-login-container');
                trigger = $('a#login-dropdown');

                if (trigger.is(event.target)) {
                    return;
                }

                if (!container.is(event.target) // if the target of the click isn't the container...
                    && container.has(event.target).length === 0) // ... nor a descendant of the container
                {
                    container.removeClass('active');
                }
            }).click(function () {
                $('.header-login-container.active').removeClass('active');
                $('.profile-menu.active').removeClass('active');
            });
        }

    };
})();

var ButtonCode = (function () {
    return {
        setup: function () {
            $('#button-code-heading').click(function () {
                if ($(this).hasClass('button-code-active')) {
                    $(this).removeClass('button-code-active');
                    $(this).parent().find('.button-code').slideUp('fast');
                    $(this).parent().find('.button-code').css('border-bottom', 'none');
                    $(this).find('.icon-expand-code').css('background-image', 'url(img/icon-expand.png)');
                } else {
                    $(this).addClass('button-code-active');
                    $(this).parent().find('.button-code').css('border-bottom', '1px solid #bdc3c7');
                    $(this).parent().find('.button-code').slideDown('fast');
                    $(this).find('.icon-expand-code').css('background-image', 'url(img/icon-contract.png)');
                }
            })
        },
        setupClipboardCopy: function (containerId) {
            $(containerId).on('mouseover', function () {
                $(this).off('mouseover');
                $(this).find('[data-clipboard-target]').each(function () {
                    var clipboardTarget = $($(this).attr('data-clipboard-target'));
                    $(this).zclip({
                        path: '/theme/flatui/js/lib/ZeroClipboard.swf',
                        copy: $.trim($(clipboardTarget).text())
                    });
                });
            });
        }
    };
})();

var ProductPage = (function () {

    return {

        setup: function () {

            $('.scroll-pane').jScrollPane({
                mouseWheelSpeed: 30,
                animateScroll: true
            });

            $('.love-it').on('click', function () {

                this_img_src = $(this).find('img').attr('src');

                if (!$(this).hasClass('no-login') && this_img_src != '/theme/flatui/img/icon-like-color.png') {
                    $(this).prev('.share-it').trigger('click');
                }
            });

            $('.payment-options').find('.options').find('span.option').on('click', function () {

                var this_number = $(this).attr('title');
                var number_input = $('.payment-options').find('input[type="number"]');

                number_input.attr('value', this_number).val(this_number);
                number_input.focus();

                $('.options').find('.selected').removeClass('selected');
                $(this).addClass('selected');
            });
        },

        setupPlingButton: function () {

            $('#pling-amount').on('focus', function () {
                $('#pling-amount').popover('destroy');
            });

            $('#pling-start').on('click', function (event) {
                validateElement = $('#pling-amount');
                validateValue = validateElement.val();

                if (validateValue == '' || (isNaN(parseFloat(validateValue)) || !isFinite(validateValue))) {
                    event.preventDefault();
                    $('#pling-amount').popover({
                        placement: 'bottom',
                        html: 'true',
                        content: '<button type="button" class="close" onclick="$(\'#pling-amount\').popover(\'destroy\')">×</button><div id="popOverBox">Donation must be a numeric value.</div>'
                    }).popover('show');
                    return false;
                }

                minValue = validateElement.attr('min');
                if (parseFloat(validateValue) < parseFloat(minValue)) {
                    event.preventDefault();
                    $('#pling-amount').popover({
                        placement: 'bottom',
                        html: 'true',
                        content: '<button type="button" class="close" onclick="$(\'#pling-amount\').popover(\'destroy\')">×</button><div id="popOverBox">Donation must be equal or greater than ' + minValue + '.</div>'
                    }).popover('show');
                    return false;
                }

                maxValue = validateElement.attr('max');
                if (parseFloat(validateValue) > parseFloat(maxValue)) {
                    event.preventDefault();
                    $('#pling-amount').popover({
                        placement: 'bottom',
                        html: 'true',
                        content: '<button type="button" class="close" onclick="$(\'#pling-amount\').popover(\'destroy\')">×</button><div id="popOverBox">Donation must be smaller or equal than ' + maxValue + '.</div>'
                    }).popover('show');
                    return false;
                }
            });

            function minmax(value, min, max) {
                if (parseInt(value) < 0 || isNaN(value))
                    return 0;
                else if (parseInt(value) > 100)
                    return 100;
                else return value;
            }
        }
    }

})();

var SlideShowRender = (function () {
    return {
        setup: function () {
            // add the active class to the first image
            $('#slideshow-container').find('img:first').addClass('active');
            $('#slideshow-container').find('#navigation').find('a:first').addClass('active');
            // trigger slideshow
            //SlideShow.setup();
        }
    }

})();

var ProductSlideShow = (function () {

    return {

        setup: function () {

            // arrows function
            $('#slideshow-container').find('#arrows').find('a').on('click', function (e) {

                var this_id = $(this).attr('id');
                var slides = $('#slideshow-container #slides');
                var current_img = slides.find('img.active');
                var current_img_rel = current_img.attr('rel');

                var current_nav_link = $('#slideshow-container').find('#navigation').find('a.active');

                if (this_id == 'prev') {
                    var prev_img_rel = parseInt(current_img_rel) - parseInt(1);
                    var next_active_img = $('#slideshow-container').find('img[rel="' + prev_img_rel + '"]');
                    var next_active_nav_link = $('#slideshow-container').find('#navigation').find('a[rel="' + prev_img_rel + '"]');
                    if (!next_active_img.size() == 1) {
                        var next_active_img = slides.find('img:last');
                        var next_active_nav_link = $('#slideshow-container').find('#navigation').find('a:last');
                    }
                } else if (this_id == 'next') {
                    var next_img_rel = parseInt(current_img_rel) + parseInt(1);
                    var next_active_img = $('#slideshow-container').find('img[rel="' + next_img_rel + '"]');
                    var next_active_nav_link = $('#slideshow-container').find('#navigation').find('a[rel="' + next_img_rel + '"]');
                    if (!next_active_img.size() == 1) {
                        var next_active_img = slides.find('img:first');
                        var next_active_nav_link = $('#slideshow-container').find('#navigation').find('a:first');
                    }
                }

                current_img.removeClass('active');
                current_nav_link.removeClass('active');
                next_active_img.addClass('active');
                next_active_nav_link.addClass('active');

                //clearTimeout(slideShowInterval);
                //SlideShow.setup();

            });

            // navigation function

            $('#slideshow-container').find('#navigation').find('a').on('click', function () {

                var this_rel = $(this).attr('rel');
                var this_image = $('#slideshow-container').find('img[rel="' + this_rel + '"]');

                $('#slideshow-container').find('img.active').removeClass('active');
                this_image.addClass('active');

                $('#slideshow-container').find('#navigation').find('a.active').removeClass('active');
                $(this).addClass('active');

            });

        }

    }

})();

var SlideShow = (function () {
    return {
        setup: function () {

            slideShowInterval = setTimeout(function () {

                var current_img = $('#slideshow-container').find('img.active');
                var current_img_rel = current_img.attr('rel');
                var next_img_rel = parseInt(current_img_rel) + parseInt(1);
                var next_img = $('#slideshow-container').find('img[rel="' + next_img_rel + '"]');

                var current_nav_link = $('#slideshow-container').find('#navigation').find('a.active');

                current_img.removeClass('active');
                current_nav_link.removeClass('active');

                if (next_img.size() == 1) {
                    next_img.addClass('active');
                    $('#slideshow-container').find('#navigation').find('a[rel="' + next_img_rel + '"]').addClass('active');
                } else {
                    $('#slideshow-container').find('img:first').addClass('active');
                    $('#slideshow-container').find('#navigation').find('a:first').addClass('active');
                }

                //SlideShow.setup();

            }, 4000);

        }
    }
})();

var AboutContent = (function () {
    return {
        setup: function () {
             $('#aboutContent').on('click', function(){
                    $.fancybox({
                          'hideOnContentClick':           true,                                                           
                          'autoScale'                     : true,                                                   
                          'cyclic'                        : 'true',
                          'transitionIn'                  : 'elastic',
                          'transitionOut'                 : 'elastic',
                          'type'        : 'iframe',
                          'scrolling'   : 'no',
                          helpers: { 
                                overlay: { 
                                    locked: false 
                                } 
                            },
                        autoSize: true,
                        href: '/partials/about.phtml',
                        type: 'ajax'
                    });
                });
        }
    }

})();

/** PRODUCT PAGE **/

    // embed code expend collapse
/**
 * @deprecated ?
 *
$('body').on("click", '.embed-code .sidebar-header', function (event) {

    var thisIsActive = $('.embed-code').find('.panel-collapse').hasClass('in');

    if (thisIsActive == true) {
        $(this).find('.expand').removeClass('active');
    }
    else {
        $(this).find('.expand').addClass('active');
    }
});
**/
// tool tips
$('body').on('mouseenter', '.supporter-thumbnail', function () {
    $(this).popover('show');
});

$('body').on('mouseleave', '.supporter-thumbnail', function () {
    $(this).popover('hide');
});

var Partials = (function () {
    return {
        setup: function () {
            $('body').on('click', 'a.partial', function (event) {
                event.preventDefault();
                var url = this.href;
                var target = $(this).attr("data-target");
                var toggle = $(this).data('toggle');
                var pageFragment = $(this).attr("data-fragment");

                //$(target).empty().html('<div class="loading">Loading ...<img src="/images/system/ajax-loader.gif" alt="Loading..." /></div>');
                $(target).load(url + ' ' + pageFragment, function (response, status, xhr) {
                    if (status == "error") {
                        if (xhr.status == 401) {
                            if (response) {
                                var data = jQuery.parseJSON(response);
                                var redirect = data.login_url;
                                if (redirect) {
                                    window.location = redirect;
                                } else {
                                    window.location = "/login";
                                }
                            }
                        } else {
                            $(target).empty().html('Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.');
                        }
                    }
                    if (toggle) {
                        $(toggle).modal('show');
                    }
                });
                return false;
            });
        }
    }
})();


var PartialsReview = (function () {
    return {
        setup: function () {
            $('body').on('click', 'a.partial', function (event) {
                event.preventDefault();
                let url = this.href;
                let target = $(this).attr("data-target");
                let toggle = $(this).data('toggle');
                let pageFragment = $(this).attr("data-fragment");

                // product owner not allow to vote
                let loginuser  = $('#review-product-modal').find('#loginuser').val();
                let productcreator  = $('#review-product-modal').find('#productcreator').val();
                if(loginuser == productcreator){
                    // ignore
                     $('#review-product-modal').find('#votelabel').text('Project owner not allowed');
                     $('#review-product-modal').find('.modal-body').empty();
                     $('#review-product-modal').modal('show');
                     return;
                }
                
                let userrate = $('#review-product-modal').find('#userrate').val();
                // -1 = no rate yet. 0= dislike  1=like                                
                
                if($( this ).hasClass( "voteup" )){
                        if(userrate==1){
                            $('#review-product-modal').find('#votelabel').empty()                                
                                .append('<a class="btn btn-success active" style="line-height: 10px;"><span class="fa fa-plus"></span></a> is given already with comment:');                                
                            $('#review-product-modal').find(':submit').attr("disabled", "disabled").css("display", "none");        
                            $('#review-product-modal').find('#commenttext').attr("disabled", "disabled");                                             
                        }else{
                            $('#review-product-modal').find('input#voteup').val(1);      
                            $('#review-product-modal').find('#votelabel').empty()                                
                                .append('<a class="btn btn-success active" style="line-height: 10px;"><span class="fa fa-plus"></span></a> Add Comment (optional):');                                                                                      
                            $('#review-product-modal').find('#commenttext').val('');
                            $('#review-product-modal').find('#commenttext').removeAttr("disabled");
                             $('#review-product-modal').find(':submit').css("display", "block").removeAttr("disabled"); 
                            
                        }                        
                }else{ // vote down
                        if(userrate==0){
                             $('#review-product-modal').find('#votelabel').empty()                                
                                 .append('<a class="btn btn-danger active" style="line-height: 10px;"><span class="fa fa-minus"></span></a> is given already with comment: ');                                                                                      
                              $('#review-product-modal').find('#commenttext').attr("disabled", "disabled"); 
                            $('#review-product-modal').find(':submit').attr("disabled", "disabled").css("display", "none");     

                        }else{
                            $('#review-product-modal').find('input#voteup').val(2);                               
                            $('#review-product-modal').find('#votelabel').empty()                                
                                .append('<a class="btn btn-danger active" style="line-height: 10px;"><span class="fa fa-minus"></span></a> Add Comment (optional): ');                                                                                                                  
                            $('#review-product-modal').find('#commenttext').val('');
                            $('#review-product-modal').find('#commenttext').removeAttr("disabled");
                            $('#review-product-modal').find(':submit').removeAttr("disabled").css("display", "block");             
                             
                        }
                }
               
               $('#review-product-modal').modal('show');
               if($('#review-product-modal').hasClass('noid')){
                    setTimeout(function() {$('#review-product-modal').modal('hide');}, 2000);
               }                          
                return false;
            });
        }
    }
})();

var PartialForms = (function () {
    return {
        setup: function () {
            $('body').on("submit", 'form.partial', function (event) {
                event.preventDefault();
                event.stopImmediatePropagation();


               $(this).find(':submit').attr("disabled", "disabled");
               $(this).find(':submit').css("white-space", "normal");
               let spin = $('<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>');
               $(this).find(':submit').append(spin);

                var target = $(this).attr("data-target");
                var trigger = $(this).attr("data-trigger");
                console.log(this);
                jQuery.ajax({
                    data: $(this).serialize(),
                    url: this.action,
                    type: this.method,
                    error: function () {
                        $(target).empty().html("<span class='error'>Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.</span>");
                        return false;
                    },
                    success: function (results) {                         
                        $(target).empty().html(results);
                        $(target).find(trigger).trigger('click');
                        return false;
                    }
                });

                return false;
            });
        }
    }
})();

var PartialJson = (function () {
    return {
        setup: function () {
            $('body').on("submit", 'form.partialjson', function (event) {
                event.preventDefault();
                event.stopImmediatePropagation();

                var target = $(this).attr("data-target");
                var trigger = $(this).attr("data-trigger");

                jQuery.ajax({
                    data: $(this).serialize(),
                    url: this.action,
                    type: this.method,
                    dataType: "json",
                    error: function () {
                        $(target).empty().html("<span class='error'>Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.</span>");
                    },
                    success: function (data, textStatus) {
                        if (data.redirect) {
                            // data.redirect contains the string URL to redirect to
                            window.location = data.redirect;
                            return;
                        }
                        if (target) {
                            // data.message contains the HTML for the replacement form
                            $(target).empty().html(data.message);
                        }
                        if (trigger) {
                            $(target).find(trigger).trigger('click');
                        }
                    }
                });

                return false;
            });
        }
    }
})();

var PartialPayPal = (function () {
    return {
        setup: function () {
            this.initPayPalForm();
            this.initPayPalFee();
        },
        initPayPalForm: function () {
            $('body').on("submit", 'form.partialpaypal', function (event) {
                event.preventDefault();
                event.stopImmediatePropagation();
                var target = $(this).attr("data-target");
                var trigger = $(this).attr("data-trigger");

                jQuery.ajax({
                    data: $(this).serialize(),
                    url: this.action,
                    type: this.method,
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('#modal-dialog').modal('hide');
                        var msgBox = $('<div class="msg-box modal" id="msg-box"></div>');
                        msgBox.html($(jqXHR.responseText).filter('.page-container').children());
                        msgBox.append('<div class="modal-footer"><a href="#" class="btn" data-dismiss="modal">Close</a></div>');
                        setTimeout(function () {
                            msgBox.modal('show');
                        }, 900);
                    },
                    success: function (results) {
                        $(target).empty().html(results);
                        $(target).find(trigger).trigger('click');
                        $('#modal-dialog').modal('hide');
                    }
                });

                return false;
            });
        },

        initPayPalFee: function () {
            $('body').on("change", '#amount_plings', function (event) {
                PartialPayPal.changeFee();
            });
        },

        changeFee: function () {
            var e = document.getElementById('amount_plings');
            var value = parseFloat(e.value);

            var pling_fee = this.round((value) * 0.05, 2);

            var pling_sum = (pling_fee) + (value);

            var paypal_fee = (pling_sum + 0.3) * 0.03 + 0.3;

            paypal_fee = this.round(paypal_fee, 2);

            var sum = value + pling_fee + paypal_fee;

            document.getElementById('pling_fee').value = this.round(pling_fee, 2).toFixed(2);
            document.getElementById('paypal_fee').value = this.round(paypal_fee, 2).toFixed(2);
            document.getElementById('sum').value = this.round(sum, 2);
        },

        round: function (x, n) {
            var a = Math.pow(10, n);
            return (Math.round(x * a) / a);
        }

    }
})();

var PartialCommentReviewForm = (function () {
    return {
        setup: function () {
            this.initForm();
          
        },
        initForm: function () {
            $('body').on("submit", 'form.product-add-comment-review', function (event) {
                event.preventDefault();
                event.stopImmediatePropagation();
                
                $(this).find(':submit').attr("disabled", "disabled");
                $(this).find(':submit').css("white-space", "normal");
                let spin = $('<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>');
                $(this).find(':submit').append(spin);
                
                jQuery.ajax({
                    data: $(this).serialize(),
                    url: this.action,
                    type: this.method,
                    error: function (jqXHR, textStatus, errorThrown) {                        
                        $('#review-product-modal').modal('hide');
                        var msgBox = $('#generic-dialog');
                        msgBox.modal('hide');
                        msgBox.find('.modal-header-text').empty().append('Please try later.');                        
                        msgBox.find('.modal-body').empty().append("<span class='error'>Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.</span>");
                        setTimeout(function () {
                            msgBox.modal('show');
                        }, 900);                        
                    },
                    success: function (results) {                        
                        $('#review-product-modal').modal('hide');                                        
                        location.reload();              
                    }
                });
                return false;
            });
        }
    }
})();

var PartialFormsAjax = (function () {
    return {
        setup: function () {
            var form = $('form.partialajax');
            var target = form.attr("data-target");
            var trigger = form.attr("data-trigger");
            
           $(form).find(':submit').on('click',function(){
                             $(form).find(':submit').attr("disabled", "disabled");
                             $(form).find(':submit').css("white-space", "normal");
                             let spin = $('<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>');
                             $(form).find(':submit').append(spin);
                     });            

            form.ajaxForm({
                error: function () {
                    $(target).empty().html("<span class='error'>Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.</span>");
                },
                success: function (results) {
                    $(target).empty().html(results);
                    $(target).find(trigger).trigger('click');
                }
            });
        }
    }
})();


var PartialFormsAjaxMemberBg = (function () {
    return {
        setup: function () {
            var form = $('form.partialajaxbg');
            var target = form.attr("data-target");
            var trigger = form.attr("data-trigger");

            $(form).find(':submit').on('click',function(){
                    $(form).find(':submit').attr("disabled", "disabled");
                    $(form).find(':submit').css("white-space", "normal");
                    let spin = $('<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>');
                    $(form).find(':submit').append(spin);
            });            

            form.ajaxForm({
                error: function () {
                    $(target).empty().html("<span class='error'>Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.</span>");
                },
                success: function (results) {
                    $(target).empty().html(results);
                    $(target).find(trigger).trigger('click');
                }
            });
        }
    }
})();

var AjaxForm = (function () {
    return {
        setup: function (idElement, idTargetElement) {
            var form = $(idElement);
            var target = $(idTargetElement);

            $('body').on("submit", idElement, function (event) {
                //event.preventDefault();
                //event.stopImmediatePropagation();
                                
                $(idElement).find('button').attr("disabled", "disabled");
                $(idElement).find('.glyphicon.glyphicon-send').removeClass('glyphicon-send').addClass('glyphicon-refresh spinning');

                jQuery.ajax({
                    data: $(this).serialize(),
                    url: this.action,
                    type: this.method,
                    dataType: "json",

                    error: function ( jqXHR, textStatus, errorThrown ) {
                        var results = JSON && JSON.parse(jqXHR.responseText) || $.parseJSON(jqXHR.responseText);
                        var msgBox = $('#generic-dialog');
                        msgBox.modal('hide');
                        msgBox.find('.modal-header-text').empty().append(results.title);
                        msgBox.find('.modal-body').empty().append(results.message);
                        setTimeout(function () {
                            msgBox.modal('show');
                        }, 900);
                    },
                    success: function (results) {
                        if (results.status == 'ok') {                           
                            $(target).empty().html(results.data);
                        }
                        if (results.status == 'error') {
                            if (results.message != '') {
                                alert(results.message);
                            } else {
                                alert('Service is temporarily unavailable.');
                            }
                        }
                    }
                });

                return false;
            });           
        }
    }
})();

var WidgetModalAjax = (function () {

    return {

        setup: function () {

            $('.my-product-item').find('a.widget-button').on('click', function () {

                var this_rel = $(this).attr('rel');
                var this_product_id = this_rel.split('product')[1];
                var target = $('.modal-body#widget-code-' + this_rel);

                $.ajax({
                    url: '/widget/config/' + this_product_id,
                    type: 'html',
                    success: function (results) {
                        target.prepend(results);
                        angular.bootstrap(target, ['widgetApp']);
                    }
                });
            });

        }
    }

})();

var LoginContainer = (function () {
    return {
        update: function () {
            if (!Date.now) {
                Date.now = function() { return new Date().getTime(); }
            }
            var timestamp = Date.now() / 1000 | 0;
            var target = '#login_container';
            var url = '/authorization/htmllogin?' + timestamp;

            $(target).load(url, function (response, status, xhr) {
                // nothing to do
            });
        }
    }
})();

var RssNews = (function () {
    return {
        setup: function () {
               
            var yql = "https://query.yahooapis.com/v1/public/yql?q=select%20title%2Clink%2CpubDate%2Cdescription%20from%20rss%20where%20url%3D%22http%3A%2F%2Fblog.opendesktop.org%2Ffeed%22&format=json&diagnostics=true&callback=";          
             $.getJSON(yql, function(res) {                
                   var crss ='';
                   $.each( res.query.results.item, function( i, item ) {                           
                              if ( i >= 3 ) {
                                return false;
                              }
                              var m = moment(item.pubDate);
                              crss+='<div class="commentstore"><a href="'+item.link+'"><span class="title">'+item.title +'</span></a><br/>' + item.description
                              +'<br/><span class="date">'+m.format('MMM DD YYYY LT')+'</span></div>';                           
                            }); 
                   $("#rss-feeds").html(crss);
             });                                                                      
        }
        
    }
})();

var ToggleSidebar = (function () {
    return {
        setup: function () {            
            if($.cookie('sidebar-right-ishidden')==1){
                $('#btnTogglesidebar').addClass('glyphicon-arrow-left');
                $('.sidebar-right').hide();
            }else{
                $('#btnTogglesidebar').addClass('glyphicon-arrow-right');
                $('.sidebar-right').show();
            }    

            $('#btnTogglesidebar').on('click',function(){               
               $('.sidebar-right').toggle();               
               if($(this).hasClass( 'glyphicon-arrow-right')){
                    $(this).removeClass('glyphicon-arrow-right').addClass('glyphicon-arrow-left');
                    $.cookie('sidebar-right-ishidden',1);
               }else if($(this).hasClass( 'glyphicon-arrow-left')){
                    $(this).removeClass('glyphicon-arrow-left').addClass('glyphicon-arrow-right');
                    $.cookie('sidebar-right-ishidden',0);
               }
               
               
            })
        }
    }
})();

var ProductDetailCarousel = (function () {
    return {
        setup: function () {            
            $('.carousel-inner img').each(function (index) {
                $(this).on("click", function () {
                  if($("#product-main-img-container").hasClass("imgfull")){
                    $('#product-main-img-container').prependTo($('#product-main')); 
                  }else{
                    $('#product-main-img-container').prependTo($('#product-page-content')); 
                  }                    
                  $("#product-main-img-container").toggleClass("imgfull");
                  $("#product-main-img-container").toggleClass("imgsmall");
                });
            });
        }
    }
})();

var Metaheader = (function () {
    return {
        setup: function () {                                             
            $('body').on('click', '#toggleStoreBtn', function (event) {
                event.stopPropagation();
                //$( "#toggleStoreContainer" ).slideToggle( "slow" );
                $( "#toggleStoreContainer" ).toggle();
            }).click(function () {
                let t = $('#toggleStoreContainer');
                if(t.css('display') == 'block'){
                 //t.slideToggle( "slow" );
                 t.toggle();
               }  
            });

        }
    }
})();

var LayoutSwitch = (function () {
    return {
        setup: function () {            
            
            $('#btnSwitchLayout').on('click',function(){               
               if($.cookie('layout')=='grid'){
                    $.cookie('layout','list',{ path: '/' });
                }else{
                    $.cookie('layout','grid',{ path: '/' });
                }    
               
                location.reload(); 
               
            })
        }
    }
})();



var productRatingToggle = (function () {
    return {
        setup: function () {                    
            $('#showRatingAll').on('click',function(){   
                $('#ratings-panel').find('.spinning').show();
                setTimeout(function() {$('#ratings-panel').find('.spinning').hide();}, 500);
                $('.btnRateFilter').removeClass('activeRating');
                $(this).addClass('activeRating');  
                $('.productRating-rows').show();               
               $('.productRating-rows-inactive').show();                                                                
            })

            $('#showRatingActive').on('click',function(){ 
            $('#ratings-panel').find('.spinning').show();
                setTimeout(function() {$('#ratings-panel').find('.spinning').hide();}, 500);  
                $('.btnRateFilter').removeClass('activeRating');
                $(this).addClass('activeRating');            
                $('.productRating-rows').show();         
               $('.productRating-rows-inactive').hide();                                            
            })

             $('#showRatingUpvotes').on('click',function(){      
             $('#ratings-panel').find('.spinning').show();
                setTimeout(function() {$('#ratings-panel').find('.spinning').hide();}, 500); 
                $('.btnRateFilter').removeClass('activeRating');
                $(this).addClass('activeRating');
               $('.productRating-rows').show();        
               $('.clsDownvotes').hide();                                                     
               $('.productRating-rows-inactive').hide();                  
            })


             $('#showRatingDownvotes').on('click',function(){    
             $('#ratings-panel').find('.spinning').show();
                setTimeout(function() {$('#ratings-panel').find('.spinning').hide();}, 500); 
                $('.btnRateFilter').removeClass('activeRating');
                $(this).addClass('activeRating');          
                $('.productRating-rows').show();                                                                                
                $('.productRating-rows-inactive').hide();                                         
                $('.clsUpvotes').hide();         
            })
        }
    }
})();

