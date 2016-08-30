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
/******************+
 HOME PAGE THUMBS
 ***********************/

$(window).load(function () {
    //$('main').find('#loading').remove();
    //$('#thumbs').show();
});

$(document).ready(function () {

    // QUICK REG

    $('#quick-reg').find('button.btn').on('click', function () {

        var cur_active_tab = $('#quick-reg').find('.tab-pane.active');
        var cur_active_li = $('#quick-reg').find('li.active');

        var this_target = $(this).attr('rel');
        var this_target_tab = $('#quick-reg').find('.tab-pane#' + this_target);
        var this_target_li = $('#quick-reg').find('li.' + this_target);

        cur_active_tab.removeClass('active');
        cur_active_li.removeClass('active');

        this_target_tab.addClass('active');
        this_target_li.addClass('active');

        if ($(this).attr('id') == 'next-step-one') {

        }

    });

    // THUMBS

    var thumbNumber = 0;

    $('.thumb').each(function () {
        thumbNumber++;
        $(this).attr('rel', thumbNumber);
    });

    $('.thumb').hover(function (event) {

        if ($(window).width() <= 992 && $(window).width() >= 768) {
            var variable = 7;
        } else if ($(window).width() <= 768 && $(window).width() >= 420) {
            var variable = 5;
        } else if ($(window).width() <= 420) {
            var variable = 3;
        } else {
            var variable = 9;
        }

        var this_thumb = $(this);
        this_thumb.addClass('hoverd');
        thumb_mouseover(this_thumb, variable);

    }, function () {

        if ($(window).width() <= 992 && $(window).width() >= 768) {
            var variable = 7;
        } else if ($(window).width() <= 768 && $(window).width() >= 420) {
            var variable = 5;
        } else if ($(window).width() <= 420) {
            var variable = 3;
        } else {
            var variable = 9;
        }

        var this_thumb = $(this);
        thumb_mouseout(this_thumb, variable);
    });

    $('.thumb').on('click', function () {
        var this_thumb = $(this);
        this_thumb.addClass('hoverd');
        thumb_mouseover(this_thumb);
    });

});

/**
 MOUSE OVER
 **/

/* main thumb */

function thumb_mouseover(this_thumb, variable) {

    var this_rel = this_thumb.attr('rel');
    var this_rel = parseInt(this_rel);
    var this_top_rel = this_rel - variable;
    var this_bottom_rel = this_rel + variable;
    var this_position = this_thumb.position().left;
    var this_offset = $(window).width() - this_position - this_thumb.width();

    var this_subhover_class = 'half-hoverd';

    var t_to = $('.thumb[rel="' + this_top_rel + '"]');
    var t_bo = $('.thumb[rel="' + this_bottom_rel + '"]');

    t_to.addClass(this_subhover_class);
    t_bo.addClass(this_subhover_class);

    /* thumbs on the left */

    if (this_position > 0) {

        var t_le = this_thumb.prev('.thumb');
        var t_tl = t_to.prev('.thumb');
        var t_bl = t_bo.prev('.thumb');

        t_le.addClass(this_subhover_class);
        t_tl.addClass(this_subhover_class);
        t_bl.addClass(this_subhover_class);

        if (t_to.size() > 0) {
            t_tl.addClass('corner');
        }

        if (t_bo.size() > 0) {
            t_bl.addClass('corner');
        }

    }

    /* thumbs on the right */

    if (this_offset > 1) {

        var t_ri = this_thumb.next('.thumb');
        var t_tr = t_to.next('.thumb');
        var t_br = t_bo.next('.thumb');

        t_ri.addClass(this_subhover_class);
        t_tr.addClass(this_subhover_class);
        t_br.addClass(this_subhover_class);

        if (t_to.size() > 0) {
            t_tr.addClass('corner');
        }

        if (t_bo.size() > 0) {
            t_br.addClass('corner');
        }
    }

    /* if this thumbs is the main hoverd thumb, animate the corner thumbs */

    if (this_thumb.hasClass('hoverd')) {

        $('.thumb.corner').each(function () {
            var this_thumb = $(this);
            corner_thumb_mouseover(this_thumb, variable);
        });
    }
}

