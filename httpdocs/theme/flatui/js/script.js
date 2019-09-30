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
var ImagePreview = {
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

                    image_element.parent().parent().find('div.bg-danger').remove();

                    if (w > 2000 || w < 20 || h > 2000 || h < 20) {
                        //image_element.attr('src', '').hide().parent().append('<div class="bg-danger">Wrong image dimensions</div>');
                        image_element.parent().parent().append('<div class="bg-danger">Wrong image dimensions</div>');
                        //input.val(null);
                        //$(input).replaceWith(input = $(input).clone(true));
                        $($(input).closest('form')[0]).trigger('reset');
                        ImagePreview.hasError = true;
                    }

                    if (s > 2000) {
                        //image_element.attr('src', '').hide().parent().append('<div class="bg-danger">File too large</div>');
                        image_element.parent().parent().append('<div class="bg-danger">File too large</div>');
                        //input.val(null);
                        //$(input).replaceWith(input = $(input).clone(true));
                        $($(input).closest('form')[0]).trigger('reset');
                        ImagePreview.hasError = true;
                    }
                    var allowedExtensions = /(jpg|jpeg|png|gif)$/i;
                    if(!allowedExtensions.exec(t)) {
                        image_element.parent().parent().append('<div class="bg-danger">Invalid file type: ' + file.type + '</div>');
                        //input.val(null);
                        //$(input).replaceWith(input = $(input).clone(true));
                        $($(input).closest('form')[0]).trigger('reset');
                        ImagePreview.hasError = true;
                    }
                    if (false == ImagePreview.hasError) {
                        ImagePreview.hasError = false;
                        image_element.attr('src', _image.target.result);
                        image_element.show();
                    }
                };
                image.onerror = function () {
                    image_element.parent().parent().find('div.bg-danger').remove();
                    image_element.parent().parent().append('<div class="bg-danger">Invalid file type: ' + file.type + '</div>');
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
                } else if (img_id == 'profile-picture-bg-preview') {
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
        $(imageTarget).attr('src', 'https://cn.opendesktop.org/cache/200x200-2/img/' + $('#image_small').attr('value'));
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
        $(imageTarget).attr('src', 'https://cn.opendesktop.org/cache/200x200-2/img/' + $('#image_big').attr('value'));
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

            $(".dropdown-toggle").dropdown();

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

var Popuppanel =  (function () {
    return {
        setup: function () {
            $('body').on('click', 'a.popuppanel', function (event) {
                if ($(this).attr('target') === "_blank"){                
                  console.log('dont prevent default');
                } else {
                  event.preventDefault();
                  var hf = $(this).attr('href');                  
                  $.fancybox({
                      'hideOnContentClick': true,
                      'autoScale': true,
                      'cyclic': 'true',
                      'transitionIn': 'elastic',
                      'transitionOut': 'elastic',
                      'type': 'iframe',
                      'scrolling': 'auto',
                      helpers: {
                          overlay: {
                              locked: false
                          }
                      },
                      autoSize: true,
                      href: hf,
                      type: 'ajax'
                  });
                }

            });                                    
        }
    }

})();

var PlingsRedirect = (function () {
    return {
        setup: function () {
            if(window.location.hash) {
                $link = $(window.location.hash);
                $link.trigger("click");
            } else if (document.location.hash) {
                $link = $(document.location.hash);
                $link.trigger("click");
            }
        }
    }

})();


var ShowPopups = (function () {
    return {
        setup: function () {
            if(window.location.hash) {
                alert('Hash');
                $('a[href="' + document.location.hash + '"]').trigger("click");
            }
            if (document.location.hash) {
                alert('Hash');
                $('a[href="' + document.location.hash + '"]').trigger("click");
            }
        }
    }

})();



/** PRODUCT PAGE **/
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

var Opendownloadfile= (function () {
    return {
        setup: function () {
            $('body').on('click', 'a.opendownloadfile', function (event) {
                event.preventDefault();
                var file_id = $(this).attr("data-file_id");
                var file_type = $(this).attr('data-file_type');
                var file_name = $(this).attr('data-file_name');
                var file_size = $(this).attr('data-file_size');
                var project_id = $(this).attr('data-project_id');
                var link_type = $(this).attr('data-link_type');
                var is_external = $(this).attr('data-is-external-link');
                var external_link = null;
                var popupHeight = '450';
                
                if(is_external == 'true') {
                    external_link = $(this).attr('data-external_link');
                    popupHeight = '500';
                }                
                
                $.fancybox({
                    'hideOnContentClick': true,
                    'autoScale': false,
                    'scrolling' : 'no',
                    'cyclic': 'true',
                    'transitionIn': 'elastic',
                    'transitionOut': 'elastic',
                    'type': 'iframe',
                    'width':'600',
                    'height':popupHeight,
                    'iframe': {'scrolling': 'no'},
                    'fitToView'   : false,
                    'autoSize'    : false,
                    'id': 'opendownloadfilepopup',
                    helpers: {
                        overlay: {
                            locked: false
                        }
                    },
                    href:'/dl?file_id='+file_id+'&file_type='+file_type+'&file_name='+file_name+'&file_size='+file_size+'&project_id='+project_id+'&link_type='+link_type+'&is_external='+is_external+'&external_link='+external_link

                });
                return false;
            });
        }
    }
})();


var PartialsButton = (function () {
    return {
        setup: function () {
            $('body').on('click', 'Button.partialbutton', function (event) {
                event.preventDefault();
                var url = $(this).attr("data-href");
                var target = $(this).attr("data-target");
                var toggle = $(this).data('toggle');
                var pageFragment = $(this).attr("data-fragment");
                var spin = $('<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>');
                $(target).append(spin);
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


var PartialsButtonHeartDetail = (function () {
    return {
        setup: function () {
            $('body').on('click', '.partialbuttonfollowproject', function (event) {
                event.preventDefault();
                var url = $(this).attr("data-href");
                var target = $(this).attr("data-target");
                var auth = $(this).attr("data-auth");
                var toggle = $(this).data('toggle');
                var pageFragment = $(this).attr("data-fragment");

                if (!auth) {
                    $('#like-product-modal').modal('show');
                    return;
                }

                // product owner not allow to heart copy from voting....
                var loginuser = $('#like-product-modal').find('#loginuser').val();
                var productcreator = $('#like-product-modal').find('#productcreator').val();
                if (loginuser == productcreator) {
                    // ignore
                    $('#like-product-modal').find('#votelabel').text('Project owner not allowed');
                    $('#like-product-modal').find('.modal-body').empty();
                    $('#like-product-modal').modal('show');
                    return;
                }

              var spin = $('<span class="glyphicon glyphicon-refresh spinning" style="opacity: 0.6; z-index:1000;position: absolute; left:24px;top: 4px;"></span>');
                 $(target).prepend(spin);

                $.ajax({
                          url: url,
                          cache: false
                        })
                      .done(function( response ) {
                        $(target).find('.spinning').remove();
                        if(response.status =='error'){
                             $(target).html( response.msg );
                        }else{
                            if(response.action=='delete'){
                                //$(target).find('.likelabel').html(response.cnt +' Likes');
                                $(target).find('.plingtext').html(response.cnt);
                                $(target).find('.plingtext').addClass('heartnumberpurple');
                                 $(target).find('.plingheart').removeClass('heartproject').addClass('heartgrey');
                                 $(target).find('.plingheart').removeClass('fa-heart').addClass('fa-heart-o');


                            }else{
                                //$(target).find('.likelabel').html(response.cnt +' Likes');
                                $(target).find('.plingtext').html(response.cnt);
                                //$(target).find('.plingtext').html(response.cnt+' Fans');
                                $(target).find('.plingtext').removeClass('heartnumberpurple');
                                $(target).find('.plingheart').removeClass('heartgrey').addClass('heartproject');
                                $(target).find('.plingheart').removeClass('fa-heart-o').addClass('fa-heart');
                            }
                        }
                      });
                return false;
            });
        }
    }
})();


var PartialsButtonPlingProject = (function () {
    return {
        setup: function () {
            $('#plingbtn').hover(function(){
                 $(this).attr('src','/images/system/pling-btn-hover.png');
            }, function(){
                $(this).attr('src',$(this).attr('data-src'));
            });

            $('body').on('click', '.partialbuttonplingproject', function (event) {
                event.preventDefault();
                var url = $(this).attr("data-href");
                var target = $(this).attr("data-target");
                var auth = $(this).attr("data-auth");
                var issupporter = $(this).attr("data-issupporter");
                var toggle = $(this).data('toggle');
                var pageFragment = $(this).attr("data-fragment");

                if (!auth) {
                    $('#like-product-modal').modal('show');
                    return;
                }

                // product owner not allow to heart copy from voting....
                var loginuser = $('#like-product-modal').find('#loginuser').val();
                var productcreator = $('#like-product-modal').find('#productcreator').val();
                if (loginuser == productcreator) {
                    // ignore

                    $('#like-product-modal').find('#votelabel').text('Project owner not allowed');
                    $('#like-product-modal').find('.modal-body').empty();
                    $('#like-product-modal').modal('show');
                    return;
                }

                if (!issupporter) {
                    // ignore
                    $('#like-product-modal').find('#votelabel').html('<div style="width:420px;text-align:center">To pling a product and help the artist please consider becoming a supporter. Thanks!</br></br><a href="/support">Become a supporter</a></div>');
                    $('#like-product-modal').modal('show');
                    return;
                }
                $(target).find('.plingnum').html('<span class="glyphicon glyphicon-refresh spinning"/>');
                $.ajax({
                          url: url,
                          cache: false
                        })
                      .done(function( response ) {

                        //$(target).find('.spinning').remove();
                        if(response.status =='error'){
                             $(target).html( response.msg );
                        }else{
                            if(response.action=='delete'){
                                //pling deleted
                                if(response.cnt==0)
                                {
                                    $(target).find('.plingnum').html('Pling me');
                                }else
                                {
                                    $(target).find('.plingnum').html(response.cnt+ ' Plings');
                                }

                                $(target).find('#plingbtn').attr('src','/images/system/pling-btn-normal.png');
                                $(target).find('#plingbtn').attr('data-src','/images/system/pling-btn-normal.png');

                            }else{
                                //pling inserted
                                $(target).find('.plingnum').html(response.cnt+ ' Plings');
                                $(target).find('#plingbtn').attr('src','/images/system/pling-btn-active.png');
                                $(target).find('#plingbtn').attr('data-src','/images/system/pling-btn-active.png');
                            }

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
            $('body').on('click', 'a.partialreview', function (event) {
                event.preventDefault();
                var url = this.href;
                var target = $(this).attr("data-target");
                var toggle = $(this).data('toggle');
                var pageFragment = $(this).attr("data-fragment");

                // product owner not allow to vote
                var loginuser = $('#review-product-modal').find('#loginuser').val();
                var productcreator = $('#review-product-modal').find('#productcreator').val();
                if (loginuser == productcreator) {
                    // ignore
                    $('#review-product-modal').find('#votelabel').text('Project owner not allowed');
                    $('#review-product-modal').find('.modal-body').empty();
                    $('#review-product-modal').modal('show');
                    return;
                }

                var userrate = $('#review-product-modal').find('#userrate').val();
                // -1 = no rate yet. 0= dislike  1=like

                if ($(this).hasClass("voteup")) {
                    if (userrate == 1) {
                        $('#review-product-modal').find('#votelabel').empty()
                            .append('<a class="btn btn-success active" style="line-height: 10px;"><span class="fa fa-plus"></span></a> is given already with comment:');
                        $('#review-product-modal').find('#commenttext').val($('#review-product-modal').find('#otxt').val());
                        //$('#review-product-modal').find(':submit').attr("disabled", "disabled").css("display", "none");
                        //$('#review-product-modal').find('#commenttext').attr("disabled", "disabled");
                        $('#review-product-modal').find(':submit').text("Remove Rating");

                    } else {
                        $('#review-product-modal').find('input#voteup').val(1);
                        $('#review-product-modal').find('#votelabel').empty()
                            .append('<a class="btn btn-success active" style="line-height: 10px;"><span class="fa fa-plus"></span></a> Add Comment (min. 1 char):');
                        $('#review-product-modal').find('#commenttext').val('+');

                        $('#review-product-modal').find(':submit').text("Rate Now");
                        $('#review-product-modal').find('#commenttext').removeAttr("disabled");
                        $('#review-product-modal').find(':submit').css("display", "block").removeAttr("disabled");


                    }
                } else { // vote down
                    if (userrate == 0) {
                        $('#review-product-modal').find('#votelabel').empty()
                            .append('<a class="btn btn-danger active" style="line-height: 10px;"><span class="fa fa-minus"></span></a> is given already with comment: ');
                        $('#review-product-modal').find('#commenttext').val($('#review-product-modal').find('#otxt').val());
                        // $('#review-product-modal').find('#commenttext').attr("disabled", "disabled");
                        // $('#review-product-modal').find(':submit').attr("disabled", "disabled").css("display", "none");

                        $('#review-product-modal').find(':submit').text("Remove Rating");

                    } else {
                        $('#review-product-modal').find('input#voteup').val(2);
                        $('#review-product-modal').find('#votelabel').empty()
                            .append('<a class="btn btn-danger active" style="line-height: 10px;"><span class="fa fa-minus"></span></a> Add Comment (min. 5 char) <span style="font-size:10px; display:block"> Please explain the reason for downvote to help the creator to make it better </span> ');
                        $('#review-product-modal').find('#commenttext').val('-');
                        $('#review-product-modal').find('#commenttext').removeAttr("disabled");
                        $('#review-product-modal').find(':submit').removeAttr("disabled").css("display", "block");
                         $('#review-product-modal').find(':submit').text("Rate Now");
                    }
                }

                $('#review-product-modal').modal('show');
                if ($('#review-product-modal').hasClass('noid')) {
                    setTimeout(function () {
                        $('#review-product-modal').modal('hide');
                    }, 2000);
                }
                return false;
            });
        }
    }
})();

var PartialsReviewDownloadHistory = (function () {
    return {
        setup: function () {
            $('body').on('click', 'button.partialReviewDownloadHistory', function (event) {
                event.preventDefault();

                var userrate = $(this).attr("data-userrate");
                // -1 = no rate yet. 0= dislike  1=like
                $('#review-product-modal').find('#commenttext').val($(this).attr("data-comment"));
                $('#review-product-modal').find('#form_p').val($(this).attr("data-project"));

                if ($(this).hasClass("voteup")) {
                    if (userrate == 1) {
                        $('#review-product-modal').find('#votelabel').empty()
                            .append('<a class="btn btn-success active" style="line-height: 10px;"><span class="fa fa-plus"></span></a> is given already with comment:');
                        $('#review-product-modal').find(':submit').attr("disabled", "disabled").css("display", "none");
                        $('#review-product-modal').find('#commenttext').attr("disabled", "disabled");
                    } else {
                        $('#review-product-modal').find('input#voteup').val(1);
                        $('#review-product-modal').find('#votelabel').empty()
                            .append('<a class="btn btn-success active" style="line-height: 10px;"><span class="fa fa-plus"></span></a> Add Comment (min. 1 char):');
                        $('#review-product-modal').find('#commenttext').val('+');
                        $('#review-product-modal').find('#commenttext').removeAttr("disabled");
                        $('#review-product-modal').find(':submit').css("display", "block").removeAttr("disabled");

                    }
                } else { // vote down
                    if (userrate == 0) {
                        $('#review-product-modal').find('#votelabel').empty()
                            .append('<a class="btn btn-danger active" style="line-height: 10px;"><span class="fa fa-minus"></span></a> is given already with comment: ');
                        $('#review-product-modal').find('#commenttext').attr("disabled", "disabled");
                        $('#review-product-modal').find(':submit').attr("disabled", "disabled").css("display", "none");

                    } else {
                        $('#review-product-modal').find('input#voteup').val(2);
                        $('#review-product-modal').find('#votelabel').empty()
                            .append('<a class="btn btn-danger active" style="line-height: 10px;"><span class="fa fa-minus"></span></a> Add Comment (min. 5 chars): ');
                        $('#review-product-modal').find('#commenttext').val('-');
                        $('#review-product-modal').find('#commenttext').removeAttr("disabled");
                        $('#review-product-modal').find(':submit').removeAttr("disabled").css("display", "block");

                    }
                }

                $('#review-product-modal').modal('show');
                if ($('#review-product-modal').hasClass('noid')) {
                    setTimeout(function () {
                        $('#review-product-modal').modal('hide');
                    }, 2000);
                }
                return false;
            });
        }
    }
})();

//PartialsReviewDownloadHistoryNew
var CreateScoreRatingPopup = (function () {
    return {
        setup: function () {
              
            var preRatingSelected=0;
            var currentSelect;
            
            $( "select.rating-select" ).focus(function() {
                currentSelect = $(this);
                preRatingSelected = $(this).val();               
            }).change(function() {                       
                 var optionSelected = $(this).find("option:selected");             
                 var valueSelected  = optionSelected.val();
                 var textSelected   = optionSelected.text();                 
                 var userscore = $(this).attr("data-userrate");
                 var oldcomment =$(this).attr("data-comment");                 
                 var productcreator = $(this).attr("data-creator");    
                 var loginuser = $('#score-product-modal').find('#loginuser').val();           

                 /*if(userscore>0)
                 {
                    $('#score-product-modal').find('#votelabel').empty()
                        .append('Score '+userscore+' is given already with comment:'+oldcomment);                
                 }*/

                 if(!loginuser)
                 {                
                    $('#score-product-modal').modal('show');
                    return;
                 }

                 if (loginuser == productcreator) {                     
                    // ignore
                    $('#score-product-modal').find('#votelabel').text('Project owner not allowed');
                    $('#score-product-modal').find('.modal-body').empty();
                    $('#score-product-modal').find('#modal-btn-onsubmit').remove();                    
                    $('#score-product-modal').modal('show');
                    return;
                 }
                 

                if(valueSelected==-1)
                 {
                    $('#score-product-modal').find('#votelabel').empty()
                            .append('Remove Rating ');
                 }
                 else if(valueSelected<=5)
                 {
                     $('#score-product-modal').find('#votelabel').empty()
                            .append('Add a review to your rating "'+textSelected +'" (min. 5 chars) <span style="font-size:10px; display:block"> Please explain the reason for downvote to help the creator to make it better </span> ');
                      $('#score-product-modal').find('#voteup').val(2);
                 }                     
                 else
                 {                
                     $('#score-product-modal').find('#votelabel').empty()
                        .append('Add a review to your rating "'+textSelected+'" (min. 1 char):');       
                        $('#score-product-modal').find('#voteup').val(1);       
                 }


                $('#score-product-modal').find('#form_p').val($(this).attr("data-project"));
                 $('#score-product-modal').find(':submit').css("display", "block").removeAttr("disabled");
                 $('#score-product-modal').find('#commenttext').removeAttr("disabled");
                 if(valueSelected>=1 && valueSelected<=3)
                 {
                    $('#score-product-modal').find('#commenttext').val('');   
                 }else
                 {
                    $('#score-product-modal').find('#commenttext').val(textSelected);   
                 }  
                 $('#score-product-modal').find('#userscorevalue').val(valueSelected);
                 
                 if(valueSelected=='-1')
                 {
                    $('#score-product-modal').find(':submit').text("Remove Rating");                             
                 }else
                 {
                    $('#score-product-modal').find(':submit').text("Rate Now");                          
                 }                 
                 $('#score-product-modal').modal('show');
            });

            

            $("#modal-btn-cancel").on("click", function(){                                                
                $(currentSelect).find("option[value="+preRatingSelected+"]").prop('selected', true);                
                $("#score-product-modal").modal('hide');                
              });     
             var bOnsubmit = false;     
            $('#modal-btn-onsubmit').on("click",function(){
                bOnsubmit = true;
            });
            $('#score-product-modal').on('hidden.bs.modal', function () {
                 if(!bOnsubmit)
                 {
                    $(currentSelect).find("option[value="+preRatingSelected+"]").prop('selected', true);   
                 }                 
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
                var spin = $('<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>');
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


var PartialJsonClone = (function () {
    return {
        setup: function () {
            $('body').on("submit", 'form.partialjsonclone', function (event) {
                event.preventDefault();
                event.stopImmediatePropagation();

                var target = $(this).attr("data-target");
                var trigger = $(this).attr("data-trigger");
                var projectid = $(this).attr("data-projectid");
                jQuery.ajax({
                    data: $(this).serialize(),
                    url: this.action,
                    type: this.method,
                    dataType: "json",
                    error: function () {
                        $(target).empty().html("<span class='error'>Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.</span>");
                    },
                    success: function (data, textStatus) {                       

                        $('form#product-report-clone').find('#clonePId').val('');
                        $('form#product-report-clone').find('#report-text').val('')                                               
                        $('#report-product-clone-'+projectid).modal('hide');

                        var msgBox = $('#generic-dialog');                        
                        msgBox.find('.modal-header-text').empty();
                        msgBox.find('.modal-body').empty().append(data.message);
                        setTimeout(function () {
                            msgBox.modal('show');
                        }, 900);


                       
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


var PartialJsonFraud = (function () {
    return {
        setup: function () {
            $('body').on("submit", 'form.partialjsonfraud', function (event) {
                event.preventDefault();
                event.stopImmediatePropagation();

                var target = $(this).attr("data-target");
                var trigger = $(this).attr("data-trigger");

                var text = $('form.partialjsonfraud').find('#report-text').val();
                if(text.length<5)
                {
                    $('form.partialjsonfraud').find('p.warning').remove();
                    $('form.partialjsonfraud').find('#report-text').parent().append('<p class="warning" style="color:red">at least 5 chars</p>');
                    return false;
                }

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
                var c = $.trim($('#commenttext').val());
                var v = $('#voteup').val();
                if(v==2) {
                    // votedown
                    if(c.length<5)
                    {
                            if($('#review-product-modal').find('#votelabel').find('.warning').length==0)
                            {
                                $('#review-product-modal').find('#votelabel').append("</br><span class='warning' style='color:red'> Please give a comment, thanks!</span>");
                            }
                            return;
                    }
                }
                if(c.length<1)
                {
                        if($('#review-product-modal').find('#votelabel').find('.warning').length==0)
                        {
                            $('#review-product-modal').find('#votelabel').append("</br><span class='warning' style='color:red'> Please give a comment, thanks!</span>");
                        }
                        return;
                }

                $(this).find(':submit').attr("disabled", "disabled");
                $(this).find(':submit').css("white-space", "normal");
                var spin = $('<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>');
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



var PartialCommentReviewFormNew = (function () {
    return {
        setup: function () {
            this.initForm();
        },
        initForm: function () {
            $('body').on("submit", 'form.product-add-comment-review-new', function (event) {
                event.preventDefault();
                event.stopImmediatePropagation();
                var c = $.trim($('#score-product-modal').find('#commenttext').val());
                var v = $('#score-product-modal').find('#voteup').val();
                if(v==2) {
                    // votedown
                    if(c.length<5)
                    {
                            if($('#score-product-modal').find('#votelabel').find('.warning').length==0)
                            {
                                $('#score-product-modal').find('#votelabel').append("</br><span class='warning' style='color:red'> Please give a comment, thanks!</span>");
                            }
                            return;
                    }
                }
                if(c.length<1)
                {
                        if($('#score-product-modal').find('#votelabel').find('.warning').length==0)
                        {
                            $('#score-product-modal').find('#votelabel').append("</br><span class='warning' style='color:red'> Please give a comment, thanks!</span>");
                        }
                        return;
                }

                $(this).find(':submit').attr("disabled", "disabled");
                $(this).find(':submit').css("white-space", "normal");
                var spin = $('<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>');
                $(this).find(':submit').append(spin);

                jQuery.ajax({
                    data: $(this).serialize(),
                    url: this.action,
                    type: this.method,
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('#score-product-modal').modal('hide');
                        var msgBox = $('#generic-dialog');
                        msgBox.modal('hide');
                        msgBox.find('.modal-header-text').empty().append('Please try later.');
                        msgBox.find('.modal-body').empty().append("<span class='error'>Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.</span>");
                        setTimeout(function () {
                            msgBox.modal('show');
                        }, 900);
                    },
                    success: function (results) {
                        $('#score-product-modal').modal('hide');
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

            $(form).find(':submit').on('click', function (e) {
                e.preventDefault();
                $(form).find(':submit').attr("disabled", "disabled");
                $(form).find(':submit').css("white-space", "normal");
                var spin = $('<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>');
                $(form).find(':submit').append(spin);
                $(form).submit();
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

var AjaxFormWithProgress = (function () {
    return {
        setup: function (idForm) {
            var form = $(idForm);
            var target = form.attr("data-target");
            var trigger = form.attr("data-trigger");
            var bar = form.find('.progress-bar');
            var percent = form.find('.progress-percent');

            $(form).find(':submit').on('click', function (e) {
                e.preventDefault();
                $(form).find(':submit').attr("disabled", "disabled");
                $(form).find(':submit').css("white-space", "normal");
                var spin = $('<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>');
                $(form).find(':submit').append(spin);
                $(form).submit();
            });

            form.ajaxForm({
                beforeSend: function() {
                    var percentVal = '0%';
                    bar.parent().removeClass('hidden');
                    bar.width(percentVal);
                    percent.html(percentVal);
                },
                uploadProgress: function(event, position, total, percentComplete) {
                    var percentVal = percentComplete + '%';
                    bar.width(percentVal);
                    percent.html(percentVal);
                },
                error: function () {
                    $(target).empty().html("<span class='error'>Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.</span>");
                },
                success: function (results) {
                    var percentVal = '100%';
                    bar.width(percentVal);
                    percent.html(percentVal);

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

            $(form).find(':submit').on('click', function (e) {
                e.preventDefault();
                $(form).find(':submit').attr("disabled", "disabled");
                $(form).find(':submit').css("white-space", "normal");
                var spin = $('<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>');
                $(form).find(':submit').append(spin);
                $(form).submit();
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
            var target = $(idTargetElement);
            $('body').on("submit", 'form.product-add-comment', function (event) {
                event.preventDefault();
                event.stopImmediatePropagation();
                $(this).find('button').attr("disabled", "disabled");
                $(this).find('.glyphicon.glyphicon-send').removeClass('glyphicon-send').addClass('glyphicon-refresh spinning');

                jQuery.ajax({
                    data: $(this).serialize(),
                    url: this.action,
                    type: this.method,
                    dataType: "json",

                    error: function (jqXHR, textStatus, errorThrown) {
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
                Date.now = function () {
                    return new Date().getTime();
                }
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

            var json_url = "https://blog.opendesktop.org/?json=1&callback=?";
            $.getJSON(json_url, function (res) {
                var crss = '';
                $.each(res.posts, function (i, item) {
                    if (i >= 3) {
                        return false;
                    }
                    var m = moment(item.date);
                    crss += '<div class="commentstore"><a href="' + item.url + '"><span class="title">' + item.title + '</span></a><div class="newsrow">'
                        + '<span class="date">' + m.format('MMM DD YYYY') + '</span><span class="newscomments">'+ item.comments.length
                        +' Comment'+(item.comments.length>1?'s':'')
                        +'</span></div></div>';
                });
                $("#rss-feeds").html(crss);

            });
        }

    }
})();


var BlogJson = (function () {
    return {
        setup: function () {
            var urlforum = 'https://forum.opendesktop.org/';
            var json_url = '/json/forum';            
            $.ajax(json_url).then(function (result) {
              var topics = result.topic_list.topics;
              var crss = '';
              var count =3;
              topics.sort(function(a,b){
                  return new Date(b.last_posted_at) - new Date(a.last_posted_at);
                });

             $.each(topics, function (i, item) {
                 if(!item.pinned){
                     var m = item.timeago;
                     var r = 'Reply';
                     var t = item.posts_count -1;
                     if(t==0){
                        r = 'Replies';
                     }else if(t==1){
                        r = 'Reply';
                     }else{
                        r = 'Replies';
                     }
                     crss += '<div class="commentstore"><a href="' + urlforum+'t/'+item.id + '"><span class="title">' + item.title + '</span></a><div class="newsrow">'
                         + '<span class="date">' + m + '</span><span class="newscomments">'+ t +' '+ r
                         +'</span></div></div>';
                    count--;
                 }
                 if(count==0) return false;

             });
             $("#blogJson").html(crss);
            });
        }

    }
})();

var GitlabNewProjectsJson = (function () {
    return {
        setup: function (url_git) {         
            var json_url = '/json/gitlabnewprojects';
            $.ajax(json_url).then(function (result) {
              var topics = result;
              var crss = '';

             $.each(topics, function (i, item) {                
                var m = item.timeago;
                crss += '<div class="commentstore"><div class="row"><div class="col-lg-2 col-md-3 col-sm-4 col-xs-4"><div class="text-center">';
                crss += '<a href="' + item.web_url + '"><div class="avatar-container">';
                if(item.avatar_url) {
                    crss += '<img class="avatar project-avatar s40 js-lazy-loaded" src="' + item.avatar_url + '" width="40" height="40">';
                } else {
                    crss += '<div class="avatar project-avatar s40 identicon bg2">' + item.name.substr(0,1) + '</div>';
                }
                crss += '</div></a></div></div><div class="col-lg-9 col-md-6 col-sm-4 col-xs-4" style="padding-left: 25px;"><a href="' + item.web_url + '">' + item.name + '</a></div></div>';
                crss += '<div class="row" style="margin-left: 0;margin-right: 0;"><div class="userinfo"><a href="' + url_git + '/' + item.namespace.path + '" class="tooltipuserleft tooltipstered">';
                showGitUserAvatar(url_git,item.namespace.name, item.id);
                crss += '<img id="avatar_' + item.namespace.name + '_' + item.id + '" src="">';
                crss += '<span style="display: block">' + item.namespace.name + '</span></a></div></div>';

                crss += '<div class="row " style="margin-left: 0;margin-right: 0;">';
                crss += '   <span style="font-size: smaller;">' + m + '</span></div></div></div>';      

             });
             $("#lastgitprojects").html(crss);
            });
        }

    }
})();

function showGitUserAvatar(url_git,username, projectid) {
    var json_url = url_git+'/api/v4/users?username=' + username;
    $.ajax(json_url).then(function (result) {
        var usr_list = result;
        $.each(usr_list, function (i,usr) {
            $img = $('#avatar_' + username + '_' + projectid);
            //alert(usr);
            //alert($img.attr('src'));
            $img.attr('src',usr.avatar_url);
            //alert($img.attr('src'));
        });
    });
}

var ProductDetailCarousel = (function () {
    return {
        setup: function () {
            $('.carousel-inner img').each(function (index) {
                $(this).on("click", function () {
                    if ($("#product-main-img-container").hasClass("imgfull")) {
                        $('#product-main-img-container').prependTo($('#product-main'));
                    } else {
                        $('#product-main-img-container').prependTo($('#product-page-content'));
                    }
                    $("#product-main-img-container").toggleClass("imgfull");
                    $("#product-main-img-container").toggleClass("imgsmall");
                });
            });
        }
    }
})();

var AppimagequestionOnClick = (function () {
    return {
        setup: function (projectid) {
           $('body').on('click', 'i.appimagequestion', function (event) {
                    var msgBox = $('#generic-dialog');
                    msgBox.modal('show');
                    msgBox.find('.modal-header-text').empty().append('AppImage Info');
                    msgBox.find('.modal-body').empty().append("<div class='info' >For easy appimage use, install appimage launcher :"
                        +"<p><a target='_blank' href='https://www.opendesktop.org/p/1228228'>www.opendesktop.org/p/1228228</a><p>More info: <br/><a target='_blank' href='https://www.linuxuprising.com/2018/04/easily-run-and-integrate-appimage-files.html'>www.linuxuprising.com/2018/04/easily-run-and-integrate-appimage-files.html  </a>  </div>");
           });

        }
    }
})();


var ProductDetailBtnGetItClick  = (function () {
    return {
        setup: function (projectid) {
           $('body').on('click', 'button#project_btn_download', function (event) {

                    $.fancybox({
                        'hideOnContentClick': true,
                        'autoScale': true,
                        'cyclic': 'true',
                        'transitionIn': 'elastic',
                        'transitionOut': 'elastic',
                        'type': 'ajax',
                        helpers: {
                            overlay: {
                                locked: false
                            }
                        },
                        autoSize: true,
                        href:'/p/'+projectid+'/ppload'

                    });


           });

        }
    }
})();

var GhnsExcludedClick  = (function () {
    return {
        setup: function (projectid) {
           $('body').on('click', '#ghns_excluded-this-checkbox', function (event) {
                        event.stopPropagation();
                        var status = 1;
                        var title = '';
                        var btntxt = '';
                        if (this.checked) {
                            status = 1;
                            title = 'Please specify why this product should be excluded (min 5 chars) :';
                            btntxt=' ghns exclude';
                        } else {
                            status = 0;
                            title = 'Please specify why this product should be included (min 5 chars) :';
                            btntxt=' ghns include';
                        }

                        var msgBox = $('#generic-dialog');
                        msgBox.modal('show');
                        msgBox.find('.modal-header-text').empty().append('GHNS ');
                        msgBox.find('.modal-body').empty().append(title+
                                    '<p> <textarea class="" id="ghns-excluded-text" name="t" cols="50" rows="3"></textarea> <p><button id="btn-ghns-excluded" data-projectid='+projectid+'  data-status = '+status+' class="btn-ghns-excluded">'+btntxt+'</button>');

                        $('body').on("click", "#btn-ghns-excluded", function(){
                                var txt = $('#ghns-excluded-text').val();
                                if(txt.length<5) {
                                    alert('min 5 chars');
                                    return false;
                                }
                                var project_id = $(this).attr('data-projectid');
                                var status = $(this).attr('data-status');
                                var target = "/backend/project/doghnsexclude";
                                 $.ajax({
                                       url: target,
                                       method:'POST',
                                       data:{'project_id':project_id,'ghns_excluded':status,'msg':txt},
                                       success: function (results) {
                                           var msgBox = $('#generic-dialog');
                                           if (status == 0) {
                                                 msgBox.find('.modal-body').empty().append('Project is successfully included into GHNS');
                                                $('#ghns_excluded-this-checkbox').prop("checked", false);

                                           } else {
                                                 msgBox.find('.modal-body').empty().append('Project is successfully excluded into GHNS');
                                               $('#ghns_excluded-this-checkbox').prop("checked", true);
                                           }

                                           setTimeout(function () {
                                               msgBox.modal('hide');
                                           }, 1000);
                                       },
                                       error: function () {
                                           alert('Service is temporarily unavailable.');
                                       }
                                   });
                        });

                    return false;
           });

        }
    }
})();

var AboutMeMyProjectsPaging = (function () {
  return {
      setup: function () {
        $(window).scroll(function() {
            var end = $("footer").offset().top;
            var viewEnd = $(window).scrollTop() + $(window).height();
            var distance = end - viewEnd;
            if (distance < 300){
            // }
            // if($(window).scrollTop() == $(document).height() - $(window).height()) {
                    if(!$('button#btnshowmoreproducts').length) return;
                    let indicator = '<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>';
                    let nextpage = $('button#btnshowmoreproducts').attr('data-page');
                    $('button#btnshowmoreproducts').remove();
                    $url = window.location.href;
                    target = '#my-products-list';
                    let container = $('<div></div>').append(indicator).load($url,{projectpage:nextpage},function (response, status, xhr) {
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
                        });
                    $('#my-products-list').append(container);
            }
        });


      }
  }
})();

var AboutMeMyProjectsPagingButton = (function () {
  return {
      setup: function () {
        let indicator = '<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>';
        $('body').on('click', 'button#btnshowmoreproducts', function (event) {
                let nextpage = $(this).attr('data-page');
                $(this).remove();
                $url = window.location.href;
                target = '#my-products-list';
                let container = $('<div></div>').append(indicator).load($url,{projectpage:nextpage},function (response, status, xhr) {
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
                    });
                $('#my-products-list').append(container);
        });


      }
  }
})();

var ProductDetailCommentTooltip = (function () {
    return {
        setup: function () {
            TooltipUser.setup('tooltipuser','right');
        }
    }
})();

function generateTooltipster(elements,tooltipSide)
{
    $(elements).each(function() {
        $(this).tooltipster(
                {
                    side: tooltipSide,
                    theme: ['tooltipster-light', 'tooltipster-light-customized'],
                    contentCloning: true,
                    contentAsHTML: true,
                    interactive: true,
                    functionBefore: function (instance, helper) {
                        var origin = $(helper.origin);
                        var userid = origin.attr('data-user');
                        if (origin.data('loaded') !== true) {
                            $.get('/member/' + userid + '/tooltip/', function (data) {
                                var d = data.data;
                                var tmp = '<div class="mytooltip"><div class="header">' + d.username
                                    + ' <span class="glyphicon glyphicon-map-marker"></span>' + d.countrycity
                                    + '</div>'
                                    + '<div class="statistic">'
                                    + '<div class="row"><span class="title">' + d.cntProjects + '</span> products </div>'
                                    + '<div class="row"><span class="title">' + d.totalComments + '</span> comments </div>'
                                    + '<div class="row">Likes <span class="title">' + d.cntLikesGave + '</span>  products </div>'
                                    + '<div class="row">Got <span class="title">' + d.cntLikesGot + '</span> Likes <i class="fa fa-heart myfav" aria-hidden="true"></i> </div>'
                                    + '<div class="row">Last time active : ' + d.lastactive_at + ' </div>'
                                    + '<div class="row">Member since : ' + d.created_at + ' </div>'
                                    + '</div>';

                                tmp = tmp + '</div>';

                                instance.content(tmp);
                                origin.data('loaded', true);
                            });
                        }
                    }

                }
            );
    });
}

var TooltipUser = (function () {
    return {
        setup: function (tooltipCls, tooltipSide) {
            $('.'+tooltipCls).tooltipster(
                {
                    side: tooltipSide,
                    theme: ['tooltipster-light', 'tooltipster-light-customized'],
                    contentCloning: true,
                    contentAsHTML: true,
                    interactive: true,
                    functionBefore: function (instance, helper) {
                        var origin = $(helper.origin);
                        var userid = origin.attr('data-user');
                        if (origin.data('loaded') !== true) {
                            $.get('/member/' + userid + '/tooltip/', function (data) {
                                var d = data.data;
                                var tmp = '<div class="mytooltip"><div class="header">' + d.username
                                    + ' <span class="glyphicon glyphicon-map-marker"></span>' + d.countrycity
                                    + '</div>'
                                    + '<div class="statistic">'
                                    + '<div class="row"><span class="title">' + d.cntProjects + '</span> products </div>'
                                    + '<div class="row"><span class="title">' + d.totalComments + '</span> comments </div>'
                                    + '<div class="row">Likes <span class="title">' + d.cntLikesGave + '</span>  products </div>'
                                    + '<div class="row">Got <span class="title">' + d.cntLikesGot + '</span> Likes <i class="fa fa-heart myfav" aria-hidden="true"></i> </div>'
                                    + '<div class="row">Last time active : ' + d.lastactive_at + ' </div>'
                                    + '<div class="row">Member since : ' + d.created_at + ' </div>'
                                    + '</div>';

                                tmp = tmp + '</div>';

                                instance.content(tmp);
                                origin.data('loaded', true);
                            });
                        }
                    }

                }
            );
        }
    }
})();



function generateTooltipUserPlings(elements,tooltipSide)
{
    $(elements).each(function() {
        $(this).tooltipster(
                {
                    side: tooltipSide,
                    theme: ['tooltipster-light', 'tooltipster-light-customized'],
                    contentCloning: true,
                    contentAsHTML: true,
                    interactive: true,
                    functionBefore: function (instance, helper) {
                        var origin = $(helper.origin);
                        var userid = origin.attr('data-user');
                        if (origin.data('loaded') !== true) {
                            $.get('/plings/tooltip/id/'+userid, function (data) {

                                var tmp = '<div class="tooltipuserplingscontainer">';
                                $.each(data.data, function( index, value ) {
                                    if(index>10) return false;
                                    if(value.profile_image_url.indexOf('http')<0)
                                    {
                                         value.profile_image_url = "https://cn.opendesktop.org/cache/40x40-2/img/"+value.profile_image_url ;
                                    }
                                    if(value.profile_image_url.indexOf('.gif')>0)
                                    {
                                         value.profile_image_url = "https://cn.opendesktop.org/img/"+value.profile_image_url ;
                                    }
                                    tmp = tmp+'<div class="user"><a href="/member/'+value.member_id+'"><img src="'+value.profile_image_url+'" /></a><span class="caption">'+value.username+'</span></div>';
                                });
                                tmp = tmp + '</div>';
                                instance.content(tmp);
                                origin.data('loaded', true);
                            });
                        }
                    }

                }
            );
    });
}

var TooltipUserPlings = (function () {
    return {
        setup: function (tooltipCls, tooltipSide) {
            $('.'+tooltipCls).tooltipster(
                {
                    side: tooltipSide,
                    theme: ['tooltipster-light', 'tooltipster-light-customized'],
                    contentCloning: true,
                    contentAsHTML: true,
                    interactive: true,
                    functionBefore: function (instance, helper) {
                        var origin = $(helper.origin);
                        var userid = origin.attr('data-user');
                        if (origin.data('loaded') !== true) {
                            $.get('/plings/tooltip/id/'+userid, function (data) {

                                var tmp = '<div class="tooltipuserplingscontainer">';
                                $.each(data.data, function( index, value ) {
                                    if(index>10) return false;
                                    if(value.profile_image_url.indexOf('http')<0)
                                    {
                                         value.profile_image_url = "https://cn.opendesktop.org/cache/40x40-2/img/"+value.profile_image_url ;
                                    }
                                    if(value.profile_image_url.indexOf('.gif')>0)
                                    {
                                         value.profile_image_url = "https://cn.opendesktop.org/img/"+value.profile_image_url ;
                                    }
                                    tmp = tmp+'<div class="user"><a href="/member/'+value.member_id+'"><img src="'+value.profile_image_url+'" /></a><span class="caption">'+value.username+'</span></div>';
                                });
                                tmp = tmp + '</div>';
                                instance.content(tmp);
                                origin.data('loaded', true);
                            });
                        }
                    }

                }
            );
        }
    }
})();


var AboutMePage = (function () {
    return {
        setup: function (username) {
            var t = $(document).prop('title');
            var tnew = username + "'s Profile " + t;
            $(document).prop('title', tnew);
        }
    }
})();

var InitActiveHashTab = (function () {
    return {
        setup: function () {
            var activeTab = document.location.hash;
            if($('a[href="'+ activeTab +'"]'))
            {
                $('a[href="'+ activeTab +'"]').tab('show');
            }

        }
    }
})();


var AboutmeTab= (function () {
    return {
        setup: function () {
            var indicator = '<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>';
            $('body').on('click', 'a.aboutmetab', function (event) {
               event.preventDefault();
               var el = $(this).attr('href');               
               var url = $(this).attr('data-href');
               if($(el).find('.product').length==0)
               {    // only load once
                    $(el).append(indicator).load(url,function (){                           
                                            
                       });              
               }
            });            
        }
    }
})();






var CommunityTabToplistMemberScrollPaging= (function () {
    return {
        setup: function (paramEl,paramUrl) {            
                $(window).scroll(function() {
                            var end = $("footer").offset().top;
                            var viewEnd = $(window).scrollTop() + $(window).height();
                            var distance = end - viewEnd;
                            if (distance < 300){                           
                                    if(!$('button#toplistmemberscrollshowmore').length) return;
                                    var indicator = '<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>';
                                    var nextpage = $('button#toplistmemberscrollshowmore').attr('data-page');
                                    $('button#toplistmemberscrollshowmore').remove();

                                    var pagingurl = paramUrl+"/nopage/1/page/"+nextpage; 
                                    var $newdiv = $('<div class="toplistmemberx"></div>');
                                    $(paramEl).find('.product-list').append($newdiv);
                                    $($newdiv).load(pagingurl,function (){   
                                        generateTooltipster($($newdiv).find('.tooltipuser'),"right");                  
                                    })
                                }
                            });
        }
    }
})();

var CommunityTab= (function () {
    return {
        setup: function () {
            var indicator = '<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>';
            $('body').on('click', 'a.communitytab', function (event) {
               event.preventDefault();
               var el = $(this).attr('href');               
               var url = $(this).attr('data-href');
               if($(el).find('.list').find('.user').length==0)
               {    // only load once
                    $(el).find('.list').append(indicator).load(url,function (){   
                        generateTooltipster($(el).find('.tooltipuser'),"right");                  
                        if($(el).find('.tooltipuserplings').length>0)
                        {
                            generateTooltipUserPlings($(el).find('.tooltipuserplings'),"right");
                        }

                        if(el=='#toplistMemberPanel')
                        {
                            CommunityTabToplistMemberScrollPaging.setup(el,url);
                        }
                        // paging
                        let spans = $(el).find('.opendesktopwidgetpager span');
                        spans.each(function(index) {
                            $(this).on("click", function(){                      
                                $(this).parent().addClass('active').siblings().removeClass('active');                                                                                                                
                                var pagingurl = url+"/nopage/1/page/"+$(this).html();                                  
                                $(el).find('.product-list').html('');
                                $(el).find('.product-list').load(pagingurl,function (){   
                                generateTooltipster($(el).find('.tooltipuser'),"right");                  
                                if($(el).find('.tooltipuserplings').length>0)
                                {
                                    generateTooltipUserPlings($(el).find('.tooltipuserplings'),"right");
                                }})
                                                               
                            });
                        });

                        //end paging

                                            
                   });              
               }
            });

            var activeTab = document.location.hash;             
            if($('a[href="'+ activeTab +'"]').length>0)
            {
                $('a[href="'+ activeTab +'"]').tab('show');
                $('a[href="'+ activeTab +'"]').trigger( "click" );
            }else
            {
                activeTab = "#supportersPanel";
                $('a[href="'+ activeTab +'"]').trigger( "click" );                  
            }

            $('body').on('mouseenter', '.product-thumbnail-startpage', function () {
                $(this).popover('show');
            });

            $('body').on('mouseleave', '.product-thumbnail-startpage', function () {
                $(this).popover('hide');
            });

        }
    }
})();

var TagingProduct = (function () {
    return {
        setup: function () {
            $('input[name=tagsuser]')
                .tagify({
                                whitelist: ['good', 'great'],
                                autocomplete:true
                        })
                .on('remove', function(e, tagName){
                    console.log('removed', tagName)
                })
                .on('add', function(e, tagName){
                    console.log('added', tagName)
                });

        }
    }
})();

var TagingProductSelect2 = (function () {
    return {
        setup: function () {

                        $.fn.select2.amd.require(['select2/selection/search'], function (Search) {
                            Search.prototype.searchRemoveChoice = function (decorated, item) {
                                this.trigger('unselect', {
                                    data: item
                                });

                                this.$search.val('');
                                this.handleSearch();
                            };
                        }, null, true);

                        var t = $(".taggingSelect2").select2({
                            placeholder: "Add Tags here ...", //placeholder
                            tags: true,
                            tokenSeparators: [",", " "],
                            minimumInputLength: 3,
                            maximumSelectionLength: 5,
                            width: 'resolve',
                            ajax: {
                                    url: '/tag/filter',
                                    dataType: 'json',
                                    type: "GET",
                                    delay: 500, // wait 250 milliseconds before triggering the request
                                    processResults: function (data) {
                                          return {
                                             results : data.data.tags
                                          };
                                        }

                                }
                        });

                        // Bind an event
                        t.on('select2:select', function (e) {
                                      var data = e.params.data;
                                      var projectid = $("#tagsuserselect").attr('data-pid');
                                      var lis = t.parent().find('ul.select2-selection__rendered').find('li.select2-selection__choice').length;
                                      if(lis>5){
                                           t.find("option[value='"+data.id+"']").remove();
                                           return;
                                      }
                                      var regexp=new RegExp("^[0-9A-Za-z_.-]+$");
                                      if(!(regexp.test(data.text))){
                                           t.find("option[value='"+data.id+"']").remove();
                                           alert('Must be letter or number and can include hyphens');
                                      }
                                      if(data.text.length>10){
                                            t.find("option[value='"+data.id+"']").remove();
                                           alert('Max. length 45 chars');
                                      }

                        });
        }
    }
})();

var TagingProductDetail = (function () {
                        return {
                            setup: function () {
                                           TagingProductDetailSelect2.setup();
                                           $('body').on('click', 'button.topic-tags-btn', function (event) {
                                                $(this).toggleClass('Done');
                                                $('.product_category').find('.usertagslabel').remove();
                                                $('.tagsuserselectpanel').toggle();
                                                if($(this).text() == 'Done'){
                                                        $(this).text('Manage tags');
                                                        var newhtml = '';
                                                        var lis = $('li.select2-selection__choice');
                                                        $.each(lis, function( index, value ) {
                                                            newhtml=newhtml+'<a rel="nofollow" href="/search/projectSearchText/'+value.title+'/t/'+value.title+'/f/tags" '
                                                                                          +'class="topic-tag topic-tag-link usertagslabel">'+value.title+'</a>';
                                                        });
                                                         $('.product_category').find('.topicslink').html(newhtml);
                                                }else{
                                                    $(this).text('Done');
                                                }
                                           });
                            }
                        }
})();



var TagingProductDetailSelect2 = (function () {
    return {
        setup: function () {

                        $.fn.select2.amd.require(['select2/selection/search'], function (Search) {
                            Search.prototype.searchRemoveChoice = function (decorated, item) {
                                this.trigger('unselect', {
                                    data: item
                                });

                                this.$search.val('');
                                this.handleSearch();
                            };
                        }, null, true);

                        var t = $("#tagsuserselect").select2({
                            placeholder: "Input tags please...", //placeholder
                            tags: true,
                            minimumInputLength: 3,
                            closeOnSelect:true,
                            maximumSelectionLength: 5,
                            tokenSeparators: [",", " "],
                            ajax: {
                                    url: '/tag/filter',
                                    dataType: 'json',
                                    type: "GET",
                                    delay: 500, // wait 250 milliseconds before triggering the request
                                    processResults: function (data) {
                                          return {
                                                results : data.data.tags
                                          };
                                        }
                                    }
                        });

                        // Bind an event
                        t.on('select2:select', function (e) {
                                        var data = e.params.data;
                                        var projectid = $("#tagsuserselect").attr('data-pid');
                                            $.post( "/tag/add", { p: projectid, t: data.id })
                                                 .done(function( data ) {

                                                           if(data.status=='error'){
                                                               $('span.topic-tags-saved').css({ color: "red" }).html(data.message).show().delay(2000).fadeOut();
                                                                t.find("option[value='"+data.data.tag+"']").last().remove();
                                                                //t.find("option[value="+data.data.tag+"]").last().remove();
                                                           }
                                                           else
                                                           {
                                                               $('span.topic-tags-saved').css({ color: "green" }).html('<i class="fa fa-check"></i> Saved').show().delay(1000).fadeOut();
                                                           }
                                                 });

                        });

                        // Unbind the event
                        t.on('select2:unselect', function(e){
                                var data = e.params.data;
                                var projectid = $("#tagsuserselect").attr('data-pid');
                                $.post( "/tag/del", { p: projectid, t: data.id })
                                  .done(function( data ) {
                                            console.log(data);
                                             $('span.topic-tags-saved').css({ color: "green" }).html('<i class="fa fa-trash"></i>'+data.message).show().delay(1000).fadeOut();
                                            //$('span.topic-tags-saved').show().delay(1000).fadeOut();
                                  });

                        });
        }
    }
})();



var TagingLoopMyProducts = (function () {
                        return {
                            setup: function () {
                                           TagingLoopMyProductsSelect2.setup();
                                           $('body').on('click', 'button.topic-tags-btn', function (event) {
                                                $(this).toggleClass('Done');
                                                $(this).parent().find('.topicsuser').html('');

                                                $(this).parent().find('.tagsuserselectpanel').toggle();
                                                if($(this).text() == 'Done'){
                                                        $(this).text('Manage tags');
                                                        var newhtml = '';
                                                        var lis =  $(this).parent().find('li.select2-selection__choice');
                                                        $.each(lis, function( index, value ) {
                                                            newhtml=newhtml+'<a rel="nofollow" href="/search/projectSearchText/'+value.title+'/t/'+value.title+'/f/tags" '
                                                                                          +'class="topic-tag topic-tag-link usertagslabel">'+value.title+'</a>';
                                                        });
                                                        $(this).parent().find('.topicsuser').html(newhtml);
                                                }else{
                                                    $(this).text('Done');
                                                }
                                           });
                            }
                        }
})();

var TagingLoopMyProductsSelect2 = (function () {
    return {
        setup: function () {

                        $.fn.select2.amd.require(['select2/selection/search'], function (Search) {
                            Search.prototype.searchRemoveChoice = function (decorated, item) {
                                this.trigger('unselect', {
                                    data: item
                                });

                                this.$search.val('');
                                this.handleSearch();
                            };
                        }, null, true);

                        //var t = $("#tagsuserselect").select2({
                        var t = $(".taggingSelect2").select2({
                            placeholder: "Input tags please...", //placeholder
                            tags: true,
                            minimumInputLength: 3,
                            closeOnSelect:true,
                            maximumSelectionLength: 5,
                            tokenSeparators: [",", " "],
                            ajax: {
                                    url: '/tag/filter',
                                    dataType: 'json',
                                    type: "GET",
                                    delay: 500, // wait 250 milliseconds before triggering the request
                                    processResults: function (data) {
                                          return {
                                                results : data.data.tags
                                          };
                                        }
                                    }
                        });

                        // Bind an event
                        t.on('select2:select', function (e) {
                                        var data = e.params.data;
                                        var projectid  = $(this).attr('id').replace('tagsuserselect','');
                                        //var projectid = $("#tagsuserselect").attr('data-pid');
                                            $.post( "/tag/add", { p: projectid, t: data.id })
                                                 .done(function( data ) {
                                                           if(data.status=='error'){
                                                                $('#topic-tags-saved'+projectid).css({ color: "red" }).html(data.message).show().delay(2000).fadeOut();
                                                                t.find("option[value='"+data.data.tag+"']").last().remove();
                                                           }else if(data.status=='existing'){
                                                                $('#topic-tags-saved'+projectid).css({ color: "red" }).html(data.message).show().delay(2000).fadeOut();
                                                           }
                                                           else
                                                           {
                                                                $('#topic-tags-saved'+projectid).css({ color: "green" }).html('<i class="fa fa-check"></i> Saved').show().delay(1000).fadeOut();
                                                           }
                                                 });

                        });

                        // Unbind the event
                        t.on('select2:unselect', function(e){
                                var data = e.params.data;
                                //var projectid = $("#tagsuserselect").attr('data-pid');
                                var projectid  = $(this).attr('id').replace('tagsuserselect','');
                                $.post( "/tag/del", { p: projectid, t: data.id })
                                  .done(function( data ) {
                                             $('#topic-tags-saved'+projectid).css({ color: "green" }).html('<i class="fa fa-trash"></i>'+data.message).show().delay(1000).fadeOut();
                                  });

                        });
        }
    }
})();

var productRatingToggle = (function () {
    return {
        setup: function () {
            $('#showRatingAll').on('click', function () {
                $('#ratings-panel').find('.spinning').show();
                setTimeout(function () {
                    $('#ratings-panel').find('.spinning').hide();
                }, 500);
                $('.btnRateFilter').removeClass('activeRating');
                $(this).addClass('activeRating');
                $('.productRating-rows').show();
                $('.productRating-rows-inactive').show();
            });

            $('#showRatingActive').on('click', function () {
                $('#ratings-panel').find('.spinning').show();
                setTimeout(function () {
                    $('#ratings-panel').find('.spinning').hide();
                }, 500);
                $('.btnRateFilter').removeClass('activeRating');
                $(this).addClass('activeRating');
                $('.productRating-rows').show();
                $('.productRating-rows-inactive').hide();
            });

            $('#showRatingUpvotes').on('click', function () {
                $('#ratings-panel').find('.spinning').show();
                setTimeout(function () {
                    $('#ratings-panel').find('.spinning').hide();
                }, 500);
                $('.btnRateFilter').removeClass('activeRating');
                $(this).addClass('activeRating');
                $('.productRating-rows').show();
                $('.clsDownvotes').hide();
                $('.productRating-rows-inactive').hide();
            });


            $('#showRatingDownvotes').on('click', function () {
                $('#ratings-panel').find('.spinning').show();
                setTimeout(function () {
                    $('#ratings-panel').find('.spinning').hide();
                }, 500);
                $('.btnRateFilter').removeClass('activeRating');
                $(this).addClass('activeRating');
                $('.productRating-rows').show();
                $('.productRating-rows-inactive').hide();
                $('.clsUpvotes').hide();
            });
        }
    }
})();

var FilterBrowseOriginalFn= (function () {
    return {
        setup: function () {
                       $('body').on('click', 'input#filter_browse_original', function (event) {
                            var checked = $(this).is( ":checked" );
                            var url  = window.location.href;
                            if(url.indexOf("browse")<0)
                            {
                                url = url+'/browse/';
                            }
                            if(url.indexOf("filteroriginal")>0)
                            {
                                url = url.substring(0,url.indexOf("filteroriginal"));
                            }
                            if(checked){
                               window.location.href = url+'filteroriginal/1';
                            }else{
                                window.location.href = url+'filteroriginal/0';
                            }
                       });
        }
    }
})();


var FilterBrowseTagGroupFn= (function () {
    return {
        setup: function () {
            $('body').on('change', 'select.filter_browse_tag_group', function (event) {
                var filterVal = $(this).val();
                var groupId = $(this).attr('data_group_id');
                
                $.ajax({
                    url: '/explore/savetaggroupfilter?group_id='+groupId+'&tag_id='+filterVal,
                    cache: false
                  })
                  .done(function( response ) {
                      var url  = window.location.href;
                      window.location.href = url;
                  });
                
                
            });
        }
    }
})();
