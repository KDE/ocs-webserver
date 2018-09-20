window.appHelpers = function () {

  function getEnv(domain) {
    let env;
    if (this.splitByLastDot(domain) === 'com') {
      env = 'live';
    } else {
      env = 'test';
    }
    return env;
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
    const hash = md5(salt, file.collection_id + timestamp);
    return hash;
    /*
    $salt = PPLOAD_DOWNLOAD_SECRET;
    $collectionID = $productInfo->ppload_collection_id;
    $timestamp = time() + 3600; // one hour valid
    $hash = md5($salt . $collectionID . $timestamp);
    */
  }

  return {
    getEnv,
    getDeviceWidth,
    splitByLastDot,
    getTimeAgo,
    getFileSize,
    generateFilterUrl,
    generateFileDownloadHash
  };
}();
window.productHelpers = function () {

  function getNumberOfProducts(device, numRows) {
    let num;
    if (device === "very-huge") {
      num = 7;
    } else if (device === "huge") {
      num = 6;
    } else if (device === "full") {
      num = 5;
    } else if (device === "large") {
      num = 4;
    } else if (device === "mid") {
      num = 3;
    } else if (device === "tablet") {
      num = 2;
    } else if (device === "phone") {
      num = 1;
    }
    if (numRows) num = num * numRows;
    return num;
  }

  function generatePaginationObject(numPages, pathname, currentCategoy, order, page) {
    let pagination = [];

    let baseHref = "/browse";
    if (pathname.indexOf('cat') > -1) {
      baseHref += "/cat/" + currentCategoy;
    }

    if (page > 1) {
      const prev = {
        number: 'previous',
        link: baseHref + "/page/" + parseInt(page - 1) + "/ord/" + order
      };
      pagination.push(prev);
    }

    for (var i = 0; i < numPages; i++) {
      const p = {
        number: parseInt(i + 1),
        link: baseHref + "/page/" + parseInt(i + 1) + "/ord/" + order
      };
      pagination.push(p);
    }

    if (page < numPages) {
      const next = {
        number: 'next',
        link: baseHref + "/page/" + parseInt(page + 1) + "/ord/" + order
      };
      pagination.push(next);
    }

    return pagination;
  }

  function calculateProductRatings(ratings) {
    let pRating;
    let totalUp = 0,
        totalDown = 0;
    ratings.forEach(function (r, index) {
      if (r.rating_active === "1") {
        if (r.user_like === "1") {
          totalUp += 1;
        } else if (r.user_dislike === "1") {
          totalDown += 1;
        }
      }
    });
    pRating = 100 / ratings.length * (totalUp - totalDown);
    return pRating;
  }

  function getActiveRatingsNumber(ratings) {
    let activeRatingsNumber = 0;
    ratings.forEach(function (r, index) {
      if (r.rating_active === "1") {
        activeRatingsNumber += 1;
      }
    });
    return activeRatingsNumber;
  }

  function getFilesSummary(files) {
    let summery = {
      downloads: 0,
      archived: 0,
      fileSize: 0,
      total: 0
    };
    files.forEach(function (file, index) {
      summery.total += 1;
      summery.fileSize += parseInt(file.size);
      summery.downloads += parseInt(file.downloaded_count);
    });

    return summery;
  }

  function checkIfLikedByUser(user, likes) {
    let likedByUser = false;
    likes.forEach(function (like, index) {
      if (user.member_id === like.member_id) {
        likedByUser = true;
      }
    });
    return likedByUser;
  }

  function getLoggedUserRatingOnProduct(user, ratings) {
    let userRating = -1;
    ratings.forEach(function (r, index) {
      if (r.member_id === user.member_id) {
        if (r.user_like === "1") {
          userRating = 1;
        } else {
          userRating = 0;
        }
      }
    });
    return userRating;
  }

  function calculateProductLaplaceScore(ratings) {
    let laplace_score = 0;
    let upvotes = 0;
    let downvotes = 0;
    ratings.forEach(function (rating, index) {
      console.log(rating.active);
      if (rating.rating_active === "1") {
        console.log(rating.user_like);
        if (rating.user_like === "1") {
          upvotes += 1;
        } else if (rating.user_like === "0") {
          downvotes += 1;
        }
      }
    });
    laplace_score = Math.round((upvotes + 6) / (upvotes + downvotes + 12), 2) * 100;
    console.log(laplace_score);
    return laplace_score;
  }

  return {
    getNumberOfProducts,
    generatePaginationObject,
    calculateProductRatings,
    getActiveRatingsNumber,
    getFilesSummary,
    checkIfLikedByUser,
    getLoggedUserRatingOnProduct,
    calculateProductLaplaceScore
  };
}();
class GetIt extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      files: window.filesJson
    };
  }

  render() {
    return React.createElement(
      "div",
      { id: "get-it" },
      React.createElement(
        "button",
        {
          "data-toggle": "modal",
          "data-target": "#myModal",
          style: { "width": "100%" },
          id: "project_btn_getit", className: "btn dropdown-toggle active btn-primary  ",
          type: "button" },
        "Get it"
      ),
      React.createElement(
        "div",
        { className: "modal fade", id: "myModal", tabIndex: "-1", role: "dialog", "aria-labelledby": "myModalLabel" },
        React.createElement(
          "div",
          { className: "modal-dialog", role: "document" },
          React.createElement(GetItFilesList, {
            files: this.state.files
          })
        )
      )
    );
  }
}

