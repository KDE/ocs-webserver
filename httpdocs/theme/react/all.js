const reducer = Redux.combineReducers({
  server_info: serverInfoReducer,
  site_info: siteInfoReducer,
  config: siteConfigReducer,
  local_storage: localStorageReducer,
  feed: feedReducer,
  route: routeReducer,
  user: userReducer,
  moderations: moderationsReducer,
  search_phrase: searchReducer,
  channel: channelReducer,
  item: itemReducer,
  currentItem: currentItemReducer,
  chunks_info: chunksReducer
});

/* reducers */

function serverInfoReducer(state = {}, action) {
  if (action.type === 'SERVER_INFO') {
    return action.server_info;
  } else {
    return state;
  }
}

function siteInfoReducer(state = {}, action) {
  switch (action.type) {
    case 'SITE_INFO':
      {
        return action.site_info;
      }
    case 'LOADING_USER':
      {
        const s = Object.assign({}, state, {
          loading: action.value
        });
        return s;
      }
    case 'CHANGE_CERT':
      {
        const s = Object.assign({}, state, {
          auth_address: action.auth_address,
          cert_user_id: action.cert_user_id
        });
        console.log('change cert reducer');
        console.log(s);
        return s;
      }
    default:
      {
        return state;
      }
  }
}

function siteConfigReducer(state = {}, action) {
  if (action.type === 'SITE_CONFIG') {
    return action.config;
  } else {
    return state;
  }
}

function localStorageReducer(state = {}, action) {
  if (action.type === 'LOCAL_STORAGE') {
    let state = {};
    if (action.local_storage) state = action.local_storage;
    return state;
  } else if (action.type === 'ZV_CERT_CREATED') {
    const s = Object.assign({}, state, {});
    s['ifs_cert_created'] = true;
    return s;
  } else {
    return state;
  }
}

function feedReducer(state = {}, action) {
  if (action.type === 'SET_FEED_LIST_FOLLOW') {
    return action.feed;
  }
  return state;
}

function routeReducer(state = {}, action) {
  if (action.type === 'SET_ROUTE') {
    return action.route;
  } else {
    return state;
  }
}

function userReducer(state = {}, action) {
  if (action.type === 'SET_USER') {
    return action.user;
  } else if (action.type === 'REMOVE_USER') {
    const s = {};
    return s;
  } else {
    return state;
  }
}

function moderationsReducer(state = {}, action) {
  if (action.type === 'SET_MODERATIONS') {
    return action.moderations;
  } else if (action.type === 'TOGGLE_MODERATIONS') {
    const show_moderations = state.show_moderations === true ? false : true;
    const s = Object.assign({}, state, {
      show_moderations: show_moderations
    });
    return s;
  }
  return state;
}

function searchReducer(state = {}, action) {
  if (action.type === 'SET_SEARCH_PHRASE') {
    return action.search_phrase;
  } else {
    return state;
  }
}

function channelReducer(state = {}, action) {
  if (action.type === 'SET_CHANNEL') {
    return action.channel;
  } else {
    return state;
  }
}

function itemReducer(state = {}, action) {
  switch (action.type) {
    case 'SET_ITEM_INFO':
      {
        const s = Object.assign({}, state, {
          item_info: action.item_info
        });
        return s;
      }
    case 'SET_FILE_INFO':
      {
        const s = Object.assign({}, state, {
          file_info: action.file_info
        });
        return s;
      }
    case 'FILE_DOWNLOAD_FAILED':
      {
        const s = Object.assign({}, state, {
          download_failed: true
        });
        return s;
      }
    default:
      {
        return state;
      }
  }
}

function currentItemReducer(state = {}, action) {
  if (action.type === 'SET_CURRENT_ITEM') {
    const s = Object.assign({}, state, {
      item: action.item,
      index: action.index
    });
    return s;
  } else {
    return state;
  }
}

function chunksReducer(state = {}, action) {
  if (action.type === 'SET_CHUNKS_INFORMATION') {
    const s = Object.assign({}, state, {
      pieces_downloaded: action.pieces_downloaded,
      pieces: action.pieces
    });
    return s;
  } else {
    return state;
  }
}

function setServerInfo(server_info) {
  return {
    type: 'SERVER_INFO',
    server_info: server_info
  };
}

function setSiteInfo(site_info) {
  return {
    type: 'SITE_INFO',
    site_info: site_info
  };
}

function changeCert(auth_address, cert_user_id) {
  return {
    type: 'CHANGE_CERT',
    auth_address: auth_address,
    cert_user_id: cert_user_id
  };
}

function setSiteConfig(config) {
  return {
    type: 'SITE_CONFIG',
    config: config
  };
}

function setLocalStorage(local_storage) {
  return {
    type: 'LOCAL_STORAGE',
    local_storage: local_storage
  };
}

function setRoute(route) {
  return {
    type: 'SET_ROUTE',
    route: route
  };
}

function setUser(user) {
  return {
    type: 'SET_USER',
    user: user
  };
}

function removeUser() {
  return {
    type: 'REMOVE_USER'
  };
}

function setFeedListFollow(feed) {
  return {
    type: 'SET_FEED_LIST_FOLLOW',
    feed: feed
  };
}

function setModerations(moderations) {
  return {
    type: 'SET_MODERATIONS',
    moderations: moderations
  };
}

function toggleModerations(value) {
  return {
    type: 'TOGGLE_MODERATIONS',
    action: value
  };
}

function setSearchPhrase(searchPhrase) {
  return {
    type: 'SET_SEARCH_PHRASE',
    search_phrase: searchPhrase
  };
}

function setChannel(channel) {
  return {
    type: 'SET_CHANNEL',
    channel: channel
  };
}

function setCurrentItem(item, index) {
  return {
    type: 'SET_CURRENT_ITEM',
    item: item,
    index: index
  };
}

function setItemInfo(item_info) {
  return {
    type: 'SET_ITEM_INFO',
    item_info: item_info
  };
}

function setFileInfo(file_info) {
  return {
    type: 'SET_FILE_INFO',
    file_info: file_info
  };
}

function fileDownloadFailed() {
  return {
    type: 'FILE_DOWNLOAD_FAILED'
  };
}

function setChunksInformation(pieces_downloaded, pieces) {
  return {
    type: 'SET_CHUNKS_INFORMATION',
    pieces_downloaded: pieces_downloaded,
    pieces: pieces
  };
}
const { Provider, connect } = ReactRedux;
const store = Redux.createStore(reducer);

class App extends React.Component {

  render() {
    return React.createElement(
      "div",
      { id: "app-root" },
      React.createElement(
        "h1",
        null,
        "Hello World! this is on React.js!"
      ),
      React.createElement(
        "p",
        null,
        "coming soon! something cool!"
      )
    );
  }

}

class AppWrapper extends React.Component {
  render() {
    return React.createElement(
      Provider,
      { store: store },
      React.createElement(App, null)
    );
  }
}

ReactDOM.render(React.createElement(AppWrapper, null), document.getElementById('explore-content'));
