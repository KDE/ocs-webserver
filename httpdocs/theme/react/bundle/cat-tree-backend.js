"use strict";

window.appHelpers = function () {

  function convertObjectToArray(object) {
    var newArray = [];
    for (var i in object) {
      newArray.push(object[i]);
    }
    return newArray;
  }

  function getSelectedCategory(categories, categoryId) {
    var selectedCategory = void 0;
    categories.forEach(function (cat, catIndex) {
      if (!selectedCategory) {
        if (parseInt(cat.id) === categoryId) {
          selectedCategory = cat;
        } else {
          if (cat.has_children === true) {
            var catChildren = appHelpers.convertObjectToArray(cat.children);
            selectedCategory = appHelpers.getSelectedCategory(catChildren, categoryId);
          }
        }
      }
    });
    return selectedCategory;
  }

  function getCategoryType(selectedCategories, selectedCategoryId, categoryId) {
    var categoryType = void 0;
    if (parseInt(categoryId) === selectedCategoryId) {
      categoryType = "selected";
    } else {
      selectedCategories.forEach(function (selectedCat, index) {
        if (selectedCat.id === categoryId) {
          categoryType = "parent";
        }
      });
    }
    return categoryType;
  }

  function generateCategoryLink(baseUrl, urlContext, catId, locationHref) {
    if (window.baseUrl !== window.location.origin) {
      baseUrl = window.location.origin;
    }
    var link = baseUrl + urlContext + "/browse/";
    if (catId !== "all") {
      link += "cat/" + catId + "/";
    }
    if (locationHref.indexOf('ord') > -1) {
      link += "ord/" + locationHref.split('/ord/')[1];
    }
    return link;
  }

  function sortArrayAlphabeticallyByTitle(a, b) {
    var titleA = a.title.toLowerCase();
    var titleB = b.title.toLowerCase();
    if (titleA < titleB) {
      return -1;
    }
    if (titleA > titleB) {
      return 1;
    }
    return 0;
  }

  function getDeviceFromWidth(width) {
    var device = void 0;
    if (width >= 910) {
      device = "large";
    } else if (width < 910 && width >= 610) {
      device = "mid";
    } else if (width < 610) {
      device = "tablet";
    }
    return device;
  }

  function getUrlContext(href) {
    var urlContext = "";
    if (href.indexOf('/s/') > -1) {
      urlContext = "/s/" + href.split('/s/')[1].split('/')[0];
    }
    return urlContext;
  }

  function getAllCatItemCssClass(href, baseUrl, urlContext, categoryId) {
    if (baseUrl !== window.location.origin) {
      baseUrl = window.location.origin;
    }
    var allCatItemCssClass = void 0;
    if (categoryId && categoryId !== 0) {
      allCatItemCssClass = "";
    } else {
      if (href === baseUrl + urlContext || href === baseUrl + urlContext + "/browse/" || href === baseUrl + urlContext + "/browse/ord/latest/" || href === baseUrl + urlContext + "/browse/ord/top/" || href === "https://store.kde.org" || href === "https://store.kde.org/browse/ord/latest/" || href === "https://store.kde.org/browse/ord/top/" || href === "https://addons.videolan.org" || href === "https://addons.videolan.org/browse/ord/latest/" || href === "https://addons.videolan.org/browse/ord/top/" || href === "https://share.krita.org/" || href === "https://share.krita.org/browse/ord/latest/" || href === "https://share.krita.org/browse/ord/top/") {
        allCatItemCssClass = "active";
      }
    }
    return allCatItemCssClass;
  }

  return {
    convertObjectToArray: convertObjectToArray,
    getSelectedCategory: getSelectedCategory,
    getCategoryType: getCategoryType,
    generateCategoryLink: generateCategoryLink,
    sortArrayAlphabeticallyByTitle: sortArrayAlphabeticallyByTitle,
    getDeviceFromWidth: getDeviceFromWidth,
    getUrlContext: getUrlContext,
    getAllCatItemCssClass: getAllCatItemCssClass
  };
}();
"use strict";

