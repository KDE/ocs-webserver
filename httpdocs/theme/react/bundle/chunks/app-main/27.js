(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[27],{

/***/ "./modules/product-media-slider/app/components/book-reader.js":
/*!********************************************************************!*\
  !*** ./modules/product-media-slider/app/components/book-reader.js ***!
  \********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var react_device_detect__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-device-detect */ \"./node_modules/react-device-detect/main.js\");\n/* harmony import */ var react_device_detect__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_device_detect__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var _style_book_reader_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../style/book-reader.css */ \"./modules/product-media-slider/style/book-reader.css\");\n/* harmony import */ var _style_book_reader_css__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_style_book_reader_css__WEBPACK_IMPORTED_MODULE_2__);\nfunction _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }\n\nfunction _nonIterableRest() { throw new TypeError(\"Invalid attempt to destructure non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\"); }\n\nfunction _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === \"string\") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === \"Object\" && o.constructor) n = o.constructor.name; if (n === \"Map\" || n === \"Set\") return Array.from(o); if (n === \"Arguments\" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }\n\nfunction _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }\n\nfunction _iterableToArrayLimit(arr, i) { if (typeof Symbol === \"undefined\" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i[\"return\"] != null) _i[\"return\"](); } finally { if (_d) throw _e; } } return _arr; }\n\nfunction _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }\n\n\n\n\n\nfunction BookReaderWrapper(props) {\n  var _useState = Object(react__WEBPACK_IMPORTED_MODULE_0__[\"useState\"])(true),\n      _useState2 = _slicedToArray(_useState, 2),\n      loading = _useState2[0],\n      setLoading = _useState2[1];\n\n  var _useState3 = Object(react__WEBPACK_IMPORTED_MODULE_0__[\"useState\"])(),\n      _useState4 = _slicedToArray(_useState3, 2),\n      renditionState = _useState4[0],\n      setRenditionState = _useState4[1];\n\n  var _useState5 = Object(react__WEBPACK_IMPORTED_MODULE_0__[\"useState\"])(),\n      _useState6 = _slicedToArray(_useState5, 2),\n      currentPage = _useState6[0],\n      setCurrentPage = _useState6[1];\n\n  var _useState7 = Object(react__WEBPACK_IMPORTED_MODULE_0__[\"useState\"])(),\n      _useState8 = _slicedToArray(_useState7, 2),\n      totalPages = _useState8[0],\n      setTotalPages = _useState8[1];\n\n  var _useState9 = Object(react__WEBPACK_IMPORTED_MODULE_0__[\"useState\"])(false),\n      _useState10 = _slicedToArray(_useState9, 2),\n      showBookMenu = _useState10[0],\n      setShowBookMenu = _useState10[1];\n\n  var _useState11 = Object(react__WEBPACK_IMPORTED_MODULE_0__[\"useState\"])(false),\n      _useState12 = _slicedToArray(_useState11, 2),\n      showPrevButton = _useState12[0],\n      setShowPrevButton = _useState12[1];\n\n  var _useState13 = Object(react__WEBPACK_IMPORTED_MODULE_0__[\"useState\"])(false),\n      _useState14 = _slicedToArray(_useState13, 2),\n      showNextButton = _useState14[0],\n      setShowNextButton = _useState14[1];\n\n  var _useState15 = Object(react__WEBPACK_IMPORTED_MODULE_0__[\"useState\"])(0),\n      _useState16 = _slicedToArray(_useState15, 2),\n      viewedPagesCount = _useState16[0],\n      setViewedPagesCount = _useState16[1];\n\n  var _useState17 = Object(react__WEBPACK_IMPORTED_MODULE_0__[\"useState\"])(false),\n      _useState18 = _slicedToArray(_useState17, 2),\n      bookReadIsReported = _useState18[0],\n      setBookReadIsReported = _useState18[1];\n\n  react__WEBPACK_IMPORTED_MODULE_0___default.a.useEffect(function () {\n    initBookReader();\n  }, []);\n  react__WEBPACK_IMPORTED_MODULE_0___default.a.useEffect(function () {\n    if (window.book) window.book.destroy();\n    initBookReader();\n  }, [props.cinemaMode, props.width]);\n  react__WEBPACK_IMPORTED_MODULE_0___default.a.useEffect(function () {\n    if (totalPages === 0) {\n      hackBookPageCount();\n    }\n  }, [totalPages, window.book]);\n  react__WEBPACK_IMPORTED_MODULE_0___default.a.useEffect(function () {\n    if (viewedPagesCount > 3 && bookReadIsReported === false) {\n      reportBookRead();\n    }\n  }, [viewedPagesCount]);\n\n  function hackBookPageCount() {\n    var newTotalPageCount = window.book.locations.total;\n\n    if (newTotalPageCount === 0) {\n      setTimeout(function () {\n        hackBookPageCount();\n      }, 200);\n    } else {\n      setTotalPages(newTotalPageCount);\n    }\n  }\n\n  function initBookReader() {\n    // Initialize the book\n    window.book = ePub(props.slide.url, {});\n    window.rendition = book.renderTo('viewer', {\n      flow: 'paginated',\n      manager: 'default',\n      spread: 'always',\n      width: props.width - 20,\n      height: props.height - 35\n    });\n    setRenditionState(rendition); // Display the book\n\n    window.displayed = window.rendition.display(window.location.hash.substr(1) || undefined);\n    displayed.then(function () {// console.log('rendition.currentLocation():', rendition.currentLocation());\n    }); // Generate location and pagination\n\n    window.book.ready.then(function () {\n      var stored = localStorage.getItem(book.key() + '-locations'); // console.log('metadata:', book.package.metadata);\n\n      if (stored) {\n        return window.book.locations.load(stored);\n      } else {\n        return window.book.locations.generate(1024); // Generates CFI for every X characters (Characters per/page)\n      }\n    }).then(function (location) {\n      // This promise will take a little while to return (About 20 seconds or so for Moby Dick)\n      localStorage.setItem(book.key() + '-locations', book.locations.save());\n    }); // When navigating to the next/previous page\n\n    window.rendition.on('relocated', function (locations) {\n      setCurrentPage(book.locations.locationFromCfi(locations.start.cfi));\n      setTotalPages(book.locations.total);\n      if (loading === true) setLoading(false);\n      if (rendition.currentLocation().atStart === true) setShowPrevButton(false);else setShowPrevButton(true);\n      if (rendition.currentLocation().atEnd === true) setShowNextButton(false);else setShowNextButton(true);\n    });\n  }\n\n  function goPrev() {\n    renditionState.prev();\n  }\n\n  function goNext() {\n    var newViewedPagesCountValue = viewedPagesCount + 1;\n    setViewedPagesCount(newViewedPagesCountValue);\n    renditionState.next();\n  }\n\n  function onStartClick() {\n    var lastPageCfi = renditionState.book.locations._locations[0];\n    renditionState.display(lastPageCfi);\n  }\n\n  function onEndClick() {\n    var lastPageCfi = renditionState.book.locations._locations[renditionState.book.locations._locations.length - 1];\n    renditionState.display(lastPageCfi);\n  }\n\n  function onPageNumberInput(val) {\n    var cfiFromNumber = renditionState.book.locations._locations[val];\n    renditionState.display(cfiFromNumber);\n  }\n\n  function toggleMenu() {\n    var newShowBookMenu = showBookMenu === true ? false : true;\n    setShowBookMenu(newShowBookMenu);\n  }\n\n  function goToTocItem(item) {\n    renditionState.display(item.href);\n    toggleMenu();\n  }\n\n  function reportBookRead() {\n    // console.log('report book reading')\n    // console.log(props);\n    var bookReadReportUrl = \"https://\" + window.location.hostname + \"/p/\" + props.product.project_id + '/startmediaviewajax?collection_id=' + props.slide.collection_id + '&file_id=' + props.slide.file_id + '&type_id=3';\n    $.ajax({\n      url: bookReadReportUrl\n    }).done(function (res) {\n      // console.log(res);\n      setBookReadIsReported(true);\n    });\n  }\n\n  var loadingDisplay = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"div\", {\n    id: \"ajax-loader\"\n  });\n  var bookNavigation;\n\n  if (loading === false) {\n    loadingDisplay = \"\";\n    var prevButtonDisplay;\n\n    if (showPrevButton === true) {\n      prevButtonDisplay = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"span\", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"a\", {\n        onClick: function onClick() {\n          return goPrev();\n        }\n      }, \"< previous\"));\n    }\n\n    var nextButtonDisplay;\n\n    if (showNextButton === true && totalPages !== 0) {\n      nextButtonDisplay = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"span\", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"a\", {\n        id: \"next-page-button\",\n        onClick: function onClick() {\n          return goNext();\n        }\n      }, \"next >\"));\n    }\n\n    var bookNavigationMidDisplay;\n\n    if (totalPages !== 0) {\n      bookNavigationMidDisplay = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"span\", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"input\", {\n        type: \"number\",\n        className: \"form-control\",\n        placeholder: currentPage,\n        min: \"0\",\n        max: totalPages,\n        onChange: function onChange(e) {\n          return onPageNumberInput(e.target.value);\n        }\n      }), \" / \" + totalPages);\n    }\n\n    bookNavigation = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"div\", {\n      id: \"book-pager\"\n    }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"div\", {\n      className: \"pager-wrapper\"\n    }, prevButtonDisplay, bookNavigationMidDisplay, nextButtonDisplay));\n  }\n\n  var bookMenuDisplay, tocMenuToggleDisplay;\n\n  if (renditionState) {\n    if (renditionState.book.navigation) {\n      tocMenuToggleDisplay = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"div\", {\n        id: \"toc-menu-toggle\",\n        onClick: toggleMenu\n      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"span\", {\n        className: \"glyphicon glyphicon-menu-hamburger\"\n      }));\n    }\n\n    if (showBookMenu === true) {\n      var items = renditionState.book.navigation.toc.map(function (item, index) {\n        return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(BookMenuItem, {\n          key: index,\n          goToTocItem: goToTocItem,\n          item: item\n        });\n      });\n      bookMenuDisplay = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"ul\", {\n        id: \"book-menu\"\n      }, items);\n    }\n  }\n\n  var bookReaderWrapperCssClass = react_device_detect__WEBPACK_IMPORTED_MODULE_1__[\"isMobile\"] === true ? \"is-mobile\" : \"is-desktop\";\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"div\", {\n    id: \"book-reader-wrapper\",\n    className: bookReaderWrapperCssClass\n  }, loadingDisplay, tocMenuToggleDisplay, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"div\", {\n    id: \"viewer\",\n    className: \"spreads\"\n  }), bookNavigation, bookMenuDisplay);\n}\n\nfunction BookMenuItem(props) {\n  function onGoToTocItem() {\n    props.goToTocItem(props.item);\n  }\n\n  var subItemsDisplay;\n\n  if (props.item.subitems && props.item.subitems.length > 0) {\n    var items = props.item.subitems.map(function (subitem, index) {\n      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(BookMenuItem, {\n        goToTocItem: props.goToTocItem,\n        key: index,\n        item: subitem\n      });\n    });\n    subItemsDisplay = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"ul\", null, \" \", items, \" \");\n  }\n\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"li\", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(\"a\", {\n    onClick: function onClick() {\n      return onGoToTocItem();\n    }\n  }, props.item.label), subItemsDisplay);\n}\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (BookReaderWrapper);\n\n//# sourceURL=webpack:///./modules/product-media-slider/app/components/book-reader.js?");

/***/ }),

