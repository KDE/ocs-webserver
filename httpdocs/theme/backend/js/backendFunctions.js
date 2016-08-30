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
// Menu initialisieren

    var activeNavId = $.cookie('activeMenuPoint');

    $("#navigation").accordion({
        navigation: true,
        autoHeight: false,
        collapsible: true,
        active: (activeNavId != "") ? '#' + activeNavId : false
    });

//Hover-Fkt für Icon-Buttons
    $(".alexIcon").hover(
        function () {
            $(this).addClass("ui-state-hover");
        },
        function () {
            $(this).removeClass("ui-state-hover");
        }
    );

// Hover-Fkt für Buttons	
    $(".button_normal").hover(
        function () {
            $(this).addClass("ui-state-hover");
        },
        function () {
            $(this).removeClass("ui-state-hover");
        }
    );

// Formelemente als notwendig markieren
    $(".required").append("&nbsp;*");

// Navigation Backend
    $("#navigation li a").click(function () {
        var headerElement = $(this).parent().parent().prev();
        var activeElement = $("#navigation").accordion('option', 'active');
        $.cookie('activeMenuPoint', headerElement.attr("id"), {path: '/'});
    });
});