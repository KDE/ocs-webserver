import React, { useState, useEffect } from 'react';
import { GetUrlContext, GetSelectedCategory, GetDeviceFromWidth, SortArrayAlphabeticallyByTitle, GetAllCatItemCssClass, GenerateCategoryLink, GetCategoryType, ConvertObjectToArray } from './category-tree-helpers';

function CategoryTree(){

    const [ loading, setLoading ] = useState(true);
    const [ device, setDevice ] = useState();
    const [ urlContext, setUrlContext ] = useState();
    const [ cateories, setCategories ] = useState(window.catTree);
    const [ categoryId, setCategoryId ] = useState(window.categoryId);
    const [ catTreeCssClass, setCatTreeCssClass ] = useState("");
    const [ selectedCategory, setSelectedCategory ] = useState([]);
    const [ showCatTree, setShowCatTree ] = useState(false);
    const [ backendView, setBackendView ] = useState(window.backendView);

    useEffect(() => {
        updateDimensions();
        initCategoryTree();
    },[])
    
    function initCategoryTree(){
        window.addEventListener("resize", updateDimensions);
        const initUrlContext = GetUrlContext(window.location.href);
        setUrlContext(initUrlContext)
        if (categoryId && categoryId !== 0) getSelectedCategories();
        else setLoading(false);
    }

    function getSelectedCategories(){
        const newSelectedCategory = GetSelectedCategory(categories,catId);
        if (typeof(newSelectedCategory) !== 'undefined'){
            newSelectedCategory.selectedIndex = selectedCategories.length;
            selectedCategories.push(selectedCategory);
        }
        setSelectedCategory(newSelectedCategory);
        if (selectedCategory && selectedCategory.parent_id) getSelectedCategories(categories,parseInt(selectedCategory.parent_id));
        else setLoading(false)
    }
  
    function updateDimensions(){
      const newDevice = GetDeviceFromWidth(window.innerWidth);
      setDevice(newDevice);
    }
  
    function toggleCatTree(){
      const newShowCatTree = showCatTree === true ? false : true;
      const newCatTreeCssClass = catTreeCssClass === "open" ? "" : "open";
      setShowCatTree(newShowCatTree);
      setCatTreeCssClass(newCatTreeCssClass)
    }
  
      let categoryTreeDisplay, selectedCategoryDisplay;
      if (!loading){
        if (device === "tablet" && selectedCategories &&  selectedCategories.length > 0){
          selectedCategoryDisplay = (
            <SelectedCategory
              categoryId={categoryId}
              selectedCategory={selectedCategories[0]}
              selectedCategories={selectedCategories}
              onCatTreeToggle={toggleCatTree}
            />
          );
        }
        if (device === "tablet" && showCatTree || device !== "tablet" || selectedCategories && selectedCategories.length === 0) {
          if (categories){
            const categoryTree = categories.sort(SortArrayAlphabeticallyByTitle).map((cat,index) => (
              <CategoryItem
                key={index}
                category={cat}
                categoryId={categoryId}
                urlContext={urlContext}
                selectedCategories={selectedCategories}
                backendView={backendView}
              />
            ));
  
            const allCatItemCssClass = GetAllCatItemCssClass(window.location.href,window.baseUrl,urlContext, categoryId);
            let baseUrl;
            if (window.baseUrl !== window.location.origin) baseUrl = window.location.origin;
            const categoryItemLink = GenerateCategoryLink(window.baseUrl,urlContext,"all",window.location.href);

            categoryTreeDisplay = (
              <ul className="main-list">
                <li className={"cat-item" + " " + allCatItemCssClass}>
                  <a href={categoryItemLink}><span className="title">All</span></a>
                </li>
                {categoryTree}
              </ul>
            );
          }
        }
      }
      return(
        <div id="category-tree" className={device + " " + catTreeCssClass}>
          {selectedCategoryDisplay}
          {categoryTreeDisplay}
        </div>
      );
    
}
  
