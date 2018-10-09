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

  function getUserQueryUrl(hostname){
    let userQuery = {};
    if (hostname === "www.opendesktop.cc"){
      userQuery.url = "https://www.opendesktop.cc/user/userdataajax";
      userQuery.dataType = "jsonp";
    } else if (hostname === "gitlab.pling.cc"){
      userQuery.url = "https://gitlab.pling.cc/external/get_ocs_data.php?url=user/userdataajax";
      userQuery.dataType = "jsonp";
    } else if (hostname === "forum.opendesktop.cc"){
      userQuery.url = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=user/userdataajax";
      userQuery.dataType = "json";
    }
    return userQuery;
  }

  function getDomainsQueryUrl(hostname){
    let domainsQueryUrl;
    if (hostname === "www.opendesktop.cc"){
      domainsQueryUrl = "https://www.opendesktop.cc/home/domainsajax";
    } else if (hostname === "gitlab.pling.cc"){
      domainsQueryUrl = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/domainsajax";
    } else if (hostname === "forum.opendesktop.cc"){
      domainsQueryUrl = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/domainsajax";
    }
    return domainsQueryUrl;
  }

  function getBaseQueryUrl(hostname){
    let baseQueryUrl;
    if (hostname === "www.opendesktop.cc"){
      baseQueryUrl = "https://www.opendesktop.cc/home/baseurlajax";
    } else if (hostname === "gitlab.pling.cc"){
      baseQueryUrl = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/baseurlajax";
    } else if (hostname === "forum.opendesktop.cc"){
      baseQueryUrl = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/baseurlajax";
    }
    return baseQueryUrl;
  }

  function getForumQueryUrl(hostname){
    let forumQueryUrl;
    if (hostname === "www.opendesktop.cc"){
      forumQueryUrl = "https://www.opendesktop.cc/home/forumurlajax";
    } else if (hostname === "gitlab.pling.cc"){
      forumQueryUrl = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/forumurlajax";
    } else if (hostname === "forum.opendesktop.cc"){
      forumQueryUrl = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/forumurlajax";
    }
    return forumQueryUrl;
  }

  function getBlogQueryUrl(hostname){
    let blogQueryUrl;
    if (hostname === "www.opendesktop.cc"){
      blogQueryUrl = "https://www.opendesktop.cc/home/blogurlajax";
    } else if (hostname === "gitlab.pling.cc"){
      blogQueryUrl = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/blogurlajax";
    }  else if (hostname === "forum.opendesktop.cc"){
      blogQueryUrl = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/blogurlajax";
    }
    return blogQueryUrl;
  }

  function getStoreQueryUrl(hostname){
    let storeQueryUrl;
    if (hostname === "www.opendesktop.cc"){
      storeQueryUrl = "";
    } else if (hostname === "gitlab.pling.cc"){
      storeQueryUrl = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/storenameajax";
    }  else if (hostname === "forum.opendesktop.cc"){
      storeQueryUrl = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/storenameajax";
    }
    return storeQueryUrl;
  }

  return {
    generateMenuGroupsArray,
    getDomainsArray,
    getUserQueryUrl,
    getDomainsQueryUrl,
    getForumQueryUrl,
    getBaseQueryUrl,
    getBlogQueryUrl,
    getStoreQueryUrl
  }

}());
