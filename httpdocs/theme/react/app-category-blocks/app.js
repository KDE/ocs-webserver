import React, { useState } from 'react';
import ReactDOM from 'react-dom';

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
    React.useEffect(()=> {
        console.log('hello')
    },[categories])
    return (
        <div id="category-blocks">
            <div className="container aih-container aih-section">
            
            </div>
        </div>
    )
}


const rootElement = document.getElementById("category-blocks-container");
ReactDOM.render(<CategoryBlocks />, rootElement);