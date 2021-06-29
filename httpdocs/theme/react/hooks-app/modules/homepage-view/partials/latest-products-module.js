import React, { useEffect, useState } from 'react';
import { GenerateImageUrl, FormatDate, GenerateToolTipTemplate } from '../../common/common-helpers';
import ScoreCircleModule from '../../common/score-circle-module';
import ReactTooltip from 'react-tooltip';
import LoadingDot from '../../common/loading-dot';

function LatestProductsModule(props){

    function onChangeUrl(e,url,title,catId){
        e.preventDefault();
        props.onChangeUrl(url,title,parseInt(catId));
    }

    const productsDisplay = props.products.map((product,index) => (
        <LatestProductsModuleListItem onChangeUrl={onChangeUrl} key={index} product={product} />
    ))

    let titleDisplay;
    if (props.title){
        const linkDisplay = props.link ? props.link : "/browse/cat/"+props.catId;
        titleDisplay = <a href={linkDisplay} onClick={e => onChangeUrl(e,linkDisplay,props.title,props.catId)} href={linkDisplay}>{props.title}</a>
    } else {
        titleDisplay = props.titles.map((title,index) => {
            const linkDisplay = title.link ? title.link : "/browse/cat/"+title.catId;
            return ( 
                <a href={linkDisplay} onClick={e => onChangeUrl(e,linkDisplay,title.title)} key={index} href={linkDisplay}>
                    {title.title} {index < props.titles.length - 1 ? ", " : " "} 
                </a> 
            )
        })
    }

    return (
        <div className="col-lg-6 col-md-6 col-sm-6 col-xs-12 latest-products-item-list">
            <div className="blockContainer">
                <div className="header">
                    <span className="title">{titleDisplay}</span>
                </div>
                <div className="prod-widget-box new-products">
                    {productsDisplay}
                </div>
            </div>
        </div>
    )
}

function LatestProductsModuleListItem(props){
    
    let xhr;

    const product = props.product;
    const productImageUrl = GenerateImageUrl(product.image_small,80,80);

    const [ toolTipLoading, setToolTipLoading ] = useState(true);
    const [ toolTip, setToolTip ] = useState(null);

    useEffect(() => {
        return () => {
            if (xhr && xhr.abort) xhr.abort();
        }
    },[])

    function loadUserToolTip(){
        if (toolTip === null){
            xhr = $.ajax({url:'/member/' + product.member_id + '/tooltip/'}).done(function(res){
                setToolTip(res.data);
                setToolTipLoading(false);
            })
        }
    }

    let toolTipDisplay, toolTipClassName = "mytooltip-container latest-product-list-item-tooltip"
    if (toolTipLoading === true){
        toolTipDisplay = <LoadingDot/>
    } else {
        toolTipDisplay = GenerateToolTipTemplate(toolTip);
        toolTipClassName = "mytooltip-container post-get-content latest-product-list-item-tooltip"
    }

    let commentsCountDisplay;
    if (product.count_comments !== "0") commentsCountDisplay = <span className="cntComments">{product.count_comments} comments </span>

    let packageNamesDisplay;
    if (product.package_names){
        packageNamesDisplay = product.package_names.split(',').map((pn,index) => (
            <span key={index} className="packagetypeos"> {pn} </span>
        ))
    }
    
    return (
        <div className="productrow startpage-product-list-item">
            <div className="row">
                <div className="col-lg-2 col-md-3 col-sm-4 col-xs-4">
                    <div className="text-center">
                        <a onClick={e => props.onChangeUrl(e,"/p/"+product.project_id,product.title,product.project_category_id)} onMouseEnter={loadUserToolTip} data-tip="" data-for={"latest-product-list-item-tooltip-"+product.project_id} href={"/p/"+product.project_id}>
                            <img className="productimg" src={productImageUrl}/>    
                        </a>
                    </div>
                </div>
                <div className="col-lg-7 col-md-6 col-sm-4 col-xs-4">
                    <a onClick={e => props.onChangeUrl(e,"/p/"+product.project_id,product.title,product.project_category_id)} onMouseEnter={loadUserToolTip} data-tip="" data-for={"latest-product-list-item-tooltip-"+product.project_id} href={"/p/"+product.project_id}>
                        {product.title}
                        <span className="version">{product.version}</span>
                    </a>
                    <span style={{display: "block", marginBottom: "5px"}}>
                        {product.cat_title}
                    </span>
                    <div className="productInfo">
                        {commentsCountDisplay}
                        {packageNamesDisplay}
                    </div>
                </div>
                <div className="col-lg-3 col-md-3 col-sm-4 col-xs-4 text-center">
                    <ScoreCircleModule 
                        score={product.laplace_score}
                        size={42}
                    />
                    <p className="product-created-at">{FormatDate(product.changed_at)}</p>
                </div>
            </div>
            <ReactTooltip 
                id={"latest-product-list-item-tooltip-"+product.project_id}
                place="right"
                effect="solid"
                type="light"
                className={toolTipClassName}
                backgroundColor="#ededed"
                borderColor="#ccc"
                border={true}
                getContent={[() => { return toolTipDisplay}]}
                >
            </ReactTooltip>
        </div>
    )
}

export default LatestProductsModule;