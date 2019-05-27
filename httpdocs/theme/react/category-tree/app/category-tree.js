import React, { useState } from 'react';
import ReactDOM from 'react-dom';

function CategoryTree(){

    const [ categoryTree, SetCategoryTree ] = useState(window.catTree);
    const [ categoryId, SetCategoryId ] = useState(window.categoryId);
    const [ currentCategoryLevel, setCurrentCategoryLevel ] = useState(0);
    const [ currentViewedCategories, setCurrentViewedCategories ] = useState([{id:0,title:'all',level:0}]);

    return(
        <div id="category-tree">
            <CategoryTreeHeader 
                categoryT={categoryTree}
                currentCategoryLevel={currentCategoryLevel}
                currentViewedCategories={currentViewedCategories}            
                setCurrentCategoryLevel={(ccl) => setCurrentCategoryLevel(ccl) }
            />
            <CategoryPanelsContainer
                categoryTree={categoryTree}
                categoryId={categoryId}
                currentCategoryLevel={currentCategoryLevel}
                currentViewedCategories={currentViewedCategories}
                setCurrentCategoryLevel={(ccl) => setCurrentCategoryLevel(ccl) }
                setCurrentViewedCategories={(cvc) => setCurrentViewedCategories(cvc)}
            />
        </div>
    )
}

function CategoryTreeHeader(props){

    const [ categories, setCategories ] = useState(props.currentViewedCategories)

    React.useEffect(() => {
        setCategories(props.currentViewedCategories);
    },[props.currentViewedCategories])

    const categoryTreeHeaderNavigationDisplay = categories.map((cvc,index) => (
        <a key={index} onClick={() => props.setCurrentCategoryLevel(cvc.level)}>{cvc.title}</a>
    ))
    
    return (
        <div id="category-tree-header">
            {categoryTreeHeaderNavigationDisplay}
        </div>
    )
}

function CategoryPanelsContainer(props){

    const [ panels, setPanels ] = useState([{categoryId:0,categories:props.categoryTree}]);
    const [ containerWidth, setContainerWidth ] = useState(document.getElementById('category-tree-container').offsetWidth);
    const [ sliderWidth, setSliderWidth ] = useState(containerWidth * panels.length);
    const [ sliderPosition, setSliderPosition ] = useState(props.currentCategoryLevel * containerWidth);
    const [ sliderHeight, setSliderHeight ] = useState();

    React.useEffect(() => {
        const newSliderPosition = props.currentCategoryLevel * containerWidth;
        setSliderPosition(newSliderPosition);
    },[props.currentCategoryLevel,props.currentViewedCategories])


    function convertObjectToArray(object){
        let newArray = [];
        for (var i in object){
          newArray.push(object[i]);
        }
        return newArray;
    }

    function onCategorySelect(c){
        const newCurrentCategoryLevel = props.currentCategoryLevel + 1;

        const trimedPanelsArray = panels;
        trimedPanelsArray.length = newCurrentCategoryLevel;
        const newPanels = [...trimedPanelsArray,{categoryId:c.id,categories:convertObjectToArray(c.children)}];
        setPanels(newPanels);

        const newSliderWidth = containerWidth * newPanels.length;
        setSliderWidth(newSliderWidth);

        const newSliderPosition = newCurrentCategoryLevel * containerWidth;
        setSliderPosition(newSliderPosition);

        const trimedCurrentViewedCategoriesArray = props.currentViewedCategories;
        trimedCurrentViewedCategoriesArray.length = newCurrentCategoryLevel;
        const newCurrentViewedCategories = [
            ...trimedCurrentViewedCategoriesArray,
            {...c,level:newCurrentCategoryLevel}
        ]

        props.setCurrentViewedCategories(newCurrentViewedCategories);

        props.setCurrentCategoryLevel(newCurrentCategoryLevel);
    }

    const categoryPanelsDislpay = panels.map((cp,index) => (
        <CategoryPanel 
            key={index}
            level={index}
            currentCategoryLevel={props.currentCategoryLevel}
            categories={cp.categories}
            parentCategory={cp.categoryId}
            containerWidth={containerWidth}
            onCategorySelect={(c) => onCategorySelect(c)}
        />
    ))

    let categoryPanelsSliderCss = {
        position:"absolute",
        top:0,
        left:"-"+sliderPosition,
        width:sliderWidth
    }

    return (
        <div id="category-panles-container" style={{position:"relative",width:containerWidth}}>
            <div id="category-panels-slider" style={categoryPanelsSliderCss}>
                {categoryPanelsDislpay}
            </div>
        </div>
    )
}

function CategoryPanel(props){

    function onCategoryClick(c){
        if (!c.has_children) console.log('navigate to category?');
        else props.onCategorySelect(c);
    }

    let categoryPanelContent;
    if (props.categories && props.categories.length > 0){
        const categories = props.categories.map((c,index) => (
            <CategoryMenuItem 
                key={index}
                category={c}
                onCategoryClick={(c) => onCategoryClick(c)}
            />
        )) 
        categoryPanelContent = <ul>{categories}</ul>
    } else {
        categoryPanelContent = <p>no categories</p>
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
    
    function onCategoryMenuItemMouseOver(){
        console.log('now show dat shiat')
    }
    
    return(
        <li onMouseOver={onCategoryMenuItemMouseOver}>
            <a onClick={() => props.onCategoryClick(c)}>
                <span className="cat-title">{c.title}</span>
                <span className="cat-product-counter">{c.product_count}</span>
            </a>
        </li>
    )
}

const rootElement = document.getElementById("category-tree-container");
ReactDOM.render(<CategoryTree />, rootElement);