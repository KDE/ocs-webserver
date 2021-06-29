import React, { useEffect, useState, useContext, useRef } from 'react';
import { AppContext } from '../../../layout/context/context-provider'
import { usePrevious } from '../../../layout/app-helpers';
import '../style/cat-tree.css';

import {
    ConvertObjectToArray, 
    GetSelectedCategory, 
    GenerateCurrentViewedCategories, 
    GetCategoriesBySearchPhrase, 
    sortArrayAlphabeticallyByTitle,
    getUrlContext,
    GetInitialCategoryTreeWidth,
    RenderCurrentViewedCategories
} from './category-helpers';

import BlurBG from '../../../layout/style/media/blur2.png';

function CategoryTree(props){
    
    const { appState, appDispatch } = useContext(AppContext)
    const previousCatId = usePrevious(appState.categoryId);

    /* STATE */

    const [ categoryTree, setCategoryTree ] = useState();
    const [ categoryId, setCategoryId ] = useState();
    const [ catTreeFilter, setcatTreeFilter ] = useState();
    const [ selectedCategory, setSelectedCategory ] = useState();
    const [ selectedCategoriesId, setSelectedCategoriesId ] = useState()
    const [ currentViewedCategories, setCurrentViewedCategories ] = useState();
    const [ currentCategoryLevel, setCurrentCategoryLevel ] = useState();
    const [ searchPhrase, setSearchPhrase ] = useState();
    const [ searchMode, setSearchMode ] = useState();
    const [ showBackButton, setShowBackButton ] = useState(true);
    const [ showBreadCrumbs, setShowBreadCrumbs ] = useState(true);
    const [ showForwardButton, setShowForwardButton ] = useState(false);

    const [ showCategoiesMenu, setShowCategoriesMenu ] = useState(true);
    const [ showTagsMenu, setShowTagsMenu ] = useState(true);

    let initialStoreInfo;
    if (window.config.sName){
        json_store_for_tree.forEach(function(d,index){
            if (d.host === window.config.sName){
                initialStoreInfo = d;
            }
        });
    }

    const [ storeInfo, setStoreInfo ] = useState(initialStoreInfo);

    /* COMPONENT */

    useEffect(() => {
        if (appState.categories !== null) updateCategoryTree();
    },[appState.categories])

    useEffect(() => {
            updateSelectedCategory();
    },[appState.categoryId])

    useEffect(() => { onSearchPhraseUpdate() },[searchPhrase])

    // update category tree
    function updateCategoryTree(){

        const newCatTree = appState.categories
        setCategoryTree(newCatTree);

        const newCategoryId =  appState.categoryId;
        setCategoryId(newCategoryId);
    
        const newCatTreeFilters = appState.filters;
        setcatTreeFilter(newCatTreeFilters);

        const newSelectedCategory = GetSelectedCategory(newCatTree,(newCategoryId ? newCategoryId : "0"));
        setSelectedCategory(newSelectedCategory);

        const newCurrentViewedCategories = RenderCurrentViewedCategories(newCatTree,newSelectedCategory)
        setCurrentViewedCategories(newCurrentViewedCategories);
    
        let newSelectedCategoriesId = [];
        if (newCurrentViewedCategories){
            newCurrentViewedCategories.forEach(function(c,index){
                newSelectedCategoriesId.push(c.id);
            });
        }
        setSelectedCategoriesId(newSelectedCategoriesId);
    
        let newCurrentCategoryLevel = newCurrentViewedCategories ? newCurrentViewedCategories.length : 0;
        setCurrentCategoryLevel(newCurrentCategoryLevel)
    }

    // update selected category
    function updateSelectedCategory(){
        const newSelectedCategory = GetSelectedCategory(appState.categories,appState.categoryId);        
        const newCurrentViewedCategories = RenderCurrentViewedCategories(appState.categories,newSelectedCategory)        
        let newSelectedCategoriesId = [];
        newCurrentViewedCategories.forEach(function(c,index){
            newSelectedCategoriesId.push(c.id);
        });        
        let newCurrentCategoryLevel = newCurrentViewedCategories.length;        
        setSelectedCategory(newSelectedCategory);
        setCurrentViewedCategories(newCurrentViewedCategories);
        setSelectedCategoriesId(newSelectedCategoriesId);
        setCurrentCategoryLevel(newCurrentCategoryLevel)
    }

    // on search phrase update
    function onSearchPhraseUpdate(){
        let newSearchMode = false;
        if (searchPhrase){
            let newCurrentViewedCategories;
            if  (searchPhrase.length > 0){
                newSearchMode = true;
                newCurrentViewedCategories = [...currentViewedCategories];
                setCurrentViewedCategories(newCurrentViewedCategories);
                if (searchMode === true) newCurrentViewedCategories.length = selectedCategoriesId.length;
                let searchPhraseCategories = GetCategoriesBySearchPhrase(categoryTree,searchPhrase);
                newCurrentViewedCategories = [
                    ...newCurrentViewedCategories,
                    {categoryId:"-1",id:"-1",title:'Search',searchPhrase:searchPhrase,categories:searchPhraseCategories}
                ]
            } else {
                newCurrentViewedCategories = [...currentViewedCategories];
                newCurrentViewedCategories.length = selectedCategoriesId.length;
            }
            setCurrentViewedCategories(newCurrentViewedCategories);
            setCurrentCategoryLevel(newCurrentViewedCategories.length)
        } else if (searchMode === true){
            let newCurrentViewedCategories = [...currentViewedCategories];
            newCurrentViewedCategories.length = selectedCategoriesId.length;
            setCurrentViewedCategories(newCurrentViewedCategories);
            setCurrentCategoryLevel(newCurrentViewedCategories.length)       
        }
        setSearchMode(newSearchMode);
    }

    // on header navigation item click
    function onHeaderNavigationItemClick(e,cvc,catLink){
        setCurrentCategoryLevel(cvc.level)
        const trimedCurrentViewedCategoriesArray = currentViewedCategories;
        trimedCurrentViewedCategoriesArray.length = cvc.level + 1;
        setCurrentViewedCategories(trimedCurrentViewedCategoriesArray);
        if (props.viewMode === "layout"){
                e.preventDefault();
                props.onChangeUrl(cvc,catLink)
        }
    }

    // go back
    function goBack(){
        if (!appState.viewLoading){
            const newCurrentCategoryLevel = currentCategoryLevel - 1;
            setCurrentCategoryLevel(newCurrentCategoryLevel);
            const newSearchPhrase = '';
            const newSearchMode = false;
            setSearchPhrase(newSearchPhrase);
            setSearchMode(newSearchMode);
        }
    }

    // go forward
    function goForward(){
        if (!appState.viewLoading){
            const newCurrentCategoryLevel = currentCategoryLevel + 1;
            setCurrentCategoryLevel(newCurrentCategoryLevel);
            const newSearchPhrase = '';
            const newSearchMode = false;
            setSearchPhrase(newSearchPhrase);
            setSearchMode(newSearchMode);
        }
    }

    // on category panel item click
    function onCategoryPanleItemClick(ccl,cvc){
        const newCurrentCategoryLevel = ccl;
        const newCurrentViewedCategories = cvc;
        setCurrentCategoryLevel(newCurrentCategoryLevel) 
        setCurrentViewedCategories(newCurrentViewedCategories)
    }

    // search phrase
    function onSetSearchPhrase(e){
        setSearchPhrase(e.target.value);
    }

    /* RENDER */

    let categoryTreeDisplay;

    if (categoryTree && categoryTree !== null){

        let tagCloudDisplay;

        if (window.tagCloud){
            tagCloudDisplay = (
                <div className="tag-sub-menu-accordion accordion accordion-flush" id="accordionFlushExample">
                    <div className="accordion-item">
                    <h2 className="accordion-header" id="flush-headingOne">
                        <button className="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="true" aria-controls="flush-collapseOne">
                            <span className="title-small-upper">Tag subcategories</span>
                        </button>
                    </h2>
                    <div id="flush-collapseOne" className="accordion-collapse collapse show" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                        <div className="accordion-body">
                            <CategoryTagCloud viewLoading={props.viewLoading} viewMode={props.viewMode} selectedCategory={selectedCategory} />
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
                        <h2 className="accordion-header" id="flush-headingMenu">
                            <button className="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseMenu" aria-expanded="true" aria-controls="flush-collapseMenu">
                                <span className="title-small-upper">Categories</span>
                            </button>
                        </h2>
                        <div id="flush-collapseMenu" className="accordion-collapse collapse show" aria-labelledby="flush-headingMenu" data-bs-parent="#accordionFlushMenu">
                            <div className="accordion-body">
                                <CategoryTreeHeader 
                                    currentCategoryLevel={currentCategoryLevel}
                                    currentViewedCategories={currentViewedCategories}
                                    showBackButton={showBackButton}
                                    showBreadCrumbs={showBreadCrumbs}
                                    showForwardButton={showForwardButton}
                                    storeInfo={storeInfo}
                                    onHeaderNavigationItemClick={onHeaderNavigationItemClick}
                                    onGoBackClick={goBack}
                                    onGoForwardClick={goForward}
                                    catTreeFilter={catTreeFilter}
                                    onSetView={props.onSetView}
                                    viewLoading={props.viewLoading}
                                    searchPhrase={searchPhrase}
                                    onSetSearchPhrase={onSetSearchPhrase}
                                />
                                <CategoryPanelsContainer
                                    {...props}
                                    categoryTree={categoryTree}
                                    categoryId={categoryId}
                                    searchPhrase={searchPhrase}
                                    searchMode={searchMode}
                                    currentCategoryLevel={currentCategoryLevel}
                                    currentViewedCategories={currentViewedCategories}
                                    selectedCategory={selectedCategory}
                                    selectedCategoriesId={selectedCategoriesId}
                                    storeInfo={storeInfo}
                                    onCategoryPanleItemClick={(ccl,cvc,catLink) => onCategoryPanleItemClick(ccl,cvc,catLink)}
                                    onSetShowBreadCrumbs={(val) => setShowBreadCrumbs(val)}
                                    onSetShowBackButton={(val) => setShowBackButton(val)}
                                    onSetShowForwardButton={(val) => setShowForwardButton(val)}
                                    catTreeFilter={catTreeFilter}
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
        <div id="category-tree">
            {categoryTreeDisplay}
            <img src={BlurBG} className="sidebar-bg"/>
        </div>
    )
}

function CategoryTreeHeader(props){

    const { appState, appDispatch } = useContext(AppContext)
    const initialCurrentViewedCategories = props.currentViewedCategories.slice(0,props.currentCategoryLevel);
    const [ categories, setCategories ] = useState(initialCurrentViewedCategories)

    React.useEffect(() => {
        const newCurrentViewedCategories = props.currentViewedCategories.slice(0,props.currentCategoryLevel);
        setCategories(newCurrentViewedCategories);
    },[props.currentViewedCategories,props.currentCategoryLevel])

    function onHeaderNavigationItemClick(e,cvc,index,catLink){
        e.preventDefault();
        if (appState.viewLoading === false){
            props.onHeaderNavigationItemClick(e,cvc,catLink);
            const newCategories = categories;
            newCategories.length = index + 1;
            setCategories(newCategories)
        }
    }

    function onStoreNameClick(e,storeHref){
        if (props.onSetView){
            e.preventDefault();
            const splitArg = ( window.location.hostname.endsWith('com') ? "com" :  window.location.hostname.endsWith('org') ?  "org" : "cc" );
            const trimedStoreHref = storeHref.split(splitArg)[1];
            let title = "", catId;
            if (trimedStoreHref.split('/')[1] === "browse") catId = 0;
            props.onSetView(trimedStoreHref + "/",title,catId);
        } else {
            window.location.href = storeHref;
        }
    }

    let categoryTreeHeaderNavigationDisplay;
    if (categories.length > 0){
        categoryTreeHeaderNavigationDisplay = categories.map((cvc,index) =>{
            if (categories.length === index + 1){
                let catLink;                                
                if (cvc.title !== "Search") catLink = getUrlContext(window.location.href) + ( cvc.id === "00" ? "/browse/" : "/browse/cat/"+cvc.id+"/order/latest/")
                if(props.catTreeFilter=='filter_favourites') catLink+='fav/1';
                return (
                    <li key={index}>
                        <a href={catLink} onClick={(e) => onHeaderNavigationItemClick(e,cvc,index,catLink)}>
                            {cvc.title}
                        </a>
                    </li>
                )
            }
        })
    }

    let backButtonDisplay;
    if (props.showBackButton) backButtonDisplay = <a id="back-button" onClick={props.onGoBackClick}><i className="bi bi-arrow-left-square-fill"></i></a>;
    else backButtonDisplay = <a id="back-button" className="cat-button-inactive"><i className="bi bi-arrow-left-square-fill"></i></a>;

    let sNameDisplay;
    //if (props.showBreadCrumbs === true){
        if (categories.length === 0){
            if (props.storeInfo){
                let storeName = window.config.sName, storeHref = window.config.sName;                               
                if (props.storeInfo.name.length > 0) storeName = props.storeInfo.name;
                if (props.storeInfo.menuhref.length > 0) storeHref = props.storeInfo.menuhref;
                if (props.catTreeFilter=='filter_favourites'){
                    storeHref= storeHref+'/my-favourites';
                } else {
                    storeHref += "/browse";
                }
                sNameDisplay = <li><a href={storeHref}>{storeName}</a></li>
            }
        }    
    //}

    let forwadButtonDisplay =  <a id="forward-button" className="cat-button-inactive"><i className="bi bi-arrow-right-square-fill"></i></a>
    if (props.showForwardButton === true) forwadButtonDisplay = <a id="forward-button" onClick={props.onGoForwardClick}><i className="bi bi-arrow-right-square-fill"></i></a>

    return (
        <React.Fragment>
            <div id="category-tree-header" className="category-controls">
                {backButtonDisplay}
                {forwadButtonDisplay}
                <input type="text" placeholder="Search categories" defaultValue={props.searchPhrase} onChange={e => props.onSetSearchPhrase(e)}/>
            </div>
            <ul className="category-panel-ul">
                {sNameDisplay}
                {categoryTreeHeaderNavigationDisplay}
            </ul>
        </React.Fragment>
    )
}

function CategoryPanelsContainer(props){

    /* STATE */
    
    const { appState, appDispatch } = useContext(AppContext)
    const previousViewedCategories = usePrevious(props.currentViewedCategories);
    const rootListingPanel = {categoryId:0,categories:props.categoryTree}
    const storeListingPanel = {categoryId:-1,categories:[...window.json_store_for_tree]}
    let initialRootCategoryPanels = [rootListingPanel];
    // if (isShowRealDomainAsUrl  === 1) initialRootCategoryPanels = [storeListingPanel,rootListingPanel];
    let initialPanelsValue = initialRootCategoryPanels;
    if (props.currentViewedCategories.length > 0) initialPanelsValue = initialRootCategoryPanels.concat(props.currentViewedCategories);
    const [ panels, setPanels ] = useState(initialPanelsValue);
    const initContainerWidthValue = GetInitialCategoryTreeWidth(window.innerWidth);
    const [ containerWidth, setContainerWidth ] = useState(initContainerWidthValue);
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
        
        let maxSliderPosition = ( containerWidth * panels.length ) - containerWidth - 0.1;

        if (parseInt(sliderPosition) < parseInt(maxSliderPosition)) showForward = true;

        if (window.location.href === "https://www.pling.com/" || window.location.href === "https://www.pling.cc/"){
            showForward = false;
        }

        props.onSetShowBackButton(showBack);
        props.onSetShowBreadCrumbs(showBreadCrumbs);
        props.onSetShowForwardButton(showForward);

    },[sliderPosition]);

    React.useEffect(() => { updateSlider() },[props.currentCategoryLevel,props.currentViewedCategories])
    React.useEffect(() => { updatePanlesOnSearch() },[props.searchMode,props.searchPhrase])

    useEffect(() => {
        if (previousViewedCategories && props.currentViewedCategories !== previousViewedCategories){
            const rootListingPanel = {categoryId:0,categories:props.categoryTree}
            let initialRootCategoryPanels = [rootListingPanel];
            let initialPanelsValue = initialRootCategoryPanels;
            if (props.currentViewedCategories.length > 0) initialPanelsValue = initialRootCategoryPanels.concat(props.currentViewedCategories);
            setPanels(initialPanelsValue);
            setSliderWidth(containerWidth * initialPanelsValue.length)
            updateSlider();
        }
    },[props.currentViewedCategories])

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
        setSliderHeight(height + 40)
    }

    /* RENDER */

    const categoryPanelsDislpay = panels.map((cp,index) => (
        <CategoryPanel 
            {...props}
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
        />
    ))

    let categoryPanelsContainerCss = {
        height:sliderHeight+"px"
    }

    let categoryPanelsSliderCss = {
        left:"-"+sliderPosition+"px",
        width:sliderWidth+"px",
    }

    let categoryPanelsContainerClassName = "";
    if (containerVisibility === true) categoryPanelsContainerClassName += "visible ";

    return (
        <div id="category-panles-container" className={categoryPanelsContainerClassName} style={categoryPanelsContainerCss}>
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
        
        if (currentCategoryLevel === props.level){
            setTimeout(() => {
                props.onSetSliderHeight(panelHeight);
            }, 0);
        }
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
                    if (categories.length === (index + 1)) onSetCategoryPanelHeight(itemIndex * 25);
                    return (
                        <CategoryMenuItem 
                            {...props}
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
    
    const { appState, appDispatch } = useContext(AppContext);

    let categoryMenuItemClassName;
    if (props.categoryId){
        if (parseInt(c.id) === props.categoryId) categoryMenuItemClassName = "active cat-active";
    } else {
        if (c.id === "0"){
            if (window.location.href === catLink || window.location.href === catLink + "/"){
                categoryMenuItemClassName = "active cat-active";
            }
        } else if (c.id === "00") {
            let baseName = window.config.sName;
            if (window.config.sName.indexOf('http') === -1 ) baseName = "https://" + window.config.sName;
            if (window.location.href === window.config.baseUrl + catLink || window.location.href === window.config.baseUrl + catLink.split("/browse")[0] ||
                window.location.href === baseName + catLink || window.location.href === baseName + catLink.split("/browse")[0]){
                categoryMenuItemClassName = "active cat-active";
            }
        } else {
            if (c.id && props.categoryId === parseInt(c.id) || props.selectedCategoriesId.indexOf(c.id) > -1 || window.location.href === catLink ||  window.location.href === catLink + "/") categoryMenuItemClassName = "active";
        }    
        if (catTitle === json_store_name) categoryMenuItemClassName = "active cat-active";
    }

    const [ menuItemClassCss, setMenuItemClassCss ] = useState(categoryMenuItemClassName);
    
    useEffect(() => {
        if (appState.categoryId === parseInt(c.id)) setMenuItemClassCss('active cat-active');
        else setMenuItemClassCss('');
    },[appState.categoryId])

    function onCategoryClick(e,c,catLink){
        if (props.viewMode === "layout"){
            e.preventDefault();
            if (appState.viewLoading === false){
                // props.onChangeUrl(c,catLink);
                appDispatch({type:'EMIT_CHNAGE_URL',url:catLink,title:c.catTitle,id:c.cat_id})
                setTimeout(() => {
                    if (c.has_children === true) props.onCategoryClick(c,catLink)            
                }, 100);
            }
        } else {
            setTimeout(() => {
                if (c.has_children === true) props.onCategoryClick(c,catLink)            
            }, 100);
        }
    }

    let catLink;
    if (c.id){
        if (c.id === "0"){
            catLink = window.config.baseUrl;
            if (window.config.baseUrl.indexOf('http') === -1) catLink = "https://" + window.config.baseUrl;
        }
        else {
            catLink = getUrlContext(window.location.href);
            catLink += c.id === "00" ? "/browse/" : "/browse/cat/"+c.id+"/order/latest/";         
        }
    }
    else {
        if (c.menuhref.indexOf('http') > -1) catLink = c.menuhref; 
        else  catLink = "https://" + c.menuhref; 
    }

    let catTitle;
    if (c.title) catTitle = c.title;
    else catTitle = c.name;

    if ( catTitle === "ALL" && props.parentCategory === -1) catLink += "/browse/";
    if ( props.catTreeFilter=='filter_favourites') catLink+='fav/1';
    
    const categoryMenuItemDisplay = (
        <a href={catLink} id={'cat-link-'+c.id}  className={menuItemClassCss}>
            <span className="cat-title">{catTitle}</span>
            <span className="cat-product-counter">{c.product_count}</span>
        </a>
    )

    return(
        <li className={menuItemClassCss} >
            {categoryMenuItemDisplay}
        </li>
    )
}

function CategoryTagCloud(props){

    const { appState, appDispatch } = useContext(AppContext);
    
    const tags = window.tagCloud;
    
    function onTagClick(e,t){
        e.preventDefault();
        const url = window.location.href.split('?')[0] + "?tag=";
        if (appState.view === "product-browse" && props.viewLoading === false){
            appDispatch({type:'SET_TAG',tag:t});
            appDispatch({type:'EMIT_CHNAGE_URL',url:url + t.tag_name,title:t.tag_name,id:appState.categoryId});
        } else {
            appDispatch({type:'EMIT_CHNAGE_URL',url:'/browse2/?tag=' + t.tag_name,title:t.tag_name,id:appState.categoryId});
        }
    }

    let tagsDisplay;
    if (tags && tags.length > 0){
        tagsDisplay = tags.map((t,index) =>{
            let url = ''
            if (window.location.href.indexOf('/browse/') > -1) url = window.location.href.split('?')[0] + "?tag=" + t.tag_name;
            else url = 'https://' + window.location.hostname + '/browse/cat/'+ props.selectedCategoryId +'/?tag=' + t.tag_name;
            let tagClass = window.location.href.indexOf('?tag=') > -1 && window.location.href.split('?tag=')[1] === t.tag_name ? "active" : "";
            return (
                <a className={tagClass} href={url}>
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

export default CategoryTree;