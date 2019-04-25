function CategoryBlocks(){
    console.log(window.catTree)
    const catList = [
        {title:'All',product_count:'427'},
        {title:'Audio',product_count:'47'},
        {title:'Education',product_count:'27'},
        {title:'Games',product_count:'17'},
        {title:'Graphics',product_count:'42'},
        {title:'Internet',product_count:'427'},
        {title:'Office',product_count:'427'},
        {title:'Programming',product_count:'427'},
        {title:'Systools',product_count:'427'},
        {title:'Video',product_count:'427'}
    ]
    const [ categories, setCategories ] = React.useState(catList)
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
    const imgUrl = "/theme/react/assets/img/aih-"+c.title.toLowerCase()+".png";
    const ribbonCssClass = "aih-ribbon aih-"+c.title.toLowerCase();

    return (
        <a href={"/browse/cat/" + props.category.cat_id}>
            <div className="aih-card">
                <div className={ribbonCssClass}></div>
                <img className="aih-thumb" src={imgUrl}/>
                <div className="aih-content">
                    <h3 className="aih-title">{c.title}</h3>
                    <p className="aih-counter">427 <span>products</span></p>
                </div>
            </div>
        </a>        
    )
}

const rootElement = document.getElementById("category-blocks-container");
ReactDOM.render(<CategoryBlocks />, rootElement);