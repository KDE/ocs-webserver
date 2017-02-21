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
                $(this).find('button').attr("disabled", "disabled");

//                $(this).ajaxForm({
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