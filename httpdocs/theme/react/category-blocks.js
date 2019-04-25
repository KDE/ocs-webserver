'use strict';

var _slicedToArray = function () { function sliceIterator(arr, i) { var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"]) _i["return"](); } finally { if (_d) throw _e; } } return _arr; } return function (arr, i) { if (Array.isArray(arr)) { return arr; } else if (Symbol.iterator in Object(arr)) { return sliceIterator(arr, i); } else { throw new TypeError("Invalid attempt to destructure non-iterable instance"); } }; }();

function CategoryBlocks() {

    var catList = [{ title: 'All', product_count: '427' }, { title: 'Audio', product_count: '47' }, { title: 'Education', product_count: '27' }, { title: 'Games', product_count: '17' }, { title: 'Graphics', product_count: '42' }, { title: 'Internet', product_count: '427' }, { title: 'Office', product_count: '427' }, { title: 'Programming', product_count: '427' }, { title: 'System & Tools', product_count: '427' }, { title: 'Video', product_count: '427' }];

    var _useState = useState(catList),
        _useState2 = _slicedToArray(_useState, 2),
        categories = _useState2[0],
        setCategories = _useState2[1];

    var categoriesDisplay = void 0;
    if (categories) categoriesDisplay = categories.map(function (c, index) {
        return React.createElement(CategoryBlockItem, { category: c });
    });
    return React.createElement(
        'div',
        { id: 'category-blocks' },
        React.createElement(
            'div',
            { className: 'container aih-container aih-section' },
            React.createElement(
                'div',
                { classNAme: 'aih-row' },
                categoriesDisplay
            )
        )
    );
}

function CategoryBlockItem(props) {

    return React.createElement(
        'a',
        { href: '#' },
        React.createElement(
            'div',
            { className: 'aih-card' },
            React.createElement('div', { className: 'aih-ribbon aih-all' }),
            React.createElement('img', { className: 'aih-thumb', src: '/theme/react/assets/img/aih-all.png' }),
            React.createElement(
                'div',
                { className: 'aih-content' },
                React.createElement(
                    'h3',
                    { className: 'aih-title' },
                    'All'
                ),
                React.createElement(
                    'p',
                    { className: 'aih-counter' },
                    '427 ',
                    React.createElement(
                        'span',
                        null,
                        'products'
                    )
                )
            )
        )
    );
}

var rootElement = document.getElementById("category-blocks-container");
ReactDOM.render(React.createElement(CategoryBlocks, null), rootElement);
