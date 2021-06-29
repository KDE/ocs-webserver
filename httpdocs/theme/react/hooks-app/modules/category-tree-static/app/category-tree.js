import React, { useState, useContext, useEffect } from 'react';
import StoreContextProvider, { Context } from './context/context-provider';
import {
    ConvertObjectToArray, 
    GetCategoriesBySearchPhrase, 
    sortArrayAlphabeticallyByTitle,
    getUrlContext
} from './category-helpers';

let isShowRealDomainAsUrl = 1, 
    showStoreListingsFirst = false;
    
if (window.is_show_real_domain_as_url === 1) isShowRealDomainAsUrl = 1;
else {
    if (window.config.sName === "www.pling.cc" || window.config.sName === "www.pling.com"){ isShowRealDomainAsUrl = 1; }
    if (window.location.href === "https://www.pling.cc" || window.location.href === "https://www.pling.com" || window.location.href === "http://192.168.2.124" ||
        window.location.href === "https://www.pling.cc/" || window.location.href === "https://www.pling.com/" || window.location.href === "http://192.168.2.124/"){
        showStoreListingsFirst = true;
    }
}

import BlurBG from '../../../layout/style/media/blur2.png';

function CategoryTreeWrapper(props){
    return (
        <StoreContextProvider>
            <CategoryTree {...props} />
        </StoreContextProvider>
    )
}

