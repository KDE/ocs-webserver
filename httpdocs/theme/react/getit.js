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
      total: 0,
      archived: 0
    };
    files.forEach(function (file, index) {
      if (file.active === "1") {
        summery.total += 1;
      } else {
        summery.archived += 1;
      }
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

  function generateOcsInstallLink(f) {
    let ocsInstallLink,
        osId = '',
        link = '',
        licenseId = '',
        license = '',
        packagetypeId = '',
        architectureId = '',
        filesTags,
        fileDescription = '';

    if (f.description) {
      fileDescription = f.description;
    }

    if (f.tags) {
      fileTags = f.tags.split(',');
      fileTags.forEach(function (tag, index) {
        let tagStr;
        if (tag.length > 0) {
          if (tag.indexOf("##") == -1) {
            tagStr = tag.split('-');
            if (tagStr.length == 2 && tagStr[0] == 'os') {
              osId = tagStr[1];
            } else if (tagStr.length == 2 && tagStr[0] == 'licensetype') {
              licenseId = tagStr[1];
            } else if (tagStr.length == 2 && tagStr[0] == 'packagetypeid') {
              packagetypeId = tagStr[1];
            } else if (tagStr.length == 2 && tagStr[0] == 'architectureid') {
              architectureId = tagStr[1];
            }
          } else {
            tagStr = tag.split('##');
            if (tagStr.length == 2 && tagStr[0] == 'link') {
              link = tagStr[1];
            } else if (tagStr.length == 2 && tagStr[0] == 'license') {
              license = tagStr[1];
              license = Base64.decode(license);
            } else if (tagStr.length == 2 && tagStr[0] == 'packagetypeid') {
              packagetypeId = tagStr[1];
            } else if (tagStr.length == 2 && tagStr[0] == 'architectureid') {
              architectureId = tagStr[1];
            }
          }
        }
      });

      if (typeof link !== 'undefined' && link) {
        ocsUrl = generateOcsUrl(decodeURIComponent(link), $pploadCollection.attr('data-xdg-type'));
      } else if (!link) {
        ocsUrl = generateOcsUrl(downloadUrl, $pploadCollection.attr('data-xdg-type'), this.name);
      }

      console.log(fileTags);
    }

    /*
    var fileDescription = '';
                                    if (this.description) {
                                        fileDescription = this.description;
                                    }
                                    var licenseId = '';
                                    var license = '';
                                    var packagetypeId = '';
                                    var architectureId = '';
                                    if (this.tags) {
                                         fileTags = this.tags;
                                          $.each(fileTags.split(','), function () {
                                              if(this.indexOf("##")==-1) {
                                              var tagStr = this.split('-');
                                              if (tagStr.length == 2 && tagStr[0] == 'os') {
                                                  osId = tagStr[1];
                                              } else if (tagStr.length == 2 && tagStr[0] == 'licensetype') {
                                                  licenseId = tagStr[1];
                                              } else if (tagStr.length == 2 && tagStr[0] == 'packagetypeid') {
                                                  packagetypeId = tagStr[1];
                                              } else if (tagStr.length == 2 && tagStr[0] == 'architectureid') {
                                                  architectureId = tagStr[1];
                                              }
                                             } else {
                                              var tagStr = this.split('##');
                                              if (tagStr.length == 2 && tagStr[0] == 'link') {
                                                  link = tagStr[1];
                                              } else if (tagStr.length == 2 && tagStr[0] == 'license') {
                                                  license = tagStr[1];
                                                  license = Base64.decode(license);
                                              } else if (tagStr.length == 2 && tagStr[0] == 'packagetypeid') {
                                                  packagetypeId = tagStr[1];
                                              } else if (tagStr.length == 2 && tagStr[0] == 'architectureid') {
                                                  architectureId = tagStr[1];
                                              }
                                             }
                                             /*else if (tagStr.length == 2 && tagStr[0] == 'package') {
                                              packageId = tagStr[1];
                                              }
                                              else if (tagStr.length == 2 && tagStr[0] == 'arch') {
                                              archId = tagStr[1];
                                              }
                                              else if (tagStr.length == 2 && tagStr[0] == 'device') {
                                              deviceId = tagStr[1];
                                              }
                                         });
                                     }
                                      var ocsUrl = '';
     function generateOcsUrl(url, type, filename) { if (!url || !type) { return ''; } if (!filename) { filename = url.split('/').pop().split('?').shift(); } return 'ocs://install' + '?url=' + encodeURIComponent(url) + '&type=' + encodeURIComponent(type) + '&filename=' + encodeURIComponent(filename); }
    */
    return ocsInstallLink;
  }

  return {
    getNumberOfProducts,
    generatePaginationObject,
    calculateProductRatings,
    getActiveRatingsNumber,
    getFilesSummary,
    checkIfLikedByUser,
    getLoggedUserRatingOnProduct,
    calculateProductLaplaceScore,
    generateOcsInstallLink
  };
}();
class GetIt extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      product: window.product,
      files: window.filesJson,
      xdgType: xdgTypeJson,
      env: 'test'
    };
  }

  render() {
    console.log(this.state.files);
    return React.createElement(
      "div",
      { id: "get-it" },
      React.createElement(
        "button",
        {
          "data-toggle": "modal",
          "data-target": "#get-it-modal-window",
          style: { "width": "100%" },
          id: "project_btn_getit", className: "btn dropdown-toggle active btn-primary  ",
          type: "button" },
        "Get it"
      ),
      React.createElement(
        "div",
        { className: "modal fade", id: "get-it-modal-window", tabIndex: "-1", role: "dialog", "aria-labelledby": "myModalLabel" },
        React.createElement(
          "div",
          { id: "get-it-modal", className: "modal-dialog", role: "document" },
          React.createElement(GetItFilesList, {
            files: this.state.files,
            product: this.state.product,
            env: this.state.env
          })
        )
      )
    );
  }
}

