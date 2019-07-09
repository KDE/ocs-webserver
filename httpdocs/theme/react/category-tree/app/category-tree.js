import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import {
    ConvertObjectToArray, 
    GetSelectedCategory, 
    GenerateCurrentViewedCategories, 
    GetCategoriesBySearchPhrase, 
    sortArrayAlphabeticallyByTitle,
    getUrlContext
} from './category-helpers';

let isShowRealDomainAsUrl = 1, showStoreListingsFirst = false;
if (window.is_show_real_domain_as_url === 1) isShowRealDomainAsUrl = 1;
else {
    if (window.config.sName === "www.pling.cc" || window.config.sName === "www.pling.com"){ isShowRealDomainAsUrl = 1; }
    if (window.location.href === "https://www.pling.cc" || window.location.href === "https://www.pling.com" || window.location.href === "http://192.168.2.124" ||
        window.location.href === "https://www.pling.cc/" || window.location.href === "https://www.pling.com/" || window.location.href === "http://192.168.2.124/"){
        showStoreListingsFirst = true;
    }
}


function CategoryTree(){

    /* STATE */

    let initialCatTree = [{title:"All",id:"0"},...window.catTree]

    const [ categoryTree, setCategoryTree ] = useState(initialCatTree);    
    const [ categoryId, SetCategoryId ] = useState(window.categoryId);
    const [ selectedCategory, setSelectedCategory ] = useState(GetSelectedCategory(categoryTree,categoryId));
    
    let initialCurrentViewedCategories = []
    if (selectedCategory){
        initialCurrentViewedCategories = GenerateCurrentViewedCategories(categoryTree,selectedCategory,[])
        if (selectedCategory.has_children === true) initialCurrentViewedCategories.push(selectedCategory);
        if (initialCurrentViewedCategories.length > 0){
            initialCurrentViewedCategories.forEach(function(icvc,index){
                icvc.level = index + 1;
            })
        }
    }

    let initialSelectedCategoriesId = [];
    initialCurrentViewedCategories.forEach(function(c,index){
        initialSelectedCategoriesId.push(c.id);
    });
    
    const [ selectedCategoriesId, setSelectedCategoriesId ] = useState(initialSelectedCategoriesId)
    const [ currentViewedCategories, setCurrentViewedCategories ] = useState(initialCurrentViewedCategories);


    let initialCurrentCategoryLevel = initialCurrentViewedCategories.length;
    if (isShowRealDomainAsUrl && showStoreListingsFirst) initialCurrentCategoryLevel = -1;
    const [ currentCategoryLevel, setCurrentCategoryLevel ] = useState(initialCurrentCategoryLevel);

    const [ searchPhrase, setSearchPhrase ] = useState();
    const [ searchMode, setSearchMode ] = useState();

    /* COMPONENT */

    React.useEffect(() => { onSearchPhraseUpdate() },[searchPhrase])

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
                    {id:"-1",title:'Search',searchPhrase:searchPhrase,categories:searchPhraseCategories}
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
    function onHeaderNavigationItemClick(cvc){
        setCurrentCategoryLevel(cvc.level)
        const trimedCurrentViewedCategoriesArray = currentViewedCategories;
        trimedCurrentViewedCategoriesArray.length = cvc.level + 1;
        setCurrentViewedCategories(trimedCurrentViewedCategoriesArray)
    }

    // go back
    function goBack(){
        //if (currentCategoryLevel > 0){
            const newCurrentCategoryLevel = currentCategoryLevel - 1;
            setCurrentCategoryLevel(newCurrentCategoryLevel);
            const trimedCurrentViewedCategoriesArray = currentViewedCategories;
            trimedCurrentViewedCategoriesArray.length = newCurrentCategoryLevel > 0 ? newCurrentCategoryLevel : 0;    
            setCurrentViewedCategories(trimedCurrentViewedCategoriesArray)
            const newSearchPhrase = '';
            const newSearchMode = false;
            setSearchPhrase(newSearchPhrase);
            setSearchMode(newSearchMode);
        //}
    }

    // on category panel item click
    function onCategoryPanleItemClick(ccl,cvc,catLink){
        const newCurrentCategoryLevel = ccl;
        const newCurrentViewedCategories = cvc;
        setCurrentCategoryLevel(newCurrentCategoryLevel) 
        setCurrentViewedCategories(newCurrentViewedCategories)
        if (catLink) window.location.href = catLink;
    }

    // search phrase
    function onSetSearchPhrase(e){
        setSearchPhrase(e.target.value);
    }


    // on back button click
    function onBackButtonClick(){
        /*props.goBack();
        let newCategories = categories;
        if (categories.length <= 1) newCategories = []
        else newCategories.length = categories.length - 1;
        setCategories(newCategories);*/
    }

    /* RENDER */

    let tagCloudDisplay;
    if (selectedCategory) tagCloudDisplay = <CategoryTagCloud selectedCategory={selectedCategory} />

    let categoryTreeHeaderDisplay;
    if (currentViewedCategories.length > 0){
        categoryTreeHeaderDisplay = (
            <CategoryTreeHeader 
                currentCategoryLevel={currentCategoryLevel}
                currentViewedCategories={currentViewedCategories}  
                onHeaderNavigationItemClick={(cvc) => onHeaderNavigationItemClick(cvc)}
            />
        )
    }

    return(
        <div id="category-tree">
            <input type="text" defaultValue={searchPhrase} onChange={e => onSetSearchPhrase(e)}/>
            {categoryTreeHeaderDisplay}
            <CategoryPanelsContainer
                categoryTree={categoryTree}
                categoryId={categoryId}
                searchPhrase={searchPhrase}
                searchMode={searchMode}
                currentCategoryLevel={currentCategoryLevel}
                currentViewedCategories={currentViewedCategories}
                selectedCategoriesId={selectedCategoriesId}
                onCategoryPanleItemClick={(ccl,cvc,catLink) => onCategoryPanleItemClick(ccl,cvc,catLink)}
                onGoBackClick={goBack}
            />
            {tagCloudDisplay}
        </div>
    )
}

