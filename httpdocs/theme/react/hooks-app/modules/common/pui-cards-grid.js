import React from 'react';
import { FormatDate, GenerateImageUrl } from './common-helpers';

function PuiCardsGrid(props){

    let titlesDisplay;
    if (props.title) titlesDisplay = <h2><a href={props.link}>{props.title}</a></h2>
    else if (props.titles){
        const titles = props.titles.map((t,index) => (
            <React.Fragment>
                <a key={index} href={t.link}>{t.title}</a>
                {props.titles.length > index + 1 ? " / " : ""}
            </React.Fragment>
        ))
        titlesDisplay = <h2>{titles}</h2>
    }

    let cardsDisplay;
    if (props.products){
        cardsDisplay = props.products.map((product,index) => (
            <PuiCard 
                key={index}
                product={product}
                showUser={props.showUser}
            />
        ));
    }

    let templateDisplay;
    if (props.gridType === "compact"){
        templateDisplay = (
            <React.Fragment>
                {titlesDisplay}
                <div className="pling-cards-grid pling-cards-grid-compact">
                    {cardsDisplay}
                </div>
            </React.Fragment>
        )
    } else {
        templateDisplay = (
            <React.Fragment>
                <div className="container-normal">
                    {titlesDisplay}
                    <div className="pling-cards-grid">
                        {cardsDisplay}
                    </div>
                </div>
                <hr className="divider"/>
            </React.Fragment>
        )
    }

    return (
        <React.Fragment>
            {templateDisplay}
        </React.Fragment>
    )
}

function PuiCard(props){
    const product = props.product;
    let plingsDisplay = 0;
    if (product.count_plings) plingsDisplay = product.count_plings;
    else if (product.sum_plings) plingsDisplay = product.sum_plings;

    let productMakerDisplay;
    if (props.showUser !== false){
        productMakerDisplay = (
            <div className="pui-card-maker">
                <a href={`/u/${product.username}`} title={`link to ${product.username}'s profile page`}>
                <div className="pui-card-maker-info">
                    <figure>
                        <img src={product.profile_image_url}/>
                    </figure>
                    <p>{product.username}</p>
                </div>
                </a>
            </div>
        )
    }

    let scoreDisplay = Math.round((product.laplace_score / 100 ) * 10) / 10;
    if (scoreDisplay % 1 === 0){
        scoreDisplay = parseInt(scoreDisplay) + '.0';
    }

    return (
        <div className="pui-card">
            {productMakerDisplay}
            <a href={"/p/"+product.project_id} title={"link to ["+product.title+"] product page"}>
                <figure>
                    <img src={GenerateImageUrl(product.image_small,225,160)}/>
                </figure>
                <div className="pui-card-title">
                    <h3>{product.title}</h3>
                    <p>{product.cat_title ? product.cat_title : product.catTitle}</p>
                </div>
                <div className="pui-card-info">
                    <p>Plings <span>{plingsDisplay}</span>
                        <span className="pui-pling">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2">
                                <path d="M17.467348 7.9178719c-.347122-2.7564841-3.201417-4.3323187-5.789058-3.9194079-1.7356129.2821847-3.460181.6085251-5.1768601.9799707.2997877 1.2572492.6011532 2.5146567.9025188 3.7720641.0899363.3771432.1356934.5656356.2256297.942937.8283607 3.4577522 1.6551436 6.9153452 2.4835046 10.3730972 1.136037-.333937 2.278386-.645559 3.427047-.934707-.362901-1.870206-.725802-3.740411-1.088703-5.610617 0 0 .61062-.129935.929342-.194823 2.440903-.497266 4.406879-2.86078 4.086579-5.4085141zm-4.768202 1.6774402c-.389724.0720101-.583797.1090439-.971943.1854854-.121493-.6234019-.241408-1.2468038-.362901-1.8702057.397613-.074701.59642-.1107848.994033-.1808958.523839-.087995 1.065035.2329646 1.154971.7609334.08994.5200555-.298209 1.0046598-.81416 1.1046827z" fill="#fff"></path>
                            </svg>
                        </span>
                    </p>
                    <p>Score <span className={"pui-score-tag pui-score-tag-" + parseInt(product.laplace_score / 100)}>{scoreDisplay}</span></p>
                    <p>Added <span>{FormatDate(product.created_at ? product.created_at : product.project_created_at)}</span></p>
                </div>
            </a>
        </div>
    )
}

export default PuiCardsGrid;