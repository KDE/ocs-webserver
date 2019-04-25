'use strict';

var _slicedToArray = function () { function sliceIterator(arr, i) { var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"]) _i["return"](); } finally { if (_d) throw _e; } } return _arr; } return function (arr, i) { if (Array.isArray(arr)) { return arr; } else if (Symbol.iterator in Object(arr)) { return sliceIterator(arr, i); } else { throw new TypeError("Invalid attempt to destructure non-iterable instance"); } }; }();

var _react = require('react');

var _react2 = _interopRequireDefault(_react);

var _reactDom = require('react-dom');

var _reactDom2 = _interopRequireDefault(_reactDom);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function CategoryBlocks() {

    var catList = [{ title: 'All', product_count: '427' }, { title: 'Audio', product_count: '47' }, { title: 'Education', product_count: '27' }, { title: 'Games', product_count: '17' }, { title: 'Graphics', product_count: '42' }, { title: 'Internet', product_count: '427' }, { title: 'Office', product_count: '427' }, { title: 'Programming', product_count: '427' }, { title: 'System & Tools', product_count: '427' }, { title: 'Video', product_count: '427' }];

    var _useState = (0, _react.useState)(catList),
        _useState2 = _slicedToArray(_useState, 2),
        categories = _useState2[0],
        setCategories = _useState2[1];

    _react2.default.useEffect(function () {
        console.log('hello');
    }, [categories]);
    return _react2.default.createElement(
        'div',
        { id: 'category-blocks' },
        _react2.default.createElement('div', { className: 'container aih-container aih-section' })
    );
}

var rootElement = document.getElementById("category-blocks-container");
_reactDom2.default.render(_react2.default.createElement(CategoryBlocks, null), rootElement);
