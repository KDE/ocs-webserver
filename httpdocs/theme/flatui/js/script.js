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

$(document).ready(function () {
    var yetVisited = localStorage['visited'];
    if (!yetVisited) {
        // open popup
        localStorage['visited'] = "yes";
        localStorage['user_os'] = 1;
    }
});

var catpage_url;
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
var SettingsExpand = (function () {
    return {
        setup: function () {
            $('.panel-heading').on('click', function () {

                var this_panel = $(this).parents('.panel');
                var this_panel_body = this_panel.find('.panel-collapse');

                if (this_panel_body.hasClass('in')) {
                    $(this).removeClass('active');
                } else {
                    $(this).addClass('active');
                }

            });
        }
    };
})();

var ProductGallery = (function () {
    var maxImageCount = 5;

    return {

        setup: function () {
            $('body').on({
                mouseenter: function () {
                    $(this).find('.icon-check').hide();
                    $(this).find('.icon-cross').show();
                },
                mouseleave: function () {
                    $(this).find('.icon-check').show();
                    $(this).find('.icon-cross').hide();
                }
            }, '.product-image');
            $('body').on('click', '.icon-cross', function () {
                $(this).closest('.product-image').remove();
                ProductGallery.incImageCount();
            });
        },

        previewImage: function (input) {

            if (!input.files.length) {
                return;
            }

            maxImageCount--;

            var reader = new FileReader();
            var image = new Image();
            var file = input.files[0];

            reader.readAsDataURL(file);
            reader.onload = function (_image) {

                $('#gallery-error').remove();

                $('.product-image').last().append('<div class="absolute icon-check"></div>' +
                    '<div class="absolute icon-cross"></div>' +
                    '<div class="image" style="background-image: url(' + _image.target.result + ');"></div>'
                );
                $('.product-image').last().after('<div class="product-image relative">' +
                    '<input type="file" name="upload_picture[]" class="gallery-picture" onchange="ProductGallery.previewImage(this);"></div>'
                );
                if (maxImageCount <= 0) {
                    $('.upload-image-container').hide();
                }

                image.src = _image.target.result;              // url.createObjectURL(file);
                image.onload = function () {
                    var w = this.width,
                        h = this.height,
                        t = file.type,                           // ext only: // file.type.split('/')[1],
                        n = file.name,
                        s = ~~(file.size / 5120)// +'KB';
                    //if (w > 1024 || w < 100 || h > 768 || h < 75) {
                    //    $('.product-image').last().prev()
                    //        .find('div.icon-check')
                    //        .after('<div class="image-error">Wrong image dimensions</div>')
                    //        .removeClass('icon-check')
                    //        .addClass('icon-report')
                    //    ;
                    //    $('.product-image').last().prev()
                    //        .find('input.gallery-picture').remove()
                    //        .append('<input type="file" name="upload_picture[]" class="gallery-picture" onchange="ProductGallery.previewImage(this);"></div>')
                    //    ;
                    //}
                    if (s > 5120) {
                        $('.product-image').last().prev()
                            .find('div.icon-check')
                            .after('<div class="image-error">File too large</div>')
                            .removeClass('icon-check')
                            .addClass('icon-report')
                        ;
                        $('.product-image').last().prev()
                            .find('input.gallery-picture').remove()
                            .append('<input type="file" name="upload_picture[]" class="gallery-picture" onchange="ProductGallery.previewImage(this);"></div>')
                        ;
                    }
                };
                ;
                image.onerror = function () {
                    $('.product-image').last().prev().find('div.image').append('Invalid file type: ' + file.type);
                };
            }
        },
        thumbClick: function (element) {
            var index = $(element).attr('data-index');
            $('.thumb').removeClass('thumb-active');
            $(element).parent().addClass('thumb-active');
            $('#gallery-carousel').find('.active').removeClass('active');
            $('#gallery-carousel').find('.item[data-index="' + index + '"]').addClass('active');

        },
        gallerySlide: function () {
            var index = $('#gallery-carousel').find('.active').attr('data-index');
            $('.thumb-active').removeClass('thumb-active');
            $('.thumb a[data-index="' + index + '"]').parent().addClass('thumb-active');
        },
        incImageCount: function () {
            if (maxImageCount < 5) {
                maxImageCount = maxImageCount + 1;
            }
            $('.upload-image-container').show();
        }

    };
})();

