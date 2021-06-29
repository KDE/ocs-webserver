import React, { useEffect, useState } from 'react';
import PuiCardsGrid from '../common/pui-cards-grid';
import LatestProductsModule from './partials/latest-products-module';

import { GenerateImageUrl, FormatDate } from '../common/common-helpers';

function HomePageView(props){

    return (
        <React.Fragment>
            <SpotlightProducts 
                /*data={} */
                products={props.data.carouselData.data.products} 
                type={props.data.type}
                onChangeUrl={props.onChangeUrl}
                onSetSpotlightProduct={props.onSetSpotlightProduct}
            />
            <hr className="divider"/>
            <PuiCardsGrid key={1} products={props.data.productsApps} title={"Application"} catId={233}/>
            <PuiCardsGrid key={2} products={props.data.productsAddons} title={"App Add-Ons"} catId={152}/>
            <PuiCardsGrid key={3} products={props.data.productsThemesPlasma} title={"KDE Plasma"} catId={365}/>
            <PuiCardsGrid key={4} products={props.data.productsThemesGTK} title={"GTK/Gnome"} catId={152}/>
            <PuiCardsGrid key={5} products={props.data.productsWindowmanager} title={"Window Managers"} catId={147}/>
            <PuiCardsGrid key={6} products={props.data.productsIconsCursors} titles={[{title:"Icons",catId:386},{title:"Cursors",catId:107}]}/>
            <PuiCardsGrid key={7} products={props.data.productsPhone} title={"Phone"} catId={491}/>
            <PuiCardsGrid key={8} products={props.data.productsDistors} title={"Distros"} catId={404}/>
            <PuiCardsGrid key={9} products={props.data.productsCollections} title={"Collections/Rankings"} catId={567} />
            <PuiCardsGrid key={10} products={props.data.productsArtwork} title={"Artwork"} link={"/s/Artwork"}/>
            <PuiCardsGrid key={11} products={props.data.productsWallpapers} title={"Wallpapers"} catId={295}/>
            <PuiCardsGrid key={12} products={props.data.productsWallpapersOriginal} title={"Wallpapers (Original)"} catId={295}/>
            <PuiCardsGrid key={13} products={props.data.productsVideos} titles={[{title:"Music",link:"/s/Music"},{title:"Videos",link:"/s/Videos"}]}/>
            <PuiCardsGrid key={14} products={props.data.productsBooksComics} titles={[{title:"Books",link:"/s/Books"},{title:"Comics",link:"/s/Comics"}]}/>
            <HomepageGitProjects />
        </React.Fragment>
    )
}

function SpotlightProducts(props){
    useEffect(() => {
        initGlider()
    },[])

    function initGlider(){
        var glider = new Glider(document.querySelector('.glider'), {
            slidesToShow: window.innerWidth < 992 ? 1 : 2,
            slidesToScroll: window.innerWidth < 992 ? 1 : 2,
            draggable: true,
            arrows: {
              prev: '.glider-prev',
              next: '.glider-next'
            }
        });
    }

    const productsDisplay = props.products.map((p,index) => (
        <SpotlightProduct key={index} product={p}/>
    ))

    return (
        <div className="container-normal">
            <div className="product-slide-wrapper">
                <h2 className="title-normal">In the Spotlight</h2>
                <div className="glider" id="glider-spotlight">
                    {productsDisplay}
                </div>
                <div className="controls-center">
                    <button aria-label="Previous" className="glider-prev disabled">‹</button>
                    <button aria-label="Next" className="glider-next">›</button>
                </div>
            </div>
        </div>
    )
}