var _slicedToArray = function () { function sliceIterator(arr, i) { var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"]) _i["return"](); } finally { if (_d) throw _e; } } return _arr; } return function (arr, i) { if (Array.isArray(arr)) { return arr; } else if (Symbol.iterator in Object(arr)) { return sliceIterator(arr, i); } else { throw new TypeError("Invalid attempt to destructure non-iterable instance"); } }; }();

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var CategoryTree = function (_React$Component) {
  _inherits(CategoryTree, _React$Component);

  function CategoryTree(props) {
    _classCallCheck(this, CategoryTree);

    var _this = _possibleConstructorReturn(this, (CategoryTree.__proto__ || Object.getPrototypeOf(CategoryTree)).call(this, props));

    _this.state = {
      categories: window.catTree,
      categoryId: window.categoryId,
      catTreeCssClass: "",
      selectedCategories: [],
      showCatTree: false,
      backendView: window.backendView,
      loading: true
    };
    _this.getSelectedCategories = _this.getSelectedCategories.bind(_this);
    _this.updateDimensions = _this.updateDimensions.bind(_this);
    _this.toggleCatTree = _this.toggleCatTree.bind(_this);
    return _this;
  }

  _createClass(CategoryTree, [{
    key: "componentWillMount",
    value: function componentWillMount() {
      this.updateDimensions();
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      window.removeEventListener("resize", this.updateDimensions);
    }
  }, {
    key: "componentDidMount",
    value: function componentDidMount() {
      window.addEventListener("resize", this.updateDimensions);
      var urlContext = appHelpers.getUrlContext(window.location.href);
      this.setState({ urlContext: urlContext }, function () {
        if (this.state.categoryId && this.state.categoryId !== 0) {
          this.getSelectedCategories(this.state.categories, this.state.categoryId);
        } else {
          this.setState({ loading: false });
        }
      });
    }
  }, {
    key: "getSelectedCategories",
    value: function getSelectedCategories(categories, catId) {
      var selectedCategory = appHelpers.getSelectedCategory(this.state.categories, catId);
      var selectedCategories = this.state.selectedCategories;
      if (typeof selectedCategory !== 'undefined') {
        selectedCategory.selectedIndex = selectedCategories.length;
        selectedCategories.push(selectedCategory);
      }
      this.setState({ selectedCategories: selectedCategories }, function () {
        if (selectedCategory && selectedCategory.parent_id) {
          this.getSelectedCategories(categories, parseInt(selectedCategory.parent_id));
        } else {
          this.setState({ loading: false });
        }
      });
    }
  }, {
    key: "updateDimensions",
    value: function updateDimensions() {
      var device = appHelpers.getDeviceFromWidth(window.innerWidth);
      this.setState({ device: device });
    }
  }, {
    key: "toggleCatTree",
    value: function toggleCatTree() {
      var showCatTree = this.state.showCatTree === true ? false : true;
      var catTreeCssClass = this.state.catTreeCssClass === "open" ? "" : "open";
      this.setState({ showCatTree: showCatTree, catTreeCssClass: catTreeCssClass });
    }
  }, {
    key: "render",
    value: function render() {
      var categoryTreeDisplay = void 0,
          selectedCategoryDisplay = void 0;
      if (!this.state.loading) {

        if (this.state.device === "tablet" && this.state.selectedCategories && this.state.selectedCategories.length > 0) {
          selectedCategoryDisplay = React.createElement(SelectedCategory, {
            categoryId: this.state.categoryId,
            selectedCategory: this.state.selectedCategories[0],
            selectedCategories: this.state.selectedCategories,
            onCatTreeToggle: this.toggleCatTree
          });
        }
        if (this.state.device === "tablet" && this.state.showCatTree || this.state.device !== "tablet" || this.state.selectedCategories && this.state.selectedCategories.length === 0) {
          if (this.state.categories) {
            var self = this;
            var categoryTree = this.state.categories.sort(appHelpers.sortArrayAlphabeticallyByTitle).map(function (cat, index) {
              return React.createElement(CategoryItem, {
                key: index,
                category: cat,
                categoryId: self.state.categoryId,
                urlContext: self.state.urlContext,
                selectedCategories: self.state.selectedCategories,
                backendView: self.state.backendView
              });
            });

            var allCatItemCssClass = appHelpers.getAllCatItemCssClass(window.location.href, window.baseUrl, this.state.urlContext, this.state.categoryId);
            var baseUrl = void 0;
            if (window.baseUrl !== window.location.origin) {
              baseUrl = window.location.origin;
            }

            var categoryItemLink = appHelpers.generateCategoryLink(window.baseUrl, this.state.urlContext, "all", window.location.href);

            categoryTreeDisplay = React.createElement(
              "ul",
              { className: "main-list" },
              React.createElement(
                "li",
                { className: "cat-item" + " " + allCatItemCssClass },
                React.createElement(
                  "a",
                  { href: categoryItemLink },
                  React.createElement(
                    "span",
                    { className: "title" },
                    "All"
                  )
                )
              ),
              categoryTree
            );
          }
        }
      }

      return React.createElement(
        "div",
        { id: "category-tree", className: this.state.device + " " + this.state.catTreeCssClass },
        selectedCategoryDisplay,
        categoryTreeDisplay
      );
    }
  }]);

  return CategoryTree;
}(React.Component);

var CategoryItem = function (_React$Component2) {
  _inherits(CategoryItem, _React$Component2);

  function CategoryItem(props) {
    _classCallCheck(this, CategoryItem);

    var _this2 = _possibleConstructorReturn(this, (CategoryItem.__proto__ || Object.getPrototypeOf(CategoryItem)).call(this, props));

    _this2.state = {
      showSubmenu: false
    };
    _this2.toggleSubmenu = _this2.toggleSubmenu.bind(_this2);
    return _this2;
  }

  _createClass(CategoryItem, [{
    key: "toggleSubmenu",
    value: function toggleSubmenu() {
      console.log('toggle sub menu');
      var showSubmenu = this.state.showSubmenu === true ? false : true;
      console.log(showSubmenu);
      this.setState({ showSubmenu: showSubmenu });
    }
  }, {
    key: "render",
    value: function render() {
      var categoryChildrenDisplay = void 0;

      var categoryType = appHelpers.getCategoryType(this.props.selectedCategories, this.props.categoryId, this.props.category.id);
      if (this.props.category.has_children === true && categoryType && this.props.lastChild !== true || this.props.category.has_children === true && this.props.backendView === true && this.state.showSubmenu === true) {

        var self = this;

        var lastChild = void 0;
        if (categoryType === "selected") {
          lastChild = true;
        }

        var children = appHelpers.convertObjectToArray(this.props.category.children);
        var categoryChildren = children.sort(appHelpers.sortArrayAlphabeticallyByTitle).map(function (cat, index) {
          return React.createElement(CategoryItem, {
            key: index,
            category: cat,
            categoryId: self.props.categoryId,
            urlContext: self.props.urlContext,
            selectedCategories: self.props.selectedCategories,
            lastChild: lastChild,
            parent: self.props.category,
            backendView: self.props.backendView
          });
        });

        categoryChildrenDisplay = React.createElement(
          "ul",
          null,
          categoryChildren
        );
      }

      var categoryItemClass = "cat-item";
      if (this.props.categoryId === parseInt(this.props.category.id)) {
        categoryItemClass += " active";
      }

      var productCountDisplay = void 0;
      if (this.props.category.product_count !== "0") {
        productCountDisplay = this.props.category.product_count;
      }

      var categoryItemLink = appHelpers.generateCategoryLink(window.baseUrl, this.props.urlContext, this.props.category.id, window.location.href);

      var catItemContentDisplay = void 0;
      if (this.props.backendView === true) {

        var submenuToggleDisplay = void 0;
        if (this.props.category.has_children === true) {
          console.log(this.props.category.title);
          console.log(this.props.category.has_children);
          if (this.state.showSubmenu === true) {
            submenuToggleDisplay = "[-]";
          } else {
            submenuToggleDisplay = "[+]";
          }
        }

        catItemContentDisplay = React.createElement(
          "span",
          null,
          React.createElement(
            "span",
            { className: "title" },
            React.createElement(
              "a",
              { href: categoryItemLink },
              this.props.category.title
            )
          ),
          React.createElement(
            "span",
            { className: "product-counter" },
            productCountDisplay
          ),
          React.createElement(
            "span",
            { className: "submenu-toggle", onClick: this.toggleSubmenu },
            submenuToggleDisplay
          )
        );
      } else {
        catItemContentDisplay = React.createElement(
          "a",
          { href: categoryItemLink },
          React.createElement(
            "span",
            { className: "title" },
            this.props.category.title
          ),
          React.createElement(
            "span",
            { className: "product-counter" },
            productCountDisplay
          )
        );
      }

      return React.createElement(
        "li",
        { id: "cat-" + this.props.category.id, className: categoryItemClass },
        catItemContentDisplay,
        categoryChildrenDisplay
      );
    }
  }]);

  return CategoryItem;
}(React.Component);

var SelectedCategory = function (_React$Component3) {
  _inherits(SelectedCategory, _React$Component3);

  function SelectedCategory(props) {
    _classCallCheck(this, SelectedCategory);

    var _this3 = _possibleConstructorReturn(this, (SelectedCategory.__proto__ || Object.getPrototypeOf(SelectedCategory)).call(this, props));

    _this3.state = {};
    return _this3;
  }

  _createClass(SelectedCategory, [{
    key: "render",
    value: function render() {
      var selectedCategoryDisplay = void 0;
      if (this.props.selectedCategory) {
        selectedCategoryDisplay = React.createElement(
          "a",
          { onClick: this.props.onCatTreeToggle },
          this.props.selectedCategory.title
        );
      }

      var selectedCategoriesDisplay = void 0;
      if (this.props.selectedCategories) {
        var selectedCategoriesReverse = this.props.selectedCategories.slice(0);
        selectedCategoriesDisplay = selectedCategoriesReverse.reverse().map(function (sc, index) {
          return React.createElement(
            "a",
            { key: index },
            sc.title
          );
        });
      }

      return React.createElement(
        "div",
        { onClick: this.props.onCatTreeToggle, id: "selected-category-tree-item" },
        selectedCategoriesDisplay,
        React.createElement("span", { className: "selected-category-arrow-down" })
      );
    }
  }]);

  return SelectedCategory;
}(React.Component);

function CategorySidePanel() {
  var _React$useState = React.useState(window.catTree),
      _React$useState2 = _slicedToArray(_React$useState, 2),
      categories = _React$useState2[0],
      setCategoies = _React$useState2[1];

  var _React$useState3 = React.useState(window.categoryId),
      _React$useState4 = _slicedToArray(_React$useState3, 2),
      categoryId = _React$useState4[0],
      setCategoryId = _React$useState4[1];

  var _React$useState5 = React.useState(''),
      _React$useState6 = _slicedToArray(_React$useState5, 2),
      catTreeSccClass = _React$useState6[0],
      setCatTreeCssClass = _React$useState6[1];

  var _React$useState7 = React.useState(false),
      _React$useState8 = _slicedToArray(_React$useState7, 2),
      showCatTree = _React$useState8[0],
      setShowCatTree = _React$useState8[1];

  var _React$useState9 = React.useState(window.backendView),
      _React$useState10 = _slicedToArray(_React$useState9, 2),
      backendView = _React$useState10[0],
      setBackendView = _React$useState10[1];

  var _React$useState11 = React.useState(true),
      _React$useState12 = _slicedToArray(_React$useState11, 2),
      loading = _React$useState12[0],
      setLoading = _React$useState12[1];

  console.log(categories);

  return React.createElement(
    "div",
    { id: "sidebar-container" },
    React.createElement(CategoryTree, null),
    React.createElement(
      "div",
      { id: "category-menu-panels-container" },
      React.createElement("div", { id: "category-menu-panels-slider" })
    )
  );
}

ReactDOM.render(React.createElement(CategorySidePanel, null), document.getElementById('category-tree-container'));
