import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import {SortByCurrentFilter} from './product-browse-helpers';
import ReactJkMusicPlayer from "react-jinke-music-player";
import {isMobile} from 'react-device-detect';

function ProductBrowse(){
    return (
        <div id="product-browse">
            <ProductBrowseFilterContainer/>
            <ProductBrowseItemList />
        </div>
    )
}

function ProductBrowseFilterContainer(){

    let filtersBaseUrl = json_serverUrl;
    filtersBaseUrl += json_store_name === "ALL" ? "/" : "/s/" + json_store_name + "/";
    filtersBaseUrl += "browse/";
    if (typeof filters.category === 'number') filtersBaseUrl += "cat/" + filters.category + "/";

    function onOriginalCheckboxClick(){
        let val = filters.original !== null ? 0 : 1;
        window.location.href = filtersBaseUrl + "filteroriginal/" + val;
    }

    return (
        <div id="product-browse-top-menu">
            <div className="pling-nav-tabs">
                <ul className="nav nav-tabs pling-nav-tabs" id="sort">
                    <li className={filters.order === "latest" ? "active" : ""}>
                        <a href={filtersBaseUrl + "ord/latest/" + window.location.search}>Latest</a>
                    </li>
                    <li className={filters.order === "rating" ? "active" : ""}>
                        <a href={filtersBaseUrl + "ord/rating/" + window.location.search}>Score</a>
                    </li>
                    <li className={filters.order === "plinged" ? "active" : ""}>
                        <a href={filtersBaseUrl + "ord/plinged/" + window.location.search}>Plinged</a>
                    </li>
                    <li style={{"float":"right","paddingTop":"10px"}}>
                        <input onChange={onOriginalCheckboxClick} defaultChecked={filters.original} type="checkbox"/>
                        <label>Original</label>
                    </li>
                </ul>
            </div>
        </div>
    )
}

function ProductBrowseItemList(props){

    const [ containerWidth, setContainerWidth ] = useState($('#product-browse-container').width() + 14);

    let productBrowseItemType = 0;

    if (browseListType === "music") productBrowseItemType = 1;

    const [ itemsInRow, setItemsInRow ] = useState(isMobile ? 2 : productBrowseItemType === 0 ? 3 : 6)
    const [ minWidth, setMinWidth ] = useState(productBrowseItemType === 0 ? 400 : 200);
    const [ itemWidth, setItemWidth ] = useState(containerWidth / itemsInRow);

    const itemHeightDivider = productBrowseItemType === 0 ? 1.85 : 1;
    const imgHeight = productBrowseItemType === 0 ? itemWidth / itemHeightDivider : ( itemWidth - 14) / itemHeightDivider;

    React.useEffect(() => {
        window.addEventListener("resize", function(event){ updateDimensions() });
        window.addEventListener("orientationchange",  function(event){ updateDimensions() });
    },[])

    function updateDimensions(){
        const newContainerWidth = $('#product-browse-container').width() + 30;
        setContainerWidth(newContainerWidth);
    }

    let productsDisplay;
    if (itemWidth){
        const productList = products.sort(SortByCurrentFilter).map((p,index) => (
            <ProductBrowseItem
                key={index} 
                index={index}
                product={p}
                productBrowseItemType={productBrowseItemType}
                itemWidth={itemWidth}
                imgHeight={imgHeight}
            />
        ));

        productsDisplay = (
            <div id="product-browse-list-container">
                {productList}
                <ProductBrowsePagination/>
            </div>
        )
    } else {
        productsDisplay = "Loading..."
    }

    return (
        <div id="product-browse-item-list" className={isMobile ? "mobile" : ""}>
            {productsDisplay}
        </div>
    )
}

