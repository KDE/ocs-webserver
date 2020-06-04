"use strict";

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/**
 * Timeago is a jQuery plugin that makes it easy to support automatically
 * updating fuzzy timestamps (e.g. "4 minutes ago" or "about 1 day ago").
 *
 * @name timeago
 * @version 1.6.4
 * @requires jQuery v1.2.3+
 * @author Ryan McGeary
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 *
 * For usage and examples, visit:
 * http://timeago.yarp.com/
 *
 * Copyright (c) 2008-2017, Ryan McGeary (ryan -[at]- mcgeary [*dot*] org)
 */
(function (factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module.
    define(['jquery'], factory);
  } else if ((typeof module === "undefined" ? "undefined" : _typeof(module)) === 'object' && _typeof(module.exports) === 'object') {
    factory(require('jquery'));
  } else {
    // Browser globals
    factory(jQuery);
  }
})(function ($) {
  $.timeago = function (timestamp) {
    if (timestamp instanceof Date) {
      return inWords(timestamp);
    } else if (typeof timestamp === "string") {
      return inWords($.timeago.parse(timestamp));
    } else if (typeof timestamp === "number") {
      return inWords(new Date(timestamp));
    } else {
      return inWords($.timeago.datetime(timestamp));
    }
  };

  var $t = $.timeago;
  $.extend($.timeago, {
    settings: {
      refreshMillis: 60000,
      allowPast: true,
      allowFuture: false,
      localeTitle: false,
      cutoff: 0,
      autoDispose: true,
      strings: {
        prefixAgo: null,
        prefixFromNow: null,
        suffixAgo: "ago",
        suffixFromNow: "from now",
        inPast: 'any moment now',
        seconds: "less than a minute",
        minute: "about a minute",
        minutes: "%d minutes",
        hour: "about an hour",
        hours: "about %d hours",
        day: "a day",
        days: "%d days",
        month: "about a month",
        months: "%d months",
        year: "about a year",
        years: "%d years",
        wordSeparator: " ",
        numbers: []
      }
    },
    inWords: function inWords(distanceMillis) {
      if (!this.settings.allowPast && !this.settings.allowFuture) {
        throw 'timeago allowPast and allowFuture settings can not both be set to false.';
      }

      var $l = this.settings.strings;
      var prefix = $l.prefixAgo;
      var suffix = $l.suffixAgo;

      if (this.settings.allowFuture) {
        if (distanceMillis < 0) {
          prefix = $l.prefixFromNow;
          suffix = $l.suffixFromNow;
        }
      }

      if (!this.settings.allowPast && distanceMillis >= 0) {
        return this.settings.strings.inPast;
      }

      var seconds = Math.abs(distanceMillis) / 1000;
      var minutes = seconds / 60;
      var hours = minutes / 60;
      var days = hours / 24;
      var years = days / 365;

      function substitute(stringOrFunction, number) {
        var string = $.isFunction(stringOrFunction) ? stringOrFunction(number, distanceMillis) : stringOrFunction;
        var value = $l.numbers && $l.numbers[number] || number;
        return string.replace(/%d/i, value);
      }

      var words = seconds < 45 && substitute($l.seconds, Math.round(seconds)) || seconds < 90 && substitute($l.minute, 1) || minutes < 45 && substitute($l.minutes, Math.round(minutes)) || minutes < 90 && substitute($l.hour, 1) || hours < 24 && substitute($l.hours, Math.round(hours)) || hours < 42 && substitute($l.day, 1) || days < 30 && substitute($l.days, Math.round(days)) || days < 45 && substitute($l.month, 1) || days < 365 && substitute($l.months, Math.round(days / 30)) || years < 1.5 && substitute($l.year, 1) || substitute($l.years, Math.round(years));
      var separator = $l.wordSeparator || "";

      if ($l.wordSeparator === undefined) {
        separator = " ";
      }

      return $.trim([prefix, words, suffix].join(separator));
    },
    parse: function parse(iso8601) {
      var s = $.trim(iso8601);
      s = s.replace(/\.\d+/, ""); // remove milliseconds

      s = s.replace(/-/, "/").replace(/-/, "/");
      s = s.replace(/T/, " ").replace(/Z/, " UTC");
      s = s.replace(/([\+\-]\d\d)\:?(\d\d)/, " $1$2"); // -04:00 -> -0400

      s = s.replace(/([\+\-]\d\d)$/, " $100"); // +09 -> +0900

      return new Date(s);
    },
    datetime: function datetime(elem) {
      var iso8601 = $t.isTime(elem) ? $(elem).attr("datetime") : $(elem).attr("title");
      return $t.parse(iso8601);
    },
    isTime: function isTime(elem) {
      // jQuery's `is()` doesn't play well with HTML5 in IE
      return $(elem).get(0).tagName.toLowerCase() === "time"; // $(elem).is("time");
    }
  }); // functions that can be called via $(el).timeago('action')
  // init is default when no action is given
  // functions are called with context of a single element

  var functions = {
    init: function init() {
      functions.dispose.call(this);
      var refresh_el = $.proxy(refresh, this);
      refresh_el();
      var $s = $t.settings;

      if ($s.refreshMillis > 0) {
        this._timeagoInterval = setInterval(refresh_el, $s.refreshMillis);
      }
    },
    update: function update(timestamp) {
      var date = timestamp instanceof Date ? timestamp : $t.parse(timestamp);
      $(this).data('timeago', {
        datetime: date
      });

      if ($t.settings.localeTitle) {
        $(this).attr("title", date.toLocaleString());
      }

      refresh.apply(this);
    },
    updateFromDOM: function updateFromDOM() {
      $(this).data('timeago', {
        datetime: $t.parse($t.isTime(this) ? $(this).attr("datetime") : $(this).attr("title"))
      });
      refresh.apply(this);
    },
    dispose: function dispose() {
      if (this._timeagoInterval) {
        window.clearInterval(this._timeagoInterval);
        this._timeagoInterval = null;
      }
    }
  };

  $.fn.timeago = function (action, options) {
    var fn = action ? functions[action] : functions.init;

    if (!fn) {
      throw new Error("Unknown function name '" + action + "' for timeago");
    } // each over objects here and call the requested function


    this.each(function () {
      fn.call(this, options);
    });
    return this;
  };

  function refresh() {
    var $s = $t.settings; //check if it's still visible

    if ($s.autoDispose && !$.contains(document.documentElement, this)) {
      //stop if it has been removed
      $(this).timeago("dispose");
      return this;
    }

    var data = prepareData(this);

    if (!isNaN(data.datetime)) {
      if ($s.cutoff === 0 || Math.abs(distance(data.datetime)) < $s.cutoff) {
        $(this).text(inWords(data.datetime));
      } else {
        if ($(this).attr('title').length > 0) {
          $(this).text($(this).attr('title'));
        }
      }
    }

    return this;
  }

  function prepareData(element) {
    element = $(element);

    if (!element.data("timeago")) {
      element.data("timeago", {
        datetime: $t.datetime(element)
      });
      var text = $.trim(element.text());

      if ($t.settings.localeTitle) {
        element.attr("title", element.data('timeago').datetime.toLocaleString());
      } else if (text.length > 0 && !($t.isTime(element) && element.attr("title"))) {
        element.attr("title", text);
      }
    }

    return element.data("timeago");
  }

  function inWords(date) {
    return $t.inWords(distance(date));
  }

  function distance(date) {
    return new Date().getTime() - date.getTime();
  } // fix for IE6 suckage


  document.createElement("abbr");
  document.createElement("time");
});
"use strict";

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

