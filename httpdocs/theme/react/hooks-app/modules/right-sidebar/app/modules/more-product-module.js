import { useState } from 'react';
import ReactTooltip from 'react-tooltip';
import TimeAgo from 'javascript-time-ago'
import en from 'javascript-time-ago/locale/en'
TimeAgo.addLocale(en)

function MoreProductsModule(props){
    
    let moduleTitleDisplay;
    if (props.type === "user") moduleTitleDisplay = "More " + props.product.cat_title + " from " + props.product.username + ":";
    else if (props.type === "category") moduleTitleDisplay = "Other "  +  props.product.cat_title + ":";
    else if (props.type === "collection") moduleTitleDisplay = "is part of these Collections:";

    const itemsDisplay = props.items.map((item,index) => (
        <MoreProductsListItem
            key={index}
            index={index}
            item={item}
            product={props.product}
            onChangeUrl={props.onChangeUrl}
        />
    ));

    return (
        <div className="prod-widget-box" id={"more-products-of-"+props.type+"-module"}>
            <p className="text-small font-bold mt5 mb3">{moduleTitleDisplay}</p>
            <div className="pling-card-group">
                {itemsDisplay}
            </div>
        </div>
    )
}

export function MoreProductsListItem(props){

    const item = props.item;
    let imageSmallUrl = item.image_small,
        imageSmallUrlPopOver = item.image_small;

    if (item.image_small && item.image_small.indexOf('https://') === -1){
        let hostnameEndsWith = window.location.hostname.endsWith('com') ? 'com' : 'cc';
        imageSmallUrl = "https://cdn.pling."+hostnameEndsWith+"/cache/200x200/img/" + item.image_small;
        imageSmallUrlPopOver = "https://cdn.pling."+hostnameEndsWith+"/cache/200x160/img/" + item.image_small;
    }

    const [ imgUrl, setImgUrl ] = useState(imageSmallUrl);
    const [ popoverImgUrl, setPopoverImgUrl ] = useState(imageSmallUrlPopOver);
    
    function onImageLoadError(){
        setImgUrl(null);
    }
    
    const timeAgo = new TimeAgo('en-US')
    const itemChangedAt = timeAgo.format(Date.parse(item.changed_at))

    const dataContent = (
        <div className="more-product-item-popover">
            <div className="profile-img-product">
                <img width="200" height="160" src={popoverImgUrl} className="imgpopover" src={popoverImgUrl} />
            </div>
            <div className="content">
                <div className="title">
                    <p>
                        {item.title}<br/>
                        last update date: {itemChangedAt}
                    </p>
                </div>
            </div>
        </div>
    )

    let imgDisplay,
        linkCssClass = "no-image";
    if (imgUrl !== null){
        linkCssClass = "";
        imgDisplay = (
            <figure>
                <img onError={() => onImageLoadError()} src={imgUrl ? imgUrl : ""} alt="product"/>
            </figure>
        )
    }
    
    const itemLink = "/p/"+item.project_id+"/";

    return (
        <div className="pui-card-list">
            <a style={{display: "block", width: "100%", height: "100%"}} href={itemLink} data-for={"more-product-item-popover-container-"+item.project_id}  data-tip="" title={item.title} className={linkCssClass}>
                {imgDisplay}
            </a>
            <ReactTooltip 
                id={"more-product-item-popover-container-"+item.project_id}
                className="more-product-item-popover-container"
                place="top"
                effect="solid"
                type="light"
                backgroundColor="#ffffff"
                borderColor="#ccc"
                border={true}
                getContent={[() => {
                    return dataContent
                }]}
                >
            </ReactTooltip>
        </div>
    )

}

export default MoreProductsModule;