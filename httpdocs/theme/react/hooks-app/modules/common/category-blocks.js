import React, {useEffect, useState } from 'react';
import './style/category-blocks.css';

function CategoryBlocks(){
    const [ categories, setCategories ] = useState(window.catTree)

    useEffect(() => {
        generateAllCatListItem()
    },[])

    function generateAllCatListItem(){
        let obj = {
            title:'All',
            id:'',
            product_count:0
        }
        window.catTree.forEach(function(cat){
            obj.product_count = parseInt(obj.product_count) + parseInt(cat.product_count)
        })
        const newCategories = [
            obj,
            ...window.catTree
        ]
        setCategories(newCategories)
    }
    let categoriesDisplay;
    if (categories) categoriesDisplay = categories.map((c,index) => (<CategoryBlockItem key={index} category={c}/> ))
    return (
        <div className="container-normal">
            <div className="pling-cards-grid">
                {categoriesDisplay}
            </div>
        </div>
    )
}

function CategoryBlockItem(props){
    
    const c = props.category;
    
    let sysTitle = c.title;
    if (c.title === "System & Tools") sysTitle = "systools";
    sysTitle = sysTitle.trim()
    sysTitle = sysTitle.toLowerCase()

    let url = "/browse" + ( c.id ? "?cat="+c.id : "");

    const imgUrl = "/theme/react/assets/img/aih-"+sysTitle+".png";
    
    return (
        <div className="pui-card">
            <a href={url} title="link to product page">
                <figure>
                    <img src={imgUrl}/>
                </figure>
                <div className="pui-card-title">
                    <h2 className="mt2 mb2">{c.title}</h2>
                    <p>{c.product_count}</p>
                </div>
            </a>        
        </div>
    )
}

export default CategoryBlocks;