(window.webpackJsonp=window.webpackJsonp||[]).push([[1],{17:function(e,t,r){"use strict";r.r(t);var n=r(0),a=r.n(n),i=r(5);function c(e){return function(e){if(Array.isArray(e))return p(e)}(e)||function(e){if("undefined"!=typeof Symbol&&Symbol.iterator in Object(e))return Array.from(e)}(e)||m(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function o(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function l(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?o(Object(r),!0).forEach((function(t){u(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):o(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}function u(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}function s(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var r=[],n=!0,a=!1,i=void 0;try{for(var c,o=e[Symbol.iterator]();!(n=(c=o.next()).done)&&(r.push(c.value),!t||r.length!==t);n=!0);}catch(e){a=!0,i=e}finally{try{n||null==o.return||o.return()}finally{if(a)throw i}}return r}(e,t)||m(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function m(e,t){if(e){if("string"==typeof e)return p(e,t);var r=Object.prototype.toString.call(e).slice(8,-1);return"Object"===r&&e.constructor&&(r=e.constructor.name),"Map"===r||"Set"===r?Array.from(e):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?p(e,t):void 0}}function p(e,t){(null==t||t>e.length)&&(t=e.length);for(var r=0,n=new Array(t);r<t;r++)n[r]=e[r];return n}function d(e){var t,r,n=a.a.createElement("svg",{fill:"currentColor",preserveAspectRatio:"xMidYMid meet",height:"1em",width:"1em",viewBox:"0 0 40 40",className:"play-icon"},a.a.createElement("g",null,a.a.createElement("path",{d:"m20.1 2.9q4.7 0 8.6 2.3t6.3 6.2 2.3 8.6-2.3 8.6-6.3 6.2-8.6 2.3-8.6-2.3-6.2-6.2-2.3-8.6 2.3-8.6 6.2-6.2 8.6-2.3z m8.6 18.3q0.7-0.4 0.7-1.2t-0.7-1.2l-12.1-7.2q-0.7-0.4-1.5 0-0.7 0.4-0.7 1.3v14.2q0 0.9 0.7 1.3 0.4 0.2 0.8 0.2 0.3 0 0.7-0.2z"}))),i=a.a.createElement("svg",{fill:"currentColor",preserveAspectRatio:"xMidYMid meet",height:"1em",width:"1em",viewBox:"0 0 40 40",className:"pause-icon"},a.a.createElement("g",null,a.a.createElement("path",{d:"m18.7 26.4v-12.8q0-0.3-0.2-0.5t-0.5-0.2h-5.7q-0.3 0-0.5 0.2t-0.2 0.5v12.8q0 0.3 0.2 0.5t0.5 0.2h5.7q0.3 0 0.5-0.2t0.2-0.5z m10 0v-12.8q0-0.3-0.2-0.5t-0.5-0.2h-5.7q-0.3 0-0.5 0.2t-0.2 0.5v12.8q0 0.3 0.2 0.5t0.5 0.2h5.7q0.3 0 0.5-0.2t0.2-0.5z m8.6-6.4q0 4.7-2.3 8.6t-6.3 6.2-8.6 2.3-8.6-2.3-6.2-6.2-2.3-8.6 2.3-8.6 6.2-6.2 8.6-2.3 8.6 2.3 6.3 6.2 2.3 8.6z"}))),c=a.a.createElement("svg",{fill:"currentColor",preserveAspectRatio:"xMidYMid meet",height:"1em",width:"1em",viewBox:"0 0 40 40",className:"prev-icon"},a.a.createElement("g",null,a.a.createElement("path",{d:"m15.9 20l14.1-10v20z m-5.9-10h3.4v20h-3.4v-20z"}))),o=a.a.createElement("svg",{fill:"currentColor",preserveAspectRatio:"xMidYMid meet",height:"1em",width:"1em",viewBox:"0 0 40 40",className:"next-icon"},a.a.createElement("g",null,a.a.createElement("path",{d:"m26.6 10h3.4v20h-3.4v-20z m-16.6 20v-20l14.1 10z"})));t=!0===e.isPlaying?!0===e.isMobile?a.a.createElement("span",{onTouchStart:function(){return e.onPauseClick()}},i):a.a.createElement("span",{onClick:function(){return e.onPauseClick()}},i):!0===e.isMobile?a.a.createElement("span",{onTouchStart:function(){return e.onPlayClick()}},n):a.a.createElement("span",{onClick:function(){return e.onPlayClick()}},n),r=!0===e.isMobile?a.a.createElement("div",{className:"music-player-audio-control"},a.a.createElement("span",{onTouchStart:function(){return e.onPrevTrackPlayClick()}},c),t,a.a.createElement("span",{onTouchStart:function(){return e.onNextTrackPlayClick()}},o)):a.a.createElement("div",{className:"music-player-audio-control"},a.a.createElement("span",{onClick:function(){return e.onPrevTrackPlayClick()}},c),t,a.a.createElement("span",{onClick:function(){return e.onNextTrackPlayClick()}},o));var l="music-player-controls-bar ";return e.isPlaying&&(l+="is-playing"),a.a.createElement("div",{id:"music-player-control-panel"},a.a.createElement("div",{className:l},a.a.createElement("div",{className:"music-player-controls-wrapper"},r),a.a.createElement("div",{className:"track-number-display"},parseInt(e.playIndex+1)+" / "+e.items.length)))}t.default=function(e){var t,r,o=a.a.useContext(i.a),u=o.productBrowseState,m=o.productBrowseDispatch,p=s(Object(n.useState)(0),2),f=p[0],y=p[1],v=(t=f,r=Object(n.useRef)(),Object(n.useEffect)((function(){r.current=t}),[t]),r.current),h=s(Object(n.useState)(),2),b=h[0],E=h[1],g=s(Object(n.useState)(),2),w=g[0],j=g[1],O=[];e.items.forEach((function(e,t){var r=l(l({},e),{},{played:0,stopped:0});O.push(r)}));var P=s(Object(n.useState)(O),2),S=P[0],k=P[1],x=e.containerWidth<600,C=s(Object(n.useState)(x),2),T=C[0];function I(t,r){var n=document.getElementById("music-player-container-"+e.product.project_id).getElementsByTagName("audio"),a=r||f,i=e.items[a].musicSrc;(!1===w||n[0].currentTime&&0===n[0].currentTime||!0===t)&&(n[0].src=i),n[0].play(),E(!0),j(!1),function(t,r){var n=S.find((function(e){return e.musicSrc===t})),a=r||S.findIndex((function(e){return e.musicSrc===t})),i=l(l({},n),{},{played:n.played+1}),o=[].concat(c(S.slice(0,a)),[i],c(S.slice(a+1,S.length)));if(0===S[a].played){var u="https://"+window.location.hostname+"/p/"+e.product.project_id+"/startmediaviewajax?collection_id="+n.collection_id+"&file_id="+n.id+"&type_id=2";$.ajax({url:u}).done((function(e){var t=l(l({},n),{},{mediaViewId:e.MediaViewId,played:n.played+1}),r=[].concat(c(S.slice(0,a)),[t],c(S.slice(a+1,S.length)));k(r)}))}else k(o)}(i,r)}function N(){document.getElementById("music-player-container-"+e.product.project_id).getElementsByTagName("audio")[0].pause(),E(!1),j(!0),_(e.items[f].musicSrc)}function _(t){var r=S.find((function(e){return e.musicSrc===t})),n=S.findIndex((function(e){return e.musicSrc===t})),a=l(l({},r),{},{stopped:r.stopped+1}),i=[].concat(c(S.slice(0,n)),[a],c(S.slice(n+1,S.length)));if(0===S[n].stopped){var o="https://"+window.location.hostname+"/p/"+e.product.project_id+"/stopmediaviewajax?media_view_id="+S[n].mediaViewId;$.ajax({url:o}).done((function(e){k(i)}))}else k(i)}return C[1],Object(n.useEffect)((function(){var t=document.getElementById("music-player-container-"+e.product.project_id).getElementsByTagName("audio"),r=e.items[f].musicSrc;t[0].src=r,t[0].volume=.5}),[]),Object(n.useEffect)((function(){b&&I(!0),w&&(v===f?I():I(!0)),!0===b&&_(e.items[v].musicSrc)}),[f]),a.a.useEffect((function(){u.current===e.product.project_id?!0===u.isPlaying?I(!0):N():!0===b&&N()}),[u.current,u.isPlaying]),a.a.createElement("div",{id:"music-player-container-"+e.product.project_id,className:"product-browse-music-player-wrapper"},a.a.createElement("audio",{volume:.5,id:"music-player-audio-"+e.product.project_id}),a.a.createElement(d,{playIndex:f,isPlaying:b,isPaused:w,isMobile:T,onPlayClick:function(t){m({type:"SET_CURRENT_ITEM",itemId:e.product.project_id,pIndex:f})},onPauseClick:function(){m({type:"PAUSE"})},onPrevTrackPlayClick:function(){var t;t=0===f?e.items.length-1:f-1,y(t)},onNextTrackPlayClick:function(){var t;t=f+1===e.items.length?0:f+1,y(t)},items:e.items}))}}}]);