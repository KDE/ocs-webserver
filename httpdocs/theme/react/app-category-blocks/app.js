function CategoryBlocks(){

    const catList = [
        {title:'All',product_count:'427'},
        {title:'Audio',product_count:'47'},
        {title:'Education',product_count:'27'},
        {title:'Games',product_count:'17'},
        {title:'Graphics',product_count:'42'},
        {title:'Internet',product_count:'427'},
        {title:'Office',product_count:'427'},
        {title:'Programming',product_count:'427'},
        {title:'System & Tools',product_count:'427'},
        {title:'Video',product_count:'427'}
    ]

    const [ categories, setCategories ] = useState(catList)


    let categoriesDisplay;
    if (categories) categoriesDisplay = categories.map((c,index) => (<CategoryBlockItem category={c}/> ))
    return (
        <div id="category-blocks">
            <div className="container aih-container aih-section">
                <div classNAme="aih-row">
                    {categoriesDisplay}
                </div>
            </div>
        </div>
    )
}

function CategoryBlockItem(props){

    return (
        <a href="#">
            <div className="aih-card">
                <div className="aih-ribbon aih-all"></div>
                <img className="aih-thumb" src="/theme/react/assets/img/aih-all.png"/>
                <div className="aih-content">
                    <h3 className="aih-title">All</h3>
                    <p className="aih-counter">427 <span>products</span></p>
                </div>
            </div>
        </a>        
    )
}

const rootElement = document.getElementById("category-blocks-container");
ReactDOM.render(<CategoryBlocks />, rootElement);