function CategoryTree(props){

    /* STATE */

    const { catTreeState, catTreeDispatch } = useContext(Context);

    const [ showBackButton, setShowBackButton ] = useState(true);
    const [ showBreadCrumbs, setShowBreadCrumbs ] = useState(true);
    const [ showForwardButton, setShowForwardButton ] = useState(false);

    const [ showCatMenu, setShowCatMenu ] = useState(true);
    const [ showTagMenu, setShowTagMenu ] = useState(true);
    const [ catContainerHeight, setCatContainerHeight ] = useState();
    const [ tagsContainerHeight, setTagsContainerHeight ] = useState();

    /* COMPONENT */

    React.useEffect(() => { 
        onSearchPhraseUpdate();
    },[catTreeState.searchPhrase])

    // on search phrase update
    function onSearchPhraseUpdate(){
        let newSearchMode = false;
        const searchPhrase = catTreeState.searchPhrase;
        const searchMode = catTreeState.searchMode;
        if (searchPhrase){
            let newCurrentViewedCategories;
            if  (searchPhrase.length > 0){
                newSearchMode = true;
                newCurrentViewedCategories = catTreeState.currentViewedCategories;
                catTreeDispatch({type:'SET_CURRENT_VIEWED_CATEGORIES',val:newCurrentViewedCategories});
                if (searchMode === true) newCurrentViewedCategories.length = catTreeState.selectedCategoriesId.length;
                let searchPhraseCategories = GetCategoriesBySearchPhrase(catTreeState.categoryTree,searchPhrase);
                newCurrentViewedCategories = [
                    ...newCurrentViewedCategories,
                    {categoryId:"-1",id:"-1",title:'Search',searchPhrase:searchPhrase,categories:searchPhraseCategories}
                ]
            } else {
                newCurrentViewedCategories = catTreeState.currentViewedCategories;
                newCurrentViewedCategories.length = catTreeState.selectedCategoriesId.length;
            }
            catTreeDispatch({type:'SET_CURRENT_VIEWED_CATEGORIES',val:newCurrentViewedCategories});
            catTreeDispatch({type:'SET_CURRENT_CATEGORY_LEVEL',val:newCurrentViewedCategories.length});
        } else if (searchMode === true){
            let newCurrentViewedCategories = catTreeState.currentViewedCategories;
            newCurrentViewedCategories.length = catTreeState.selectedCategoriesId.length;
            catTreeDispatch({type:'SET_CURRENT_VIEWED_CATEGORIES',val:newCurrentViewedCategories});
            catTreeDispatch({type:'SET_CURRENT_CATEGORY_LEVEL',val:newCurrentViewedCategories.length});
        }
        catTreeDispatch({type:'SET_SEARCH_MODE',val:newSearchMode})
    }

    // on header navigation item click
    function onHeaderNavigationItemClick(cvc){
        const trimedCurrentViewedCategoriesArray = catTreeState.currentViewedCategories;
        trimedCurrentViewedCategoriesArray.length = cvc.level + 1;
        catTreeDispatch({type:'SET_CURRENT_VIEWED_CATEGORIES',val:trimedCurrentViewedCategoriesArray});
        catTreeDispatch({type:'SET_CURRENT_CATEGORY_LEVEL',val:cvc.level});
    }

    // go back
    function goBack(){
        const newCurrentCategoryLevel = catTreeState.currentCategoryLevel - 1;
        catTreeDispatch({type:'SET_CURRENT_CATEGORY_LEVEL',val:newCurrentCategoryLevel});
        const newSearchPhrase = '';
        const newSearchMode = false;
        catTreeDispatch({type:'SET_SEARCH_PHRASE',val:newSearchPhrase})
        catTreeDispatch({type:'SET_SEARCH_MODE',val:newSearchMode})
    }

    // go forward
    function goForward(){
        const newCurrentCategoryLevel = catTreeState.currentCategoryLevel + 1;
        catTreeDispatch({type:'SET_CURRENT_CATEGORY_LEVEL',val:newCurrentCategoryLevel});
        const newSearchPhrase = '';
        const newSearchMode = false;
        catTreeDispatch({type:'SET_SEARCH_PHRASE',val:newSearchPhrase})
        catTreeDispatch({type:'SET_SEARCH_MODE',val:newSearchMode})
    }

    // on category panel item click
    function onCategoryPanleItemClick(ccl,cvc){
        const newCurrentCategoryLevel = ccl;
        const newCurrentViewedCategories = cvc;
        catTreeDispatch({type:'SET_CURRENT_VIEWED_CATEGORIES',val:newCurrentViewedCategories});
        catTreeDispatch({type:'SET_CURRENT_CATEGORY_LEVEL',val:newCurrentCategoryLevel});
    }

    // search phrase
    function onSetSearchPhrase(e){
        catTreeDispatch({type:'SET_SEARCH_PHRASE',val:e.target.value})
    }

    // slider height update
    function onSliderHeightUpdate(sliderHeight){
        setCatContainerHeight(sliderHeight + 82);
    }

    function onTagsHeightUpdate(tagsHeight){
        setTagsContainerHeight(tagsHeight);
    }

    /* RENDER */

    let categoryTreeDisplay;
    if (catTreeState.categoryTree && catTreeState.categoryTree !== null){

        let activeCategoryStyle;
        if (props.storeStyle){
            let color = props.storeStyle["header-nav-tabs"].link["color-active"];
            activeCategoryStyle = {
                backgroundColor:props.storeStyle["header-nav-tabs"]["background-color-hover"],
                color:color
            }
        }

        let catAccordionStyle = {
            height:catContainerHeight,
            transition:"0.4s"
        }

        if (showCatMenu === false){
            catAccordionStyle.height = 0;
            catAccordionStyle.overflow = "hidden";
            catAccordionStyle.transition = "0s";
        }

        let tagsAccordionStyle = {
            height:tagsContainerHeight,
            transition:"0.2s"
        }

        if (showTagMenu === false){
            tagsAccordionStyle.height = 0
            tagsAccordionStyle.overflow = "hidden";
        }

        let tagCloudDisplay;
        if (window.tagCloud && window.tagCloud.length > 0){
            tagCloudDisplay = (
                <div className="tag-sub-menu-accordion accordion accordion-flush" id="accordion-tags">
                    <div className="accordion-item">
                      <h2 onClick={() => setShowTagMenu(showTagMenu === true ? false : true)}  className="accordion-header" id="flush-headingOne">
                        <button className={"accordion-button " + (showTagMenu === true ? "" : "collapsed")} type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="true" aria-controls="flush-collapseOne">
                            <span className="title-small-upper">Tag subcategories</span>
                        </button>
                      </h2>
                      <div style={tagsAccordionStyle} id="flush-collapseOne" className={"accordion-collapse collapse show"} aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                        <div className="accordion-body">
                            <CategoryTagCloud onTagsHeightUpdate={onTagsHeightUpdate} selectedCategory={catTreeState.selectedCategory ? catTreeState.selectedCategory.id : props.selectedCategory} tags={props.tags} />
                        </div>
                      </div>
                    </div>
                </div>
            )
        }

        categoryTreeDisplay = (
            <div className="pui-sidebar-content">
                <div className="tag-sub-menu-accordion accordion accordion-flush" id="accordionFlushMenu">
                    <div className="accordion-item">
                        <h2 onClick={() => setShowCatMenu(showCatMenu === true ? false : true)} className="accordion-header" id="flush-headingMenu" >
                            <button className={"accordion-button " + (showCatMenu === true ? "" : "collapsed")} type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseMenu" aria-expanded="true" aria-controls="flush-collapseMenu">
                                <span className="title-small-upper">Categories</span>
                            </button>
                        </h2>
                        <div style={catAccordionStyle} id="flush-collapseMenu" className={"accordion-collapse collapse show"} aria-labelledby="flush-headingMenu" data-bs-parent="#accordionFlushMenu">
                            <div className="accordion-body">
                                <CategoryTreeHeader 
                                    currentCategoryLevel={catTreeState.currentCategoryLevel}
                                    currentViewedCategories={catTreeState.currentViewedCategories}
                                    showBackButton={showBackButton}
                                    showBreadCrumbs={showBreadCrumbs}
                                    showForwardButton={showForwardButton}
                                    storeInfo={catTreeState.storeInfo}
                                    onHeaderNavigationItemClick={(cvc) => onHeaderNavigationItemClick(cvc)}
                                    onGoBackClick={goBack}
                                    onGoForwardClick={goForward}
                                    catTreeFilter={catTreeState.catTreeFilter}
                                    onSetSearchPhrase={onSetSearchPhrase}
                                    activeCategoryStyle={activeCategoryStyle}
                                />
                                <CategoryPanelsContainer
                                    categoryTree={catTreeState.categoryTree}
                                    categoryId={catTreeState.categoryId}
                                    searchPhrase={catTreeState.searchPhrase}
                                    searchMode={catTreeState.searchMode}
                                    currentCategoryLevel={catTreeState.currentCategoryLevel}
                                    currentViewedCategories={catTreeState.currentViewedCategories}
                                    selectedCategoriesId={catTreeState.selectedCategoriesId}
                                    storeInfo={catTreeState.storeInfo}
                                    onCategoryPanleItemClick={(ccl,cvc,catLink) => onCategoryPanleItemClick(ccl,cvc,catLink)}
                                    onSetShowBreadCrumbs={(val) => setShowBreadCrumbs(val)}
                                    onSetShowBackButton={(val) => setShowBackButton(val)}
                                    onSetShowForwardButton={(val) => setShowForwardButton(val)}
                                    catTreeFilter={catTreeState.catTreeFilter}
                                    activeCategoryStyle={activeCategoryStyle}
                                    onSliderHeightUpdate={onSliderHeightUpdate}
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <hr className="hr-dark"/>
                {tagCloudDisplay}
            </div>
        )
    }

    return(
        <React.Fragment>
            {categoryTreeDisplay}
            <img className="sidebar-bg" src={BlurBG}/>
        </React.Fragment>
    )
}