class GetItFilesList extends React.Component {
  render() {
    let filesDisplay;
    const files = this.props.files.map((f, index) => React.createElement(GetItFilesListItem, {
      product: this.props.product,
      key: index,
      file: f
    }));
    const summeryRow = productHelpers.getFilesSummary(this.props.files);
    filesDisplay = React.createElement(
      "tbody",
      null,
      files,
      React.createElement(
        "tr",
        null,
        React.createElement(
          "td",
          null,
          summeryRow.total,
          " files (0 archived)"
        ),
        React.createElement("td", null),
        React.createElement("td", null),
        React.createElement("td", null),
        React.createElement("td", null),
        React.createElement(
          "td",
          null,
          summeryRow.downloads
        ),
        React.createElement("td", null),
        React.createElement(
          "td",
          null,
          appHelpers.getFileSize(summeryRow.fileSize)
        ),
        React.createElement("td", null),
        React.createElement("td", null)
      )
    );
    return React.createElement(
      "div",
      { id: "files-tab", className: "product-tab" },
      React.createElement(
        "table",
        { className: "mdl-data-table mdl-js-data-table mdl-shadow--2dp" },
        React.createElement(
          "thead",
          null,
          React.createElement(
            "tr",
            null,
            React.createElement(
              "th",
              { className: "mdl-data-table__cell--non-numericm" },
              "File"
            ),
            React.createElement(
              "th",
              { className: "mdl-data-table__cell--non-numericm" },
              "Version"
            ),
            React.createElement(
              "th",
              { className: "mdl-data-table__cell--non-numericm" },
              "Description"
            ),
            React.createElement(
              "th",
              { className: "mdl-data-table__cell--non-numericm" },
              "Packagetype"
            ),
            React.createElement(
              "th",
              { className: "mdl-data-table__cell--non-numericm" },
              "Architecture"
            ),
            React.createElement(
              "th",
              { className: "mdl-data-table__cell--non-numericm" },
              "Downloads"
            ),
            React.createElement(
              "th",
              { className: "mdl-data-table__cell--non-numericm" },
              "Date"
            ),
            React.createElement(
              "th",
              { className: "mdl-data-table__cell--non-numericm" },
              "Filesize"
            ),
            React.createElement(
              "th",
              { className: "mdl-data-table__cell--non-numericm" },
              "DL"
            ),
            React.createElement(
              "th",
              { className: "mdl-data-table__cell--non-numericm" },
              "OCS-Install"
            )
          )
        ),
        filesDisplay
      )
    );
  }
}

class GetItFilesListItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = { downloadLink: "" };
  }

  componentDidMount() {
    let baseUrl, downloadLinkUrlAttr;
    // if (store.getState().env === 'live') {
    baseUrl = 'opendesktop.org';
    downloadLinkUrlAttr = "https%3A%2F%dl.opendesktop.org%2Fapi%2F";
    // } else {
    // baseUrl = 'pling.cc';
    // downloadLinkUrlAttr = "https%3A%2F%2Fcc.ppload.com%2Fapi%2F";
    // }

    const f = this.props.file;
    const timestamp = Math.floor(new Date().getTime() / 1000 + 3600);
    const fileDownloadHash = appHelpers.generateFileDownloadHash(f, store.getState().env);
    let downloadLink = "https://" + baseUrl + "/p/" + this.props.product.project_id + "/startdownload?file_id=" + f.id + "&file_name=" + f.title + "&file_type=" + f.type + "&file_size=" + f.size + "&url=" + downloadLinkUrlAttr + "files%2Fdownloadfile%2Fid%2F" + f.id + "%2Fs%2F" + fileDownloadHash + "%2Ft%2F" + timestamp + "%2Fu%2F" + this.props.product.member_id + "%2F" + f.title;
    this.setState({ downloadLink: downloadLink });
  }

  render() {
    const f = this.props.file;
    return React.createElement(
      "tr",
      null,
      React.createElement(
        "td",
        { className: "mdl-data-table__cell--non-numericm" },
        React.createElement(
          "a",
          { href: this.state.downloadLink },
          f.title
        )
      ),
      React.createElement(
        "td",
        null,
        f.version
      ),
      React.createElement(
        "td",
        { className: "mdl-data-table__cell--non-numericm" },
        f.description
      ),
      React.createElement(
        "td",
        { className: "mdl-data-table__cell--non-numericm" },
        f.packagename
      ),
      React.createElement(
        "td",
        { className: "mdl-data-table__cell--non-numericm" },
        f.archname
      ),
      React.createElement(
        "td",
        null,
        f.downloaded_count
      ),
      React.createElement(
        "td",
        { className: "mdl-data-table__cell--non-numericm" },
        appHelpers.getTimeAgo(f.created_timestamp)
      ),
      React.createElement(
        "td",
        { className: "mdl-data-table__cell--non-numericm" },
        appHelpers.getFileSize(f.size)
      ),
      React.createElement(
        "td",
        null,
        React.createElement(
          "a",
          { href: this.state.downloadLink },
          React.createElement(
            "i",
            { className: "material-icons" },
            "cloud_download"
          )
        )
      ),
      React.createElement(
        "td",
        null,
        f.ocs_compatible
      )
    );
  }
}

ReactDOM.render(React.createElement(GetIt, null), document.getElementById('get-it-container'));
