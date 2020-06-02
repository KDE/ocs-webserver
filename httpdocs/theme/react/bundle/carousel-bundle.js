!function(e){var t={};function r(n){if(t[n])return t[n].exports;var o=t[n]={i:n,l:!1,exports:{}};return e[n].call(o.exports,o,o.exports,r),o.l=!0,o.exports}r.m=e,r.c=t,r.d=function(e,t,n){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(r.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)r.d(n,o,function(t){return e[t]}.bind(null,o));return n},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="",r(r.s=12)}([,function(e,t,r){var n=r(2);e.exports={locale:n.locale,long:n.long,short:n.short,narrow:n.narrow,"short-time":r(7),"short-convenient":r(8),"long-time":r(9),"long-convenient":r(10),tiny:r(11),quantify:n.quantify}},function(e,t,r){e.exports={locale:"en",long:r(3),short:r(4),narrow:r(5),quantify:r(6)}},function(e){e.exports=JSON.parse('{"year":{"previous":"last year","current":"this year","next":"next year","past":{"one":"{0} year ago","other":"{0} years ago"},"future":{"one":"in {0} year","other":"in {0} years"}},"quarter":{"previous":"last quarter","current":"this quarter","next":"next quarter","past":{"one":"{0} quarter ago","other":"{0} quarters ago"},"future":{"one":"in {0} quarter","other":"in {0} quarters"}},"month":{"previous":"last month","current":"this month","next":"next month","past":{"one":"{0} month ago","other":"{0} months ago"},"future":{"one":"in {0} month","other":"in {0} months"}},"week":{"previous":"last week","current":"this week","next":"next week","past":{"one":"{0} week ago","other":"{0} weeks ago"},"future":{"one":"in {0} week","other":"in {0} weeks"}},"day":{"previous":"yesterday","current":"today","next":"tomorrow","past":{"one":"{0} day ago","other":"{0} days ago"},"future":{"one":"in {0} day","other":"in {0} days"}},"hour":{"current":"this hour","past":{"one":"{0} hour ago","other":"{0} hours ago"},"future":{"one":"in {0} hour","other":"in {0} hours"}},"minute":{"current":"this minute","past":{"one":"{0} minute ago","other":"{0} minutes ago"},"future":{"one":"in {0} minute","other":"in {0} minutes"}},"second":{"current":"now","past":{"one":"{0} second ago","other":"{0} seconds ago"},"future":{"one":"in {0} second","other":"in {0} seconds"}}}')},function(e){e.exports=JSON.parse('{"year":{"previous":"last yr.","current":"this yr.","next":"next yr.","past":"{0} yr. ago","future":"in {0} yr."},"quarter":{"previous":"last qtr.","current":"this qtr.","next":"next qtr.","past":{"one":"{0} qtr. ago","other":"{0} qtrs. ago"},"future":{"one":"in {0} qtr.","other":"in {0} qtrs."}},"month":{"previous":"last mo.","current":"this mo.","next":"next mo.","past":"{0} mo. ago","future":"in {0} mo."},"week":{"previous":"last wk.","current":"this wk.","next":"next wk.","past":"{0} wk. ago","future":"in {0} wk."},"day":{"previous":"yesterday","current":"today","next":"tomorrow","past":{"one":"{0} day ago","other":"{0} days ago"},"future":{"one":"in {0} day","other":"in {0} days"}},"hour":{"current":"this hour","past":"{0} hr. ago","future":"in {0} hr."},"minute":{"current":"this minute","past":"{0} min. ago","future":"in {0} min."},"second":{"current":"now","past":"{0} sec. ago","future":"in {0} sec."}}')},function(e){e.exports=JSON.parse('{"year":{"previous":"last yr.","current":"this yr.","next":"next yr.","past":"{0} yr. ago","future":"in {0} yr."},"quarter":{"previous":"last qtr.","current":"this qtr.","next":"next qtr.","past":{"one":"{0} qtr. ago","other":"{0} qtrs. ago"},"future":{"one":"in {0} qtr.","other":"in {0} qtrs."}},"month":{"previous":"last mo.","current":"this mo.","next":"next mo.","past":"{0} mo. ago","future":"in {0} mo."},"week":{"previous":"last wk.","current":"this wk.","next":"next wk.","past":"{0} wk. ago","future":"in {0} wk."},"day":{"previous":"yesterday","current":"today","next":"tomorrow","past":{"one":"{0} day ago","other":"{0} days ago"},"future":{"one":"in {0} day","other":"in {0} days"}},"hour":{"current":"this hour","past":"{0} hr. ago","future":"in {0} hr."},"minute":{"current":"this minute","past":"{0} min. ago","future":"in {0} min."},"second":{"current":"now","past":"{0} sec. ago","future":"in {0} sec."}}')},function(e,t){e.exports=function(e){var t=!String(e).split(".")[1];return 1==e&&t?"one":"other"}},function(e){e.exports=JSON.parse('{"year":"{0} yr.","month":"{0} mo.","week":"{0} wk.","day":{"one":"{0} day","other":"{0} days"},"hour":"{0} hr.","minute":"{0} min.","second":"{0} sec.","now":"now"}')},function(e){e.exports=JSON.parse('{"year":{"previous":"last yr.","current":"this yr.","next":"next yr.","past":"{0} yr. ago","future":"in {0} yr."},"quarter":{"previous":"last qtr.","current":"this qtr.","next":"next qtr.","past":{"one":"{0} qtr. ago","other":"{0} qtrs. ago"},"future":{"one":"in {0} qtr.","other":"in {0} qtrs."}},"month":{"previous":"last mo.","current":"this mo.","next":"next mo.","past":"{0} mo. ago","future":"in {0} mo."},"week":{"previous":"last wk.","current":"this wk.","next":"next wk.","past":"{0} wk. ago","future":"in {0} wk."},"day":{"previous":"yesterday","current":"today","next":"tomorrow","past":{"one":"{0} day ago","other":"{0} days ago"},"future":{"one":"in {0} day","other":"in {0} days"}},"hour":{"current":"this hour","past":"{0} hr. ago","future":"in {0} hr."},"minute":{"current":"this minute","past":"{0} min. ago","future":"in {0} min."},"second":{"current":"now","past":"{0} sec. ago","future":"in {0} sec."},"now":{"future":"in a moment","past":"just now"}}')},function(e){e.exports=JSON.parse('{"year":{"one":"{0} year","other":"{0} years"},"month":{"one":"{0} month","other":"{0} months"},"week":{"one":"{0} week","other":"{0} weeks"},"day":{"one":"{0} day","other":"{0} days"},"hour":{"one":"{0} hour","other":"{0} hours"},"minute":{"one":"{0} minute","other":"{0} minutes"},"second":{"one":"{0} second","other":"{0} seconds"},"now":{"future":"in a moment","past":"just now"}}')},function(e){e.exports=JSON.parse('{"year":{"previous":"last year","current":"this year","next":"next year","past":{"one":"a year ago","other":"{0} years ago"},"future":{"one":"in a year","other":"in {0} years"}},"quarter":{"previous":"last quarter","current":"this quarter","next":"next quarter","past":{"one":"a quarter ago","other":"{0} quarters ago"},"future":{"one":"in a quarter","other":"in {0} quarters"}},"month":{"previous":"last month","current":"this month","next":"next month","past":{"one":"a month ago","other":"{0} months ago"},"future":{"one":"in a month","other":"in {0} months"}},"week":{"previous":"last week","current":"this week","next":"next week","past":{"one":"a week ago","other":"{0} weeks ago"},"future":{"one":"in a week","other":"in {0} weeks"}},"day":{"previous":"yesterday","current":"today","next":"tomorrow","past":{"one":"a day ago","other":"{0} days ago"},"future":{"one":"in a day","other":"in {0} days"}},"hour":{"current":"this hour","past":{"one":"an hour ago","other":"{0} hours ago"},"future":{"one":"in an hour","other":"in {0} hours"}},"minute":{"current":"this minute","past":{"one":"a minute ago","other":"{0} minutes ago"},"future":{"one":"in a minute","other":"in {0} minutes"}},"second":{"current":"now","past":{"one":"a second ago","other":"{0} seconds ago"},"future":{"one":"in a second","other":"in {0} seconds"}},"now":{"future":"in a moment","past":"just now"}}')},function(e){e.exports=JSON.parse('{"year":"{0}yr","month":"{0}mo","week":"{0}wk","day":"{0}d","hour":"{0}h","minute":"{0}m","second":"{0}s","now":"now"}')},function(e,t,r){"use strict";r.r(t);var n="en",o={};function a(){return n}function i(e){return o[e]}function u(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r=t.localeMatcher||"lookup";switch(r){case"lookup":case"best fit":return c(e);default:throw new RangeError('Invalid "localeMatcher" option: '.concat(r))}}function c(e){if(i(e))return e;for(var t=e.split("-");e.length>1;)if(t.pop(),i(e=t.join("-")))return e}function s(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function l(e,t){for(var r=0;r<t.length;r++){var n=t[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function f(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}var h=["second","minute","hour","day","week","month","quarter","year"],p=["auto","always"],d=["long","short","narrow"],m=function(){function e(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],r=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};s(this,e),f(this,"numeric","always"),f(this,"style","long"),f(this,"localeMatcher","lookup");var n=r.numeric,o=r.style,i=r.localeMatcher;if(n){if(p.indexOf(n)<0)throw new RangeError('Invalid "numeric" option: '.concat(n));this.numeric=n}if(o){if(d.indexOf(o)<0)throw new RangeError('Invalid "style" option: '.concat(o));this.style=o}if(i&&(this.localeMatcher=i),"string"==typeof t&&(t=[t]),t.push(a()),this.locale=e.supportedLocalesOf(t,{localeMatcher:this.localeMatcher})[0],!this.locale)throw new TypeError("No supported locale was found");this.locale=u(this.locale,{localeMatcher:this.localeMatcher}),"undefined"!=typeof Intl&&Intl.NumberFormat&&(this.numberFormat=new Intl.NumberFormat(this.locale))}var t,r,n;return t=e,(r=[{key:"format",value:function(e,t){return this.getRule(e,t).replace("{0}",this.formatNumber(Math.abs(e)))}},{key:"formatToParts",value:function(e,t){var r=this.getRule(e,t),n=r.indexOf("{0}");if(n<0)return[{type:"literal",value:r}];var o=[];return n>0&&o.push({type:"literal",value:r.slice(0,n)}),o.push({unit:t,type:"integer",value:this.formatNumber(Math.abs(e))}),n+"{0}".length<r.length-1&&o.push({type:"literal",value:r.slice(n+"{0}".length)}),o}},{key:"getRule",value:function(e,t){if(h.indexOf(t)<0)throw new RangeError("Unknown time unit: ".concat(t,"."));var r=i(this.locale)[this.style][t];if("auto"===this.numeric)if(-2===e||-1===e){var n=r["previous".concat(-1===e?"":"-"+Math.abs(e))];if(n)return n}else if(1===e||2===e){var o=r["next".concat(1===e?"":"-"+Math.abs(e))];if(o)return o}else if(0===e&&r.current)return r.current;var a=r[e<=0?"past":"future"];if("string"==typeof a)return a;var u=i(this.locale).quantify,c=u&&u(Math.abs(e));return a[c=c||"other"]||a.other}},{key:"formatNumber",value:function(e){return this.numberFormat?this.numberFormat.format(e):String(e)}},{key:"resolvedOptions",value:function(){return{locale:this.locale,style:this.style,numeric:this.numeric}}}])&&l(t.prototype,r),n&&l(t,n),e}();function y(e){return(y="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function g(e,t){for(var r=0;r<t.length;r++){var n=t[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}m.supportedLocalesOf=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};return"string"==typeof e&&(e=[e]),e.filter((function(e){return u(e,t)}))},m.addLocale=function(e){if(!e)throw new Error("No locale data passed");o[e.locale]=e},m.setDefaultLocale=function(e){n=e},m.getDefaultLocale=a;var v=function(){function e(){var t,r,n;!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e),n={},(r="cache")in(t=this)?Object.defineProperty(t,r,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[r]=n}var t,r,n;return t=e,(r=[{key:"get",value:function(){for(var e=this.cache,t=arguments.length,r=new Array(t),n=0;n<t;n++)r[n]=arguments[n];for(var o=0;o<r.length;o++){var a=r[o];if("object"!==y(e))return;e=e[a]}return e}},{key:"put",value:function(){for(var e=arguments.length,t=new Array(e),r=0;r<e;r++)t[r]=arguments[r];for(var n=t.pop(),o=t.pop(),a=this.cache,i=0;i<t.length;i++){var u=t[i];"object"!==y(a[u])&&(a[u]={}),a=a[u]}return a[o]=n}}])&&g(t.prototype,r),n&&g(t,n),e}(),w=86400;function b(e,t){var r=e,n=Array.isArray(r),o=0;for(r=n?r:r[Symbol.iterator]();;){var a;if(n){if(o>=r.length)break;a=r[o++]}else{if((o=r.next()).done)break;a=o.value}var i=a;if(i.unit===t)return i}}function k(e){return e instanceof Date?e:new Date(e)}var x=[{factor:1,unit:"now"},{threshold:1,threshold_for_now:45,factor:1,unit:"second"},{threshold:45,factor:60,unit:"minute"},{threshold:150,factor:60,granularity:5,unit:"minute"},{threshold:1350,factor:1800,unit:"half-hour"},{threshold:2550,threshold_for_minute:3150,factor:3600,unit:"hour"},{threshold:73800,factor:w,unit:"day"},{threshold:475200,factor:7*w,unit:"week"},{threshold:2116800,factor:2630016,unit:"month"},{threshold:27615168,factor:31556952,unit:"year"}];function R(e){return(R="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function E(e,t,r){var n=arguments.length>3&&void 0!==arguments[3]?arguments[3]:x;if(0!==(n=O(n,r)).length){var o=N(e,t,n),a=n[o];if(-1!==o){if(a.granularity){var i=Math.round(Math.abs(e)/a.factor/a.granularity)*a.granularity;if(0===i&&o>0)return n[o-1]}return a}}}function S(e,t,r,n){var o;if(e&&(e.id||e.unit)&&(o=t["threshold_for_".concat(e.id||e.unit)]),void 0===o&&(o=t.threshold),"function"==typeof o&&(o=o(r,n)),e&&"number"!=typeof o){var a=R(o);throw new Error('Each step of a gradation must have a threshold defined except for the first one. Got "'.concat(o,'", ').concat(a,". Step: ").concat(JSON.stringify(t)))}return o}function N(e,t,r){var n=arguments.length>3&&void 0!==arguments[3]?arguments[3]:0;return Math.abs(e)<S(r[n-1],r[n],t,e<0)?n-1:n===r.length-1?n:N(e,t,r,n+1)}function O(e,t){return e.filter((function(e){var r=e.unit;return!r||t.indexOf(r)>=0}))}function q(e){return(q="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function j(e,t){var r=e,n=Array.isArray(r),o=0;for(r=n?r:r[Symbol.iterator]();;){var a;if(n){if(o>=r.length)break;a=r[o++]}else{if((o=r.next()).done)break;a=o.value}var i=a;if(t(i))return i;for(var u=i.split("-");u.length>1;)if(u.pop(),t(i=u.join("-")))return i}throw new Error("No locale data has been registered for any of the locales: ".concat(e.join(", ")))}function M(){return"object"===("undefined"==typeof Intl?"undefined":q(Intl))&&"function"==typeof Intl.DateTimeFormat}var D={gradation:x,flavour:["long-convenient","long"],units:["now","minute","hour","day","week","month","year"]},_=[{factor:1,unit:"now"},{threshold:.5,factor:1,unit:"second"},{threshold:59.5,factor:60,unit:"minute"},{threshold:3570,factor:3600,unit:"hour"},{threshold:84600,factor:w,unit:"day"},{threshold:561600,factor:7*w,unit:"week"},{threshold:2116800,factor:2630016,unit:"month"},{threshold:30245184,factor:31556952,unit:"year"}];function I(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}var L={},F={gradation:[function(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{},n=Object.keys(r);"function"==typeof Object.getOwnPropertySymbols&&(n=n.concat(Object.getOwnPropertySymbols(r).filter((function(e){return Object.getOwnPropertyDescriptor(r,e).enumerable})))),n.forEach((function(t){I(e,t,r[t])}))}return e}({},b(_,"minute"),{threshold:45}),b(_,"hour"),{threshold:84600,format:function(e,t){if(M())return L[t]||(L[t]={}),L[t].this_year||(L[t].this_year=new Intl.DateTimeFormat(t,{month:"short",day:"numeric"})),L[t].this_year.format(k(e))}},{threshold:function(e,t){return t?(new Date(new Date(e).getFullYear()+1,0).getTime()-e)/1e3:(e-new Date(new Date(e).getFullYear(),0).getTime())/1e3},format:function(e,t){if(M())return L[t]||(L[t]={}),L[t].other||(L[t].other=new Intl.DateTimeFormat(t,{year:"numeric",month:"short",day:"numeric"})),L[t].other.format(k(e))}}],flavour:["tiny","short-time","narrow","short"]},P={gradation:x,flavour:"long-time",units:["now","minute","hour","day","week","month","year"]},T={};function A(e){return T[e]}function J(e){return(J="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function C(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function W(e,t){for(var r=0;r<t.length;r++){var n=t[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}var U=function(){function e(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[];C(this,e),"string"==typeof t&&(t=[t]),this.locale=j(t.concat(m.getDefaultLocale()),A),"undefined"!=typeof Intl&&Intl.NumberFormat&&(this.numberFormat=new Intl.NumberFormat(this.locale)),this.relativeTimeFormatCache=new v}var t,r,n;return t=e,(r=[{key:"format",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:D;if("string"==typeof t)switch(t){case"twitter":t=F;break;case"time":t=P;break;default:t=D}var r=V(e),n=r.date,o=r.time,a=this.getLocaleData(t.flavour),i=a.flavour,u=a.localeData,c=t.now||Date.now(),s=(c-o)/1e3;if(t.custom){var l=t.custom({now:c,date:n,time:o,elapsed:s,locale:this.locale});if(void 0!==l)return l}var f=z(u,t.units);if(0===f.length)return console.error('Units "'.concat(f.join(", "),'" were not found in locale data for "').concat(this.locale,'".')),"";var h=E(s,c,f,t.gradation);if(!h)return"";if(h.format)return h.format(n||o,this.locale);var p=h.unit,d=h.factor,m=h.granularity,y=Math.abs(s)/d;if(m&&(y=Math.round(y/m)*m),"now"===p)return Y(u,-1*Math.sign(s));switch(i){case"long":case"short":case"narrow":return this.getFormatter(i).format(-1*Math.sign(s)*Math.round(y),p);default:return this.formatValue(-1*Math.sign(s)*Math.round(y),p,u)}}},{key:"formatValue",value:function(e,t,r){return this.getRule(e,t,r).replace("{0}",this.formatNumber(Math.abs(e)))}},{key:"getRule",value:function(e,t,r){var n=r[t];if("string"==typeof n)return n;var o=n[e<=0?"past":"future"]||n;if("string"==typeof o)return o;var a=A(this.locale).quantify,i=a&&a(Math.abs(e));return o[i=i||"other"]||o.other}},{key:"formatNumber",value:function(e){return this.numberFormat?this.numberFormat.format(e):String(e)}},{key:"getFormatter",value:function(e){return this.relativeTimeFormatCache.get(this.locale,e)||this.relativeTimeFormatCache.put(this.locale,e,new m(this.locale,{style:e}))}},{key:"getLocaleData",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],t=A(this.locale);"string"==typeof e&&(e=[e]);var r=e=e.concat("long"),n=Array.isArray(r),o=0;for(r=n?r:r[Symbol.iterator]();;){var a;if(n){if(o>=r.length)break;a=r[o++]}else{if((o=r.next()).done)break;a=o.value}var i=a;if(t[i])return{flavour:i,localeData:t[i]}}}}])&&W(t.prototype,r),n&&W(t,n),e}();function V(e){if(e.constructor===Date||"object"===J(t=e)&&"function"==typeof t.getTime)return{date:e,time:e.getTime()};var t;if("number"==typeof e)return{time:e};throw new Error("Unsupported relative time formatter input: ".concat(J(e),", ").concat(e))}function z(e,t){var r=Object.keys(e);return t&&(r=t.filter((function(e){return r.indexOf(e)>=0}))),(!t||t.indexOf("now")>=0)&&r.indexOf("now")<0&&e.second.current&&r.unshift("now"),r}function Y(e,t){return e.now?"string"==typeof e.now?e.now:t<=0?e.now.past:e.now.future:e.second.current}U.getDefaultLocale=m.getDefaultLocale,U.setDefaultLocale=m.setDefaultLocale,U.addLocale=function(e){!function(e){if(!e)throw new Error("[javascript-time-ago] No locale data passed.");T[e.locale]=e}(e),m.addLocale(e)},U.locale=U.addLocale;var B=r(1),G=r.n(B);function H(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if(!(Symbol.iterator in Object(e))&&"[object Arguments]"!==Object.prototype.toString.call(e))return;var r=[],n=!0,o=!1,a=void 0;try{for(var i,u=e[Symbol.iterator]();!(n=(i=u.next()).done)&&(r.push(i.value),!t||r.length!==t);n=!0);}catch(e){o=!0,a=e}finally{try{n||null==u.return||u.return()}finally{if(o)throw a}}return r}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance")}()}function K(e){var t,r,n,o=e.products,a=H(React.useState(!0),2),i=(a[0],a[1],H(React.useState(!0),2)),u=i[0],c=i[1],s=H(React.useState(!1),2),l=s[0],f=s[1],h=H(React.useState(),2),p=h[0],d=h[1],m=H(React.useState(),2),y=m[0],g=m[1],v=H(React.useState(),2),w=v[0],b=v[1],k=H(React.useState(),2),x=(k[0],k[1]),R=H(React.useState(),2),E=(R[0],R[1]),S=H(React.useState(),2),N=S[0],O=S[1];function q(t){var r,n=5;2===window.hpVersion&&("large"===e.device||"mid"===e.device?n=6:"tablet"===e.device&&(n=2)),"opendesktop"===window.page?r=$("#main-content").width():"appimages"!==window.page&&"libreoffice"!==window.page||(r=$("#introduction").find(".container").width());var a=Math.ceil(o.length/(n-1)),i=r/n,u=(r-i)*a,c=0;y&&(c=y),"appimages"!==window.page&&"libreoffice"!==window.page||$("#carousel-module-container").width(r),g(c),b(r),E(n-1),d(u),O(i),x(a),t?j("right",t):!0===finishedProducts&&f(!0)}function j(e,t){var r=y,n=p-(w-N);"left"===e?y>0&&(r=w<3*N?y-N:y-2*N):r=Math.trunc(y)<Math.trunc(n)?w<3*N?y+N:y+2*N:0,g(r);var o=!1;y<=0&&(o=!0);c(o),f(!1)}if(React.useEffect((function(){window.addEventListener("resize",q)}),[]),o&&o.length>0){var M=!1;e.catIds||(M=!0),t=o.map((function(t,r){return React.createElement(Q,{key:r,product:t,itemWidth:N,env:e.env,plingedProduct:M})}))}r=u?React.createElement("a",{className:"carousel-arrow arrow-left disabled"},React.createElement("span",{className:"glyphicon glyphicon-chevron-left"})):React.createElement("a",{onClick:function(){return j("left")},className:"carousel-arrow arrow-left"},React.createElement("span",{className:"glyphicon glyphicon-chevron-left"})),n=l?React.createElement("a",{className:"carousel-arrow arrow-right disabled"},React.createElement("span",{className:"glyphicon glyphicon-chevron-right"})):React.createElement("a",{onClick:function(){return j("right")},className:"carousel-arrow arrow-right"},React.createElement("span",{className:"glyphicon glyphicon-chevron-right"}));var D,_="one",I={};2===window.hpVersion&&N&&(_="two",I={paddingLeft:N/2,paddingRight:N/2,height:1.35*N},D=N/4);var L="";"libreoffice"===window.page&&(L="/s/LibreOffice");var F=L+"/browse/cat/"+e.catIds+"/";return e.catIds?e.catIds.indexOf(",")>0&&(F=L+"/browse/"):F="/community#plingedproductsPanel",React.createElement("div",{className:"product-carousel "+_},React.createElement("div",{className:"product-carousel-header"},React.createElement("h2",null,React.createElement("a",{href:F},e.title," ",React.createElement("span",{className:"glyphicon glyphicon-chevron-right"})))),React.createElement("div",{className:"product-carousel-wrapper",style:I},React.createElement("div",{className:"product-carousel-left",style:{left:D}},r),React.createElement("div",{className:"product-carousel-container"},React.createElement("div",{className:"product-carousel-slider",style:{width:p,left:"-"+y+"px"}},t)),React.createElement("div",{className:"product-carousel-right",style:{right:D}},n)))}function Q(){var e,t=React.createElement("div",{className:"product-info"},React.createElement("span",{className:"product-info-title"},props.product.title),React.createElement("span",{className:"product-info-user"},props.product.username));if(2===window.hpVersion){var r;props.itemWidth&&(e=1.35*props.itemWidth/2-10),r=props.product.changed_at?props.product.changed_at:props.product.created_at;var n,o=new Date(r),a=new U("en-US").format(o),i=React.createElement("div",{className:"score-info"},React.createElement("div",{className:"score-number"},"Score ",(props.product.laplace_score/10).toFixed(1),"%"),React.createElement("div",{className:"score-bar-container"},React.createElement("div",{className:"score-bar",style:{width:props.product.laplace_score/10+"%"}})));if(n=i,props.plingedProduct){var u=React.createElement("div",{className:"plings"},React.createElement("img",{src:"/images/system/pling-btn-active.png"}),props.product.sum_plings);n=React.createElement("div",null,u,i)}t=React.createElement("div",{className:"product-info"},React.createElement("span",{className:"product-info-title"},props.product.title),React.createElement("span",{className:"product-info-category"},props.product.cat_title),React.createElement("span",{className:"product-info-date"},a),n)}var c="";return c="libreoffice"===window.page?window.baseUrl+"p/"+props.product.project_id:"/p/"+props.product.project_id,React.createElement("div",{className:"product-carousel-item",style:{width:props.itemWidth}},React.createElement("div",{className:"product-carousel-item-wrapper"},React.createElement("a",{href:c,style:{paddingTop:e}},React.createElement("figure",{style:{height:e}},React.createElement("img",{className:"very-rounded-corners",src:props.product.image_small})),t)))}U.addLocale(G.a);var X=function(){var e,t=H(React.useState(!0),2),r=t[0],n=t[1],o=H(React.useState(),2),a=o[0],i=o[1],u=H(React.useState(),2),c=u[0],s=u[1],l=H(React.useState(),2),f=l[0],h=l[1];function p(){var e=window.innerWidth;i(a)}return React.useEffect((function(){return p(),function(){window.addEventListener("resize",p),window.addEventListener("orientationchange",p);var e="live";(location.hostname.endsWith("cc")||location.hostname.endsWith("localhost"))&&(e="test");s(e),function(){var e=[];for(var t in window.data)if("comments"!==t&&"featureProducts"!==t){var r={title:window.data[t].title,catIds:window.data[t].catIds,products:JSON.parse(window.data[t].products)};e.push(r)}h(e),n(!1)}()}(),function(){window.removeEventListener("resize",p),window.removeEventListener("orientationchange",p)}}),[]),!1===r&&(e=f.map((function(e,t){return React.createElement("div",{key:t,className:"section"},React.createElement("div",{className:"container"},React.createElement(K,{products:e.products,device:a,title:e.title,catIds:e.catIds,link:"/",env:c})))}))),React.createElement("div",{id:"carousels-module"},e)};ReactDOM.render(React.createElement(X,null),document.getElementById("carousel-module-container"))}]);