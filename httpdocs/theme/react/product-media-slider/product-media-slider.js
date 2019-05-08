/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./app/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./app/index.js":
/*!**********************!*\
  !*** ./app/index.js ***!
  \**********************/
/*! no exports provided */
/***/ (function(module, exports) {

eval("throw new Error(\"Module build failed (from ./node_modules/babel-loader/lib/index.js):\\nSyntaxError: C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\app\\\\index.js: Unexpected token, expected \\\",\\\" (72:69)\\n\\n\\u001b[0m \\u001b[90m 70 | \\u001b[39m  let slideContentDisplay\\u001b[33m;\\u001b[39m\\u001b[0m\\n\\u001b[0m \\u001b[90m 71 | \\u001b[39m  \\u001b[36mif\\u001b[39m (mediaType \\u001b[33m===\\u001b[39m \\u001b[32m\\\"embed\\\"\\u001b[39m){\\u001b[0m\\n\\u001b[0m\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 72 | \\u001b[39m    slideContentDisplay \\u001b[33m=\\u001b[39m \\u001b[33m<\\u001b[39m\\u001b[33mdiv\\u001b[39m dangerouslySetInnerHTML\\u001b[33m=\\u001b[39m{__html\\u001b[33m=\\u001b[39m{props\\u001b[33m.\\u001b[39mslideUrl}}\\u001b[33m>\\u001b[39m\\u001b[33m<\\u001b[39m\\u001b[33m/\\u001b[39m\\u001b[33mdiv\\u001b[39m\\u001b[33m>\\u001b[39m\\u001b[0m\\n\\u001b[0m \\u001b[90m    | \\u001b[39m                                                                     \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\u001b[0m\\n\\u001b[0m \\u001b[90m 73 | \\u001b[39m  } \\u001b[36melse\\u001b[39m \\u001b[36mif\\u001b[39m (mediaType \\u001b[33m===\\u001b[39m \\u001b[32m\\\"image\\\"\\u001b[39m){\\u001b[0m\\n\\u001b[0m \\u001b[90m 74 | \\u001b[39m    slideContentDisplay \\u001b[33m=\\u001b[39m \\u001b[33m<\\u001b[39m\\u001b[33mimg\\u001b[39m src\\u001b[33m=\\u001b[39m{props\\u001b[33m.\\u001b[39mslideUrl}\\u001b[33m/\\u001b[39m\\u001b[33m>\\u001b[39m\\u001b[0m\\n\\u001b[0m \\u001b[90m 75 | \\u001b[39m  } \\u001b[36melse\\u001b[39m { \\u001b[0m\\n    at Object.raise (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:6322:17)\\n    at Object.unexpected (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:7638:16)\\n    at Object.expect (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:7624:28)\\n    at Object.parseObj (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9108:14)\\n    at Object.parseExprAtom (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8745:21)\\n    at Object.parseExprAtom (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:3596:20)\\n    at Object.parseExprSubscripts (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8385:23)\\n    at Object.parseMaybeUnary (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8365:21)\\n    at Object.parseExprOps (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8252:23)\\n    at Object.parseMaybeConditional (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8225:23)\\n    at Object.parseMaybeAssign (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8172:21)\\n    at Object.parseMaybeAssign (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8211:25)\\n    at Object.parseExpression (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8120:23)\\n    at Object.jsxParseExpressionContainer (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:3452:30)\\n    at Object.jsxParseAttributeValue (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:3414:21)\\n    at Object.jsxParseAttribute (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:3470:44)\\n    at Object.jsxParseOpeningElementAfterName (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:3490:28)\\n    at Object.jsxParseOpeningElementAt (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:3483:17)\\n    at Object.jsxParseElementAt (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:3515:33)\\n    at Object.jsxParseElement (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:3584:17)\\n    at Object.parseExprAtom (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:3591:19)\\n    at Object.parseExprSubscripts (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8385:23)\\n    at Object.parseMaybeUnary (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8365:21)\\n    at Object.parseExprOps (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8252:23)\\n    at Object.parseMaybeConditional (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8225:23)\\n    at Object.parseMaybeAssign (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8172:21)\\n    at Object.parseMaybeAssign (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8211:25)\\n    at Object.parseExpression (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8120:23)\\n    at Object.parseStatementContent (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9892:23)\\n    at Object.parseStatement (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9763:17)\");\n\n//# sourceURL=webpack:///./app/index.js?");

/***/ })

/******/ });