function CategoryTreeHeader(props){

    const initialCurrentViewedCategories = props.currentViewedCategories.slice(0,props.currentCategoryLevel);
    const [ categories, setCategories ] = useState(initialCurrentViewedCategories);

    React.useEffect(() => {
        const newCurrentViewedCategories = props.currentViewedCategories.slice(0,props.currentCategoryLevel);
        setCategories(newCurrentViewedCategories);
    },[props.currentViewedCategories,props.currentCategoryLevel])

    function onHeaderNavigationItemClick(cvc,index){
        props.onHeaderNavigationItemClick(cvc);
        const newCategories = categories;
        newCategories.length = index + 1;
        setCategories(newCategories)
    }

    const headerCatStyle = {
        color: "#5551FF",
        backgroundColor: "rgba(255,255,255,.5)"
    }

    let categoryTreeHeaderNavigationDisplay;
    if (categories.length > 0){
        categoryTreeHeaderNavigationDisplay = categories.map((cvc,index) =>{
            if (categories.length === index + 1){
                let catLink;                                
                if (cvc.title !== "Search") catLink = getUrlContext(window.location.href) + ( cvc.id === "00" ? "/browse?" : "/browse?cat="+cvc.id+"&order=latest")
                if (props.catTreeFilter=='filter_favourites') catLink+='fav/1';
                return (
                    <li key={index}>
                        <a style={headerCatStyle} href={catLink} onClick={(e) => onHeaderNavigationItemClick(e,cvc,index,catLink)}>
                            {cvc.title}
                        </a>
                    </li>
                )
            }
        })
    }

    let sNameDisplay;
    //if (props.showBreadCrumbs === true){
        if (categories.length === 0){
            if (props.storeInfo){
                let storeName = window.config.sName, storeHref = window.config.sName;                               
                if (props.storeInfo.name.length > 0) storeName = props.storeInfo.name;
                if (props.storeInfo.menuhref.length > 0) storeHref = props.storeInfo.menuhref;
                if (props.catTreeFilter=='filter_favourites') storeHref= storeHref+'/my-favourites';
                else storeHref += "/browse";
                sNameDisplay = <li><a style={headerCatStyle} href={storeHref}>{storeName}</a></li>
            }
        }    
    //}

    let backButtonDisplay;
    if (props.showBackButton) backButtonDisplay = <a id="back-button" onClick={props.onGoBackClick}><i className="bi bi-arrow-left-square-fill"></i></a>;
    else backButtonDisplay = <a id="back-button" className="cat-button-inactive"><i className="bi bi-arrow-left-square-fill"></i></a>;

    let forwadButtonDisplay =  <a id="forward-button" className="cat-button-inactive"><i className="bi bi-arrow-right-square-fill"></i></a>
    if (props.showForwardButton === true) forwadButtonDisplay = <a id="forward-button" onClick={props.onGoForwardClick}><i className="bi bi-arrow-right-square-fill"></i></a>

    return (
        <React.Fragment>
            <div id="category-tree-header" className="category-controls">
                {backButtonDisplay}
                {forwadButtonDisplay}
                <input type="text" placeholder="Search categories" defaultValue={props.searchPhrase} onChange={e => props.onSetSearchPhrase(e)}/>
            </div>
            <ul className="category-panel-ul" style={{display:"flex"}}>
                {sNameDisplay}
                {categoryTreeHeaderNavigationDisplay}
            </ul>
        </React.Fragment>
    )
}