window.hpHelpers = function () {
  function dechex(number) {
    //  discuss at: http://locutus.io/php/dechex/
    // original by: Philippe Baumann
    // bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
    // improved by: http://stackoverflow.com/questions/57803/how-to-convert-decimal-to-hex-in-javascript
    //    input by: pilus
    //   example 1: dechex(10)
    //   returns 1: 'a'
    //   example 2: dechex(47)
    //   returns 2: '2f'
    //   example 3: dechex(-1415723993)
    //   returns 3: 'ab9dc427'
    if (number < 0) {
      number = 0xFFFFFFFF + number + 1;
    }

    return parseInt(number, 10).toString(16);
  }

  function calculateScoreColor(score) {
    var blue,
        red,
        green,
        defaultColor = 200;

    if (score > 50) {
      red = defaultColor - (score - 50) * 4;
      green = defaultColor;
      blue = defaultColor - (score - 50) * 4;
    } else if (score < 51) {
      red = defaultColor;
      green = defaultColor - (score - 50) * 4;
      blue = defaultColor - (score - 50) * 4;
    }

    return "rgb(" + red + "," + green + "," + blue + ")";
  }

  return {
    dechex: dechex,
    calculateScoreColor: calculateScoreColor
  };
}();

var CarouselsModule =
/*#__PURE__*/
function (_React$Component) {
  _inherits(CarouselsModule, _React$Component);

  function CarouselsModule(props) {
    var _this;

    _classCallCheck(this, CarouselsModule);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(CarouselsModule).call(this, props));
    _this.state = {};
    _this.initCarouselModule = _this.initCarouselModule.bind(_assertThisInitialized(_this));
    _this.updateDimensions = _this.updateDimensions.bind(_assertThisInitialized(_this));
    _this.convertDataObject = _this.convertDataObject.bind(_assertThisInitialized(_this));
    return _this;
  }

  _createClass(CarouselsModule, [{
    key: "componentWillMount",
    value: function componentWillMount() {
      this.updateDimensions();
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      window.removeEventListener("resize", this.updateDimensions);
      window.removeEventListener("orientationchange", this.updateDimensions);
    }
  }, {
    key: "componentDidMount",
    value: function componentDidMount() {
      this.initCarouselModule();
    }
  }, {
    key: "initCarouselModule",
    value: function initCarouselModule() {
      window.addEventListener("resize", this.updateDimensions);
      window.addEventListener("orientationchange", this.updateDimensions);
      var env = "live";

      if (location.hostname.endsWith('cc')) {
        env = "test";
      } else if (location.hostname.endsWith('localhost')) {
        env = "test";
      }

      this.setState({
        env: env
      }, function () {
        this.convertDataObject();
      });
    }
  }, {
    key: "updateDimensions",
    value: function updateDimensions() {
      var width = window.innerWidth;
      var device;

      if (width >= 910) {
        device = "large";
      } else if (width < 910 && width >= 610) {
        device = "mid";
      } else if (width < 610) {
        device = "tablet";
      }

      this.setState({
        device: device
      });
    }
  }, {
    key: "convertDataObject",
    value: function convertDataObject() {
      var productGroupsArray = [];

      for (var i in window.data) {
        if (i !== "comments" && i !== "featureProducts") {
          var productGroup = {
            title: window.data[i].title,
            catIds: window.data[i].catIds,
            products: JSON.parse(window.data[i].products)
          };
          productGroupsArray.push(productGroup);
        }
      }

      this.setState({
        productGroupsArray: productGroupsArray,
        loading: false
      });
    }
  }, {
    key: "render",
    value: function render() {
      var _this2 = this;

      var productCarouselsContainer;

      if (this.state.loading === false) {
        productCarouselsContainer = this.state.productGroupsArray.map(function (pgc, index) {
          //if (pgc.catIds){
          return React.createElement("div", {
            key: index,
            className: "section"
          }, React.createElement("div", {
            className: "container"
          }, React.createElement(Carousel, {
            products: pgc.products,
            device: _this2.state.device,
            title: pgc.title,
            catIds: pgc.catIds,
            link: '/',
            env: _this2.state.env
          }))); //}
        });
      }

      return React.createElement("div", {
        id: "carousels-module"
      }, productCarouselsContainer);
    }
  }]);

  return CarouselsModule;
}(React.Component);

