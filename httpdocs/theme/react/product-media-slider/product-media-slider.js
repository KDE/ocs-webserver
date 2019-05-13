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
/******/ 	return __webpack_require__(__webpack_require__.s = "./app/product-media-slider.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./app/product-media-slider.js":
/*!*************************************!*\
  !*** ./app/product-media-slider.js ***!
  \*************************************/
/*! no exports provided */
/***/ (function(module, exports) {

eval("throw new Error(\"Module build failed (from ./node_modules/babel-loader/lib/index.js):\\nSyntaxError: D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\app\\\\product-media-slider.js: Unexpected token, expected \\\",\\\" (199:13)\\n\\n\\u001b[0m \\u001b[90m 197 | \\u001b[39m    thumbSliderStyle \\u001b[33m=\\u001b[39m {\\u001b[0m\\n\\u001b[0m \\u001b[90m 198 | \\u001b[39m      position\\u001b[33m:\\u001b[39m\\u001b[32m'absolute'\\u001b[39m\\u001b[33m,\\u001b[39m\\u001b[0m\\n\\u001b[0m\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 199 | \\u001b[39m      top\\u001b[33m:\\u001b[39m\\u001b[32m'0'\\u001b[39m\\u001b[33m;\\u001b[39m\\u001b[0m\\n\\u001b[0m \\u001b[90m     | \\u001b[39m             \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\u001b[0m\\n\\u001b[0m \\u001b[90m 200 | \\u001b[39m      width\\u001b[33m:\\u001b[39mthumbSliderWidth\\u001b[33m+\\u001b[39m\\u001b[32m'px'\\u001b[39m\\u001b[33m,\\u001b[39m\\u001b[0m\\n\\u001b[0m \\u001b[90m 201 | \\u001b[39m      left\\u001b[33m:\\u001b[39m\\u001b[32m'-'\\u001b[39m \\u001b[33m+\\u001b[39m thumbSliderPosition \\u001b[33m+\\u001b[39m\\u001b[32m'px'\\u001b[39m\\u001b[0m\\n\\u001b[0m \\u001b[90m 202 | \\u001b[39m    }\\u001b[0m\\n    at Object.raise (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:6322:17)\\n    at Object.unexpected (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:7638:16)\\n    at Object.expect (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:7624:28)\\n    at Object.parseObj (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9108:14)\\n    at Object.parseExprAtom (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8745:21)\\n    at Object.parseExprAtom (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:3596:20)\\n    at Object.parseExprSubscripts (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8385:23)\\n    at Object.parseMaybeUnary (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8365:21)\\n    at Object.parseExprOps (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8252:23)\\n    at Object.parseMaybeConditional (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8225:23)\\n    at Object.parseMaybeAssign (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8172:21)\\n    at Object.parseMaybeAssign (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8211:25)\\n    at Object.parseExpression (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:8120:23)\\n    at Object.parseStatementContent (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9892:23)\\n    at Object.parseStatement (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9763:17)\\n    at Object.parseBlockOrModuleBlockBody (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10340:25)\\n    at Object.parseBlockBody (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10327:10)\\n    at Object.parseBlock (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10311:10)\\n    at Object.parseStatementContent (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9839:21)\\n    at Object.parseStatement (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9763:17)\\n    at Object.parseIfStatement (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10117:28)\\n    at Object.parseStatementContent (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9808:21)\\n    at Object.parseStatement (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9763:17)\\n    at Object.parseBlockOrModuleBlockBody (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10340:25)\\n    at Object.parseBlockBody (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10327:10)\\n    at Object.parseBlock (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10311:10)\\n    at Object.parseFunctionBody (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9382:24)\\n    at Object.parseFunctionBodyAndFinish (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9352:10)\\n    at withTopicForbiddingContext (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10474:12)\\n    at Object.withTopicForbiddingContext (D:\\\\Work\\\\Web\\\\OCS\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9657:14)\");\n\n//# sourceURL=webpack:///./app/product-media-slider.js?");

/***/ })

/******/ });