/***/ "./modules/product-media-slider/style/book-reader.css":
/*!************************************************************!*\
  !*** ./modules/product-media-slider/style/book-reader.css ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var api = __webpack_require__(/*! ../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ \"./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js\");\n            var content = __webpack_require__(/*! !../../../node_modules/css-loader/dist/cjs.js!./book-reader.css */ \"./node_modules/css-loader/dist/cjs.js!./modules/product-media-slider/style/book-reader.css\");\n\n            content = content.__esModule ? content.default : content;\n\n            if (typeof content === 'string') {\n              content = [[module.i, content, '']];\n            }\n\nvar options = {};\n\noptions.insert = \"head\";\noptions.singleton = false;\n\nvar update = api(content, options);\n\n\n\nmodule.exports = content.locals || {};\n\n//# sourceURL=webpack:///./modules/product-media-slider/style/book-reader.css?");

/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js!./modules/product-media-slider/style/book-reader.css":
/*!**************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js!./modules/product-media-slider/style/book-reader.css ***!
  \**************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// Imports\nvar ___CSS_LOADER_API_IMPORT___ = __webpack_require__(/*! ../../../node_modules/css-loader/dist/runtime/api.js */ \"./node_modules/css-loader/dist/runtime/api.js\");\nexports = ___CSS_LOADER_API_IMPORT___(false);\n// Module\nexports.push([module.i, \"#book-reader-wrapper {\\n  position: relative;\\n  height: inherit;\\n  padding: 10px 10px 0 10px;\\n}\\n#book-reader-wrapper.is-mobile #book-menu {\\n  width: 100%;\\n}\\n#book-reader-wrapper #ajax-loader {\\n  width: 24px;\\n  height: 24px;\\n  position: absolute;\\n  top: 50%;\\n  left: 50%;\\n  margin-left: -12px;\\n  margin-top: -12px;\\n  background-color: white;\\n}\\n#book-reader-wrapper #book-container {\\n  border: 1px solid #ccc;\\n  width: 100% !important;\\n  display: block !important;\\n  padding: 20px;\\n  box-sizing: border-box;\\n  height: 100%;\\n  background-color: #524631;\\n  text-align: center;\\n}\\n#book-reader-wrapper #book-container .epub-container {\\n  border: 1px solid #eee;\\n  background: white;\\n  margin: 0 auto;\\n}\\n#book-reader-wrapper #book-pager {\\n  position: absolute;\\n  bottom: 2px;\\n  z-index: 100;\\n  font-weight: bold;\\n  left: 0;\\n  width: 100%;\\n  font-size: 14px;\\n  margin-bottom: 5px;\\n}\\n#book-reader-wrapper #book-pager .pager-wrapper {\\n  display: block;\\n  min-width: 205px;\\n}\\n#book-reader-wrapper #book-pager .pager-wrapper input[type=\\\"number\\\"] {\\n  width: 30px;\\n  display: inline-block;\\n  padding: 2px;\\n  margin: 0 5px 0 0;\\n  height: 30px;\\n}\\n#book-reader-wrapper #book-pager .pager-wrapper span {\\n  margin-right: 5px;\\n}\\n#book-reader-wrapper #book-pager .pager-wrapper a {\\n  cursor: pointer;\\n}\\n#book-reader-wrapper .arrow {\\n  outline: none;\\n  border: none;\\n  background: none;\\n  position: absolute;\\n  top: 50%;\\n  margin-top: -10px !important;\\n  font-size: 25px;\\n  padding: 0px 10px;\\n  color: #e2e2e2;\\n  font-family: arial, sans-serif;\\n  cursor: pointer;\\n  user-select: none;\\n  z-index: 100;\\n  font-weight: normal;\\n  top: auto;\\n  bottom: 6px;\\n  z-index: 10000;\\n}\\n#book-reader-wrapper .arrow#prev {\\n  left: 40px !important;\\n}\\n#book-reader-wrapper .arrow#next {\\n  right: 40px !important;\\n}\\n#book-reader-wrapper .arrow:hover,\\n#book-reader-wrapper .navlink:hover {\\n  color: #777;\\n}\\n#book-reader-wrapper .arrow:active,\\n#book-reader-wrapper .navlink:hover {\\n  color: #000;\\n}\\n#book-reader-wrapper #toc-menu-toggle {\\n  position: absolute;\\n  bottom: 8px;\\n  left: 20px;\\n  z-index: 300;\\n  font-size: 22px;\\n  color: #e2e2e2;\\n  cursor: pointer;\\n}\\n#book-reader-wrapper #book-menu {\\n  position: absolute;\\n  top: 10px;\\n  left: 10px;\\n  list-style-type: none;\\n  margin: 0;\\n  padding: 0 20px 0 40px;\\n  background-color: #f7f7f7;\\n  z-index: 199;\\n  max-width: 100%;\\n  height: calc(100% - 10px);\\n  /* border-radius: 5px 0 0 5px; */\\n  overflow-y: scroll;\\n  text-align: left;\\n}\\n#book-reader-wrapper #book-menu li a {\\n  padding: 5px;\\n  cursor: pointer;\\n  display: block;\\n  font-size: 12px;\\n}\\n#book-reader-wrapper #book-menu li a:hover {\\n  background: rgba(0, 0, 0, 0.1);\\n}\\n#book-reader-wrapper #book-menu li > ul {\\n  padding: 0 0 0 10px;\\n  list-style-type: none;\\n  margin: 5px 0 0 0;\\n}\\n#book-reader-wrapper #title {\\n  width: 900px;\\n  min-height: 18px;\\n  margin: 10px auto;\\n  text-align: center;\\n  font-size: 16px;\\n  color: #E2E2E2;\\n  font-weight: 400;\\n}\\n#book-reader-wrapper #title:hover {\\n  color: #777;\\n}\\n#book-reader-wrapper #viewer.spreads {\\n  width: 100%;\\n  height: 100%;\\n  box-shadow: 0 0 4px #ccc;\\n  border-radius: 5px;\\n  padding: 0;\\n  position: relative;\\n  margin: 0;\\n  background: transparent center center no-repeat;\\n  top: 0;\\n  padding: 0px;\\n}\\n#book-reader-wrapper #viewer.spreads .epub-view > iframe {\\n  background: white;\\n}\\n#book-reader-wrapper #viewer.spreads .epub-view > iframe a:hover {\\n  color: black !important;\\n}\\n@media (min-width: 969px) {\\n  #book-reader-wrapper {\\n    /*#viewer.spreads:after {\\n        position: absolute;\\n        width: 1px;\\n        border-right: 1px #000 solid;\\n        height: 90%;\\n        z-index: 1;\\n        left: 50%;\\n        margin-left: -1px;\\n        top: 5%;\\n        opacity: .15;\\n        box-shadow: -2px 0 15px rgba(0, 0, 0, 1);\\n        content:  \\\"\\\";\\n      }*/\\n  }\\n}\\n#book-reader-wrapper #viewer.scrolled {\\n  overflow: hidden;\\n  width: 800px;\\n  margin: 0 auto;\\n  position: relative;\\n  background: center center no-repeat;\\n}\\n#book-reader-wrapper #viewer.scrolled .epub-container {\\n  background: white;\\n  box-shadow: 0 0 4px #ccc;\\n  margin: 10px;\\n  padding: 20px;\\n}\\n#book-reader-wrapper #viewer.scrolled .epub-view > iframe {\\n  background: white;\\n}\\n#book-reader-wrapper #toc {\\n  display: block;\\n  margin: 10px auto;\\n}\\n@media (min-width: 1000px) {\\n  #book-reader-wrapper #viewer.spreads.single:after {\\n    display: none;\\n  }\\n  #book-reader-wrapper #prev {\\n    left: 40px;\\n  }\\n  #book-reader-wrapper #next {\\n    right: 40px;\\n  }\\n}\\n#book-reader-wrapper .navlink {\\n  margin: 14px;\\n  display: block;\\n  text-align: center;\\n  text-decoration: none;\\n  color: #ccc;\\n}\\n#book-reader-wrapper #book-wrapper {\\n  width: 480px;\\n  height: 640px;\\n  overflow: hidden;\\n  border: 1px solid #ccc;\\n  margin: 28px auto;\\n  background: #fff;\\n  border-radius: 0 5px 5px 0;\\n  position: absolute;\\n}\\n#book-reader-wrapper #book-viewer {\\n  width: 480px;\\n  height: 660px;\\n  margin: -30px auto;\\n  -moz-box-shadow: inset 10px 0 20px rgba(0, 0, 0, 0.1);\\n  -webkit-box-shadow: inset 10px 0 20px rgba(0, 0, 0, 0.1);\\n  box-shadow: inset 10px 0 20px rgba(0, 0, 0, 0.1);\\n}\\n#book-reader-wrapper #book-viewer iframe {\\n  padding: 40px 40px;\\n}\\n#book-reader-wrapper #controls {\\n  position: absolute;\\n  bottom: 16px;\\n  left: 50%;\\n  width: 400px;\\n  margin-left: -200px;\\n  text-align: center;\\n  display: none;\\n}\\n#book-reader-wrapper #controls > input[type=range] {\\n  width: 400px;\\n}\\n#book-reader-wrapper #navigation {\\n  width: 400px;\\n  height: 100vh;\\n  position: absolute;\\n  overflow: auto;\\n  top: 0;\\n  left: 0;\\n  background: #777;\\n  -webkit-transition: -webkit-transform 0.25s ease-out;\\n  -moz-transition: -moz-transform 0.25s ease-out;\\n  -ms-transition: -moz-transform 0.25s ease-out;\\n  transition: transform 0.25s ease-out;\\n}\\n#book-reader-wrapper #navigation.fixed {\\n  position: fixed;\\n}\\n#book-reader-wrapper #navigation h1 {\\n  width: 200px;\\n  font-size: 16px;\\n  font-weight: normal;\\n  color: #fff;\\n  margin-bottom: 10px;\\n}\\n#book-reader-wrapper #navigation h2 {\\n  font-size: 14px;\\n  font-weight: normal;\\n  color: #B0B0B0;\\n  margin-bottom: 20px;\\n}\\n#book-reader-wrapper #navigation ul {\\n  padding-left: 36px;\\n  margin-left: 0;\\n  margin-top: 12px;\\n  margin-bottom: 12px;\\n  width: 340px;\\n}\\n#book-reader-wrapper #navigation ul li {\\n  list-style: decimal;\\n  margin-bottom: 10px;\\n  color: #cccddd;\\n  font-size: 12px;\\n  padding-left: 0;\\n  margin-left: 0;\\n}\\n#book-reader-wrapper #navigation ul li a {\\n  color: #ccc;\\n  text-decoration: none;\\n}\\n#book-reader-wrapper #navigation ul li a:hover {\\n  color: #fff;\\n  text-decoration: underline;\\n}\\n#book-reader-wrapper #navigation ul li a.active {\\n  color: #fff;\\n}\\n#book-reader-wrapper #navigation #cover {\\n  display: block;\\n  margin: 24px auto;\\n}\\n#book-reader-wrapper #navigation #closer {\\n  position: absolute;\\n  top: 0;\\n  right: 0;\\n  padding: 12px;\\n  color: #cccddd;\\n  width: 24px;\\n}\\n#book-reader-wrapper #navigation.closed {\\n  -webkit-transform: translate(-400px, 0);\\n  -moz-transform: translate(-400px, 0);\\n  -ms-transform: translate(-400px, 0);\\n  transform: translate(-400px, 0);\\n}\\n#book-reader-wrapper svg {\\n  display: block;\\n}\\n#book-reader-wrapper .close-x {\\n  stroke: #cccddd;\\n  fill: transparent;\\n  stroke-linecap: round;\\n  stroke-width: 5;\\n}\\n#book-reader-wrapper .close-x:hover {\\n  stroke: #fff;\\n}\\n#book-reader-wrapper #opener {\\n  position: absolute;\\n  top: 0;\\n  left: 0;\\n  padding: 10px;\\n  stroke: #E2E2E2;\\n  fill: #E2E2E2;\\n}\\n#book-reader-wrapper #opener:hover {\\n  stroke: #777;\\n  fill: #777;\\n}\\n\", \"\"]);\n// Exports\nmodule.exports = exports;\n\n\n//# sourceURL=webpack:///./modules/product-media-slider/style/book-reader.css?./node_modules/css-loader/dist/cjs.js");

/***/ })

}]);