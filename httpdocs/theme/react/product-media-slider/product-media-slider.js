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

eval("throw new Error(\"Module build failed (from ./node_modules/babel-loader/lib/index.js):\\nSyntaxError: Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\app\\\\product-media-slider.js: Unexpected keyword 'if' (314:18)\\n\\n\\u001b[0m \\u001b[90m 312 | \\u001b[39m              }          \\u001b[0m\\n\\u001b[0m \\u001b[90m 313 | \\u001b[39m            } \\u001b[36melse\\u001b[39m {\\u001b[0m\\n\\u001b[0m\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 314 | \\u001b[39m              let \\u001b[36mif\\u001b[39m \\u001b[0m\\n\\u001b[0m \\u001b[90m     | \\u001b[39m                  \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\u001b[0m\\n\\u001b[0m \\u001b[90m 315 | \\u001b[39m              \\u001b[36mif\\u001b[39m (props\\u001b[33m.\\u001b[39mdisableGallery) setMediaStyle({maxHeight\\u001b[33m:\\u001b[39m\\u001b[35m360\\u001b[39m})\\u001b[0m\\n\\u001b[0m \\u001b[90m 316 | \\u001b[39m            }\\u001b[0m\\n\\u001b[0m \\u001b[90m 317 | \\u001b[39m          }\\u001b[0m\\n    at Object.raise (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:6322:17)\\n    at Object.checkReservedWord (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9504:12)\\n    at Object.parseIdentifierName (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9483:12)\\n    at Object.parseIdentifier (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9457:23)\\n    at Object.parseBindingAtom (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:7920:17)\\n    at Object.parseVarId (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10440:20)\\n    at Object.parseVar (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10412:12)\\n    at Object.parseVarStatement (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10234:10)\\n    at Object.parseStatementContent (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9830:21)\\n    at Object.parseStatement (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9763:17)\\n    at Object.parseBlockOrModuleBlockBody (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10340:25)\\n    at Object.parseBlockBody (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10327:10)\\n    at Object.parseBlock (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10311:10)\\n    at Object.parseStatementContent (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9839:21)\\n    at Object.parseStatement (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9763:17)\\n    at Object.parseIfStatement (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10118:51)\\n    at Object.parseStatementContent (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9808:21)\\n    at Object.parseStatement (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9763:17)\\n    at Object.parseBlockOrModuleBlockBody (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10340:25)\\n    at Object.parseBlockBody (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10327:10)\\n    at Object.parseBlock (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10311:10)\\n    at Object.parseStatementContent (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9839:21)\\n    at Object.parseStatement (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9763:17)\\n    at Object.parseIfStatement (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10118:51)\\n    at Object.parseStatementContent (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9808:21)\\n    at Object.parseStatement (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9763:17)\\n    at Object.parseBlockOrModuleBlockBody (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10340:25)\\n    at Object.parseBlockBody (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10327:10)\\n    at Object.parseBlock (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10311:10)\\n    at Object.parseStatementContent (Z:\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9839:21)\");\n\n//# sourceURL=webpack:///./app/product-media-slider.js?");

/***/ })

/******/ });