var ImagePreview =  {
        hasError: false,
        setup: function () {
            this.initProductPicture();
            this.initTitlePicture();
            this.initProfilePicture();
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
        }
    };

var MenuHover = (function () {
    return {

        setup: function () {
            var user_os = localStorage['user_os'];
            var this_li = $('#pickos-dropdown-element' + user_os).parent('li');
            this_li.addClass('active');

            $('body').on('click', 'a#login-dropdown', function (event) {
                event.stopPropagation();
                $('.header-login-container').toggleClass('active');
            }).on('click', '.header-login-container', function (event) {
                event.stopPropagation();
            }).on('click', 'a.menu-trigger', function (event) {
                event.stopPropagation();
                var html_menu_element = $(this).attr('rel');
                $('.' + html_menu_element).toggleClass('active');
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

var FormCreateButton = (function () {
    return {
        setup: function () {
            $('body').on("click", "form[data-validate='ajax'] a[name='next'] , a[name='get_code']", function (event) {
                event.preventDefault();
                event.stopImmediatePropagation();

                var selector = $(this).attr('data-group');
                var data = {};

                $("[data-validateGroup=" + selector + ']').each(function () {
                    data[$(this).attr('id')] = $(this).val();
                });

                if ($(this).attr('name') == 'next') {
                    FormCreateButton.validate(data, $(this).attr('id'), FormCreateButton.next);
                }

                if ($(this).attr('name') == 'get_code') {
                    FormCreateButton.validate(data, $(this).attr('id'), FormCreateButton.submit);
                }

                return false;
            });
        },
        validate: function (data, button_id, callback) {

            jQuery.ajax({
                data: data,
                url: '/button/a/validate/',
                type: 'post',
                dataType: 'json',
                error: function () {

                },
                success: function (response) {
                    response = $.parseJSON(response);

                    if (callback != undefined && typeof callback == 'function') callback(response, button_id);

                }
            });

        },
        getHTML: function (arrayMessage) {
            var txthtml = '<ul class="validate-error small text-danger">';
            $.each(arrayMessage, function (key, value) {
                txthtml += '<li>' + value + '</li>';
            });
            txthtml += '</ul>';
            return txthtml;
        },
        next: function (response, button_id) {
            $('#' + button_id).parents('.tab-pane').find('.validate-error').remove();

            if (response == true) {
                //$('#'+button_id).removeProp('disabled');
                $('#' + button_id).parents('.tab-pane').removeClass('active');
                var nextStep = $('#' + button_id).attr('rel');
                $('#' + nextStep).addClass('active');
                $(".nav-tabs li").filter('.active').addClass('validated').removeClass('active');
                $(".nav-tabs li").filter('.validated').last().next('li').addClass('active');
                $('.nav-tabs li.active').children('a').tab('show');
                return;
            }

            //$('#'+button_id).prop('disabled', true);
            $.each(response, function (element_id, message) {
                $('#' + button_id).parents('.tab-pane').find('#' + element_id).parent().append(FormCreateButton.getHTML(message));
            });

        },
        submit: function (response, button_id) {
            $('#' + button_id).parents('.tab-pane').find('.validate-error').remove();

            if (response == true) {
                $('#' + button_id).parents('form').submit();
                return;
            }

            $.each(response, function (element_id, message) {
                $('#' + button_id).parents('.tab-pane').find('#' + element_id).parent().append(FormCreateButton.getHTML(message));
            });
        }

    }
})();

var InputAvatar = (function () {
    return {
        setup: function () {
            $('body').on("change", 'input.avatar', function (event) {
                event.preventDefault();
                var username = this.value;
                var dataHref = $(this).attr("data-href");
                var target = $(this).attr("data-target");
                var dataFunc = $(this).attr("data-func");
                var dataSrc = $(this).data('src');

                if (dataFunc) {
                    var funcCall = dataFunc + '(\'' + username + '\')';
                    username = eval(funcCall);
                }

                var imageSrc = dataHref.replace('{username}', username);
                $(target).attr('src', imageSrc);
                $('#profile_img_src').val(dataSrc);

                return false;
            });
            $('body').on("change", 'input#profile_picture_upload', function (event) {
                event.preventDefault();
                $('#profile_img_src').val('local');
            });
        }
    }
})();

var ProductPage = (function () {

    return {

        setup: function () {

            $('.scroll-pane').jScrollPane({
                mouseWheelSpeed: 30,
                animateScroll: true
            });

            // $('.comment-row').each(function () {
            //
            //     var this_comment = $(this);
            //     var this_maker_comment_form = this_comment.find('.maker-form');
            //     var this_show_reply = this_comment.find('a.show-maker-reply');
            //     var this_hide_reply = this_comment.find('.glyphicon-remove');
            //
            //     this_show_reply.on('click', function () {
            //         this_show_reply.hide();
            //         this_maker_comment_form.show();
            //     });
            //
            //     this_hide_reply.on('click', function () {
            //         this_maker_comment_form.hide();
            //         this_show_reply.show();
            //     })
            //
            // });

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

var AboutMePage = (function () {

    return {

        setup: function () {

            $('.scroll-pane').jScrollPane({
                mouseWheelSpeed: 30,
                animateScroll: true
            });
        }
    }

})();

var BrowseCategoriesPage = (function () {

    return {

        setup: function () {

            $(window).load(function () {

                $('#cat-list').find('.container').each(function () {

                    var $container = $(this).find('.row');

                    $container.masonry({
                        itemSelector: '.item'
                    });

                });

            });

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

var exploreCarousel = (function () {

    return {


        setup: function () {

            $(document).ready(function () {

                // This event is triggered when you visit a page in the history
                // like when yu push the "back" button
                $(window).on('popstate', function (e) {
                    //alert('State: ' + e.originalEvent.state.command);
                    if (e.originalEvent.state.command == 'showList') {
                        $('.bootstrap-dialog').hide();
                        $('#explore-content').css('visibility', 'visible');
                        $('body > #page_header').css('visibility', 'visible');
                        $('body > footer').css('visibility', 'visible');

                    } else {
                        $('body > #page_header').css('visibility', 'hidden');
                        $('#explore-content').css('visibility', 'hidden');
                        $('body > footer').css('visibility', 'hidden');
                        $('.bootstrap-dialog').show();

                    }
                    //console.log(e.originalEvent.state);
                });


                adjustMainHeight();
                /*
                 adjustMainHeight();
                 initializeCarousel();

                 // carousel slide animation
                 $('.navigation').delegate('ul > li', 'click', function () {
                 var this_li = $(this);
                 //slotMachineAnimation(this_li);
                 animateExploreCarousel(this_li);
                 });

                 // carousel arrows
                 $('#explore-carousel').delegate('a.carousel-arrow', 'click', function () {
                 if ($(this).attr('id') == 'right') {
                 this_li = $('li.active').next('li');
                 } else {
                 this_li = $('li.active').prev('li');
                 }
                 if (this_li.size()) {
                 animateExploreCarousel(this_li);
                 }
                 });
                 */
                // show product
                $('.explore-product').delegate('a', 'click', function (e) {
                    e.preventDefault();
                    var this_url = $(this).attr('href');
                    catpage_url = window.location.href;
                    var app_elememnt = $('#explore-product-modal').find('.modal-body').find('#support-widget');

                    var stateObj = {url: this_url, title: "a title", command: 'showDetail'};
                    history.pushState(stateObj, "Product", this_url);

                    BootstrapDialog.show({
                        //id: 'product_popup',
                        message: function (dialog) {
                            var $message = $('<div></div>');
                            var pageToLoad = dialog.getData('pageToLoad');

                            $('#explore-content').css('visibility', 'hidden');
                            $('body > #page_header').css('visibility', 'hidden');
                            $('body > footer').css('visibility', 'hidden');


                            $.get(pageToLoad, function (data) {
                                $message.html(data);

                                var slideShowInterval;
                                SlideShowRender.setup();
                                ProductSlideShow.setup();

                                var app_elememnt = $('#support-widget');
                                angular.bootstrap(app_elememnt, ['widgetApp']);
                            });

                            /*
                             $message.load(pageToLoad, function(response, status, xhr) {

                             })
                             */
                            return $message;
                        },
                        data: {
                            'pageToLoad': this_url
                        },
                        onhide: function (dialogRef) {
                            //window.history.replaceState('object or string', 'title', catpage_url);
                            var stateObj = {url: catpage_url, title: "a title", command: 'showList'};
                            history.pushState(stateObj, "Product List", catpage_url);

                            $('body > #page_header').css('visibility', 'visible');
                            $('body > footer').css('visibility', 'visible');
                            $('#explore-content').css('visibility', 'visible');
                        },
                        onshow: function (dialogRef) {
                            adjustMainHeight();
                        },
                        onshown: function (dialogRef) {
                            $('body > #page_header').css('visibility', 'hidden');
                            $('body > footer').css('visibility', 'hidden');
                            $('#explore-content').css('visibility', 'hidden');
                        }
                    });

                });
                /**
                 e.preventDefault();
                 var this_url = $(this).attr('href');
                 var catpage_url = window.location.href;


                 $( "#dialog_iframe" ).attr('src',this_url);

                 $( "#dialog" ).dialog( "open" );
                 });
                 $("#dialog").dialog({
                	    autoOpen: false,
                	    position: 'center' ,
                	    title: false,
                	    draggable: false,
                	    width : '100%',
                	    height : '100%', 
                	    resizable : false,
                	    modal : true,
                	});
                 */
                /**
                 $.ajax({
                        url: this_url,
                        type: 'post',
                        success: function (results) {
                            var $response = $(results);
                            //var product_page = $response.filter('#product-page-content').html();
                            var header = $response.html();
                            var product_page = $response.filter('#product-page-content').html();
                            var app_elememnt = $('#explore-product-modal').find('.modal-body').find('#support-widget');
                            
                            
                            window.history.replaceState('object or string', 'title', this_url);
                            $('#explore-product-modal').find('.modal-body').html(header);
                            //$('#explore-product-modal').find('.modal-body').html(product_page);
                            angular.bootstrap(app_elememnt,['widgetApp']);
                            $('#explore-product-modal').modal('show');
                            adjustMainHeight();

                            $('#explore-product-modal').on('hide.bs.modal', function (e) {
                                window.history.replaceState('object or string', 'title', catpage_url);                                
                                $('#explore-product-modal').find('.modal-body').html('');
                            });
                        }
                    });

                 });
                 */
            });

            $(window).on('resize', function () {
                adjustMainHeight();
            });

            /** ADJUST MAIN SIZE **/

            function adjustMainHeight() {
                if ($('#product-showcase').is(':empty')) {
                    var win_height = $(window).height();
                    var header_height = $('header').height();
                    var modal_body_height = win_height - header_height;
                    $('#explore-product-modal').find('.modal-body').css('height', modal_body_height);
                }
            }

            /** ADJUST MAIN SIZE **/

            /** CAROUSEL INITIALIZATION **/

            function initializeCarousel() {

                var crousel_items = $(".explore-product");

                for (var i = 0; i < crousel_items.length; i += 10) {
                    var carousel_i = i / 10;

                    if (carousel_i == 0) {
                        crousel_items.slice(i, i + 10).wrapAll('<div class="item active" id="slide-' + carousel_i + '"></div>');
                        var this_indicator = '<li class="active" rel="' + carousel_i + '"><a>1</a></li>';
                    } else {
                        var carousel_slide_number = parseInt(carousel_i) + 1;
                        crousel_items.slice(i, i + 10).wrapAll('<div class="item" id="slide-' + carousel_i + '"></div>');
                        var this_indicator = '<li rel="' + carousel_i + '"><a>' + carousel_slide_number + '</a></li>';
                    }

                    $('#explore-carousel').find('ul').append(this_indicator);
                }

                $('.item').each(function () {
                    var item_number = 0;
                    var this_item = $(this);
                    $(this).find('.explore-product').each(function () {
                        item_number++;
                        $(this).addClass('col-' + item_number);
                        if (this_item.hasClass('active')) {
                            $(this).css('top', '0px');
                        }
                    });
                });
            }

            /** /CAROUSEL INITIALIZATION **/

            /** ANIMATE EXPLORE CAROUSEL **/

            function animateExploreCarousel(this_li) {
                var current_li = $('li.active');
                var current_slide_id = current_li.attr('rel');
                var next_slide_id = this_li.attr('rel');
                var current_slide = $('.item.active');
                var next_slide = $('.item#slide-' + next_slide_id);

                if (current_slide_id < next_slide_id) {
                    var cur_left = '-100%';
                    var next_left = '100%';
                } else {
                    var cur_left = '100%';
                    var next_left = '-100%';
                }
                next_slide.css('left', next_left);

                current_slide.stop().animate({left: cur_left}, 300, 'easeInCubic', function () {
                    current_slide.removeClass('active');
                    current_li.removeClass('active');
                });

                next_slide.stop().animate({left: '0px'}, 300, 'easeInCubic', function () {
                    next_slide.addClass('active');
                    this_li.addClass('active');
                });

            }

            /** /ANIMATE EXPLORE CAROUSEL **/

            /** SLOT MACHINE SLIDE EFFECT

             function slotMachineAnimation(this_li){
                    var this_rel = this_li.attr('rel');
                    var this_slide = $('.item#slide-'+this_rel);
                    var active_slide = $('.item.active');
                    if ( (!this_slide.hasClass('active')) && ($(":animated").length == 0 ) ) {

                        var speed_1 = Math.floor(Math.random()*1000) + 300;
                        var speed_2 = Math.floor(Math.random()*1000) + 300;
                        var speed_3 = Math.floor(Math.random()*1000) + 300;
                        var speed_4 = Math.floor(Math.random()*1000) + 300;
                        var speed_5 = Math.floor(Math.random()*1000) + 300;

                        var item_height = $('.items').height();
                        var item_animation_counter = 0;

                        this_slide.find('.explore-product').each(function(){
                            if ($(this).hasClass('col-1')){
                                $(this).animate({top:'0px'},speed_1,function(){

                                });
                            } else if ($(this).hasClass('col-2')){
                                $(this).animate({top:'0px'},speed_2,function(){

                                });
                            } else if ($(this).hasClass('col-3')){
                                $(this).animate({top:'0px'},speed_3,function(){

                                });
                            } else if ($(this).hasClass('col-4')){
                                $(this).animate({top:'0px'},speed_4,function(){

                                });
                            } else if ($(this).hasClass('col-5')){
                                $(this).animate({top:'0px'},speed_5,function(){

                                });
                            }
                        });

                        $('.item.active').find('.explore-product').each(function(){
                            
                            item_animation_counter++;

                            if ($(this).hasClass('col-1')){
                                $(this).animate({top:'-'+item_height+'px'},speed_1,function(){
                                    $(this).css('top',item_height);
                                });
                            } else if ($(this).hasClass('col-2')){
                                $(this).animate({top:'-'+item_height+'px'},speed_2,function(){
                                    $(this).css('top',item_height);
                                });
                            } else if ($(this).hasClass('col-3')){
                                $(this).animate({top:'-'+item_height+'px'},speed_3,function(){
                                    $(this).css('top',item_height);
                                });
                            } else if ($(this).hasClass('col-4')){
                                $(this).animate({top:'-'+item_height+'px'},speed_4,function(){
                                    $(this).css('top',item_height);
                                });
                            } else if ($(this).hasClass('col-5')){
                                $(this).animate({top:'-'+item_height+'px'},speed_5,function(){
                                    $(this).css('top',item_height);
                                });
                            }

                            if (item_animation_counter == 5){
                                $('.item.active').removeClass('active');
                                this_slide.addClass('active');
                                $('#explore-carousel').find('li.active').removeClass('active');
                                this_li.addClass('active');
                            }
                        });
                    }
                }

             /SLOT MACHINE SLIDE EFFECT **/

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


var BlogContent = (function () {
    return {
        setup: function () {
             $('#blogContent').on('click', function(){
                    $.fancybox({
                          'hideOnContentClick':           true,                                                           
                          'autoScale'                     : true,                                                   
                          'cyclic'                        : 'true',
                          'transitionIn'                  : 'elastic',
                          'transitionOut'                 : 'elastic',
                          helpers: { 
                                overlay: { 
                                    locked: false 
                                } 
                            },
                        autoSize: true,
                        href: '/partials/blog.phtml',
                        type: 'ajax'
                    });
                });
        }
    }

})();

var HomePageCarousel = (function () {

    return {

        setup: function () {

            $('#products .categories').find('a.category').on('click', function (event) {
                event.preventDefault();
                var cat_id = $(this).attr('rel');
                var cat_url = $(this).attr('href');
                $.ajax({
                    data: {'cat_id': cat_id},
                    url: '/productcategory/fetchcategoryproducts/',
                    type: 'post',
                    success: function (results) {

                        var cat_products = '';

                        for (var i in results) {
                            imageSmall = results[i].image_small;
                            if (imageSmall.indexOf('http', 0) == -1) {
                                imageSmall = 'https://cn.pling.com/cache/160x90-2/img/' + imageSmall;
                            }
                            cat_products += '<div class="product">' +
                                '<div class="product-wrap">' +
                                '<a href="/p/' + results[i].project_id + '">' +
                                '<img src="' + imageSmall + '"/>' +
                                '<h3>' + results[i].title + '</h3>' +
                                '</a>' +
                                '</div>' +
                                '</div>';
                        }

                        var cat_link = '<div class="more"><a class="btn btn-native btn-large " href="' + cat_url + '">show more products</a></div>';

                        $('#category-products').html('').prepend(cat_products);
                        $('#category-products').append(cat_link);

                    }
                });
            });
        }
    }

})();

/**************************
 CUSTOM & ADDONS BY DAVID
 ***************************/

/** HEADER **/

function setOS(osId) {
    $('.pickos-dropdown-element').removeClass('active');
    //$('#pickos-dropdown-element'+osId).toggleClass('active');

    var this_li = $('#pickos-dropdown-element' + osId).parent('li');
    this_li.addClass('active');

    localStorage['user_os'] = osId;

    return false;
}
$('body').click(function () {
    $('.header-pickstore-container.active').removeClass('active');
    $('.header-pickos-container.active').removeClass('active');
});

$('a#pickstore-dropdown span').click(function (event) {
    event.stopPropagation();
    element = $('a#pickstore-dropdown').parent().find('ul');
    position = element.position();
    if (position.left < 0) {
        element.css('left', 0);
    } else {
        element.css('left', '');
    }
});

$('a#pickos-dropdown span').click(function (event) {
    event.stopPropagation();
    element = $('a#pickos-dropdown').parent().find('ul');
    position = element.position();
    if (position.left < 0) {
        element.css('left', 0);
    } else {
        element.css('left', '');
    }
});

$('a.pickos-dropdown-element').click(function (event) {
    event.stopPropagation();
    //os_id = event.target;
    //alert("Do something. OS-ID = " + os_id);
});

$(document).mouseup(function (e) {

    //PickStore
    var container = $('a#pickstore-dropdown').parent().find('ul');
    var trigger = $('a#pickstore-dropdown span');

    if (trigger.is(e.target)) {
        return;
    }

    if (!container.is(e.target) // if the target of the click isn't the container...
        && container.has(e.target).length === 0) // ... nor a descendant of the container
    {
        container.css('left', '');
    }

    //PickOS
    container = $('a#pickos-dropdown').parent().find('ul');
    trigger = $('a#pickos-dropdown span');

    if (trigger.is(e.target)) {
        return;
    }

    if (!container.is(e.target) // if the target of the click isn't the container...
        && container.has(e.target).length === 0) // ... nor a descendant of the container
    {
        container.css('left', '');
    }

});


/** /HEADER **/

/** PRODUCT PAGE **/

    // embed code expend collapse

$('body').on("click", '.embed-code .sidebar-header', function (event) {

    var thisIsActive = $('.embed-code').find('.panel-collapse').hasClass('in');

    if (thisIsActive == true) {
        $(this).find('.expand').removeClass('active');
    }
    else {
        $(this).find('.expand').addClass('active');
    }
});

// report form custom select function

$('body').on('click', '.selectbox_select', function () {
    $('.report-form').find('.selectbox_menu').show();
});

$('body').on('click', '.selectbox_menu a', function () {
    var thisDataValue = $(this).attr('data-option');
    var thisTitle = $(this).attr('title');
    $('.report-form').find('#project_report_category_id').attr('value', thisDataValue).attr('label', thisTitle);
    $('.report-form').find('.selectbox_selected').text(thisTitle);
    $('.report-form').find('.selectbox_menu').hide();
});

// tool tips

$('body').on('mouseenter', '.product-thumbnail, .supporter-thumbnail', function () {
    $(this).popover('show');
});

$('body').on('mouseleave', '.product-thumbnail, .supporter-thumbnail', function () {
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
                                var redirect = data.data;
                                if (redirect) {
                                    var urlParam = '?redirect=' + redirect;
                                }
                            }
                            window.location = "/login" + urlParam;
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

var PartialForms = (function () {
    return {
        setup: function () {
            $('body').on("submit", 'form.partial', function (event) {
                event.preventDefault();
                event.stopImmediatePropagation();
                var target = $(this).attr("data-target");
                var trigger = $(this).attr("data-trigger");

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
                            window.location.href = data.redirect;
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

var PartialFormsAjax = (function () {
    return {
        setup: function () {
            var form = $('form.partialajax');
            var target = form.attr("data-target");
            var trigger = form.attr("data-trigger");

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

//                $(this).ajaxForm({
                jQuery.ajax({
                    data: $(this).serialize(),
                    url: this.action,
                    type: this.method,
                    dataType: "json",

                    error: function () {
                        alert('Service is temporarily unavailable.');
                    },
                    success: function (results) {
                        if (results.status == 'ok') {
                            $(target).empty().html(results.data);
                        }
                        if (results.status == 'error') {
                            alert('Service is temporarily unavailable.');
                        }
                    }
                });

                return false;
            });
            // form.ajaxForm({
            //     error: function () {
            //         alert('Service is temporarily unavailable.');
            //     },
            //     success: function (results) {
            //         if (results.status == 'ok') {
            //             $(target).empty().html(results.data);
            //         }
            //         if (results.status == 'error') {
            //             alert('Service is temporarily unavailable.');
            //         }
            //     }
            // });
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