var Carousel =
/*#__PURE__*/
function (_React$Component2) {
  _inherits(Carousel, _React$Component2);

  function Carousel(props) {
    var _this3;

    _classCallCheck(this, Carousel);

    _this3 = _possibleConstructorReturn(this, _getPrototypeOf(Carousel).call(this, props));
    _this3.state = {
      products: _this3.props.products,
      disableleftArrow: true
    };
    _this3.updateDimensions = _this3.updateDimensions.bind(_assertThisInitialized(_this3));
    _this3.animateProductCarousel = _this3.animateProductCarousel.bind(_assertThisInitialized(_this3));
    _this3.getNextProductsBatch = _this3.getNextProductsBatch.bind(_assertThisInitialized(_this3));
    return _this3;
  }

  _createClass(Carousel, [{
    key: "componentWillMount",
    value: function componentWillMount() {
      window.addEventListener("resize", this.updateDimensions);
    }
  }, {
    key: "componentDidMount",
    value: function componentDidMount() {
      this.updateDimensions();
    }
  }, {
    key: "updateDimensions",
    value: function updateDimensions(animateCarousel) {
      var itemsPerRow = 5;

      if (window.hpVersion === 2) {
        if (this.props.device === 'large') {
          itemsPerRow = 6;
        } else if (this.props.device === 'mid') {
          itemsPerRow = 6;
        } else if (this.props.device === 'tablet') {
          itemsPerRow = 2;
        }
      }

      var containerWidth;

      if (window.page === "opendesktop") {
        containerWidth = $('#main-content').width();
      } else if (window.page === "appimages" || window.page === "libreoffice") {
        containerWidth = $('#introduction').find('.container').width();
      }

      var containerNumber = Math.ceil(this.state.products.length / (itemsPerRow - 1));
      var itemWidth = containerWidth / itemsPerRow;
      var sliderWidth = (containerWidth - itemWidth) * containerNumber;
      var sliderPosition = 0;

      if (this.state.sliderPosition) {
        sliderPosition = this.state.sliderPosition;
      }

      if (window.page === "appimages" || window.page === "libreoffice") {
        $('#carousel-module-container').width(containerWidth);
      }

      this.setState({
        sliderPosition: sliderPosition,
        containerWidth: containerWidth,
        containerNumber: containerNumber,
        sliderWidth: sliderWidth,
        itemWidth: itemWidth,
        itemsPerRow: itemsPerRow - 1
      }, function () {
        if (animateCarousel) {
          this.animateProductCarousel('right', animateCarousel);
        } else if (this.state.finishedProducts) {
          this.setState({
            disableRightArrow: true
          });
        }
      });
    }
  }, {
    key: "animateProductCarousel",
    value: function animateProductCarousel(dir, animateCarousel) {
      var newSliderPosition = this.state.sliderPosition;
      var endPoint = this.state.sliderWidth - (this.state.containerWidth - this.state.itemWidth);

      if (dir === 'left') {
        if (this.state.sliderPosition > 0) {
          //newSliderPosition = this.state.sliderPosition - (this.state.containerWidth - this.state.itemWidth);
          if (this.state.containerWidth < this.state.itemWidth * 3) {
            newSliderPosition = this.state.sliderPosition - this.state.itemWidth;
          } else {
            newSliderPosition = this.state.sliderPosition - this.state.itemWidth * 2;
          }
        }
      } else {
        if (Math.trunc(this.state.sliderPosition) < Math.trunc(endPoint)) {
          //newSliderPosition = this.state.sliderPosition + (this.state.containerWidth - this.state.itemWidth);
          if (this.state.containerWidth < this.state.itemWidth * 3) {
            newSliderPosition = this.state.sliderPosition + this.state.itemWidth;
          } else {
            newSliderPosition = this.state.sliderPosition + this.state.itemWidth * 2;
          }
        } else {
          newSliderPosition = 0;
          /*if (!animateCarousel){
          if (this.state.products.length >= 15 ||Ã‚ this.state.finishedProducts){
              newSliderPosition = 0;
            } else {
              this.getNextProductsBatch();
            }
          }*/
        }
      }

      this.setState({
        sliderPosition: newSliderPosition
      }, function () {
        var disableleftArrow = false;

        if (this.state.sliderPosition <= 0) {
          disableleftArrow = true;
        }

        var disableRightArrow = false;
        /*if (this.state.sliderPosition >= endPoint &&Ã‚ this.state.finishedProducts === true){
          disableRightArrow = true;
        }*/

        this.setState({
          disableRightArrow: disableRightArrow,
          disableleftArrow: disableleftArrow
        });
      });
    }
  }, {
    key: "getNextProductsBatch",
    value: function getNextProductsBatch() {
      this.setState({
        disableRightArrow: true
      }, function () {
        var limit = this.state.itemsPerRow * (this.state.containerNumber + 1) - this.state.products.length;

        if (limit <= 0) {
          limit = this.state.itemsPerRow;
        }

        var url;

        if (!this.props.catIds) {
          url = "/home/getnewactiveplingedproductjson/?limit=" + limit + "&offset=" + this.state.offset;
        } else {
          url = "/home/showlastproductsjson/?page=1&limit=" + limit + "&offset=" + this.state.offset + "&catIDs=" + this.props.catIds + "&isoriginal=0";
        }

        var self = this;
        $.ajax({
          url: url,
          cache: false
        }).done(function (response) {
          var products = self.state.products,
              finishedProducts = false,
              animateCarousel = true;

          if (response.length > 0) {
            products = products.concat(response);
          } else {
            finishedProducts = true;
            animateCarousel = false;
          }

          if (response.length < limit) {
            finishedProducts = true;
          }

          self.setState({
            products: products,
            offset: self.state.offset + response.length,
            finishedProducts: finishedProducts
          }, function () {
            self.updateDimensions(animateCarousel);
          });
        });
      });
    }
  }, {
    key: "render",
    value: function render() {
      var _this4 = this;

      var carouselItemsDisplay;

      if (this.state.products && this.state.products.length > 0) {
        var plingedProduct = false;
        if (!this.props.catIds) plingedProduct = true;
        carouselItemsDisplay = this.state.products.map(function (product, index) {
          return React.createElement(CarouselItem, {
            key: index,
            product: product,
            itemWidth: _this4.state.itemWidth,
            env: _this4.props.env,
            plingedProduct: plingedProduct
          });
        });
      }

      var carouselArrowLeftDisplay;

      if (this.state.disableleftArrow) {
        carouselArrowLeftDisplay = React.createElement("a", {
          className: "carousel-arrow arrow-left disabled"
        }, React.createElement("span", {
          className: "glyphicon glyphicon-chevron-left"
        }));
      } else {
        carouselArrowLeftDisplay = React.createElement("a", {
          onClick: function onClick() {
            return _this4.animateProductCarousel('left');
          },
          className: "carousel-arrow arrow-left"
        }, React.createElement("span", {
          className: "glyphicon glyphicon-chevron-left"
        }));
      }

      var carouselArrowRightDisplay;

      if (this.state.disableRightArrow) {
        carouselArrowRightDisplay = React.createElement("a", {
          className: "carousel-arrow arrow-right disabled"
        }, React.createElement("span", {
          className: "glyphicon glyphicon-chevron-right"
        }));
      } else {
        carouselArrowRightDisplay = React.createElement("a", {
          onClick: function onClick() {
            return _this4.animateProductCarousel('right');
          },
          className: "carousel-arrow arrow-right"
        }, React.createElement("span", {
          className: "glyphicon glyphicon-chevron-right"
        }));
      }

      var hpVersionClass = "one";
      var carouselWrapperStyling = {};
      var carouselArrowsMargin;

      if (window.hpVersion === 2 && this.state.itemWidth) {
        hpVersionClass = "two";
        var itemHeightMultiplier; // if (this.state.itemWidth > 150){

        itemHeightMultiplier = 1.35;
        /*} else {
          itemHeightMultiplier = 1.85;
        }*/

        carouselWrapperStyling = {
          "paddingLeft": this.state.itemWidth / 2,
          "paddingRight": this.state.itemWidth / 2,
          "height": this.state.itemWidth * itemHeightMultiplier
        };
        carouselArrowsMargin = this.state.itemWidth / 4;
      }

      var urlSuffix = '';

      if (window.page === "libreoffice") {
        urlSuffix = "/s/LibreOffice";
      }

      var titleLink = urlSuffix + "/browse/cat/" + this.props.catIds + "/";

      if (!this.props.catIds) {
        titleLink = "/community#plingedproductsPanel";
      } else if (this.props.catIds.indexOf(',') > 0) {
        titleLink = urlSuffix + "/browse/";
      }

      return React.createElement("div", {
        className: "product-carousel " + hpVersionClass
      }, React.createElement("div", {
        className: "product-carousel-header"
      }, React.createElement("h2", null, React.createElement("a", {
        href: titleLink
      }, this.props.title, " ", React.createElement("span", {
        className: "glyphicon glyphicon-chevron-right"
      })))), React.createElement("div", {
        className: "product-carousel-wrapper",
        style: carouselWrapperStyling
      }, React.createElement("div", {
        className: "product-carousel-left",
        style: {
          "left": carouselArrowsMargin
        }
      }, carouselArrowLeftDisplay), React.createElement("div", {
        className: "product-carousel-container"
      }, React.createElement("div", {
        className: "product-carousel-slider",
        style: {
          "width": this.state.sliderWidth,
          "left": "-" + this.state.sliderPosition + "px"
        }
      }, carouselItemsDisplay)), React.createElement("div", {
        className: "product-carousel-right",
        style: {
          "right": carouselArrowsMargin
        }
      }, carouselArrowRightDisplay)));
    }
  }]);

  return Carousel;
}(React.Component);

