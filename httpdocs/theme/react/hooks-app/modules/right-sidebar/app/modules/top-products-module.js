import React from "react";

function TopProductsModule(props){

    const topProductsItems = props.topProducts.map((tpItem, index) => (
        <TopProductListItem
            key={index}
            index={index}
            item={tpItem}
            onChangeUrl={props.onChangeUrl}
        />
    ))

    return (
        <React.Fragment>
            <hr class="hr-dark"/>
            <span className="mt0 mb3 title-small-upper"> 
                <a href="#">
                    Popularity {'>'} <br/>
                    <small> (based on plings, downloads, etc.)</small>
                </a>
            </span>
            <div className="pling-cards-list-batch">
                {topProductsItems}
            </div>
        </React.Fragment>
    )
}

function TopProductListItem(props){

    const listItemNumber = props.index + 1;
    const item = props.item;
    const productLink = "/p/"+item.project_id+"/"

    return (
        <div className="pui-card-list" id={"top-product" + listItemNumber}>
            <a href={productLink}>
                <div className="rownum">{listItemNumber}</div>
                <figure>
                    <img src={"https://cdn.pling.cc/cache/40x40/img/" + item.image_small}/>
                </figure>
                <div className="pui-card-title">
                    <div className="explore-product-details-wrapper">
                        <h3>{item.title}</h3>
                        <p>{item.category_title}</p>
                    </div>
                </div>
            </a>
        </div>
    )
}

export default TopProductsModule;