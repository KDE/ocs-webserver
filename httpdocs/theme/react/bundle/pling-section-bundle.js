!function(e){var t={};function r(n){if(t[n])return t[n].exports;var o=t[n]={i:n,l:!1,exports:{}};return e[n].call(o.exports,o,o.exports,r),o.l=!0,o.exports}r.m=e,r.c=t,r.d=function(e,t,n){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(r.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)r.d(n,o,function(t){return e[t]}.bind(null,o));return n},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="",r(r.s=3)}([function(e,t,r){"use strict";e.exports=r(1)},function(e,t,r){"use strict";
/** @license React v16.6.1
 * react.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */var n=r(2),o="function"==typeof Symbol&&Symbol.for,a=o?Symbol.for("react.element"):60103,i=o?Symbol.for("react.portal"):60106,c=o?Symbol.for("react.fragment"):60107,l=o?Symbol.for("react.strict_mode"):60108,u=o?Symbol.for("react.profiler"):60114,s=o?Symbol.for("react.provider"):60109,f=o?Symbol.for("react.context"):60110,p=o?Symbol.for("react.concurrent_mode"):60111,y=o?Symbol.for("react.forward_ref"):60112,m=o?Symbol.for("react.suspense"):60113,b=o?Symbol.for("react.memo"):60115,d=o?Symbol.for("react.lazy"):60116,h="function"==typeof Symbol&&Symbol.iterator;function v(e){for(var t=arguments.length-1,r="https://reactjs.org/docs/error-decoder.html?invariant="+e,n=0;n<t;n++)r+="&args[]="+encodeURIComponent(arguments[n+1]);!function(e,t,r,n,o,a,i,c){if(!e){if(e=void 0,void 0===t)e=Error("Minified exception occurred; use the non-minified dev environment for the full error message and additional helpful warnings.");else{var l=[r,n,o,a,i,c],u=0;(e=Error(t.replace(/%s/g,function(){return l[u++]}))).name="Invariant Violation"}throw e.framesToPop=1,e}}(!1,"Minified React error #"+e+"; visit %s for the full message or use the non-minified dev environment for full errors and additional helpful warnings. ",r)}var g={isMounted:function(){return!1},enqueueForceUpdate:function(){},enqueueReplaceState:function(){},enqueueSetState:function(){}},_={};function E(e,t,r){this.props=e,this.context=t,this.refs=_,this.updater=r||g}function O(){}function S(e,t,r){this.props=e,this.context=t,this.refs=_,this.updater=r||g}E.prototype.isReactComponent={},E.prototype.setState=function(e,t){"object"!=typeof e&&"function"!=typeof e&&null!=e&&v("85"),this.updater.enqueueSetState(this,e,t,"setState")},E.prototype.forceUpdate=function(e){this.updater.enqueueForceUpdate(this,e,"forceUpdate")},O.prototype=E.prototype;var j=S.prototype=new O;j.constructor=S,n(j,E.prototype),j.isPureReactComponent=!0;var w={current:null,currentDispatcher:null},P=Object.prototype.hasOwnProperty,k={key:!0,ref:!0,__self:!0,__source:!0};function C(e,t,r){var n=void 0,o={},i=null,c=null;if(null!=t)for(n in void 0!==t.ref&&(c=t.ref),void 0!==t.key&&(i=""+t.key),t)P.call(t,n)&&!k.hasOwnProperty(n)&&(o[n]=t[n]);var l=arguments.length-2;if(1===l)o.children=r;else if(1<l){for(var u=Array(l),s=0;s<l;s++)u[s]=arguments[s+2];o.children=u}if(e&&e.defaultProps)for(n in l=e.defaultProps)void 0===o[n]&&(o[n]=l[n]);return{$$typeof:a,type:e,key:i,ref:c,props:o,_owner:w.current}}function N(e){return"object"==typeof e&&null!==e&&e.$$typeof===a}var x=/\/+/g,$=[];function T(e,t,r,n){if($.length){var o=$.pop();return o.result=e,o.keyPrefix=t,o.func=r,o.context=n,o.count=0,o}return{result:e,keyPrefix:t,func:r,context:n,count:0}}function U(e){e.result=null,e.keyPrefix=null,e.func=null,e.context=null,e.count=0,10>$.length&&$.push(e)}function R(e,t,r){return null==e?0:function e(t,r,n,o){var c=typeof t;"undefined"!==c&&"boolean"!==c||(t=null);var l=!1;if(null===t)l=!0;else switch(c){case"string":case"number":l=!0;break;case"object":switch(t.$$typeof){case a:case i:l=!0}}if(l)return n(o,t,""===r?"."+M(t,0):r),1;if(l=0,r=""===r?".":r+":",Array.isArray(t))for(var u=0;u<t.length;u++){var s=r+M(c=t[u],u);l+=e(c,s,n,o)}else if(s=null===t||"object"!=typeof t?null:"function"==typeof(s=h&&t[h]||t["@@iterator"])?s:null,"function"==typeof s)for(t=s.call(t),u=0;!(c=t.next()).done;)l+=e(c=c.value,s=r+M(c,u++),n,o);else"object"===c&&v("31","[object Object]"==(n=""+t)?"object with keys {"+Object.keys(t).join(", ")+"}":n,"");return l}(e,"",t,r)}function M(e,t){return"object"==typeof e&&null!==e&&null!=e.key?function(e){var t={"=":"=0",":":"=2"};return"$"+(""+e).replace(/[=:]/g,function(e){return t[e]})}(e.key):t.toString(36)}function F(e,t){e.func.call(e.context,t,e.count++)}function A(e,t,r){var n=e.result,o=e.keyPrefix;e=e.func.call(e.context,t,e.count++),Array.isArray(e)?q(e,n,r,function(e){return e}):null!=e&&(N(e)&&(e=function(e,t){return{$$typeof:a,type:e.type,key:t,ref:e.ref,props:e.props,_owner:e._owner}}(e,o+(!e.key||t&&t.key===e.key?"":(""+e.key).replace(x,"$&/")+"/")+r)),n.push(e))}function q(e,t,r,n,o){var a="";null!=r&&(a=(""+r).replace(x,"$&/")+"/"),R(e,A,t=T(t,a,n,o)),U(t)}var D={Children:{map:function(e,t,r){if(null==e)return e;var n=[];return q(e,n,null,t,r),n},forEach:function(e,t,r){if(null==e)return e;R(e,F,t=T(null,null,t,r)),U(t)},count:function(e){return R(e,function(){return null},null)},toArray:function(e){var t=[];return q(e,t,null,function(e){return e}),t},only:function(e){return N(e)||v("143"),e}},createRef:function(){return{current:null}},Component:E,PureComponent:S,createContext:function(e,t){return void 0===t&&(t=null),(e={$$typeof:f,_calculateChangedBits:t,_currentValue:e,_currentValue2:e,_threadCount:0,Provider:null,Consumer:null}).Provider={$$typeof:s,_context:e},e.Consumer=e},forwardRef:function(e){return{$$typeof:y,render:e}},lazy:function(e){return{$$typeof:d,_ctor:e,_status:-1,_result:null}},memo:function(e,t){return{$$typeof:b,type:e,compare:void 0===t?null:t}},Fragment:c,StrictMode:l,Suspense:m,createElement:C,cloneElement:function(e,t,r){null==e&&v("267",e);var o=void 0,i=n({},e.props),c=e.key,l=e.ref,u=e._owner;if(null!=t){void 0!==t.ref&&(l=t.ref,u=w.current),void 0!==t.key&&(c=""+t.key);var s=void 0;for(o in e.type&&e.type.defaultProps&&(s=e.type.defaultProps),t)P.call(t,o)&&!k.hasOwnProperty(o)&&(i[o]=void 0===t[o]&&void 0!==s?s[o]:t[o])}if(1===(o=arguments.length-2))i.children=r;else if(1<o){s=Array(o);for(var f=0;f<o;f++)s[f]=arguments[f+2];i.children=s}return{$$typeof:a,type:e.type,key:c,ref:l,props:i,_owner:u}},createFactory:function(e){var t=C.bind(null,e);return t.type=e,t},isValidElement:N,version:"16.6.3",__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED:{ReactCurrentOwner:w,assign:n}};D.unstable_ConcurrentMode=p,D.unstable_Profiler=u;var I={default:D},L=I&&D||I;e.exports=L.default||L},function(e,t,r){"use strict";
/*
object-assign
(c) Sindre Sorhus
@license MIT
*/var n=Object.getOwnPropertySymbols,o=Object.prototype.hasOwnProperty,a=Object.prototype.propertyIsEnumerable;e.exports=function(){try{if(!Object.assign)return!1;var e=new String("abc");if(e[5]="de","5"===Object.getOwnPropertyNames(e)[0])return!1;for(var t={},r=0;r<10;r++)t["_"+String.fromCharCode(r)]=r;if("0123456789"!==Object.getOwnPropertyNames(t).map(function(e){return t[e]}).join(""))return!1;var n={};return"abcdefghijklmnopqrst".split("").forEach(function(e){n[e]=e}),"abcdefghijklmnopqrst"===Object.keys(Object.assign({},n)).join("")}catch(e){return!1}}()?Object.assign:function(e,t){for(var r,i,c=function(e){if(null==e)throw new TypeError("Object.assign cannot be called with null or undefined");return Object(e)}(e),l=1;l<arguments.length;l++){for(var u in r=Object(arguments[l]))o.call(r,u)&&(c[u]=r[u]);if(n){i=n(r);for(var s=0;s<i.length;s++)a.call(r,i[s])&&(c[i[s]]=r[i[s]])}}return c}},function(e,t,r){"use strict";r.r(t);var n=r(0),o=r.n(n);function a(e){return(a="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function i(e,t){for(var r=0;r<t.length;r++){var n=t[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function c(e,t){return!t||"object"!==a(t)&&"function"!=typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function l(e){return(l=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function u(e,t){return(u=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}var s=function(e){function t(){return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),c(this,l(t).apply(this,arguments))}var r,n,a;return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&u(e,t)}(t,o.a.Component),r=t,(n=[{key:"render",value:function(){var e=this.props.baseUrlStore+"/p/"+this.props.product.project_id,t=o.a.createElement("div",{className:"score-info"},o.a.createElement("div",{className:"score-number"},(this.props.product.laplace_score/10).toFixed(1)+"%"),o.a.createElement("div",{className:"score-bar-container"},o.a.createElement("div",{className:"score-bar",style:{width:this.props.product.laplace_score/10+"%"}}),o.a.createElement("span",null,"$",this.props.product.probably_payout_amount," "))),r=o.a.createElement("div",{className:"product-info"},o.a.createElement("span",{className:"product-info-title"},o.a.createElement("a",{href:e},this.props.product.title)),o.a.createElement("span",{className:"product-info-category"},this.props.product.cat_title),o.a.createElement("span",{className:"product-info-date"},this.props.product.updated_at));return o.a.createElement("div",{className:"productrow row"},o.a.createElement("div",{className:"col-lg-2"},o.a.createElement("a",{href:e},o.a.createElement("figure",null,o.a.createElement("img",{className:"productimg",src:this.props.product.image_small})))),o.a.createElement("div",{className:"col-lg-7"},r),o.a.createElement("div",{className:"col-lg-3"},t))}}])&&i(r.prototype,n),a&&i(r,a),t}();function f(e){return(f="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function p(e,t){for(var r=0;r<t.length;r++){var n=t[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function y(e,t){return!t||"object"!==f(t)&&"function"!=typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function m(e){return(m=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function b(e,t){return(b=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}var d=function(e){function t(e){var r;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),(r=y(this,m(t).call(this,e))).state={},r}var r,n,a;return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&b(e,t)}(t,o.a.Component),r=t,(n=[{key:"render",value:function(){var e,t=this;if(this.props.products){var r=this.props.products.map(function(e,r){return o.a.createElement("li",{key:r},o.a.createElement(s,{product:e,baseUrlStore:t.props.baseUrlStore}))});e=o.a.createElement("ul",null,r)}return this.props.category?this.props.category.title:this.props.section?this.props.section.name:"All",o.a.createElement("div",{className:"panelContainer"},o.a.createElement("div",{className:"title"},"Top 20 Products Last Month"),e)}}])&&p(r.prototype,n),a&&p(r,a),t}();var h=function(e){return o.a.createElement("div",{className:"creatorrow row"},o.a.createElement("div",{className:"col-lg-4"},o.a.createElement("a",{href:e.baseUrlStore+"/u/"+e.creator.username},o.a.createElement("figure",null,o.a.createElement("img",{className:"productimg",src:e.creator.profile_image_url})))),o.a.createElement("div",{className:"col-lg-8 userinfo"},o.a.createElement("div",{className:"userinfo-title"},e.creator.username),o.a.createElement("span",null,"$",e.creator.probably_payout_amount)))};function v(e){return(v="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function g(e,t){for(var r=0;r<t.length;r++){var n=t[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function _(e,t){return!t||"object"!==v(t)&&"function"!=typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function E(e){return(E=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function O(e,t){return(O=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}var S=function(e){function t(e){var r;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),(r=_(this,E(t).call(this,e))).state={},r}var r,n,a;return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&O(e,t)}(t,o.a.Component),r=t,(n=[{key:"render",value:function(){var e,t=this;if(this.props.creators){var r=this.props.creators.map(function(e,r){return o.a.createElement("li",{key:r},o.a.createElement(h,{creator:e,baseUrlStore:t.props.baseUrlStore}))});e=o.a.createElement("ul",null,r)}return this.props.category?this.props.category.title:this.props.section?this.props.section.name:"All",o.a.createElement("div",{className:"panelContainer"},o.a.createElement("div",{className:"title"},"Top 20 Creators Last Month"),e)}}])&&g(r.prototype,n),a&&g(r,a),t}();function j(e){return(j="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function w(e,t){for(var r=0;r<t.length;r++){var n=t[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function P(e){return(P=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function k(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function C(e,t){return(C=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}var N=function(e){function t(e){var r,n,o;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),n=this,(r=!(o=P(t).call(this,e))||"object"!==j(o)&&"function"!=typeof o?k(n):o).state={},r.onChangeFreeamount=r.onChangeFreeamount.bind(k(r)),r}var r,n,a;return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&C(e,t)}(t,o.a.Component),r=t,(n=[{key:"onChangeFreeamount",value:function(e){this.setState({typed:e.target.value})}},{key:"render",value:function(){var e,t,r=this,n=[.99,2,5,10],a=n.map(function(e,t){var n,a=e,i=r.props.supporters.filter(function(e){return e.section_support_tier==a}).map(function(e,t){return o.a.createElement("li",{key:t},o.a.createElement("a",{href:r.props.baseUrlStore+"/u/"+e.username},o.a.createElement("img",{src:e.profile_image_url})))});n=o.a.createElement("ul",null,i);var c=r.props.baseUrlStore+"/support-predefined?section_id="+r.props.section.section_id;return c=c+"&amount_predefined="+a,o.a.createElement("div",{className:"tier-container"},o.a.createElement("span",null,"the following people chose $",e," tier to support this section:"),n,o.a.createElement("div",{className:"join"},o.a.createElement("a",{href:c},"Join $",e," Tier")))}),i=this.props.supporters;if(n.forEach(function(e){i=i.filter(function(t){return t.section_support_tier!=e})}),i.length>0){var c=i.map(function(e,t){return o.a.createElement("li",{key:t},o.a.createElement("a",{href:r.props.baseUrlStore+"/u/"+e.username},o.a.createElement("img",{src:e.profile_image_url})))});t=o.a.createElement("ul",null,c)}var l=this.props.baseUrlStore+"/support-predefined?section_id="+this.props.section.section_id;return l=l+"&amount_predefined="+this.state.typed,e=o.a.createElement("div",{className:"tier-container"},t&&o.a.createElement("span",null,"the following people chose other tier to support this section:"),t,o.a.createElement("div",{className:"join"},o.a.createElement("div",null,"$",o.a.createElement("input",{className:"free-amount",onChange:this.onChangeFreeamount.bind(this)}),o.a.createElement("span",null,"Enter a free amount")),o.a.createElement("a",{href:l,id:"free-amount-link"},"Join "))),o.a.createElement("div",{className:"support-container"},o.a.createElement("div",{className:"tiers"},o.a.createElement("h5",null,"Tiers")),a,e)}}])&&w(r.prototype,n),a&&w(r,a),t}();function x(e){return(x="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function $(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}function T(e,t){for(var r=0;r<t.length;r++){var n=t[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function U(e){return(U=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function R(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function M(e,t){return(M=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}var F=function(e){function t(e){var r,n,o;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),n=this,(r=!(o=U(t).call(this,e))||"object"!==x(o)&&"function"!=typeof o?R(n):o).state=function(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{},n=Object.keys(r);"function"==typeof Object.getOwnPropertySymbols&&(n=n.concat(Object.getOwnPropertySymbols(r).filter(function(e){return Object.getOwnPropertyDescriptor(r,e).enumerable}))),n.forEach(function(t){$(e,t,r[t])})}return e}({},window.data),r.onClickCategory=r.onClickCategory.bind(R(r)),r}var r,a,i;return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&M(e,t)}(t,n["Component"]),r=t,(a=[{key:"componentDidMount",value:function(){}},{key:"onClickCategory",value:function(e){var t=this,r="/section/topcat?cat_id="+e.project_category_id;fetch(r).then(function(e){return e.json()}).then(function(r){t.setState(function(t){return{loading:!1,products:r.products,creators:r.creators,category:e}})})}},{key:"render",value:function(){var e,t,r,n=this;if(this.state.sections){var a=this.state.sections.map(function(e,t){return o.a.createElement("li",{key:e.section_id,className:n.state.section&&e.section_id==n.state.section.section_id?"active":""},o.a.createElement("a",{href:"/section?id="+e.section_id},e.name))});e=o.a.createElement("div",{className:"pling-nav-tabs"},o.a.createElement("ul",{className:"nav nav-tabs pling-section-tabs"},a))}return this.state.details&&this.state.section&&(r=this.state.details.map(function(e,t){if(e.section_id==n.state.section.section_id)return o.a.createElement("li",null,o.a.createElement("a",{onClick:function(){return n.onClickCategory(e)}},e.title))})),t=o.a.createElement("div",{className:"pling-section-detail"},this.state.section&&o.a.createElement("div",{className:"pling-section-detail-left"},o.a.createElement("h2",null,"Categories"),o.a.createElement("ul",{className:"pling-section-detail-ul"},r)),o.a.createElement("div",{className:"pling-section-detail-middle"},o.a.createElement(d,{baseUrlStore:this.state.baseurlStore,products:this.state.products,section:this.state.section,category:this.state.category}),o.a.createElement(S,{creators:this.state.creators,section:this.state.section,category:this.state.category,baseUrlStore:this.state.baseurlStore})),o.a.createElement("div",{className:"pling-section-detail-right"},o.a.createElement("a",{href:this.state.baseurlStore+"/support",className:"btnSupporter"},"Become a Supporter"),this.state.section&&o.a.createElement(N,{baseUrlStore:this.state.baseurlStore,section:this.state.section,supporters:this.state.supporters}))),o.a.createElement(o.a.Fragment,null,o.a.createElement("h1",null,"Section Detail "),e,t)}}])&&T(r.prototype,a),i&&T(r,i),t}();ReactDOM.render(o.a.createElement(F,null),document.getElementById("pling-section-content"))}]);