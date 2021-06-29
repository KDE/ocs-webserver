(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[26],{

/***/ "./modules/common/ratings-reviews.js":
/*!*******************************************!*\
  !*** ./modules/common/ratings-reviews.js ***!
  \*******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _common_helpers__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./common-helpers */ \"./modules/common/common-helpers.js\");\n/* harmony import */ var react_timeago__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react-timeago */ \"./node_modules/react-timeago/lib/index.js\");\n/* harmony import */ var react_timeago__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(react_timeago__WEBPACK_IMPORTED_MODULE_2__);\n/* harmony import */ var _style_ratings_reviews_css__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./style/ratings-reviews.css */ \"./modules/common/style/ratings-reviews.css\");\n/* harmony import */ var _style_ratings_reviews_css__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_style_ratings_reviews_css__WEBPACK_IMPORTED_MODULE_3__);\nfunction _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }\n\nfunction _nonIterableRest() { throw new TypeError(\"Invalid attempt to destructure non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\"); }\n\nfunction _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === \"string\") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === \"Object\" && o.constructor) n = o.constructor.name; if (n === \"Map\" || n === \"Set\") return Array.from(o); if (n === \"Arguments\" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }\n\nfunction _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }\n\nfunction _iterableToArrayLimit(arr, i) { if (typeof Symbol === \"undefined\" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i[\"return\"] != null) _i[\"return\"](); } finally { if (_d) throw _e; } } return _arr; }\n\nfunction _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }\n\n\n\n\n\n\nfunction RatingsReviewsModule(props) {\n  var _useState = Object(react__WEBPACK_IMPORTED_MODULE_0__[\"useState\"])(null),\n      _useState2 = _slicedToArray(_useState, 2),\n      selectedRatings = _useState2[0],\n      setSelectedRatings = _useState2[1];\n\n  var ratingsNumbersDisplay = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map(function (rn, index) {\n    var ratingsColor = Object(_common_helpers__WEBPACK_IMPORTED_MODULE_1__[\"GenerateColorBasedOnRatings\"])(rn);\n    var ratingsCounterDisplay;\n    var ratingsCounter = props.ratings.filter(function (r) {\n      return parseInt(r.score) === rn;\n    }).length;\n    if (ratingsCounter > 0) ratingsCounterDisplay = /*#__PURE__*/React.createElement(\"span\", {\n      style: {\n        color: ratingsColor\n      },\n      className: \"ratings-counter\"\n    }, ratingsCounter);\n    return /*#__PURE__*/React.createElement(\"div\", {\n      key: index,\n      onClick: function onClick(e) {\n        return setSelectedRatings(selectedRatings === rn ? null : rn);\n      },\n      className: \"ratings-number-container\"\n    }, /*#__PURE__*/React.createElement(\"span\", {\n      className: selectedRatings === parseInt(rn) ? \"selected number-display\" : \"number-display\",\n      style: {\n        \"backgroundColor\": ratingsColor\n      }\n    }, rn), ratingsCounterDisplay);\n  });\n  var ratingsReviewsDisplay;\n\n  if (props.ratings && props.ratings.length > 0) {\n    var ratings;\n    if (selectedRatings !== null) ratings = props.ratings.filter(function (r) {\n      return parseInt(r.score) === selectedRatings;\n    });else ratings = props.ratings;\n    ratingsReviewsDisplay = ratings.map(function (rating, index) {\n      return /*#__PURE__*/React.createElement(RatingsReviewsListItem, {\n        key: index,\n        rating: rating,\n        onRatingsItemClick: props.onRatingsItemClick\n      });\n    });\n  }\n\n  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(\"div\", {\n    id: \"products-ratings-container\"\n  }, /*#__PURE__*/React.createElement(\"div\", {\n    id: \"product-ratings-summary-container\"\n  }, /*#__PURE__*/React.createElement(\"div\", {\n    id: \"product-ratings-summary\"\n  }, ratingsNumbersDisplay))), /*#__PURE__*/React.createElement(\"div\", {\n    id: \"product-ratings-list\"\n  }, ratingsReviewsDisplay, /*#__PURE__*/React.createElement(RatingsReviewsListItem, {\n    type: 'info'\n  })));\n}\n\nfunction RatingsReviewsListItem(props) {\n  var r = props.rating;\n\n  function onRatingsItemClick(e) {\n    e.preventDefault();\n    props.onRatingsItemClick(\"/u/\" + r.username, r.username);\n  }\n\n  var ratingsReviewsListItemDisplay;\n\n  if (props.type === \"info\") {\n    ratingsReviewsListItemDisplay = /*#__PURE__*/React.createElement(\"div\", {\n      className: \"product-ratings-list-item\",\n      id: \"ratings-list-item-info\"\n    }, /*#__PURE__*/React.createElement(\"div\", {\n      className: \"rating-container\"\n    }, /*#__PURE__*/React.createElement(\"div\", {\n      className: \"rating-header\"\n    }, /*#__PURE__*/React.createElement(\"a\", {\n      className: \"rating-profile-image\"\n    }, /*#__PURE__*/React.createElement(\"figure\", null, /*#__PURE__*/React.createElement(\"img\", {\n      src: \"https://cdn.pling.cc/cache/40x40/img/hive/user-pics/nopic.png\"\n    }))), /*#__PURE__*/React.createElement(\"span\", {\n      style: {\n        backgroundColor: Object(_common_helpers__WEBPACK_IMPORTED_MODULE_1__[\"GenerateColorBasedOnRatings\"])(5)\n      },\n      className: \"rating-number\"\n    }, \"Base: 4 x 5.0 Ratings\"))));\n  } else {\n    var ratingsItemProfileImage = Object(_common_helpers__WEBPACK_IMPORTED_MODULE_1__[\"GenerateImageUrl\"])(r.profile_image_url, 40, 40);\n    var scoreWordDisplay = Object(_common_helpers__WEBPACK_IMPORTED_MODULE_1__[\"GenerateWordBasedOnRatingScore\"])(r.score);\n    ratingsReviewsListItemDisplay = /*#__PURE__*/React.createElement(\"div\", {\n      className: \"product-ratings-list-item\",\n      id: \"ratings-list-item-\" + r.rating_id\n    }, /*#__PURE__*/React.createElement(\"div\", {\n      className: \"rating-container\"\n    }, /*#__PURE__*/React.createElement(\"div\", {\n      className: \"rating-header\"\n    }, /*#__PURE__*/React.createElement(\"a\", {\n      onClick: function onClick(e) {\n        return onRatingsItemClick(e);\n      },\n      className: \"rating-profile-image\",\n      href: \"/u/\" + r.username\n    }, /*#__PURE__*/React.createElement(\"figure\", null, /*#__PURE__*/React.createElement(\"img\", {\n      src: ratingsItemProfileImage\n    }))), /*#__PURE__*/React.createElement(\"span\", {\n      className: \"rating-username\"\n    }, /*#__PURE__*/React.createElement(\"a\", {\n      onClick: function onClick(e) {\n        return onRatingsItemClick(e);\n      },\n      href: \"/u/\" + r.member_id\n    }, r.username)), /*#__PURE__*/React.createElement(\"span\", {\n      className: \"small light lightgrey product-update-date rating-created-at\"\n    }, /*#__PURE__*/React.createElement(react_timeago__WEBPACK_IMPORTED_MODULE_2___default.a, {\n      date: r.created_at\n    })), /*#__PURE__*/React.createElement(\"span\", {\n      style: {\n        backgroundColor: Object(_common_helpers__WEBPACK_IMPORTED_MODULE_1__[\"GenerateColorBasedOnRatings\"])(r.score)\n      },\n      className: \"rating-number\"\n    }, r.score), /*#__PURE__*/React.createElement(\"span\", {\n      className: \"rating-number-word\"\n    }, scoreWordDisplay)), /*#__PURE__*/React.createElement(\"div\", {\n      className: \"rating-text\"\n    }, r.comment_text)));\n  }\n\n  return /*#__PURE__*/React.createElement(React.Fragment, null, ratingsReviewsListItemDisplay);\n}\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (RatingsReviewsModule);\n\n//# sourceURL=webpack:///./modules/common/ratings-reviews.js?");

/***/ }),

