import { useRef, useEffect } from 'react';

export const GetViewFromUrl = (url) => {
    let view;
    if (url !== null){
        if (url.indexOf('/p/') > -1  || url.indexOf('/p2/') > -1) view = "product-view";
        else if (url.indexOf('/browse') > -1 || url.indexOf('/browse2') > -1) view = "product-browse";
        else if (url.indexOf('/u/') > -1 || url.indexOf('/u2/') > -1 ) view = "user-profile";
        else if (url.indexOf('/home2') > -1) view = "home-page";
    } 
    else view = "home-page";
    return view
}

export const GetIdFromUrl = (url) => {
    let id;
    if (url.indexOf('/p/') > -1  || url.indexOf('/p2/') > -1) id = url.split('/p2/')[1];
    else if (url.indexOf('/u/') > -1 || url.indexOf('/u2/') > -1 ) id = url.split('/u2/')[1];
    if (id && id.indexOf('/') > -1) id = id.split('/')[0];
    return id;
}

export const GenerateHeaderDataFromJson = () => {
    const headerData = {
        auth:window.json_member,
        baseurl:window.json_baseurl,
        baseurlStore:window.json_baseurlStore,
        searchBaseUrl:window.json_searchbaseurl,
        cat_title:window.json_cat_title,
        hasIdentity:window.json_hasIdentity,
        is_show_title:window.json_is_show_title,
        redirectString:window.json_redirectString,
        serverUri:window.json_serverUri,
        ocsStoreConfig:{
          sName:window.json_sname,
          name:window.json_store_name,
          order:window.json_store_order,
          last_char_store_order:window.json_last_char_store_order,
        },
        logo:window.json_logoWidth,
        cat_title_left:window.json_cat_title_left,
        tabs_left:window.tabs_left,
        ocsStoreTemplate:window.json_template,
        status:"",
        sectiondata: window.json_section,
        url_logout:window.json_logouturl,
        cat_id:window.json_cat_id,
        isShowAddProject: window.json_isShowAddProduct,
        json_header_links:window.json_header_links
    }
    return headerData;
}

// Hook use Previous
export const usePrevious = (value) => {
    // The ref object is a generic container whose current property is mutable ...
    // ... and can hold any value, similar to an instance property on a class
    const ref = useRef();
    
    // Store current value in ref
    useEffect(() => {
        ref.current = value;
    }, [value]); // Only re-run if value changes
    
    // Return previous value (happens before update in useEffect above)
    return ref.current;
}