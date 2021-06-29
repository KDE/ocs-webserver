(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[31],{

/***/ "./modules/common/pagination.js":
/*!**************************************!*\
  !*** ./modules/common/pagination.js ***!
  \**************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\nfunction Pagination(props) {\n  function onPaginationButtonClick(page) {\n    props.onPageChange(page);\n  }\n\n  var firstPageNumber = 0,\n      lastPageNumber = props.numberOfPages;\n\n  if (props.numberOfPages > 10) {\n    lastPageNumber = 10;\n\n    if (props.currentPage - 1 > 5) {\n      firstPageNumber = props.currentPage - 5;\n      lastPageNumber = props.currentPage + 5;\n\n      if (lastPageNumber > props.numberOfPages) {\n        lastPageNumber = lastPageNumber + (props.numberOfPages - lastPageNumber);\n        firstPageNumber = firstPageNumber - (5 - (props.numberOfPages - props.currentPage));\n      }\n    }\n  }\n\n  var paginationArray = [];\n\n  for (var i = firstPageNumber; i < lastPageNumber; i++) {\n    paginationArray.push(i);\n  }\n\n  var paginationDisplay = paginationArray.map(function (page, index) {\n    return /*#__PURE__*/React.createElement(PaginationButton, {\n      type: props.type,\n      onPaginationButtonClick: onPaginationButtonClick,\n      key: index,\n      currentPage: props.currentPage,\n      page: page\n    });\n  });\n  return /*#__PURE__*/React.createElement(\"div\", {\n    className: \"pagination-container \" + props.type\n  }, /*#__PURE__*/React.createElement(\"ul\", {\n    className: \"pagination\"\n  }, /*#__PURE__*/React.createElement(PaginationButton, {\n    onPaginationButtonClick: onPaginationButtonClick,\n    currentPage: props.currentPage,\n    numberOfPages: props.numberOfPages,\n    page: \"-1\",\n    type: props.type\n  }), paginationDisplay, /*#__PURE__*/React.createElement(PaginationButton, {\n    onPaginationButtonClick: onPaginationButtonClick,\n    currentPage: props.currentPage,\n    numberOfPages: props.numberOfPages,\n    page: \"+1\",\n    type: props.type\n  })));\n}\n\nfunction PaginationButton(props) {\n  var pageLink = props.page + 1,\n      pageDisplay = props.page + 1,\n      buttonCssStyle = {\n    cursor: \"pointer\",\n    padding: \"4px 8px\"\n  };\n  if (props.type === \"browse\") buttonCssStyle.padding = \"4px\";\n\n  if (props.page === \"-1\") {\n    pageLink = props.currentPage - 1;\n    pageDisplay = /*#__PURE__*/React.createElement(\"span\", {\n      className: \"glyphicon glyphicon-chevron-left\"\n    });\n\n    if (props.type === \"browse\") {\n      pageDisplay = /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(\"span\", {\n        className: \"glyphicon glyphicon-chevron-left\"\n      }), \" Prev\");\n    }\n\n    if (props.currentPage === 1) {\n      buttonCssStyle.cursor = \"no-drop\";\n      if (props.type === \"browse\") pageDisplay = \"\";\n    }\n  } else if (props.page === \"+1\") {\n    pageLink = props.currentPage + 1;\n    pageDisplay = /*#__PURE__*/React.createElement(\"span\", {\n      className: \"glyphicon glyphicon-chevron-right\"\n    });\n\n    if (props.type === \"browse\") {\n      pageDisplay = /*#__PURE__*/React.createElement(React.Fragment, null, \"Next \", /*#__PURE__*/React.createElement(\"span\", {\n        className: \"glyphicon glyphicon-chevron-right\"\n      }));\n    }\n\n    if (pageLink > props.numberOfPages) {\n      buttonCssStyle.cursor = \"no-drop\";\n      if (props.type === \"browse\") pageDisplay = \"\";\n    }\n  }\n\n  function onPaginationButtonClick() {\n    var loadPage = true;\n    if (props.page === \"-1\" && props.currentPage === 1 || props.page === \"+1\" && pageLink > props.numberOfPages) loadPage = false;\n    if (loadPage === true) props.onPaginationButtonClick(pageLink);\n  }\n\n  return /*#__PURE__*/React.createElement(\"li\", {\n    className: props.currentPage === props.page + 1 ? \"active\" : \"\"\n  }, /*#__PURE__*/React.createElement(\"a\", {\n    style: buttonCssStyle,\n    onClick: onPaginationButtonClick\n  }, /*#__PURE__*/React.createElement(\"span\", null, pageDisplay)));\n}\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Pagination);\n\n//# sourceURL=webpack:///./modules/common/pagination.js?");

/***/ })

}]);