/***/ "./modules/common/style/ratings-reviews.css":
/*!**************************************************!*\
  !*** ./modules/common/style/ratings-reviews.css ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var api = __webpack_require__(/*! ../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ \"./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js\");\n            var content = __webpack_require__(/*! !../../../node_modules/css-loader/dist/cjs.js!./ratings-reviews.css */ \"./node_modules/css-loader/dist/cjs.js!./modules/common/style/ratings-reviews.css\");\n\n            content = content.__esModule ? content.default : content;\n\n            if (typeof content === 'string') {\n              content = [[module.i, content, '']];\n            }\n\nvar options = {};\n\noptions.insert = \"head\";\noptions.singleton = false;\n\nvar update = api(content, options);\n\n\n\nmodule.exports = content.locals || {};\n\n//# sourceURL=webpack:///./modules/common/style/ratings-reviews.css?");

/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js!./modules/common/style/ratings-reviews.css":
/*!****************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js!./modules/common/style/ratings-reviews.css ***!
  \****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// Imports\nvar ___CSS_LOADER_API_IMPORT___ = __webpack_require__(/*! ../../../node_modules/css-loader/dist/runtime/api.js */ \"./node_modules/css-loader/dist/runtime/api.js\");\nexports = ___CSS_LOADER_API_IMPORT___(false);\n// Module\nexports.push([module.i, \"#products-ratings-container {\\n  width: 100%;\\n  float: left;\\n  height: auto;\\n}\\n#products-ratings-container #product-ratings-summary-container {\\n  margin: 0 auto;\\n  display: table;\\n  width: auto;\\n  margin-bottom: 30px;\\n}\\n#products-ratings-container #product-ratings-summary .ratings-number-container {\\n  float: left;\\n  margin: 0 5px;\\n  text-align: center;\\n}\\n#products-ratings-container #product-ratings-summary .ratings-number-container span.number-display {\\n  padding: 0 1em 0;\\n  color: #fff;\\n  border-radius: 0.25em;\\n  font-weight: 700;\\n  font-size: 90%;\\n  clear: both;\\n  cursor: pointer;\\n}\\n#products-ratings-container #product-ratings-summary .ratings-number-container span.number-display.selected {\\n  border: 1px solid #2185D0;\\n}\\n#products-ratings-container #product-ratings-summary .ratings-number-container .ratings-counter {\\n  display: block;\\n  cursor: pointer;\\n}\\n#product-ratings-list .product-ratings-list-item {\\n  width: 100%;\\n  float: left;\\n}\\n#product-ratings-list .product-ratings-list-item .rating-container {\\n  width: 100%;\\n  padding-left: 45px;\\n  position: relative;\\n  float: left;\\n  margin-bottom: 10px;\\n}\\n#product-ratings-list .product-ratings-list-item .rating-container .rating-header {\\n  width: 100%;\\n  font-size: 12px;\\n  margin-top: 10px;\\n}\\n#product-ratings-list .product-ratings-list-item .rating-container .rating-header .rating-profile-image {\\n  position: absolute;\\n  top: 0;\\n  left: 0;\\n  display: block;\\n  width: 40px;\\n  height: 40px;\\n}\\n#product-ratings-list .product-ratings-list-item .rating-container .rating-header .rating-profile-image figure {\\n  width: 40px;\\n  height: 40px;\\n  overflow: hidden;\\n  border-radius: 100%;\\n}\\n#product-ratings-list .product-ratings-list-item .rating-container .rating-header .rating-profile-image img {\\n  border-radius: 100%;\\n  width: 40px;\\n  border: 1px solid #ccc;\\n  height: 40px;\\n}\\n#product-ratings-list .product-ratings-list-item .rating-container .rating-header .rating-username {\\n  font-size: 16px;\\n  margin: 0 5px 0 0;\\n  font-weight: bold;\\n}\\n#product-ratings-list .product-ratings-list-item .rating-container .rating-header .rating-number {\\n  display: inline-block;\\n  padding: 0 1em 0;\\n  color: #fff;\\n  border-radius: 0.25em;\\n  font-weight: 700;\\n  font-size: 14px;\\n  margin: 0 4px;\\n}\\n#product-ratings-list .product-ratings-list-item .rating-container .rating-text {\\n  padding: 10px 0 0 0;\\n  width: 100%;\\n  float: left;\\n}\\n\", \"\"]);\n// Exports\nmodule.exports = exports;\n\n\n//# sourceURL=webpack:///./modules/common/style/ratings-reviews.css?./node_modules/css-loader/dist/cjs.js");

/***/ })

}]);