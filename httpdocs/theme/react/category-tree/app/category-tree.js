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

    /* COMPONENT */

    React.useEffect(() => { onSearchPhraseUpdate() },[searchPhrase])

    // on search phrase update
    function onSearchPhraseUpdate(){
        if (searchPhrase){
            let newCurrentCategoryLevel, newCurrentViewedCategories;
            if (searchPhrase.length > 0){
                newCurrentViewedCategories = [...currentViewedCategories];
                newCurrentViewedCategories.length = selectedCategoriesId.length;
                newCurrentViewedCategories.push({id:"-1",title:'Search',categories:GetCategoriesBySearchPhrase(categoryTree,searchPhrase)})
                console.log(GetCategoriesBySearchPhrase(categoryTree,searchPhrase));
                newCurrentCategoryLevel = newCurrentViewedCategories.length;
            }
            else {
                newCurrentViewedCategories = [...currentViewedCategories];
                newCurrentViewedCategories.length = selectedCategoriesId.length;
                newCurrentCategoryLevel = selectedCategoriesId.length;
            }
            console.log(newCurrentViewedCategories);
            setCurrentViewedCategories(newCurrentViewedCategories);
            setCurrentCategoryLevel(newCurrentCategoryLevel)
        }
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
        }
    }

    // on category panel item click
    function onCategoryPanleItemClick(ccl,cvc){
        setCurrentCategoryLevel(ccl) 
        setCurrentViewedCategories(cvc)
    }

    /* RENDER */
    /* <input type="text" defaultValue={searchPhrase} onChange={e => setSearchPhrase(e.target.value)}/> */
    return(
        <div id="category-tree">
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
                currentCategoryLevel={currentCategoryLevel}
                currentViewedCategories={currentViewedCategories}
                selectedCategoriesId={selectedCategoriesId}
                onCategoryPanleItemClick={(ccl,cvc) => onCategoryPanleItemClick(ccl,cvc)}
            />
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
            let title = "/"
            if (categories.length === index + 1) title = cvc.title;
            return (
                <a key={index} onClick={() => onHeaderNavigationItemClick(cvc,index)}>{title}</a>
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

    let initialPanelsValue = [{categoryId:0,categories:props.categoryTree}];
    if (props.currentViewedCategories.length > 0) initialPanelsValue = [{categoryId:0,categories:props.categoryTree},...props.currentViewedCategories];
    const [ panels, setPanels ] = useState(initialPanelsValue);
    const [ containerWidth, setContainerWidth ] = useState(document.getElementById('category-tree-container').offsetWidth);
    const [ sliderWidth, setSliderWidth ] = useState(containerWidth * panels.length);
    const [ sliderHeight, setSliderHeight ] = useState();
    const [ sliderPosition, setSliderPosition ] = useState(props.currentCategoryLevel * containerWidth);

    React.useEffect(() => { updateSlider() },[props.currentCategoryLevel,props.currentViewedCategories,props.searchPhrase])
    React.useEffect(() => { },[])

    function updateSlider(){
        const trimedPanelsArray = panels;
        trimedPanelsArray.length = props.currentCategoryLevel + 1;
        setPanels(trimedPanelsArray);       
        const newSliderPosition = props.currentCategoryLevel * containerWidth;
        setSliderPosition(newSliderPosition);
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
            const panelHeight = props.categories.length * 24;
            props.onSetSliderHeight(panelHeight);
        }
    }

    function onCategoryClick(c){
        if (!c.has_children) console.log('navigate to category?');
        else props.onCategorySelect(c);
    }

    let categoryPanelContent;
    if (props.categories && props.categories.length > 0){
        const categories = props.categories.sort(sortArrayAlphabeticallyByTitle).map((c,index) => (
            <CategoryMenuItem 
                key={index}
                category={c}
                categoryId={props.categoryId}
                currentViewedCategories={props.currentViewedCategories}
                selectedCategoriesId={props.selectedCategoriesId}
                onCategoryClick={(c) => onCategoryClick(c)}
            />
        ))
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
        <li className={categoryMenuItemClassName}>
            {categoryMenuItemDisplay}
        </li>
    )
}

const rootElement = document.getElementById("category-tree-container");
ReactDOM.render(<CategoryTree />, rootElement);