function ProductBrowseItem(props){

    const p = props.product;

    const [ productsFetched, setProductFetched ] = useState(false);
    const [ productFiles, setProductFiles ] = useState();


    let imgUrl = "";
    if (p.image_small && p.image_small.indexOf('https://') > -1 || p.image_small && p.image_small.indexOf('http://') > -1 ) imgUrl = p.image_small;
    else {
        imgUrl = "https://cn.opendesktop.";
        imgUrl += window.location.host.endsWith('org') === true || window.location.host.endsWith('com') === true  ? "org" : "cc";
        imgUrl += "/cache/" + Math.ceil(props.itemWidth * 2) + "x" + Math.ceil(props.imgHeight * 2) + "/img/" + p.image_small;    
    }

    let itemLink = json_serverUrl;
    itemLink = json_store_name === "ALL" ? "/" : "/s/" + json_store_name + "/";
    itemLink += p.type_id === "3" ? "c" : "p";
    itemLink += "/" + p.project_id;    

    React.useEffect(() => {

        if (props.productBrowseItemType === 1 && productsFetched === false){
            setProductFetched(true);
            const ajaxUrl = window.location.origin + "/p/"+p.project_id+"/loadfilesjson";
            $.ajax({
                url: ajaxUrl
            }).done(function(res) {
                let newProductFiles = [];
                res.forEach(function(f,index){
                    if ( f.type.split('/')[0] === "audio"){
                        let nf = f;
                        nf.musicSrc = f.url.replace(/%2F/g,'/').replace(/%3A/g,':');
                        nf.cover = imgUrl;
                        newProductFiles.push(nf);
                    }
                });
                setProductFiles(newProductFiles);
            });
        }

    },[])
        
    let itemInfoDisplay;
    if (props.productBrowseItemType === 0){
        itemInfoDisplay = (
            <div className="product-browse-item-info">
                <h2>{p.title}</h2>
                <span>{p.cat_title}</span>
                <span>by <a>{p.username}</a></span>
            </div>
        )
    }

    let musicItemInfoDisplay, musicPlayerDisplay;
    if (props.productBrowseItemType === 1){
        musicItemInfoDisplay = (
            <div className="product-browse-music-item-info">
                <h2>{p.title}</h2>
                <span>{p.cat_title}</span>
                <span>by <b>{p.username}</b></span>
            </div>            
        );
        if (productFiles && productFiles.length > 0) musicPlayerDisplay = <ProductBrowseItemPreviewMusicPlayer productFiles={productFiles} projectId={p.project_id} imgHeight={props.imgHeight}/>
    }
    
    return (
        <div className={"product-browse-item "} id={"product-" + p.project_id} style={{"width":props.itemWidth}}>
            <div className="wrapper">
                {musicPlayerDisplay}
                <a href={itemLink} className="product-browse-item-wrapper">
                    <div className="product-browse-image">
                        <img src={imgUrl} height={props.imgHeight}/>
                        {musicItemInfoDisplay}
                    </div>
                    {itemInfoDisplay}
                </a>
            </div>
        </div>
    )
}

