import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import {
    ConvertObjectToArray, 
    GetSelectedCategory, 
    GenerateCurrentViewedCategories, 
    GetCategoriesBySearchPhrase, 
    sortArrayAlphabeticallyByTitle
} from './category-helpers';

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
    const [ currentCategoryLevel, setCurrentCategoryLevel ] = useState(initialCurrentViewedCategories.length);

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
                if (searchMode === true) newCurrentViewedCategories.length = selectedCategoriesId.length;
                newCurrentViewedCategories = [
                    ...newCurrentViewedCategories,
                    {id:"-1",title:'Search',searchPhrase:searchPhrase,categories:GetCategoriesBySearchPhrase(categoryTree,searchPhrase)}
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
        if (currentCategoryLevel > 0){
            const newCurrentCategoryLevel = currentCategoryLevel - 1;
            setCurrentCategoryLevel(newCurrentCategoryLevel);
            const trimedCurrentViewedCategoriesArray = currentViewedCategories;
            trimedCurrentViewedCategoriesArray.length = newCurrentCategoryLevel;    
            setCurrentViewedCategories(trimedCurrentViewedCategoriesArray)
            const newSearchPhrase = '';
            const newSearchMode = false;
            setSearchPhrase(newSearchPhrase);
            setSearchMode(newSearchMode);
        }
    }

    // on category panel item click
    function onCategoryPanleItemClick(ccl,cvc){
        setCurrentCategoryLevel(ccl) 
        setCurrentViewedCategories(cvc)
    }

    // search phrase
    function onSetSearchPhrase(e){
        setSearchPhrase(e.target.value);
    }

    /* RENDER */

    let tagCloudDisplay;
    if (selectedCategory) tagCloudDisplay = <CategoryTagCloud selectedCategory={selectedCategory} />

    return(
        <div id="category-tree">
            <input type="text" defaultValue={searchPhrase} onChange={e => onSetSearchPhrase(e)}/>       
            <CategoryTreeHeader 
                currentCategoryLevel={currentCategoryLevel}
                currentViewedCategories={currentViewedCategories}  
                onHeaderNavigationItemClick={(cvc) => onHeaderNavigationItemClick(cvc)}
                goBack={goBack}
            />
            <CategoryPanelsContainer
                categoryTree={categoryTree}
                categoryId={categoryId}
                searchPhrase={searchPhrase}
                searchMode={searchMode}
                currentCategoryLevel={currentCategoryLevel}
                currentViewedCategories={currentViewedCategories}
                selectedCategoriesId={selectedCategoriesId}
                onCategoryPanleItemClick={(ccl,cvc) => onCategoryPanleItemClick(ccl,cvc)}
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
        const catLink = cvc.id === "0" ? "/browse/" : "/browse/cat/"+cvc.id+"/order/latest/"
        window.location.href = catLink;
    }

    function onBackButtonClick(){
        props.goBack();
        let newCategories = categories;
        if (categories.length <= 1) newCategories = []
        else newCategories.length = categories.length - 1;
        setCategories(newCategories);
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
            <a id="back-button" onClick={onBackButtonClick}>{"<<"}</a>
            {categoryTreeHeaderNavigationDisplay}
        </div>
    )
}