class GetItFilesList extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      activeTab: 'active'
    };
    this.toggleActiveTab = this.toggleActiveTab.bind(this);
  }

  toggleActiveTab(tab) {
    this.setState({ activeTab: tab });
  }

  render() {

    const activeFiles = this.props.files.filter(file => file.active == "1").map((f, index) => React.createElement(GetItFilesListItem, {
      product: this.props.product,
      env: this.props.env,
      key: index,
      file: f
    }));
    const archivedFiles = this.props.files.filter(file => file.active == "0").map((f, index) => React.createElement(GetItFilesListItem, {
      product: this.props.product,
      env: this.props.env,
      key: index,
      file: f
    }));
    const summeryRow = productHelpers.getFilesSummary(this.props.files);
    const summeryRowDisplay = React.createElement(
      "tr",
      null,
      React.createElement(
        "td",
        null,
        summeryRow.total,
        " files (",
        summeryRow.archived,
        " archived)"
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
    );
    const tableHeader = React.createElement(
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
    );

    let tableFilesDisplay;
    if (this.state.activeTab === "active") {
      tableFilesDisplay = React.createElement(
        "tbody",
        null,
        activeFiles,
        summeryRowDisplay
      );
    } else if (this.state.activeTab === "archived") {
      tableFilesDisplay = React.createElement(
        "tbody",
        null,
        archivedFiles,
        summeryRowDisplay
      );
    }

    return React.createElement(
      "div",
      { id: "files-tabs-container" },
      React.createElement(
        "button",
        { type: "button", className: "close", "data-dismiss": "modal", "aria-label": "Close" },
        React.createElement(
          "span",
          { "aria-hidden": "true" },
          "\xD7"
        )
      ),
      React.createElement(
        "div",
        { className: "files-tabs-header" },
        React.createElement(
          "h2",
          null,
          "Thanks for your support!"
        )
      ),
      React.createElement(
        "div",
        { className: "tabs-menu" },
        React.createElement(
          "ul",
          { className: "nav nav-tabs", role: "tablist" },
          React.createElement(
            "li",
            { role: "presentation", className: this.state.activeTab === "active" ? "active" : "" },
            React.createElement(
              "a",
              { onClick: () => this.toggleActiveTab('active') },
              "Files (",
              summeryRow.total,
              ")"
            )
          ),
          React.createElement(
            "li",
            { role: "presentation", className: this.state.activeTab === "archived" ? "active pull-right" : "pull-right" },
            React.createElement(
              "a",
              { onClick: () => this.toggleActiveTab('archived') },
              "Archive (",
              summeryRow.archived,
              ")"
            )
          )
        )
      ),
      React.createElement(
        "div",
        { id: "files-tab", className: "product-tab" },
        React.createElement(
          "table",
          { id: "active-files-table", className: "mdl-data-table mdl-js-data-table mdl-shadow--2dp" },
          tableHeader,
          tableFilesDisplay
        )
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
    if (this.props.env === 'live') {
      baseUrl = 'opendesktop.org';
      downloadLinkUrlAttr = "https%3A%2F%dl.opendesktop.org%2Fapi%2F";
    } else {
      baseUrl = 'pling.cc';
      downloadLinkUrlAttr = "https%3A%2F%2Fcc.ppload.com%2Fapi%2F";
    }

    const f = this.props.file;
    const timestamp = Math.floor(new Date().getTime() / 1000 + 3600);
    const fileDownloadHash = appHelpers.generateFileDownloadHash(f, this.props.env);
    const downloadLink = "https://" + baseUrl + "/p/" + this.props.product.project_id + "/startdownload?file_id=" + f.id + "&file_name=" + f.title + "&file_type=" + f.type + "&file_size=" + f.size + "&url=" + downloadLinkUrlAttr + "files%2Fdownloadfile%2Fid%2F" + f.id + "%2Fs%2F" + fileDownloadHash + "%2Ft%2F" + timestamp + "%2Fu%2F" + this.props.product.member_id + "%2F" + f.title;

    const ocsInstallLink = productHelpers.generateOcsInstallLink(f);
    this.setState({ downloadLink: downloadLink });
  }

  render() {
    const f = this.props.file;
    let title;
    if (f.title.length > 30) {
      title = f.title.substring(0, 30) + "...";
    } else {
      title = f.title;
    }
    return React.createElement(
      "tr",
      null,
      React.createElement(
        "td",
        { className: "mdl-data-table__cell--non-numericm" },
        React.createElement(
          "a",
          { href: this.state.downloadLink },
          title
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
          { href: this.state.downloadLink, className: "btn btn-native download-button" },
          React.createElement("img", { src: "/images/system/download.svg", alt: "download", style: { width: "20px", height: "20px" } })
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
