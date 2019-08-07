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
/**
 * backend navigation with yui-lib
 */
var aItemData = [

    {
        text: "General",
        submenu: {
            id: "allabout",
            itemdata: [
                {text: "Dashboard", url: "/backend/"},
                {text: "File-Browser", url: "/backend/index/filebrowser"}
            ]
        }
    },
    {
        text: "Product",
        submenu: {
            id: "projects",
            itemdata: [
                {text: "Products", url: "/backend/project"},
                {text: "Clones", url: "/backend/projectclone"},
                {text: "Claims", url: "/backend/claim"}
            ]
        }
    },
    {
        text: "GHNS",
        submenu: {
            id: "ghns",
            itemdata: [
                {text: "GHNS-Excluded", url: "/backend/ghnsexcluded"}
            ]
        }
    },
    {
        text: "Category",
        submenu: {
            id: "category",
            itemdata: [
                {text: "Categories", url: "/backend/categories"},
                {text: "Browse-List-Types", url: "/backend/browselisttype"},
                {text: "Virtual Categories", url: "/backend/vcategories"},
                {text: "Category-Tag", url: "/backend/categorytag"},
                {text: "Category-Tag-Group", url: "/backend/categorytaggroup"},
                
            ]
        }
    },
    {
        text: "User",
        submenu: {
            id: "user",
            itemdata: [
                {text: "Users", url: "/backend/user"},
                {text: "Config Paypal-Valid-Stati", url: "/backend/paypalvalidstatus"},
                {text: "Generate Letter Avatar", url: "/backend/letteravatar"}
            ]
        }
    },
    {
        text: "Payout",
        submenu: {
            id: "payout",
            itemdata: [
                {text: "Member Payouts", url: "/backend/memberpayout"},
                {text: "Config Payout-Stati", url: "/backend/payoutstatus"},
                {text: "Member Paypal-Addresses", url: "/backend/memberpaypaladdress"}
            ]
        }
    },
    {
        text: "Comment",
        submenu: {
            id: "comments",
            itemdata: [
                {text: "Comments", url: "/backend/comments"}
            ]
        }
    },
    {
        text: "Content",
        submenu: {
            id: "contents",
            itemdata: [
                {text: "eMail-Templates", url: "/backend/mail"}
            ]
        }
    },
    {
        text: "Reported",
        submenu: {
            id: "reports",
            itemdata: [
                {text: "Comments", url: "/backend/reportcomments"},
                {text: "Products", url: "/backend/reportproducts"}
            ]
        }
    },
    {
        text: "Tags",
        submenu: {
            id: "tags",
            itemdata: [
                {text: "Manage", url: "/backend/tags"}
            ]
        }
    },
    {
        text: "Store",
        submenu: {
            id: "stores",
            itemdata: [
                {text: "Config", url: "/backend/store"},
                {text: "Categories", url: "/backend/storecategories"},
                {text: "Virtual Categories", url: "/backend/vstorecategories"},
                {text: "Init Cache", url: "/backend/store/initcache"}
            ]
        }
    },
    { 
        text: "Section",
        submenu: {
            id: "section",
            itemdata: [
                {text: "Sections", url: "/backend/section"},
                {text: "Categories", url: "/backend/sectioncategories"},
                {text: "Sponsors", url: "/backend/sponsor"}
            ]
        }
    },
    {
        text: "Operating System",
        submenu: {
            id: "operatingsystem",
            itemdata: [
                {text: "Config", url: "/backend/operatingsystem"}
            ]
        }
    },
    {
        text: "Import",
        submenu: {
            id: "import",
            itemdata: [
                {text: "Hive Content", url: "/backend/hive"},
                {text: "Hive User", url: "/backend/hiveuser"}
            ]
        }
    },
    {
        text: "Spam",
        submenu: {
            id: "spam",
            itemdata: [
                {text: "Keywords", url: "/backend/spamkeywords"}
            ]
        }
    },
    {
        text: "Account",
        submenu: {
            id: "account",
            itemdata: [
                {text: "logout", url: "/logout"},
                {text: "frontend", url: "/"}
            ]
        }
    }


];


$(document).ready(function () {

    $("body").addClass("yui-skin-sam");

    var oMenuBar = new YAHOO.widget.MenuBar("ocsbackendnavigation", {
        lazyload: true,
        itemdata: aItemData
    });

    oMenuBar.render(document.body);

    // Add a "show" event listener for each submenu.

    function onSubmenuShow() {

        var oIFrame,
            oElement,
            nOffsetWidth;


        // Keep the left-most submenu against the left edge of the browser viewport

        if (this.id == "allgemein") {

            YAHOO.util.Dom.setX(this.element, 0);

            oIFrame = this.iframe;


            if (oIFrame) {

                YAHOO.util.Dom.setX(oIFrame, 0);

            }

            this.cfg.setProperty("x", 0, true);

        }


        /*
         Need to set the width for submenus of submenus in IE to prevent the mouseout
         event from firing prematurely when the user mouses off of a MenuItem's
         text node.
         */

        if ((this.id == "filemenu" || this.id == "editmenu") && YAHOO.env.ua.ie) {

            oElement = this.element;
            nOffsetWidth = oElement.offsetWidth;

            /*
             Measuring the difference of the offsetWidth before and after
             setting the "width" style attribute allows us to compute the
             about of padding and borders applied to the element, which in
             turn allows us to set the "width" property correctly.
             */

            oElement.style.width = nOffsetWidth + "px";
            oElement.style.width = (nOffsetWidth - (oElement.offsetWidth - nOffsetWidth)) + "px";

        }

    }


    // Subscribe to the "show" event for each submenu

    oMenuBar.subscribe("show", onSubmenuShow);
});