function CategoryPanelsContainer(props){

    const storeListingPanel = {categoryId:0,categories:props.categoryTree}
    let initialPanelsValue = [storeListingPanel];
    if (props.currentViewedCategories.length > 0) initialPanelsValue = [storeListingPanel,...props.currentViewedCategories];
    const [ panels, setPanels ] = useState(initialPanelsValue);
    const [ containerWidth, setContainerWidth ] = useState(document.getElementById('category-tree-container').offsetWidth);
    const [ sliderWidth, setSliderWidth ] = useState(containerWidth * panels.length);
    const [ sliderHeight, setSliderHeight ] = useState();
    const [ sliderPosition, setSliderPosition ] = useState(props.currentCategoryLevel * containerWidth);

    React.useEffect(() => { updateSlider() },[props.currentCategoryLevel,props.currentViewedCategories])
    React.useEffect(() => { updatePanlesOnSearch() },[props.searchMode,props.searchPhrase])

    function updateSlider(){

        const trimedPanelsArray =  [storeListingPanel,...props.currentViewedCategories];
        if (props.searchMode === false ) trimedPanelsArray.length = props.currentCategoryLevel + 1;
        const newSliderPosition = props.currentCategoryLevel * containerWidth;

        setPanels(trimedPanelsArray);
        setSliderPosition(newSliderPosition);
    
    }

    function updatePanlesOnSearch(){
        
        const newPanels = [storeListingPanel,...props.currentViewedCategories];
        const newSliderWidth = containerWidth * newPanels.length;
        
        setPanels(newPanels);
        setSliderWidth(newSliderWidth);

    }

    function onCategorySelect(c){

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

        const newCurrentViewedCategories = [
            ...trimedCurrentViewedCategoriesArray,
            {...c, level:newCurrentCategoryLevel}
        ]

        props.onCategoryPanleItemClick(newCurrentCategoryLevel,newCurrentViewedCategories)
    }

    const categoryPanelsDislpay = panels.map((cp,index) => (
        <CategoryPanel 
            key={index}
            level={index}
            currentCategoryLevel={props.currentCategoryLevel}
            currentViewedCategories={props.currentViewedCategories}
            selectedCategoriesId={props.selectedCategoriesId}
            categories={cp.categories}
            parentCategory={cp.categoryId}
            categoryId={props.categoryId}
            containerWidth={containerWidth}
            searchPhrase={props.searchPhrase}
            onSetSliderHeight={(height) => setSliderHeight(height)}
            onCategorySelect={(c) => onCategorySelect(c)}
        />
    ))

    let categoryPanelsContainerCss = {
        height:sliderHeight+"px"
    }

    let categoryPanelsSliderCss = {
        left:"-"+sliderPosition+"px",
        width:sliderWidth+"px",
    }

    return (
        <div id="category-panles-container" style={categoryPanelsContainerCss}>
            <div id="category-panels-slider" style={categoryPanelsSliderCss}>
                {categoryPanelsDislpay}
            </div>
        </div>
    )
}

function CategoryPanel(props){

    React.useEffect(() => {adjustSliderHeight()},[])
    React.useEffect(() => {adjustSliderHeight()},[props.currentCategoryLevel])

    function adjustSliderHeight(){
        if (props.currentCategoryLevel === props.level){
            const panelHeight = (props.categories.length * 24) + props.categories.length;
            props.onSetSliderHeight(panelHeight);
        }
    }

    function onCategoryClick(c){
        if (!c.has_children) console.log('navigate to category?');
        else props.onCategorySelect(c);
    }

    let categoryPanelContent;
    if (props.categories){
        let categories;
        if (props.categories.length > 0){
            categories = props.categories.sort(sortArrayAlphabeticallyByTitle).map((c,index) => (
                <CategoryMenuItem 
                    key={index}
                    category={c}
                    categoryId={props.categoryId}
                    currentViewedCategories={props.currentViewedCategories}
                    selectedCategoriesId={props.selectedCategoriesId}
                    onCategoryClick={(c) => onCategoryClick(c)}
                />
            ))
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
    const [ catLink, setCatLink ] = useState(c.id === "0" ? "/browse/" : "/browse/cat/"+c.id+"/order/latest/")

    function onCategoryClick(c){
        props.onCategoryClick(c);
        window.top.location.href = catLink;
    }

    let categoryMenuItemDisplay;
    if (c.has_children === true){
        categoryMenuItemDisplay = (
            <a onClick={() => onCategoryClick(c)}>
                <span className="cat-title">{c.title}</span>
                <span className="cat-product-counter">{c.product_count}</span>
            </a>
        )
    } else {
        categoryMenuItemDisplay = (
            <a href={catLink}>
                <span className="cat-title">{c.title}</span>
                <span className="cat-product-counter">{c.product_count}</span>
            </a>
        )        
    }

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
        console.log('get tags')
        console.log(window.location.host);
        let url = window.location.host + "/json/cattags/id/" + props.selectedCategory.id;
        $.ajax({
            dataType: "json",
            url: url,
            success: function(res){
                console.log(res);
                setTags(res);
            }
          });
    }

    let tagsDisplay;
    if (tags){
        tagsDisplay = tags.map((t,index) => (
            <a key={index} href={window.location.host + "/search?projectSearchText="+t.tag_fullname+"&f=tags"}>{t.tag_name}</a>
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