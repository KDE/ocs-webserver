import React from 'react';
import ReactDOM from 'react-dom';

function CategoryBlocksContainer(){
    
}

function CategoryBlocks(){
    const [ categories, setCategories ] = React.useState(window.catTree)
    React.useEffect(() => {
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
    if (categories) categoriesDisplay = categories.map((c,index) => (<CategoryBlockItem category={c}/> ))
    return (
        <div id="category-blocks">
            <div className="container aih-container aih-section">
                <div className="aih-row">
                    {categoriesDisplay}
                </div>
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

    let url = "/browse/cat/"+c.id;
    if (!c.id) url = "/browse/"

    const imgUrl = "/theme/react/assets/img/aih-"+sysTitle+".png";
    const ribbonCssClass = "aih-ribbon aih-"+sysTitle
    
    return (
        <a href={"/browse/cat/" + c.id}>
            <div className="aih-card">
                <div className={ribbonCssClass}></div>
                <img className="aih-thumb" src={imgUrl}/>
                <div className="aih-content">
                    <h3 className="aih-title">{c.title}</h3>
                    <p className="aih-counter">{c.product_count} <span>products</span></p>
                </div>
            </div>
        </a>        
    )
}

const rootElement = document.getElementById("category-blocks-container");
ReactDOM.render(<CategoryBlocks />, rootElement);