function ProductBrowseItemPreviewMusicPlayer(props){

    const [ productFiles, setProductFiles ] = useState(props.productFiles)
    const [ showAudioControls, setShowAudioControls ] = useState(false);
    const [ playIndex, setPlayIndex ] = useState();

    let musicPlayerDisplay;

    if (productFiles) {

        const options = {
            //audio lists model
            audioLists:productFiles,
            audioListsPanelVisible:false,
            //default play index of the audio player  [type `number` default `0`]
            defaultPlayIndex: 0,
            //if you want dynamic change current play audio you can change it [type `number` default `0`]
            // playIndex: 0,
            //color of the music player theme    [ type `string: 'light' or 'dark'  ` default 'dark' ]
            theme: "dark",
            // Specifies movement boundaries. Accepted values:
            // - `parent` restricts movement within the node's offsetParent
            //    (nearest node with position relative or absolute), or
            // - a selector, restricts movement within the targeted node
            // - An object with `left, top, right, and bottom` properties.
            //   These indicate how far in each direction the draggable
            //   can be moved.
            bounds: "product-"+props.projectId,
            //Whether to load audio immediately after the page loads.  [type `Boolean | String`, default `false`]
            //"auto|metadata|none" "true| false"
            preload: false,
            //Whether the player's background displays frosted glass effect  [type `Boolean`, default `false`]
            glassBg: false,
            //The next time you access the player, do you keep the last state  [type `Boolean` default `false`]
            remember: false,
            //The Audio Can be deleted  [type `Boolean`, default `true`]
            remove: false,
            //audio controller initial position    [ type `Object` default '{top:0,left:0}' ]
            defaultPosition: {
              top: 50,
              left: 50
            },
            // play mode text config of the audio player
            playModeText: {
              order: "order",
              orderLoop: "loop",
              singleLoop: "single loop",
              shufflePlay: "shuffle"
            },
            //audio controller open text  [ type `String | ReactNode` default 'open']
            openText: "open",
            //audio controller close text  [ type `String | ReactNode` default 'close']
            closeText: "close",
            //audio theme switch checkedText  [ type `String | ReactNode` default '-']
            checkedText: "dark",      
            //audio theme switch unCheckedText [ type `String | ReactNode` default '-']
            unCheckedText: "light",
            // audio list panel show text of the playlist has no songs [ type `String` | ReactNode  default 'no music']
            notContentText: "No Music",
            panelTitle: "Test",
            defaultPlayMode: "order",
            //audio mode        mini | full          [type `String`  default `mini`]
            mode: "full",
              // [ type `Boolean` default 'false' ]
              // The default audioPlay handle function will be played again after each pause, If you only want to trigger it once, you can set 'true'
            once: true,
            //Whether the audio is played after loading is completed. [type `Boolean` default 'true']
            autoPlay: false,
            //Whether you can switch between two modes, full => mini  or mini => full   [type 'Boolean' default 'true']
            toggleMode: false,
            //audio cover is show of the "mini" mode [type `Boolean` default 'true']
            showMiniModeCover: false,   
            //audio playing progress is show of the "mini"  mode
            showMiniProcessBar: false,
            //audio controller is can be drag of the "mini" mode     [type `Boolean` default `true`]
            drag: true,
            //drag the audio progress bar [type `Boolean` default `true`]
            seeked: false,
            //audio controller title [type `String | ReactNode`  default <FaHeadphones/>]
            // controllerTitle: <FaHeadphones />,
            //Displays the audio load progress bar.  [type `Boolean` default `true`]
            showProgressLoadBar: false,
            //play button display of the audio player panel   [type `Boolean` default `true`]
            showPlay: true,
            //reload button display of the audio player panel   [type `Boolean` default `true`]
            showReload: false,
            //download button display of the audio player panel   [type `Boolean` default `true`]
            showDownload: false,
            //loop button display of the audio player panel   [type `Boolean` default `true`]
            showPlayMode: false,
            //theme toggle switch  display of the audio player panel   [type `Boolean` default `true`]
            showThemeSwitch: false,
            //lyric display of the audio player panel   [type `Boolean` default `false`]
            showLyric: false,
            //Extensible custom content       [type 'Array' default '[]' ]
            extendsContent: [],
            //default volume of the audio player [type `Number` default `100` range `0-100`]
            defaultVolume: 30,
            //playModeText show time [type `Number(ms)` default `700`]
            playModeShowTime: 600,
            //Whether to try playing the next audio when the current audio playback fails [type `Boolean` default `true`]
            loadAudioErrorPlayNext: false,
            //Music is downloaded handle
            //onAudioDownload(audioInfo) { console.log("audio download", audioInfo); },
            //audio play handle
            onAudioPlay(audioInfo) {
                console.log('audio play');
                setShowAudioControls(true);
                const currentIndex = productFiles.findIndex(f => audioInfo.name === f.title);
                setPlayIndex(currentIndex + 1);
            },
            //audio pause handle
            onAudioPause(audioInfo) { 
              console.log("audio pause"); 
              setShowAudioControls(false)
            },
            //When the user has moved/jumped to a new location in audio
            onAudioSeeked(audioInfo) { console.log("audio seeked", audioInfo); },
            //When the volume has changed  min = 0.0  max = 1.0
            onAudioVolumeChange(currentVolume) { console.log("audio volume change", currentVolume); },
            //The single song is ended handle
            onAudioEnded(audioInfo) { console.log("audio ended", audioInfo); },
            //audio load abort The target event like {...,audioName:xx,audioSrc:xx,playMode:xx}
            onAudioAbort(e) { console.log("audio abort", e); },
            //audio play progress handle
            onAudioProgress(audioInfo) { 
                if (audioInfo.paused === false){
                    $('#music-player-'+props.projectId).find('.play-btn.play').trigger("click");
                }
            },
            //audio reload handle
            onAudioReload(audioInfo) { 
                console.log("audio reload:", audioInfo);
            },
            //audio load failed error handle
            onAudioLoadError(e) { 
                console.log("audio load err", e); 
            },
            //theme change handle
            onThemeChange(theme) { 
                console.log("theme change:", theme); 
            },
            //audio lists change
            onAudioListsChange(currentPlayId, audioLists, audioInfo) {
              console.log("[currentPlayId] audio lists change:", currentPlayId);
              console.log("[audioLists] audio lists change:", audioLists);
              console.log("[audioInfo] audio lists change:", audioInfo);
            },
            onAudioPlayTrackChange(currentPlayId, audioLists, audioInfo) {
                const currentIndex = productFiles.findIndex(f => audioInfo.name === f.title);
                setPlayIndex(currentIndex + 1);
            },
            onPlayModeChange(playMode) { 
                console.log("play mode change:", playMode); 
            },
            onModeChange(mode) { 
                console.log("mode change:", mode); 
            },
            onAudioListsPanelChange(panelVisible) {
              /*const newShowPlayListValue = showPlaylist === true ? false : true;
              setShowPlaylist(newShowPlayListValue);*/
            }, 
            onAudioListsDragEnd(fromIndex, endIndex) {
              console.log("audio lists drag end:", fromIndex, endIndex);
            },
            onAudioLyricChange(lineNum, currentLyric) {
              console.log("audio lyric change:", lineNum, currentLyric);
            }
        };
    
        musicPlayerDisplay = (
            <div>
                <ReactJkMusicPlayer {...options} />
                <span className="music-player-counter">{playIndex}/{productFiles.length}</span>
            </div>
        )
    }

    let showControlsCssClass = "";
    if (showAudioControls === true) {
        showControlsCssClass = "show-controls"
    }

    return (
        <div className={"product-browse-item-preview-music-player " + showControlsCssClass} id={"music-player-"+props.projectId}>
            {musicPlayerDisplay}
        </div>
    )
}