function SpotlightProduct(props){

    const product = props.product;
    
    const productImageUrl = GenerateImageUrl(product.image_small,300,230);
    const productUserAvatarUrl = GenerateImageUrl(product.profile_image_url,50,50)
    
    let descriptionDisplay = product.description;
    if (product.description.length > 280){
        descriptionDisplay = product.description.substr(0,280) + '...';
    }

    let scoreDisplay = Math.round((product.laplace_score / 100 ) * 10) / 10;
    if (scoreDisplay % 1 === 0){
        scoreDisplay = parseInt(scoreDisplay) + '.0';
    }

    return (
    <div className="pui-card glider-slide active left-1 visible" style={{height: "auto", width: "632px"}}>
        <div className="pui-card-maker">
            <a href={"/u/"+product.username} title={`link to ${product.username} profile`}>
            <div className="pui-card-maker-info">
                <figure>
                    <img src={productUserAvatarUrl}/>
                </figure>
                <p>{product.username}</p>
            </div>
            </a>
        </div>
        <a href={"/p/"+product.project_id} title={"link to " + product.title + " page"}>
        <figure>
            <img src={productImageUrl}/>
        </figure>
        <div className="pui-card-title">
            <h3>{product.title}</h3>
            <p>{product.cat_title}</p>
        </div>
        <div className="pui-short-description">
            <p>{descriptionDisplay}</p>
        </div>
        <div className="pui-card-info">
            <p>Plings <span>{product.sum_plings ? product.sum_plings : 0}</span>
                <span className="pui-pling">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2">
                        <path d="M17.467348 7.9178719c-.347122-2.7564841-3.201417-4.3323187-5.789058-3.9194079-1.7356129.2821847-3.460181.6085251-5.1768601.9799707.2997877 1.2572492.6011532 2.5146567.9025188 3.7720641.0899363.3771432.1356934.5656356.2256297.942937.8283607 3.4577522 1.6551436 6.9153452 2.4835046 10.3730972 1.136037-.333937 2.278386-.645559 3.427047-.934707-.362901-1.870206-.725802-3.740411-1.088703-5.610617 0 0 .61062-.129935.929342-.194823 2.440903-.497266 4.406879-2.86078 4.086579-5.4085141zm-4.768202 1.6774402c-.389724.0720101-.583797.1090439-.971943.1854854-.121493-.6234019-.241408-1.2468038-.362901-1.8702057.397613-.074701.59642-.1107848.994033-.1808958.523839-.087995 1.065035.2329646 1.154971.7609334.08994.5200555-.298209 1.0046598-.81416 1.1046827z" fill="#fff"></path>
                    </svg>
                </span>
            </p>
            <p>Score <span className={"pui-score-tag pui-score-tag-"+parseInt(product.laplace_score / 100)}>{scoreDisplay}</span></p>
            <p>Added <span>{FormatDate(product.created_at ? product.created_at : product.changed_at)}</span></p>
        </div>
        </a>
    </div>
    )
}