function CategoryPanelsContainer(props){

    /* STATE */
 
    const rootListingPanel = {categoryId:0,categories:props.categoryTree}
    const storeListingPanel = {categoryId:-1,categories:[...window.json_store_for_tree]}
    let initialRootCategoryPanels = [rootListingPanel];
    // if (isShowRealDomainAsUrl  === 1) initialRootCategoryPanels = [storeListingPanel,rootListingPanel];
    let initialPanelsValue = initialRootCategoryPanels;
    if (props.currentViewedCategories.length > 0) initialPanelsValue = initialRootCategoryPanels.concat(props.currentViewedCategories);
    const [ panels, setPanels ] = useState(initialPanelsValue);
    const [ containerWidth, setContainerWidth ] = useState(window.innerWidth > 767 ? 208 : window.innerWidth - 32);
    const [ sliderWidth, setSliderWidth ] = useState(containerWidth * panels.length);
    const [ sliderHeight, setSliderHeight ] = useState();
    let currentCategoryLevel = props.currentCategoryLevel;
    if (window.location.href === "https://www.pling.com/" || window.location.href === "https://www.pling.cc/") currentCategoryLevel = 0;
    let initialSliderPosition = currentCategoryLevel * containerWidth;
    const [ sliderPosition, setSliderPosition ] = useState(initialSliderPosition);
    let initialShowBackButtonValue = true;
    if (sliderPosition === 0) initialShowBackButtonValue = false;
    const [ showBackButton, setShowBackButton ] = useState(initialShowBackButtonValue);

    const [ containerVisibility, setContainerVisibility ] = useState(false);

    /* COMPONENT */

    React.useEffect(() => {
        props.onSliderHeightUpdate(sliderHeight);
    },[sliderHeight])

    React.useEffect(() => {

        let showBack = true, showBreadCrumbs = true, showForward = false;
        let minSliderPosition = 0;
        if (props.storeInfo && props.storeInfo.is_show_in_menu === "0"){
            if (window.location.href !== "https://www.pling.com/" || window.location.href !== "https://www.pling.cc/") minSliderPosition = containerWidth;
        }

        if (sliderPosition === minSliderPosition){
            showBack = false;
            showBreadCrumbs = false;
            if (panels.length > 1 && sliderPosition > 0) showBreadCrumbs = true;
        }
        
        let maxSliderPosition = ( containerWidth * panels.length ) - containerWidth;
        if (sliderPosition !== maxSliderPosition) {
            showForward = true;
        }

        if (window.location.href === "https://www.pling.com/" || window.location.href === "https://www.pling.cc/"){
            showForward = false;
        }

        props.onSetShowBackButton(showBack);
        props.onSetShowBreadCrumbs(showBreadCrumbs);
        props.onSetShowForwardButton(showForward);

    },[sliderPosition]);

    React.useEffect(() => { updateSlider() },[props.currentCategoryLevel,props.currentViewedCategories])
    React.useEffect(() => { updatePanlesOnSearch() },[props.searchMode,props.searchPhrase])

    // update slider
    function updateSlider(){
        /*const trimedPanelsArray =  [...initialRootCategoryPanels,...props.currentViewedCategories];
        if (props.searchMode === false ){
            let currentCategoryLevel = props.currentCategoryLevel;
            if (isShowRealDomainAsUrl === 1 ) currentCategoryLevel = props.currentCategoryLevel + 1;
            trimedPanelsArray.length = currentCategoryLevel + 1;
        }
        setPanels(trimedPanelsArray);

        const newSliderWidth = containerWidth * trimedPanelsArray.length;
        setSliderWidth(newSliderWidth);*/

        let currentCategoryLevel = props.currentCategoryLevel;
        let newSliderPosition = currentCategoryLevel * containerWidth;
        if (window.location.href === "https://www.pling.com/" || window.location.href === "https://www.pling.cc/"){
            if (props.searchMode !== true) newSliderPosition = 0;
        }
        setSliderPosition(newSliderPosition);

        let newShowBackButton = true;
        if (newSliderPosition === 0) newShowBackButton = false;
        setShowBackButton(newShowBackButton);
    }

    // update panels on search
    function updatePanlesOnSearch(){
        const newPanels = [...initialRootCategoryPanels,...props.currentViewedCategories];
        const newSliderWidth = containerWidth * newPanels.length;
        setPanels(newPanels);
        setSliderWidth(newSliderWidth);
    }

    // on category select
    function onCategorySelect(c,catLink){

        const newCurrentCategoryLevel = props.currentCategoryLevel + 1;

        const trimedPanelsArray = panels;
        trimedPanelsArray.length = newCurrentCategoryLevel;
        const newPanels = [...trimedPanelsArray,{categoryId:c.id,categories:ConvertObjectToArray(c.children)}];
        setPanels(newPanels);

        const newSliderWidth = containerWidth * newPanels.length;
        setSliderWidth(newSliderWidth);

        const newSliderPosition = newCurrentCategoryLevel * containerWidth;
        setSliderPosition(newSliderPosition);

        let trimedCurrentViewedCategoriesArray = []
        if (props.currentViewedCategories.length > 0) {
            trimedCurrentViewedCategoriesArray = props.currentViewedCategories;
            trimedCurrentViewedCategoriesArray.length = props.currentCategoryLevel;
        }

        c.categories = ConvertObjectToArray(c.children)

        const newCurrentViewedCategories = [
            ...trimedCurrentViewedCategoriesArray,
            {...c, level:newCurrentCategoryLevel}
        ]

        props.onCategoryPanleItemClick(newCurrentCategoryLevel,newCurrentViewedCategories,catLink)
    }

    // on set slider height
    function onSetSliderHeight(height){
        setContainerVisibility(true);
        setSliderHeight(height)
    }

    /* RENDER */

    const categoryPanelsDislpay = panels.map((cp,index) => (
        <CategoryPanel 
            key={index}
            level={index}
            currentCategoryLevel={props.currentCategoryLevel}
            currentViewedCategories={props.currentViewedCategories}
            selectedCategoriesId={props.selectedCategoriesId}
            sliderPosition={sliderPosition}
            categories={cp.categories}
            parentCategory={cp.categoryId}
            categoryId={props.categoryId}
            containerWidth={containerWidth}
            searchMode={props.searchMode}
            searchPhrase={props.searchPhrase}
            onSetSliderHeight={(height) => onSetSliderHeight(height)}
            onCategorySelect={(c,catLink) => onCategorySelect(c,catLink)}
            catTreeFilter={props.catTreeFilter}
            activeCategoryStyle={props.activeCategoryStyle}
        />
    ))

    let categoryPanelsContainerCss = {
        height:sliderHeight+"px",
        overflow:"hidden",
        position:"relative"
    }

    let categoryPanelsSliderCss = {
        left:"-"+sliderPosition+"px",
        width:sliderWidth+"px",
        position:"absolute",
        transition:"0.2s"
    }

    let categoryPanelsContainerClassName = "";
    if (containerVisibility === true) categoryPanelsContainerClassName += "visible ";

    return (
        <div id="category-panels-container" className={categoryPanelsContainerClassName} style={categoryPanelsContainerCss}>
            <div id="category-panels-slider" style={categoryPanelsSliderCss}>
                {categoryPanelsDislpay}
            </div>
        </div>
    )
}