function CategoryTreeHeader(props){

    const [ categories, setCategories ] = useState(props.currentViewedCategories)
    React.useEffect(() => {
        const newCurrentViewedCategories = props.currentViewedCategories;
        setCategories(newCurrentViewedCategories);
    },[props.currentViewedCategories,props.currentCategoryLevel])

    function onHeaderNavigationItemClick(cvc,index){
        props.onHeaderNavigationItemClick(cvc);
        const newCategories = categories;
        newCategories.length = index + 1;
        setCategories(newCategories)
        const catLink = getUrlContext(window.location.href) + ( cvc.id === "0" ? "/browse/" : "/browse/cat/"+cvc.id+"/order/latest/")
        window.location.href = catLink;
    }

    let categoryTreeHeaderNavigationDisplay;
    if (categories.length > 0){
        categoryTreeHeaderNavigationDisplay = categories.map((cvc,index) =>{
            let title = "/", titleHoverElement = <span>{cvc.title}</span>;
            if (categories.length === index + 1){
                title = cvc.title;
                titleHoverElement = '';
            }
            return (
                <a key={index} onClick={() => onHeaderNavigationItemClick(cvc,index)}>
                    {title}
                    {titleHoverElement}
                </a>
            )
        })
    }

    return (
        <div id="category-tree-header">
            {categoryTreeHeaderNavigationDisplay}
        </div>
    )
}

function CategoryPanelsContainer(props){

    /* STATE */

    const rootListingPanel = {categoryId:0,categories:props.categoryTree}
    const storeListingPanel = {categoryId:-1,categories:[{name:"All",id:"0"}, ...window.config.domains]}
    let initialRootCategoryPanels = [rootListingPanel];
    if (isShowRealDomainAsUrl  === 1) initialRootCategoryPanels = [storeListingPanel,rootListingPanel];
    let initialPanelsValue = initialRootCategoryPanels;
    if (props.currentViewedCategories.length > 0) initialPanelsValue = initialRootCategoryPanels.concat(props.currentViewedCategories);
    const [ panels, setPanels ] = useState(initialPanelsValue);
    
    const [ containerWidth, setContainerWidth ] = useState(document.getElementById('category-tree-container').offsetWidth);
    const [ sliderWidth, setSliderWidth ] = useState(containerWidth * panels.length);
    const [ sliderHeight, setSliderHeight ] = useState();

    let currentCategoryLevel = props.currentCategoryLevel + 1;
    const [ sliderPosition, setSliderPosition ] = useState(currentCategoryLevel * containerWidth);

    let initialShowBackButtonValue = true;
    if (sliderPosition === 0) initialShowBackButtonValue = false;
    const [ showBackButton, setShowBackButton ] = useState(initialShowBackButtonValue);

    const [ containerVisibility, setContainerVisibility ] = useState(false);

    /* COMPONENT */

    React.useEffect(() => { updateSlider() },[props.currentCategoryLevel,props.currentViewedCategories])
    React.useEffect(() => { updatePanlesOnSearch() },[props.searchMode,props.searchPhrase])

    // update slider
    function updateSlider(){
        const trimedPanelsArray =  [...initialRootCategoryPanels,...props.currentViewedCategories];
        if (props.searchMode === false ){
            let currentCategoryLevel = props.currentCategoryLevel;
            if (isShowRealDomainAsUrl === 1 ) currentCategoryLevel = props.currentCategoryLevel + 1;
            trimedPanelsArray.length = currentCategoryLevel + 1;
        }
        setPanels(trimedPanelsArray);

        const newSliderWidth = containerWidth * trimedPanelsArray.length;
        setSliderWidth(newSliderWidth);

        let currentCategoryLevel = props.currentCategoryLevel + 1;
        const newSliderPosition = currentCategoryLevel * containerWidth;
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
        />
    ))

    let categoryPanelsContainerCss = {
        height:sliderHeight+"px"
    }

    let categoryPanelsSliderCss = {
        left:"-"+sliderPosition+"px",
        width:sliderWidth+"px",
    }

    let categoryPanelsContainerClassName = "", backButtonDisplay;
    if (showBackButton){
        categoryPanelsContainerClassName += "show-back-button ";
        backButtonDisplay = <a id="back-button" onClick={props.onGoBackClick}><span className="glyphicon glyphicon-chevron-left"></span></a>
    }

    if (containerVisibility === true) categoryPanelsContainerClassName += "visible ";

    return (
        <div id="category-panles-container" className={categoryPanelsContainerClassName} style={categoryPanelsContainerCss}>
            {backButtonDisplay}
            <div id="category-panels-slider" style={categoryPanelsSliderCss}>
                {categoryPanelsDislpay}
            </div>
        </div>
    )
}