/* corner thumbs */

function corner_thumb_mouseover(this_thumb, variable) {
    var this_rel = this_thumb.attr('rel');
    var this_rel = parseInt(this_rel);
    var this_top_rel = this_rel - variable;
    var this_bottom_rel = this_rel + variable;
    var this_position = this_thumb.position().left;
    var this_offset = $(window).width() - this_position - this_thumb.width();
    var this_subhover_class = 'quarter-hoverd';
    var t_to = $('.thumb[rel="' + this_top_rel + '"]');
    var t_bo = $('.thumb[rel="' + this_bottom_rel + '"]');
    t_to.addClass(this_subhover_class);
    t_bo.addClass(this_subhover_class);
    /* thumbs on the left */
    if (this_position > 0) {
        var t_le = this_thumb.prev('.thumb');
        var t_tl = t_to.prev('.thumb');
        var t_bl = t_bo.prev('.thumb');
        t_le.addClass(this_subhover_class);
        t_tl.addClass(this_subhover_class);
        t_bl.addClass(this_subhover_class);
    }
    /* thumbs on the right */
    if (this_offset > 1) {
        var t_ri = this_thumb.next('.thumb');
        var t_tr = t_to.next('.thumb');
        var t_br = t_bo.next('.thumb');
        t_ri.addClass(this_subhover_class);
        t_tr.addClass(this_subhover_class);
        t_br.addClass(this_subhover_class);
    }
}


/**
 MOUSE OUT
 **/

/* main thumb */

function thumb_mouseout(this_thumb, variable) {

    var this_rel = this_thumb.attr('rel');
    var this_rel = parseInt(this_rel);

    var t_le = this_thumb.prev('.thumb');
    var t_ri = this_thumb.next('.thumb');
    var t_to = $('.thumb[rel="' + (this_rel - variable) + '"]');
    var t_tl = t_to.prev('.thumb');
    var t_tr = t_to.next('.thumb');
    var t_bo = $('.thumb[rel="' + (this_rel + variable) + '"]');
    var t_bl = t_bo.prev('.thumb');
    var t_br = t_bo.next('.thumb');

    if (this_thumb.hasClass('hoverd')) {
        $('.thumb.corner').each(function () {
            var this_thumb = $(this);
            corner_thumb_mouseout(this_thumb, variable);
        });
    }

    this_thumb.removeClass('hoverd');
    t_le.removeClass('half-hoverd');
    t_ri.removeClass('half-hoverd');
    t_to.removeClass('half-hoverd');
    t_tl.removeClass('half-hoverd');
    t_tr.removeClass('half-hoverd');
    t_bo.removeClass('half-hoverd');
    t_bl.removeClass('half-hoverd');
    t_br.removeClass('half-hoverd');
}

/* corner thumbs */

function corner_thumb_mouseout(this_thumb, variable) {
    var this_rel = this_thumb.attr('rel');
    var this_rel = parseInt(this_rel);
    var this_top_rel = this_rel - variable;
    var this_bottom_rel = this_rel + variable;
    var this_position = this_thumb.position().left;
    var this_offset = $(window).width() - this_position - this_thumb.width();
    var this_subhover_class = 'quarter-hoverd';
    /* top and bottom */
    var t_to = $('.thumb[rel="' + this_top_rel + '"]');
    var t_bo = $('.thumb[rel="' + this_bottom_rel + '"]');
    t_to.removeClass(this_subhover_class);
    t_bo.removeClass(this_subhover_class);
    /* thumbs on the left */
    var t_le = this_thumb.prev('.thumb');
    var t_tl = t_to.prev('.thumb');
    var t_bl = t_bo.prev('.thumb');
    t_le.removeClass(this_subhover_class);
    t_tl.removeClass(this_subhover_class);
    t_bl.removeClass(this_subhover_class);
    /* thumbs on the right */
    var t_ri = this_thumb.next('.thumb');
    var t_tr = t_to.next('.thumb');
    var t_br = t_bo.next('.thumb');
    t_ri.removeClass(this_subhover_class);
    t_tr.removeClass(this_subhover_class);
    t_br.removeClass(this_subhover_class);

    this_thumb.removeClass('corner');
}