function CategoryPanel(props){

    function adjustSliderHeight(panelHeight){
        let currentCategoryLevel = props.currentCategoryLevel
        /*if (isShowRealDomainAsUrl){
            if (window.location.href !== "https://www.pling.com/" && window.location.href !== "https://www.pling.cc/") currentCategoryLevel = props.currentCategoryLevel + 1;
        }*/
        if (currentCategoryLevel === props.level) props.onSetSliderHeight(panelHeight);
    }

    function onSetCategoryPanelHeight(panelHeight){
        adjustSliderHeight(panelHeight + 25);
    }

    let panelCategories = props.categories;
    if (props.parentCategory === "-1"){
        if (props.currentViewedCategories && props.currentViewedCategories.length > 0) panelCategories = props.currentViewedCategories[props.currentViewedCategories.length - 1].categories;
    }

    let categoryPanelContent;
    if (panelCategories){
        let categories;
        if (panelCategories.length > 0){
            categories = panelCategories.sort(sortArrayAlphabeticallyByTitle);
            let itemIndex = 0;
            categories = categories.map((c,index) =>{
                let showCategory = true;
                if (c.is_show_in_menu && c.is_show_in_menu === "0") showCategory = false;
                if (showCategory === true){
                    itemIndex += 1;
                    if (categories.length === (index + 1)) onSetCategoryPanelHeight(itemIndex * 30);
                    return (
                        <CategoryMenuItem 
                            key={index}
                            category={c}
                            categoryId={props.categoryId}
                            parentCategory={props.parentCategory}
                            currentViewedCategories={props.currentViewedCategories}
                            selectedCategoriesId={props.selectedCategoriesId}
                            onCategoryClick={(c,catLink) => props.onCategorySelect(c,catLink)}
                            searchMode={props.searchMode}
                            searchPhrase={props.searchPhrase}
                            catTreeFilter={props.catTreeFilter}
                            activeCategoryStyle={props.activeCategoryStyle}
                        />
                    )
                }
            })
        } else {
            categories = <li><p>no categories matching {props.searchPhrase}</p></li>
        }
        categoryPanelContent = <ul className="category-panel-ul">{categories}</ul>
    }

    const categoryPanelCss = {
        width:props.containerWidth,
        float:"left"
    }

    return(
        <div className="category-panel" id={"panel-"+props.level} style={categoryPanelCss}>
            {categoryPanelContent}
        </div>
    )
}