function CategoryPanel(props){

    function adjustSliderHeight(panelHeight){
        let currentCategoryLevel = props.currentCategoryLevel
        if (isShowRealDomainAsUrl ) currentCategoryLevel = props.currentCategoryLevel + 1;
        if (currentCategoryLevel === props.level) props.onSetSliderHeight(panelHeight);
    }

    function onSetCategoryPanelHeight(panelHeight){
        adjustSliderHeight(panelHeight + 25);
    }

    let categoryPanelContent;
    if (props.categories){
        let categories;
        if (props.categories.length > 0){
            categories = props.categories.sort(sortArrayAlphabeticallyByTitle);
            categories = categories.map((c,index) =>{
                if (categories.length === (index + 1)){ onSetCategoryPanelHeight(categories.length * 24); } 
                let showCategory = true;
                if (c.is_show_in_menu){
                    if (c.is_show_in_menu === "0") showCategory = false;
                }
                if (showCategory === true){
                    return (
                        <CategoryMenuItem 
                            key={index}
                            category={c}
                            categoryId={props.categoryId}
                            currentViewedCategories={props.currentViewedCategories}
                            selectedCategoriesId={props.selectedCategoriesId}
                            onCategoryClick={(c,catLink) => props.onCategorySelect(c,catLink)}
                            searchMode={props.searchMode}
                            searchPhrase={props.searchPhrase}
                        />
                    )
                }
            })
        } else {
            categories = <li><p>no categories matching {props.searchPhrase}</p></li>
        }
        categoryPanelContent = <ul>{categories}</ul>
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
        if (c.has_children) props.onCategoryClick(c,catLink)
    }

    let catLink;
    if (c.id){
        catLink = getUrlContext(window.location.href);
        catLink += c.id === "0" ? "/browse/" : "/browse/cat/"+c.id+"/order/latest/"
    }
    else {
        if (c.menuhref.indexOf('http') > -1) catLink = c.menuhref; 
        else  catLink = "https://" + c.menuhref; 
    }

    let catTitle;
    if (c.title) catTitle = c.title;
    else catTitle = c.name;

    let categoryMenuItemDisplay;
    /*if (c.has_children === true){
        categoryMenuItemDisplay = (
            <a>
                <span className="cat-title">{catTitle}</span>
                <span className="cat-product-counter">{c.product_count}</span>
            </a>
        )
    } else {*/
        categoryMenuItemDisplay = (
            <a href={catLink} onClick={() => onCategoryClick(c,catLink)}>
                <span className="cat-title">{catTitle}</span>
                <span className="cat-product-counter">{c.product_count}</span>
            </a>
        )        
    //}

    let categoryMenuItemClassName;
    if (props.categoryId === parseInt(c.id) || props.selectedCategoriesId.indexOf(c.id) > -1) categoryMenuItemClassName = "active";

    return(
        <li className={categoryMenuItemClassName} >
            {categoryMenuItemDisplay}
        </li>
    )
}

function CategoryTagCloud(props){

    const [ tags, setTags ] = useState();

    React.useEffect(() => { getCategoryTags() },[])
    React.useEffect(() => { getCategoryTags() },[props.selectedCategory])

    function getCategoryTags(){

        let baseAjaxUrl = "https://www.pling."
        if (window.location.host.endsWith('com') || window.location.host.endsWith('org')){
            baseAjaxUrl += "com";
        } else {
            baseAjaxUrl += "cc";
        }
        
        let url = baseAjaxUrl + "/json/cattags/id/" + props.selectedCategory.id;
        $.ajax({
            dataType: "json",
            url: url,
            success: function(res){
                setTags(res);
            }
          });
    }

    let tagsDisplay;
    if (tags){
        tagsDisplay = tags.map((t,index) => (
            <a key={index} href={"http://" + window.location.host + "/search?projectSearchText="+t.tag_fullname+"&f=tags"}>
                <span className="glyphicon glyphicon-tag"></span>
                {t.tag_name}
            </a>
        ))
    }

    return (
        <div id="category-tag-cloud">
            {tagsDisplay}
        </div>
    )
}

const rootElement = document.getElementById("category-tree-container");
ReactDOM.render(<CategoryTree />, rootElement);