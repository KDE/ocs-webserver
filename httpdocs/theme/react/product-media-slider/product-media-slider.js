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

eval("throw new Error(\"Module build failed (from ./node_modules/babel-loader/lib/index.js):\\nSyntaxError: C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\app\\\\index.js: Unexpected token, expected \\\";\\\" (29:26)\\n\\n\\u001b[0m \\u001b[90m 27 | \\u001b[39m\\u001b[0m\\n\\u001b[0m \\u001b[90m 28 | \\u001b[39m  \\u001b[90m// init product media slider\\u001b[39m\\u001b[0m\\n\\u001b[0m\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 29 | \\u001b[39m  initProductMediaSlider(){\\u001b[0m\\n\\u001b[0m \\u001b[90m    | \\u001b[39m                          \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\u001b[0m\\n\\u001b[0m \\u001b[90m 30 | \\u001b[39m    window\\u001b[33m.\\u001b[39maddEventListener(\\u001b[32m\\\"resize\\\"\\u001b[39m\\u001b[33m,\\u001b[39m updateDimensions)\\u001b[33m;\\u001b[39m\\u001b[0m\\n\\u001b[0m \\u001b[90m 31 | \\u001b[39m    window\\u001b[33m.\\u001b[39maddEventListener(\\u001b[32m\\\"orientationchange\\\"\\u001b[39m\\u001b[33m,\\u001b[39m updateDimensions)\\u001b[33m;\\u001b[39m\\u001b[0m\\n\\u001b[0m \\u001b[90m 32 | \\u001b[39m    \\u001b[36mif\\u001b[39m (window\\u001b[33m.\\u001b[39mfilesJson){\\u001b[0m\\n    at Object.raise (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:6322:17)\\n    at Object.unexpected (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:7638:16)\\n    at Object.semicolon (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:7620:40)\\n    at Object.parseExpressionStatement (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10299:10)\\n    at Object.parseStatementContent (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9897:19)\\n    at Object.parseStatement (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9763:17)\\n    at Object.parseBlockOrModuleBlockBody (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10340:25)\\n    at Object.parseBlockBody (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10327:10)\\n    at Object.parseBlock (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10311:10)\\n    at Object.parseFunctionBody (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9382:24)\\n    at Object.parseFunctionBodyAndFinish (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9352:10)\\n    at withTopicForbiddingContext (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10474:12)\\n    at Object.withTopicForbiddingContext (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9657:14)\\n    at Object.parseFunction (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10473:10)\\n    at Object.parseFunctionStatement (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10111:17)\\n    at Object.parseStatementContent (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9801:21)\\n    at Object.parseStatement (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9763:17)\\n    at Object.parseBlockOrModuleBlockBody (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10340:25)\\n    at Object.parseBlockBody (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:10327:10)\\n    at Object.parseTopLevel (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:9692:10)\\n    at Object.parse (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:11209:17)\\n    at parse (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\parser\\\\lib\\\\index.js:11245:38)\\n    at parser (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\core\\\\lib\\\\transformation\\\\normalize-file.js:170:34)\\n    at normalizeFile (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\core\\\\lib\\\\transformation\\\\normalize-file.js:138:11)\\n    at runSync (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\core\\\\lib\\\\transformation\\\\index.js:44:43)\\n    at runAsync (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\core\\\\lib\\\\transformation\\\\index.js:35:14)\\n    at process.nextTick (C:\\\\Apache24\\\\htdocs\\\\ocs-webserver\\\\httpdocs\\\\theme\\\\react\\\\product-media-slider\\\\node_modules\\\\@babel\\\\core\\\\lib\\\\transform.js:34:34)\\n    at process._tickCallback (internal/process/next_tick.js:61:11)\");\n\n//# sourceURL=webpack:///./app/index.js?");

/***/ })

/******/ });