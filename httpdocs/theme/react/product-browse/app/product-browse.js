import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import {SortByCurrentFilter} from './product-browse-helpers';
import ReactJkMusicPlayer from "react-jinke-music-player";
import {isMobile} from 'react-device-detect';

function ProductBrowse(){
    console.log(window.config);
    return (
        <div id="product-browse">
            <ProductBrowseFilterContainer/>
            <ProductBrowseItemList />
            <ProductBrowsePagination/>
        </div>
    )
}

function ProductBrowseFilterContainer(){

    let filtersBaseUrl = window.config.baseUrl + "/browse/";
    if (typeof filters.category === Number) filtersBaseUrl += "cat/" + filters.category + "/";

    function onOriginalCheckboxClick(){
        let val = filters.original !== null ? 0 : 1;
        window.location.href = window.config.baseUrl + "/" + window.location.pathname + "filteroriginal/" + val;
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
    console.log(files);
    const productsDisplay = products.sort(SortByCurrentFilter).map((p,index) => (
        <ProductBrowseItem
            key={index} 
            index={index}
            product={p}
        />
    ))

    return (
        <div id="product-browse-item-list">
            {productsDisplay}
        </div>
    )
}

function ProductBrowseItem(props){

    const p = props.product;
    const containerWidth = $('#product-browse-container').width() + 30;

    let productBrowseItemType = 0;
    if (window.location.search === "?index=3") productBrowseItemType = 1;
    
    const itemsInRow = productBrowseItemType === 0 ? 3 : 6;
    const itemWidth = containerWidth / itemsInRow;

    const itemHeightDivider = productBrowseItemType === 0 ? 1.85 : 1;
    const imgHeight = productBrowseItemType === 0 ? itemWidth / itemHeightDivider : ( itemWidth - 30) / itemHeightDivider;

    let imgUrl = "https://cn.opendesktop.";
    imgUrl += window.location.host.endsWith('org') === true || window.location.host.endsWith('com') === true  ? "org" : "cc";
    imgUrl += "/img/" + p.image_small;

    let itemLink = window.config.baseUrl + "/";
    itemLink += p.type_id === "3" ? "c" : "p";
    itemLink += "/" + p.project_id;
    
    let itemInfoDisplay;
    if (productBrowseItemType === 0){
        itemInfoDisplay = (
            <div className="product-browse-item-info">
                <h2>{p.title}</h2>
                <span>{p.cat_title}</span>
                <span>by <a>{p.username}</a></span>
            </div>
        )
    }

    let musicItemInfoDisplay;
    if (productBrowseItemType === 1){
        musicItemInfoDisplay = (
            <div className="product-browse-music-item-info">
                <h2>{p.title}</h2>
                <span>{p.cat_title}</span>
                <span>by <a>{p.username}</a></span>
            </div>            
        )
    }

    let musicPlayerDisplay;
    if (productBrowseItemType === 1){
        let productFiles = []
        files.forEach(function(f,index){
            if (f.project_id === p.project_id && f.type.split('/')[0] === "audio"){
                const nf = f;
                nf.musicSrc = f.url.replace(/%2F/g,'/').replace(/%3A/g,':');
                productFiles.push(nf);
            }
        });
        console.log(productFiles);
        if (productFiles.length > 0 ){
            musicPlayerDisplay = <ProductBrowseItemPreviewMusicPlayer files={productFiles} projectId={p.project_id} imgHeight={imgHeight}/>
        }
    }

    return (
        <div className={"product-browse-item " + (itemsInRow === 6 ? "six-in-row" : "three-in-row")} id={"product-" + p.project_id} style={{"width":itemWidth}}>
            <div className="wrapper">
                {musicPlayerDisplay}
                <a href={itemLink} className="product-browse-item-wrapper">
                    <div className="product-browse-image">
                        <img src={imgUrl} height={imgHeight}/>
                        {musicItemInfoDisplay}
                    </div>
                    {itemInfoDisplay}
                </a>
            </div>
        </div>
    )
}

function ProductBrowseItemPreviewMusicPlayer(props){

    let musicPlayerDisplay;

    if (props.files) {

        const options = {
            //audio lists model
            audioLists:props.files,
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
            },
            //audio pause handle
            onAudioPause(audioInfo) { 
              console.log("audio pause"); 
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
                console.log(audioInfo.paused);
                if (audioInfo.paused === false) $('#music-player-'+props.projectId).find('.play-btn.play').trigger("click");
            },
            //audio reload handle
            onAudioReload(audioInfo) { console.log("audio reload:", audioInfo);},
            //audio load failed error handle
            onAudioLoadError(e) { console.log("audio load err", e); },
            //theme change handle
            onThemeChange(theme) { console.log("theme change:", theme); },
            //audio lists change
            onAudioListsChange(currentPlayId, audioLists, audioInfo) {
              console.log("[currentPlayId] audio lists change:", currentPlayId);
              console.log("[audioLists] audio lists change:", audioLists);
              console.log("[audioInfo] audio lists change:", audioInfo);
              console.log(audioInfo)
            },
            onAudioPlayTrackChange(currentPlayId, audioLists, audioInfo) {
                console.log( "audio play track change:", currentPlayId, audioLists, audioInfo ); 
                // $('#music-player-'+props.projectId).find('.play-btn[title="Click to play"]').trigger("click");
            },
            onPlayModeChange(playMode) { console.log("play mode change:", playMode); },
            onModeChange(mode) { console.log("mode change:", mode); },
            onAudioListsPanelChange(panelVisible) {
              const newShowPlayListValue = showPlaylist === true ? false : true;
              setShowPlaylist(newShowPlayListValue);
            }, 
            onAudioListsDragEnd(fromIndex, endIndex) {
              console.log("audio lists drag end:", fromIndex, endIndex);
            },
            onAudioLyricChange(lineNum, currentLyric) {
              console.log("audio lyric change:", lineNum, currentLyric);
            }
        };
    
        musicPlayerDisplay = <ReactJkMusicPlayer {...options} />
        
        
    }

    return (
        <div className="product-browse-item-preview-music-player" id={"music-player-"+props.projectId}>
            {musicPlayerDisplay}
        </div>
    )
}


function ProductBrowsePagination(){

    const [ totalItems, setTotalItems ] = useState(pagination.totalcount);
    const [ itemsPerPage, setItemsPerPage ] = useState(50);
    const [ currentPage, setCurrentPage ] = useState(pagination.page);
    const [ totalPages, setTotalPages ] = useState(Math.ceil(totalItems / itemsPerPage));

    let minPage = 0, maxPage = 10;
    if (currentPage > 5){
        minPage = currentPage - 5;
        maxPage = currentPage + 5;
    }

    let paginationArray = [];
    for (var i = minPage; i < maxPage; i++){ paginationArray.push(i + 1); }
    
    let pageLinkBase = window.config.baseUrl + "/browse/page/";
    if (typeof filters.category === Number) pageLinkBase += "cat/" + filters.category + "/";
    let pageLinkSuffix = "/ord/" + filters.order;
    if (filters.original !== null) pageLinkSuffix += "/filteroriginal/" + filters.original + window.location.search;

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