window.appHelpers = function () {

  function convertObjectToArray(object) {
    newArray = [];
    for (i in object) {
      console.log(object);
      console.log(object[i]);
      console.log(i);
    }
    return newArray;
  }

  function getDeviceWidth(width) {
    let device;
    if (width > 1720) {
      device = "very-huge";
    } else if (width < 1720 && width > 1500) {
      device = "huge";
    } else if (width < 1500 && width > 1250) {
      device = "full";
    } else if (width < 1250 && width >= 1000) {
      device = "large";
    } else if (width < 1000 && width >= 661) {
      device = "mid";
    } else if (width < 661 && width >= 400) {
      device = "tablet";
    } else if (width < 400) {
      device = "phone";
    }
    return device;
  }

  function splitByLastDot(text) {
    var index = text.lastIndexOf('.');
    return text.slice(index + 1);
  }

  function getTimeAgo(datetime) {
    const a = timeago().format(datetime);
    return a;
  }

  function getFileSize(size) {
    if (isNaN(size)) size = 0;

    if (size < 1024) return size + ' Bytes';

    size /= 1024;

    if (size < 1024) return size.toFixed(2) + ' Kb';

    size /= 1024;

    if (size < 1024) return size.toFixed(2) + ' Mb';

    size /= 1024;

    if (size < 1024) return size.toFixed(2) + ' Gb';

    size /= 1024;

    return size.toFixed(2) + ' Tb';
  }

  function generateFilterUrl(location, currentCat) {
    let link = {};
    if (currentCat && currentCat !== 0) {
      link.base = "/browse/cat/" + currentCat + "/ord/";
    } else {
      link.base = "/browse/ord/";
    }
    if (location.search) link.search = location.search;
    return link;
  }

  function generateFileDownloadHash(file, env) {
    let salt;
    if (env === "test") {
      salt = "vBHnf7bbdhz120bhNsd530LsA2mkMvh6sDsCm4jKlm23D186Fj";
    } else {
      salt = "Kcn6cv7&dmvkS40Hna§4ffcvl=021nfMs2sdlPs123MChf4s0K";
    }

    const timestamp = Math.floor(new Date().getTime() / 1000 + 3600);
    const hash = md5(salt + file.collection_id + timestamp);
    return hash;
  }

  return {
    convertObjectToArray
  };
}();
class CategoryTree extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      categories: window.catTree,
      categoryId: window.categoryId
    };
  }

  componentDidMount() {}

  render() {
    let categoryTreeDisplay;
    if (this.state.categories) {
      const categoryId = this.state.categoryId;
      categoryTreeDisplay = this.state.categories.map((cat, index) => React.createElement(CategoryItem, {
        key: index,
        category: cat,
        categoryId: categoryId
      }));
    }

    return React.createElement(
      "div",
      { id: "category-tree" },
      React.createElement(
        "ul",
        null,
        categoryTreeDisplay
      )
    );
  }
}

class CategoryItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentDidMount() {}

  render() {
    let categoryChildrenDisplay;
    if (this.props.category.has_children) {

      const categoryId = this.props.categoryId;
      const category = this.props.category;
      const children = appHelpers.convertObjectToArray(this.props.category.children);

      const categoryChildren = children.map((cat, index) => React.createElement(CategoryItem, {
        key: index,
        category: cat,
        categoryId: categoryId,
        parent: category
      }));

      categoryChildrenDisplay = React.createElement(
        "ul",
        null,
        categoryChildren
      );
    }

    let categoryItemClass = "cat-item";
    if (this.props.categoryId === this.props.category.id) {
      categoryItemClass += " active";
    }

    return React.createElement(
      "li",
      { id: "cat-" - this.props.category.id, className: categoryItemClass },
      React.createElement(
        "a",
        { href: window.baseUrl + "/browse/cat/" + this.props.category.id },
        this.props.category.title,
        React.createElement(
          "span",
          { className: "product-counter" },
          this.props.product_count
        )
      ),
      categoryChildrenDisplay
    );
  }
}

ReactDOM.render(React.createElement(CategoryTree, null), document.getElementById('category-tree-container'));
