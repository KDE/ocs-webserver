window.appHelpers = (function(){

  function generateMenuGroupsArray(domains){
    let menuGroups = [];
    domains.forEach(function(domain,index){
      if (menuGroups.indexOf(domain.menugroup) === -1){
        menuGroups.push(domain.menugroup);
      }
    });
    return menuGroups;
  }

  function getDomainsArray(){
    const domains = [
      {
        "order": "30101",
        "calcOrder": 9,
        "host": "books.pling.cc",
        "name": "Books",
        "menuactive": 0,
        "menuhref": "books.pling.cc",
        "menugroup": "Desktops"
      },
      {
        "order": "30200",
        "calcOrder": 9,
        "host": "comics.pling.cc",
        "name": "Comics",
        "menuactive": 0,
        "menuhref": "opendesktop.cc/s/Comics",
        "menugroup": "Desktops"
      },
      {
        "order": "30300",
        "calcOrder": 9,
        "host": "music.pling.cc",
        "name": "Music",
        "menuactive": 0,
        "menuhref": "opendesktop.cc/s/Music",
        "menugroup": "Desktops"
      },
      {
        "order": "10000",
        "calcOrder": 10,
        "host": "www.pling.cc",
        "name": "Pling",
        "menuactive": 0,
        "menuhref": "opendesktop.cc/s/Pling",
        "menugroup": "Applications"
      },
      {
        "order": "10000",
        "calcOrder": 10,
        "host": "snappy",
        "name": "Snappy",
        "menuactive": 0,
        "menuhref": "opendesktop.cc/s/Snappy",
        "menugroup": "Applications"
      },
      {
        "order": "10100",
        "calcOrder": 10,
        "host": "android.pling.cc",
        "name": "Android",
        "menuactive": 0,
        "menuhref": "opendesktop.cc/s/Android",
        "menugroup": "Applications"
      },
      {
        "order": "10101",
        "calcOrder": 10,
        "host": "www.opendesktop.cc",
        "name": "opendesktop",
        "menuactive": 0,
        "menuhref": "www.opendesktop.cc",
        "menugroup": "Applications"
      },
      {
        "order": "10200",
        "calcOrder": 10,
        "host": "linux.pling.cc",
        "name": "Appimages",
        "menuactive": 0,
        "menuhref": "opendesktop.cc/s/Appimages",
        "menugroup": "Applications"
      },
      {
        "order": "10300",
        "calcOrder": 10,
        "host": "windows.pling.cc",
        "name": "Windows",
        "menuactive": 0,
        "menuhref": "opendesktop.cc/s/Windows",
        "menugroup": "Applications"
      },
      {
        "order": "20100",
        "calcOrder": 20,
        "host": "kde.pling.cc",
        "name": "KDE",
        "menuactive": 0,
        "menuhref": "opendesktop.cc/s/KDE",
        "menugroup": "Addons"
      },
      {
        "order": "20200",
        "calcOrder": 20,
        "host": "gnome.pling.cc",
        "name": "Gnome",
        "menuactive": 0,
        "menuhref": "opendesktop.cc/s/Gnome",
        "menugroup": "Addons"
      },
      {
        "order": "20400",
        "calcOrder": 20,
        "host": "xfce.opendesktop.cc",
        "name": "XFCE",
        "menuactive": 0,
        "menuhref": "opendesktop.cc/s/XFCE",
        "menugroup": "Addons"
      },
      {
        "order": "20901",
        "calcOrder": 20,
        "host": "pling.local",
        "name": "Siyuan's personal store",
        "menuactive": 0,
        "menuhref": "pling.local",
        "menugroup": "Addons"
      },
      {
        "order": "40400",
        "calcOrder": 40,
        "host": "videos.pling.cc",
        "name": "Videos",
        "menuactive": 0,
        "menuhref": "opendesktop.cc/s/Videos",
        "menugroup": "Artwork"
      },
      {
        "order": "50300",
        "calcOrder": 50,
        "host": "xfce.pling.cc",
        "name": "XFCE-Pling-CC",
        "menuactive": 0,
        "menuhref": "opendesktop.cc/s/XFCE-Pling-CC",
        "menugroup": "Other"
      }
    ]
    return domains;
  }

  function getLoginQueryUrl(hostname){
    let loginQuery = {};
    if (hostname === "www.opendesktop.cc"){
      loginQuery.url = "https://www.opendesktop.cc/user/loginurlajax&returnurl=https://www.opendesktop.cc";
      loginQuery.dataType = "jsonp";
    } else if (hostname === "gitlab.pling.cc"){
      loginQuery.url = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/loginurlajax&returnurl=https://gitlab.pling.cc";
      loginQuery.dataType = "jsonp";
    } else if (hostname === "forum.opendesktop.cc"){
      loginQuery.url = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/loginurlajax&returnurl=https://forum.opendesktop.cc";
      loginQuery.dataType = "json";
    }
    return loginQuery;
  }

  function getUserQueryUrl(hostname){
    let userQuery = {};
    if (hostname === "www.opendesktop.cc"){
      userQuery.url = "https://www.opendesktop.cc/user/userdataajax";
      userQuery.dataType = "jsonp";
    } else if (hostname === "gitlab.pling.cc"){
      userQuery.url = "https://gitlab.pling.cc/external/get_ocs_data.php?url=user/userdataajax";
      userQuery.dataType = "jsonp";
    } else if (hostname === "forum.opendesktop.cc"){
      userQuery.url = "https://forum.opendesktop.cc:8443/get_ocs_data.php";
      userQuery.dataType = "json";
    }
    return userQuery;
  }

  function getDomainsQueryUrl(hostname){
    let domainsQuery = {};
    if (hostname === "www.opendesktop.cc"){
      domainsQuery.url = "https://www.opendesktop.cc/home/domainsajax";
      domainsQuery.dataType = "jsonp";
    } else if (hostname === "gitlab.pling.cc"){
      domainsQuery.url = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/domainsajax";
      domainsQuery.dataType = "jsonp";
    } else if (hostname === "forum.opendesktop.cc"){
      domainsQuery.url = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/domainsajax";
      domainsQuery.dataType = "json";
    }
    return domainsQuery;
  }

  function getBaseQueryUrl(hostname){
    let baseQuery = {};
    if (hostname === "www.opendesktop.cc"){
      baseQuery.url = "https://www.opendesktop.cc/home/baseurlajax";
      baseQuery.dataType = "jsonp";
    } else if (hostname === "gitlab.pling.cc"){
      baseQuery.url = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/baseurlajax";
      baseQuery.dataType = "jsonp";
    } else if (hostname === "forum.opendesktop.cc"){
      baseQuery.url = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/baseurlajax";
      baseQuery.dataType = "json";
    }
    return baseQuery;
  }

  function getForumQueryUrl(hostname){
    let forumQuery = {};
    if (hostname === "www.opendesktop.cc"){
      forumQuery.url = "https://www.opendesktop.cc/home/forumurlajax";
      forumQuery.dataType = "jsonp";
    } else if (hostname === "gitlab.pling.cc"){
      forumQuery.url = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/forumurlajax";
      forumQuery.dataType = "jsonp";
    } else if (hostname === "forum.opendesktop.cc"){
      forumQuery.url = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/forumurlajax";
      forumQuery.dataType = "json";
    }
    return forumQuery;
  }

  function getBlogQueryUrl(hostname){
    let blogQuery = {};
    if (hostname === "www.opendesktop.cc"){
      blogQuery.url = "https://www.opendesktop.cc/home/blogurlajax";
      blogQuery.dataType = "jsonp";
    } else if (hostname === "gitlab.pling.cc"){
      blogQuery.url = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/blogurlajax";
      blogQuery.dataType = "jsonp";
    } else if (hostname === "forum.opendesktop.cc"){
      blogQuery.url = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/blogurlajax";
      blogQuery.dataType = "json";
    }
    return blogQuery;
  }

  function getStoreQueryUrl(hostname){
    let storeQuery = {};
    if (hostname === "www.opendesktop.cc"){
      storeQuery.url = "";
      storeQuery.dataType = "jsonp";
    } else if (hostname === "gitlab.pling.cc"){
      storeQuery.url = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/storenameajax";
      storeQuery.dataType = "jsonp";
    }  else if (hostname === "forum.opendesktop.cc"){
      storeQuery.url = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/storenameajax";
      storeQuery.dataType = "json";
    }
    return storeQuery;
  }

  return {
    generateMenuGroupsArray,
    getDomainsArray,
    getLoginQueryUrl,
    getUserQueryUrl,
    getDomainsQueryUrl,
    getForumQueryUrl,
    getBaseQueryUrl,
    getBlogQueryUrl,
    getStoreQueryUrl
  }

}());