function CategoryMenuItem(props){

    const c = props.category;

    function onCategoryClick(c,catLink){
        setTimeout(() => {
            if (c.has_children === true) props.onCategoryClick(c,catLink)            
        }, 100);
    }

    let catLink;
    if (c.id){
        if (c.id === "0"){
            catLink = window.config.baseUrl;
            if (window.config.baseUrl.indexOf('http') === -1) catLink = "https://" + window.config.baseUrl;
        }
        else {
            catLink = getUrlContext(window.location.href);
            catLink += c.id === "00" ? "/browse" : "/browse?cat="+c.id+"&order=latest";         
        }
    }
    else {
        if (c.menuhref.indexOf('http') > -1) catLink = c.menuhref; 
        else  catLink = "https://" + c.menuhref; 
    }

    let catTitle;
    if (c.title) catTitle = c.title;
    else catTitle = c.name;

    if (catTitle === "ALL" && props.parentCategory === -1) catLink += "/browse";

    if(props.catTreeFilter=='filter_favourites'){
        catLink+='fav/1';
    }

    let categoryMenuItemClassName, catStyle;
    if (c.id === "0"){
        if (window.location.href === catLink || window.location.href === catLink + "/"){
            categoryMenuItemClassName = "cat-active";
            catStyle = props.activeCategoryStyle;
        }
    } else if (c.id === "00") {
        let baseName = window.config.sName;
        if (window.config.sName.indexOf('http') === -1 ) baseName = "https://" + window.config.sName;
        if (window.location.href === window.config.baseUrl + catLink || window.location.href === window.config.baseUrl + catLink.split("/browse")[0] ||
            window.location.href === baseName + catLink || window.location.href === baseName + catLink.split("/browse")[0]){
            categoryMenuItemClassName = "cat-active";
            catStyle = props.activeCategoryStyle;
        }
    } else {
        if (c.id && props.categoryId === parseInt(c.id) || props.selectedCategoriesId.indexOf(c.id) > -1 || window.location.href === catLink ||  window.location.href === catLink + "/"){
            categoryMenuItemClassName = "cat-active";
            catStyle = props.activeCategoryStyle;
        }
    }    
    if (catTitle === json_store_name) {
        categoryMenuItemClassName = "cat-active";
        catStyle = props.activeCategoryStyle;
    }

    const categoryMenuItemDisplay = (
        <a href={catLink} style={catStyle} className={categoryMenuItemClassName}>
            <span className="cat-title">{catTitle}</span>
            <span className="cat-product-counter">{c.product_count}</span>
        </a>
    )

    return(
        <li>
            {categoryMenuItemDisplay}
        </li>
    )
}

function CategoryTagCloud(props){

    const tags = props.tags ? props.tags : window.tagCloud;

    useEffect(() => {
        setTimeout(() => {
            props.onTagsHeightUpdate(document.getElementById('accordion-tags').clientHeight)
        }, 1000);
    },[])

    let tagsDisplay;
    if (tags && tags.length > 0){
        tagsDisplay = tags.map((t,index) =>{
            let url = ''
            if (window.location.href.indexOf('/browse/') > -1) url = window.location.href.split('&tag=')[0] + "&tag=" + t.tag_name;
            else url = 'https://' + window.location.hostname + '/browse?cat='+ props.selectedCategory +'&tag=' + t.tag_name;
            let tagClass = window.location.href.indexOf('?tag=') > -1 && window.location.href.split('&tag=')[1] === t.tag_name ? "active" : "";
            return (
                <a key={index} className={tagClass} href={url}>
                    <span className="pui-pill tag">{t.tag_name}</span>
                </a>
            )
        })
    }
    return (
        <div id="tag-sub-menu">
            {tagsDisplay}
        </div>
    )
}

export default CategoryTreeWrapper;