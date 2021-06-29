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

var OcsStorage = {
    set: function (key, value) {
        localStorage[key] = JSON.stringify(value);
    },
    get: function (key) {
        return localStorage[key] ? JSON.parse(localStorage[key]) : null;
    }
};

var OcsStats = {
    props: {},
    a: 0,
    readIp: function (apiv4, apiv6) {
        var xhr1 = $.getJSON(apiv4, function (data) {
            OcsStats.props.ipv4 = data.ip;
            OcsStats.saveProps(1);
        });
        xhr1.fail(function () {
            OcsStats.saveProps(1);
        });
        var xhr2 = $.getJSON(apiv6, function (data) {
            OcsStats.props.ipv6 = data.ip;
            OcsStats.saveProps(2);
        });
        xhr2.fail(function () {
            OcsStats.saveProps(2);
        });
    },
    genId: function () {
        var options = {};
        Fingerprint2.get(options, function (components) {
            var values = components.map(function (component) {
                return component.value
            });
            OcsStats.props.fp = Fingerprint2.x64hash128(values.join(''), 31);
            OcsStats.saveProps(4);
        })
    },
    saveProps: function (c) {
        OcsStorage.set('ocs', OcsStats.props);
        OcsStats.a += c;
        if (OcsStats.a == 7) {
            OcsStats.postProps();
        }
    },
    postProps: function () {
        $.post("/l/fp", OcsStats.props);
    },
    readStats: function (apiv4, apiv6) {
        if (window.requestIdleCallback) {
            requestIdleCallback(function () {
                OcsStats.genId();
                OcsStats.readIp(apiv4, apiv6);
            })
        } else {
            setTimeout(function () {
                OcsStats.genId();
                OcsStats.readIp(apiv4, apiv6);
            }, 500)
        }
    }
};