function CategoryItem(props){

    const [showSubmenu, setShowsubmenu ] = useState(false);
  
    function toggleSubmenu(){
      const newShowSubmenu = showSubmenu === true ? false : true;
      setShowsubmenu(newShowSubmenu)
    }
  
      let categoryChildrenDisplay;
  
      const categoryType = GetCategoryType(props.selectedCategories,props.categoryId,props.category.id);
      if (props.category.has_children === true && categoryType && props.lastChild !== true ||
          props.category.has_children === true && props.backendView === true && showSubmenu === true){
  
        const self = this;
  
        let lastChild;
        if (categoryType === "selected"){
          lastChild = true;
        }
  
        const children = ConvertObjectToArray(props.category.children);
        const categoryChildren = children.sort(appHelpers.sortArrayAlphabeticallyByTitle).map((cat,index) => (
          <CategoryItem
            key={index}
            category={cat}
            categoryId={self.props.categoryId}
            urlContext={self.props.urlContext}
            selectedCategories={self.props.selectedCategories}
            lastChild={lastChild}
            parent={self.props.category}
            backendView={self.props.backendView}
          />
        ));
  
        categoryChildrenDisplay = (
          <ul>
            {categoryChildren}
          </ul>
        );
  
      }
  
      let categoryItemClass = "cat-item";
      if (props.categoryId === parseInt(props.category.id)){
        categoryItemClass += " active";
      }
  
      let productCountDisplay;
      if (props.category.product_count !== "0"){
        productCountDisplay = props.category.product_count;
      }
  
      const categoryItemLink = GenerateCategoryLink(window.baseUrl,props.urlContext,props.category.id,window.location.href);
  
      let catItemContentDisplay;
      if (props.backendView === true){
  
        let submenuToggleDisplay;
        if (props.category.has_children === true){
          console.log(props.category.title);
          console.log(props.category.has_children);
          if (showSubmenu === true){
            submenuToggleDisplay = "[-]";
          } else {
            submenuToggleDisplay = "[+]";
          }
        }
  
        catItemContentDisplay = (
          <span>
            <span className="title"><a href={categoryItemLink}>{props.category.title}</a></span>
            <span className="product-counter">{productCountDisplay}</span>
            <span className="submenu-toggle" onClick={this.toggleSubmenu}>{submenuToggleDisplay}</span>
          </span>
        );
      } else {
        catItemContentDisplay = (
          <a href={categoryItemLink}>
            <span className="title">{props.category.title}</span>
            <span className="product-counter">{productCountDisplay}</span>
          </a>
        );
      }
  
      return(
        <li id={"cat-"+props.category.id} className={categoryItemClass}>
          {catItemContentDisplay}
          {categoryChildrenDisplay}
        </li>
      )
    
}
  
function SelectedCategory(props){  
  let selectedCategoryDisplay;
  if (props.selectedCategory){
    selectedCategoryDisplay = (
      <a onClick={props.onCatTreeToggle}>{props.selectedCategory.title}</a>
    );
  }

  let selectedCategoriesDisplay;
  if (props.selectedCategories){
    const selectedCategoriesReverse = props.selectedCategories.slice(0);
    selectedCategoriesDisplay = selectedCategoriesReverse.reverse().map((sc,index) => (
      <a key={index}>{sc.title}</a>
    ));
  }

  return (
    <div onClick={props.onCatTreeToggle} id="selected-category-tree-item">
      {selectedCategoriesDisplay}
      <span className="selected-category-arrow-down"></span>
    </div>
  )
}
  
function CategorySidePanelContainer(props){
    
    const [ categories, setCategoies ] = React.useState(window.catTree);
    const [ categoryId, setCategoryId ] = React.useState(window.categoryId);
    const [ catTreeSccClass, setCatTreeCssClass ] = React.useState('');
    const [ showCatTree, setShowCatTree ] = React.useState(false);
    const [ backendView, setBackendView ] = React.useState(window.backendView);
    const [ loading, setLoading ] = React.useState(true);
    
    console.log(categories);
  
    const mainCategoriesDisplay = <CategorySidePanel categories={categories} />
  
    return(
      <div id="sidebar-container">
        <CategoryTree/>
        <div id="category-menu-panels-container">
          <div id="category-menu-panels-slider">
            {mainCategoriesDisplay}
          </div>
        </div>
      </div>
    )
}
  
function CategorySidePanel(props){
    
}
  
ReactDOM.render(
  <CategorySidePanelContainer />,
  document.getElementById('category-tree-container')
);
  