function ProductBrowsePagination(){
    
    const [ totalItems, setTotalItems ] = useState(pagination.totalcount);
    const [ itemsPerPage, setItemsPerPage ] = useState(50);
    const [ currentPage, setCurrentPage ] = useState(pagination.page);
    const [ totalPages, setTotalPages ] = useState(Math.ceil(totalItems / itemsPerPage));

    const minPage = currentPage - 5 > 0 ? currentPage - 5 : 0;
    const maxPage = minPage + 10 < totalPages ? minPage + 10 : totalPages;

    let paginationArray = [];
    for (var i = minPage; i < maxPage; i++){ paginationArray.push(i + 1); }
    
    let pageLinkBase = json_serverUrl;
    pageLinkBase += json_store_name === "ALL" ? "/" : "/s/" + json_store_name + "/";
    pageLinkBase += "browse/page/";

    let pageLinkSuffix = "/" 
    if (typeof filters.category === 'number') pageLinkSuffix += "cat/" + filters.category + "/";
    pageLinkSuffix += "ord/" + filters.order + "/";
    if (filters.original !== null) pageLinkSuffix += "filteroriginal/" + filters.original + window.location.search;

    let previousButtonDisplay;
    if (currentPage > 1) previousButtonDisplay = <li><a href={pageLinkBase + (currentPage - 1) + pageLinkSuffix}><span className="glyphicon glyphicon-chevron-left"></span> Previous</a></li>

    let nextButtonDisplay;
    if (currentPage < totalPages) nextButtonDisplay = <li><a href={pageLinkBase + (currentPage + 1) + pageLinkSuffix}>Next <span className="glyphicon glyphicon-chevron-right"></span></a></li>

    const paginationDisplay = paginationArray.map((p,index) => {
        let pageLinkDisplay;
        if (currentPage === p) pageLinkDisplay = <span className="no-link">{p}</span>
        else pageLinkDisplay = <a href={pageLinkBase + p + pageLinkSuffix}>{p}</a>
        return (                
            <li key={index}>
                {pageLinkDisplay}
            </li>
        )
    });

    return (
        <div id="product-browse-pagination">
            <ul>
                {previousButtonDisplay}
                {paginationDisplay}
                {nextButtonDisplay}
            </ul>
        </div>
    )
}

const rootElement = document.getElementById("product-browse-container");
ReactDOM.render(<ProductBrowse />, rootElement);