var CarouselItem =
/*#__PURE__*/
function (_React$Component3) {
  _inherits(CarouselItem, _React$Component3);

  function CarouselItem(props) {
    var _this5;

    _classCallCheck(this, CarouselItem);

    _this5 = _possibleConstructorReturn(this, _getPrototypeOf(CarouselItem).call(this, props));
    _this5.state = {};
    return _this5;
  }

  _createClass(CarouselItem, [{
    key: "render",
    value: function render() {
      var paddingTop;
      var productInfoDisplay = React.createElement("div", {
        className: "product-info"
      }, React.createElement("span", {
        className: "product-info-title"
      }, this.props.product.title), React.createElement("span", {
        className: "product-info-user"
      }, this.props.product.username));

      if (window.hpVersion === 2) {
        if (this.props.itemWidth) {
          paddingTop = this.props.itemWidth * 1.35 / 2 - 10;
        }

        var lastDate;

        if (this.props.product.changed_at) {
          lastDate = this.props.product.changed_at;
        } else {
          lastDate = this.props.product.created_at;
        }

        var cDate = new Date(lastDate); // cDate = cDate.toString();
        // const createdDate = cDate.split(' ')[1] + " " + cDate.split(' ')[2] + " " + cDate.split(' ')[3];

        var createdDate = jQuery.timeago(cDate); // const productScoreColor = window.hpHelpers.calculateScoreColor(this.props.product.laplace_score);

        var infoDisplay;
        var scoreDisplay = React.createElement("div", {
          className: "score-info"
        }, React.createElement("div", {
          className: "score-number"
        }, "Score ", (this.props.product.laplace_score / 10).toFixed(1), "%"), React.createElement("div", {
          className: "score-bar-container"
        }, React.createElement("div", {
          className: "score-bar",
          style: {
            "width": this.props.product.laplace_score / 10 + "%"
          }
        })));
        infoDisplay = scoreDisplay;

        if (this.props.plingedProduct) {
          var plingDisplay = React.createElement("div", {
            className: "plings"
          }, React.createElement("img", {
            src: "/images/system/pling-btn-active.png"
          }), this.props.product.sum_plings);
          infoDisplay = React.createElement("div", null, plingDisplay, scoreDisplay);
        }
        /*let scoreDisplay;
        if (this.props.plingedProduct){
          scoreDisplay = (
            <div className="score-info plings">
              <img src="/images/system/pling-btn-active.png" />
              {this.props.product.sum_plings}
            </div>
          );
        } else {
          scoreDisplay = (
            <div className="score-info">
              <div className="score-number">
                score {this.props.product.laplace_score + "%"}
              </div>
              <div className="score-bar-container">
                <div className={"score-bar"} style={{"width":this.props.product.laplace_score + "%"}}></div>
              </div>
            </div>
          );
        }*/


        productInfoDisplay = React.createElement("div", {
          className: "product-info"
        }, React.createElement("span", {
          className: "product-info-title"
        }, this.props.product.title), React.createElement("span", {
          className: "product-info-category"
        }, this.props.product.cat_title), React.createElement("span", {
          className: "product-info-date"
        }, createdDate), infoDisplay);
      }

      var projectUrl = "";

      if (window.page === "libreoffice") {
        projectUrl = window.baseUrl + "p/" + this.props.product.project_id;
      } else {
        projectUrl = "/p/" + this.props.product.project_id;
      }

      return React.createElement("div", {
        className: "product-carousel-item",
        style: {
          "width": this.props.itemWidth
        }
      }, React.createElement("div", {
        className: "product-carousel-item-wrapper"
      }, React.createElement("a", {
        href: projectUrl,
        style: {
          "paddingTop": paddingTop
        }
      }, React.createElement("figure", {
        style: {
          "height": paddingTop
        }
      }, React.createElement("img", {
        className: "very-rounded-corners",
        src: this.props.product.image_small
      })), productInfoDisplay)));
    }
  }]);

  return CarouselItem;
}(React.Component);

ReactDOM.render(React.createElement(CarouselsModule, null), document.getElementById('carousel-module-container'));