function LatestProductsModulesContainer(props){

    return (
        <React.Fragment>
            <div className="row blockpadding disply-flex">
                <LatestProductsModule onChangeUrl={props.onChangeUrl} products={props.data.productsApps} title={"Application"} catId={233}/>
                <LatestProductsModule onChangeUrl={props.onChangeUrl}  products={props.data.productsAddons} title={"App Add-Ons"} catId={152}/>
                <LatestProductsModule onChangeUrl={props.onChangeUrl}  products={props.data.productsThemesPlasma} title={"KDE Plasma"} catId={365}/>
                <LatestProductsModule onChangeUrl={props.onChangeUrl}  products={props.data.productsThemesGTK} title={"GTK/Gnome"} catId={152}/>
                <LatestProductsModule onChangeUrl={props.onChangeUrl}  products={props.data.productsWindowmanager} title={"Window Managers"} catId={147}/>
                <LatestProductsModule onChangeUrl={props.onChangeUrl}  products={props.data.productsIconsCursors} titles={[{title:"Icons",catId:386},{title:"Cursors",catId:107}]}/>
                <LatestProductsModule onChangeUrl={props.onChangeUrl}  products={props.data.productsPhone} title={"Phone"} catId={491}/>
                <LatestProductsModule onChangeUrl={props.onChangeUrl}  products={props.data.productsDistors} title={"Distros"} catId={404}/>
                <LatestProductsModule onChangeUrl={props.onChangeUrl}  products={props.data.productsCollections} title={"Collections/Rankings"} catId={567} />
                <LatestProductsModule onChangeUrl={props.onChangeUrl}  products={props.data.productsArtwork} title={"Artwork"} link={"/s/Artwork"}/>
                <LatestProductsModule onChangeUrl={props.onChangeUrl}  products={props.data.productsWallpapers} title={"Wallpapers"} catId={295}/>
                <LatestProductsModule onChangeUrl={props.onChangeUrl}  products={props.data.productsWallpapersOriginal} title={"Wallpapers (Original)"} catId={295}/>
                <LatestProductsModule onChangeUrl={props.onChangeUrl}  products={props.data.productsVideos} titles={[{title:"Music",link:"/s/Music"},{title:"Videos",link:"/s/Videos"}]}/>
                <LatestProductsModule onChangeUrl={props.onChangeUrl}  products={props.data.productsBooksComics} titles={[{title:"Books",link:"/s/Books"},{title:"Comics",link:"/s/Comics"}]}/>
            </div>
        </React.Fragment>
    )
}

function HomepageGitProjects(){

    let xhr = null;

    const [ loading, setLoading ] = useState(true);
    const [ gitItems, setGitItems ] = useState();
    
    useEffect(() => {
        initGitProjectsModule();
        return () => {
            if (xhr && xhr.abort) xhr.abort();
        }
    },[])

    function initGitProjectsModule(){
        xhr = $.ajax('/json/gitlabnewprojects').then(function (result) {
            setGitItems(result);
            setLoading(false);
        });
    }

    let gitProjectsDisplay = "LOADING...";
    if (loading === false){
        gitProjectsDisplay = gitItems.map((gItem,index) => (
            <HomepageGitProject
                key={index}
                item={gItem}
            />    
        ))
    }

    return (
        <div className="container-normal">
            <a href="#" title="Link to opencode explore page"><h2>Opencode Git Projects ›</h2></a>
            <div className="oc-cards-grid">
                {gitProjectsDisplay}
            </div>
        </div>
    )
}

function HomepageGitProject(props){

    const item = props.item;

    const initGitUrlVal = "https://git.opendesktop." + ( window.location.hostname.endsWith('com') || window.location.hostname.endsWith('org')  ? 'org' : 'cc' );
    const [ gitUrl, setGitUrl ] = useState(initGitUrlVal);
    const [ userAvatarUrl, setUserAvatarUrl ] = useState('')

    useEffect(() => {
        const json_url = 'https://git.opendesktop.org/api/v4/users?username=' + item.namespace.name;
        $.ajax(json_url).then(function (result) {
            setUserAvatarUrl(result[0].avatar_url);
        });
    },[])

    let avatarDisplay;
    if (item.avatar_url) avatarDisplay = <img src={item.avatar_url} />
    else avatarDisplay =  item.namespace.name.substr(0,1)
    
    return (
        <div className="oc-card">
            <div className="oc-card-info">
                <a href={item.web_url} title={"link to "+item.web_url}>
                    <div>
                        {avatarDisplay}
                    </div>
                    <h3>{item.name}</h3>
                </a>
            </div>
            <div className="oc-card-meta">
                <a href={gitUrl+"/"+item.namespace.name} title={item.namespace.name + " profile link"}>
                    <figure>
                        <img id={"avatar_" + item.namespace.name + "_" + item.id} src={userAvatarUrl}/>    
                    </figure>
                    <div>
                        <p>{item.namespace.name}</p>
                        <p className="oc-date">6 hours ago</p>
                    </div>
                </a>
            </div>
        </div>
    )
}

export default HomePageView;