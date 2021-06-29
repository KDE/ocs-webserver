import ScoreCircleModule from './score-circle-module';
import { GenerateImageUrl, FormatDate } from './common-helpers';
import { useState } from 'react';
import TimeAgo from 'react-timeago';

function MiniCardsModule(props){

    let itemsDisplay;
    if (props.items){
        itemsDisplay = props.items.map((item,index) => (
            <MiniCardListItem {...props} key={index} item={item} />
        ))
    }

    return (
        <div className="row">
            <div className="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                {itemsDisplay}
            </div>
        </div>
    )
}

export function MiniCardListItem(props){

    const item = props.item;
    const [ imgUrl, setImgUrl ] = useState(GenerateImageUrl(item.image_small,280,171))

    function onMiniCardImageLoadError(){
        const newImageUrl = "https://cdn.pling."+ (window.location.hostname.endsWith('cc') ? "cc" : "com") +"/cache/280x171/img/default.png"
        setImgUrl(newImageUrl)
    }

    function onMiniCardClick(e){
        if (props.onChangeUrl){
            e.preventDefault();
            let categoryId;
            if (item.project_category_id) categoryId = parseInt(item.project_category_id)
            props.onChangeUrl("/p/"+item.project_id+"/",item.title,categoryId)
        }
    }

    let miniCardWrapperStyle;
    if (props.itemStyle) miniCardWrapperStyle = props.itemStyle;

    let scoreModuleCss = {
        position: "absolute",
        bottom: "0",
        right: "4px",
        padding: "2px 4px"
    }

    let catTitleDisplay;
    if (item.catTitle) catTitleDisplay = <span className="productCategory"> {item.catTitle}</span>

    let userDisplay;
    if (props.showUser !== false && item.username) userDisplay = <span className="productCategory"> by {item.username} </span>

    let plingsDisplay;
    if (props.showPlings === true){
        if (item.sum_plings){
            plingsDisplay = (
                <div className="plings"><img src="/images/system/pling-btn-active.png"/>{item.sum_plings}</div>
            )        
        } else if (item.countplings && item.countplings !== "0"){
            plingsDisplay = (
                <div style={{fontSize: "12px",lineHeight: "20px",paddingLeft:"3px"}} className="plings">
                    {item.countplings}
                    <img style={{marginLeft:"3px"}} src="/images/system/pling-btn-active.png"/>
                </div>
            )
        }
    }
        
    let dateDisplay;
    const dateToFormat = item.pling_created_at ? item.pling_created_at : item.project_created_at;
    if (dateToFormat){
        if (props.dateDisplay === "timeAgo"){
            dateDisplay =  <TimeAgo date={item.created_at}></TimeAgo>
        }
        else dateDisplay = FormatDate(dateToFormat);
    }

    return (
        <div className={"product mini-card col-lg-2 col-md-2 col-sm-3 col-xs-6 " + (props.itemCssClass ? props.itemCssClass : "" ) } style={miniCardWrapperStyle}>
            <div className="u-wrap">
                <a href={"/p/"+item.project_id+"/"} onClick={e => onMiniCardClick(e)}>
                    <figure>
                        <img onError={onMiniCardImageLoadError} src={imgUrl}/>
                    </figure>
                    <div className="u-content">
                        <h3>{item.title}</h3>
                        <span className="productCategory"> {item.cat_title} </span>
                        { catTitleDisplay }
                        { userDisplay }
                        <span className="productCategory"> {dateDisplay} </span>
                    </div>
                </a>
            </div>
            {plingsDisplay}
            <div style={scoreModuleCss} className="mini-card-score-wrapper">
                <a href={"/p/"+item.project_id+"/"}>
                    <ScoreCircleModule 
                        score={item.laplace_score}
                        size={props.scoreCircleSize ? props.scoreCircleSize : 40}
                    />
                </a>
            </div>
        </div>
    )
}

export default MiniCardsModule;