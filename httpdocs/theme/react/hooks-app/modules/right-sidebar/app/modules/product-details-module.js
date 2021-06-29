import React, { useState, useEffect } from 'react';
import { FormatDate } from './../helpers/right-sidebar-helpers';
import CustomModal from '../../../common/modal';
import LoadingDot from '../../../common/loading-dot';

function ProductDetailsModule(props){

    const initIsSpamChecked = props.product.spam_checked;
    const [ isSpamChecked, setIsSpamChecked ] = useState(initIsSpamChecked);

    const initIsFeatured = props.product.featured;
    const [ isFeatured, setIsFeatured ] = useState(initIsFeatured);

    const initIsGhnsExcluded = props.data.isGhnsExcluded;
    const [ isGhnsExcluded, setIsGhnsExcluded ] = useState(initIsGhnsExcluded);

    const [ showGhnsModal, setShowGhnsModal ] = useState(false);

    const initIsPlingExcluded = props.product.pling_excluded;
    const [ isPlingExcluded, setIsPlingExcluded ] = useState(initIsPlingExcluded);

    const initProductCategoryId = props.product.project_category_id;
    const [ productCategoryId, setProductCategoryId ] = useState(initProductCategoryId);

    function onDeleteProductClick(event){
        event.stopPropagation();
        const url = "/backend/project/delete?project_id="+props.product.project_id;
        const result = confirm("Delete Product?");
        if (result) {
            $.ajax({
                url: url,
                success: function (results) {
                    alert('Product deleted successfully');
                },
                error: function () {
                    alert('Service is temporarily unavailable.');
                }
            });
        }
    }

    function onSpamCheckedClick(event){
        event.preventDefault();
        event.stopPropagation();
        let feature = 0;
        if (isSpamChecked === "0") feature = 1; 
        const url = "/backend/project/dospamchecked?project_id="+props.product.project_id+"&checked="+feature;
        $.ajax({
            url: url,
            success: function (results) {
                if (results.spam_checked === 0) {
                    setIsSpamChecked('0');
                } else {
                    setIsSpamChecked('1');
                }
            },
            error: function () {
                alert('Service is temporarily unavailable.');
            }
        });
    }

    function onFeatureCheckedClick(event){

            event.preventDefault();
            event.stopPropagation();

            let feature = 0;
            if (isFeatured === "0") feature = 1;

            const url = "/backend/project/dofeature?project_id="+props.product.project_id+"&featured=" + feature;
            $.ajax({
                url: url,
                success: function (results) {
                    if (isFeatured === '1') {
                        alert('Project remove featured successfully');
                        setIsFeatured('0');
                    } else {
                        alert('Project set featured successfully');
                        setIsFeatured('1');
                    }
                },
                error: function () {
                    alert('Service is temporarily unavailable.');
                }
            });

    }

    function onGhnsExcludedCheckedClick(event){
        event.preventDefault();
        event.stopPropagation();
        setShowGhnsModal(true);
    }

    function onChangeGhnsValue(val){
        setIsGhnsExcluded(val);
    }

    function onPlingExcludedCheckedClick(event){

        event.preventDefault();
        event.stopPropagation();

        let status = 1;
        if (isPlingExcluded === "1") status = 0;

        const url = "/backend/project/doexclude?project_id="+props.product.project_id+"&pling_excluded=" + status;
        $.ajax({
            url: url,
            success: function (results) {
                if (isPlingExcluded == true) {
                    alert('Project was successfully included for plinging');
                    setIsPlingExcluded('0');

                } else {
                    alert('Project was successfully excluded for plinging');
                    setIsPlingExcluded('1');
                }
            },
            error: function () {
                alert('Service is temporarily unavailable.');
            }
        });
    }

    function onChangeProductCategoryClick(event){
            event.stopPropagation();
            event.preventDefault();
            $.ajax({
                url: "/backend/project/changecat?project_id="+props.product.project_id+"&project_category_id=" + productCategoryId,
                success: function (results) {
                    alert('Project updated successfully');
                    location.reload();
                },
                error: function () {
                    alert('Service is temporarily unavailable.');
                }
            });
    }

    let adminDetailsSectionDisplay;
    if (props.isAdmin === true){
        adminDetailsSectionDisplay = (
            <React.Fragment>
                <span className="prod-widget-details text-small font-bold link-primary-invert mt4 mb3">
                    <a target="_NEW" href={"http://cp1.hive01.com/content/show.php?content="+props.product.project_source_pk}><i>link to hive</i></a>
                    <a id="delete-this" onClick={(event) =>onDeleteProductClick(event)} href={"/backend/project/delete?project_id="+props.product.project_id}><i>delete product</i></a>
                </span>
                <div style={{"clear":"both"}} className="small">(remember the cache) after you change some value below and refresh the page you may encounter some differences to your changes</div>
                <span className="prod-widget-details text-small">
                    <span className="page-views font-italic">
                        <input type="checkbox" onChange={(event) => onSpamCheckedClick(event)} id="spam-checked-checkbox" autoComplete="off" checked={isSpamChecked === '1' ? 'checked' : ''}/> 
                        <i>spam checked ({isSpamChecked === '1' ? '1' : '0'})</i>
                    </span>
                    <span className="page-views font-italic value">
                        <input type="checkbox" onChange={(event) => onFeatureCheckedClick(event)} id="feature-this-checkbox" autoComplete="off" checked={isFeatured === '1' ? 'checked' : ''} />  
                        <i>featured  ({isFeatured === '1' ? '1' : '0'})</i>
                    </span>
                </span>
                <span className="prod-widget-details text-small">

                    <span className="page-views font-italic" style={{"color": "red"}}>
                        <input type="checkbox" onChange={(event) => onGhnsExcludedCheckedClick(event)} autoComplete="off" checked={isGhnsExcluded === true ? 'checked' : ''}/>
                            <i>ghns-excluded ({isGhnsExcluded === true ? '1' : '0'})</i>
                            <CustomModal 
                                isOpen={showGhnsModal}
                                header={"GHNS"}
                                closeModal={() => setShowGhnsModal(false)}
                                hasFooter={true}
                            >
                                <GhnsExcludeForm 
                                    isGhnsExcluded={isGhnsExcluded}
                                    onChangeGhnsValue={onChangeGhnsValue} 
                                    closeModal={() => setShowGhnsModal(false)}
                                    {...props}
                                />
                            </CustomModal>
                    </span>
                    
                    <span className="page-views font-italic value" style={{"color": "red"}}>
                        <input type="checkbox" onChange={(event) => onPlingExcludedCheckedClick(event)} id="pling-excluded-checkbox" autoComplete="off" checked={isPlingExcluded === '1' ? 'checked' : ''}/>
                        <i>pling-excluded ({isPlingExcluded === '1' ? '1' : '0'})</i>
                    </span>

                </span>

                <span className="page-views" id="change-product-category-container">
                    <input onChange={(e) => setProductCategoryId(e.target.value)} style={{"width":"40px"}} type="text" id="project_category_id" value={productCategoryId}/> 
                    <button onClick={onChangeProductCategoryClick} id="change_cat">change category</button>
                </span>
            </React.Fragment>
        )
    }

    return (
        <div className="prod-widget-box details mt5">
            <span className="section-title"> Details </span>
            <ProductDetailsModuleDataTable {...props} />
            <ProductDetailsModuleUserActions {...props} />
            {adminDetailsSectionDisplay}
        </div>
    )
}

function ProductDetailsModuleDataTable(props){
    
    const [ adminInfoLoading, setAdminInfoLoading ] = useState(false);

    const [ cntDldsToday, setCntDldsToday ] = useState(props.data['countDownloadsTodayUk']);
    const [ cntMediaViewsAllTime, setCntMediaViewsAllTime ] = useState( props.data['countMediaViewsAlltime'])

    function loadAdminExtraInfo(){
        if (adminInfoLoading === false){
            setAdminInfoLoading(true);
            $.ajax({url:`/p/${props.product.project_id}/loadAdminInfo`}).done(function(res){
                setAdminInfoLoading(false);
                setCntDldsToday(res.cntDlToday)
                setCntMediaViewsAllTime(res.cntMediaViewsAlltime)
            });
        }
    }

    let majorUpdatedRow,
        downloads24oldRow,
        mediaViewsTotal,
        pageViews24,
        spamReports24,
        misuseReports24;

    if (props.isAdmin){
        majorUpdatedRow = (
            <div className="prod-widget-details text-small font-italic">
                <span>major updated</span>
                <span className="value"> {FormatDate(props.product['project_major_updated_at'].split(" ")[0])}</span>
            </div>
        )
        
        downloads24oldRow = (
            <div className="prod-widget-details text-small font-italic">
                <span>downloads 24h old</span>
                <span className="value"> 
                    {cntDldsToday}
                    <a onClick={() => loadAdminExtraInfo()} className="btn btn-primary active pui-btn-smaller">
                        {adminInfoLoading === true ? <LoadingDot/> : ""}
                        Load
                    </a>
                </span>
            </div>
        )

        mediaViewsTotal = (
            <div className="prod-widget-details text-small font-italic">
                <span>mediaviews total</span>
                <span className="value"> {props.data['countMediaViewsAlltime']}</span>
            </div>            
        )

        pageViews24 = (
            <div className="prod-widget-details text-small font-italic">
                <span>pageviews 24h</span>
                <span className="value"> {props.data['countPageviewsTotal']}</span>
            </div>            
        )

        spamReports24 = (
            <div className="prod-widget-details text-small font-italic">
                <span>spam reports</span>
                <span className="value"> {props.data['countMediaViewsAlltime']}</span>
            </div>            
        )

        misuseReports24 = (
            <div className="prod-widget-details text-small font-italic">
                <span>mediaviews total</span>
                <span className="value"> {cntMediaViewsAllTime}</span>
            </div>            
        )

    }

    return (
        <React.Fragment>
            <div className="prod-widget-details text-small">
                <span>license</span>
                <span className="value"> {props.product['project_license_title']}</span>
            </div>
            <div className="prod-widget-details text-small">
                <span>version</span>
                <span className="value"> {props.product['project_version']}</span>
            </div>
            <div className="prod-widget-details text-small">
                <span>updated</span>
                <span className="value"> {FormatDate(props.product['project_changed_at'].split(" ")[0])}</span>
            </div>
            {majorUpdatedRow}
            <div className="prod-widget-details text-small">
                <span>added</span>
                <span className="value"> {FormatDate(props.product['project_created_at'].split(" ")[0])}</span>
            </div>
            {downloads24oldRow}
            <div className="prod-widget-details text-small">
                <span>downloads 24h</span>
                <span className="value"> {props.data['countDownloadsToday']}</span>
            </div>
            <div className="prod-widget-details text-small">
                <span>mediaviews 24h</span>
                <span className="value"> {props.data['countMediaViewsToday']}</span>
            </div>
            {mediaViewsTotal}
            <div className="prod-widget-details text-small">
                <span>pageviews 24h</span>
                <span className="value"> {props.data['countMediaViewsToday']}</span>
            </div>
            {pageViews24}
            {spamReports24}
            {misuseReports24}
        </React.Fragment>
    )
}

function ProductDetailsModuleUserActions(props){
    
    const [ showModal, setShowModal ] = useState(false);
    const [ modalType, setModalType ] = useState(null)

    const [ isReported, setIsReported ] = useState(false);
    const [ isSpamReported, setIsSpamReported ] = useState(false);

    function onReportMisuseClick(){
        if (props.user) setModalType('misuse');
        else setModalType('login');
        setShowModal(true);
    }

    function onReportSpamClick(){
        if (props.user) setModalType('spam');
        else setModalType('login');
        setShowModal(true);
    }

    let modalHeaderDisplay, modalContentDisplay, modalBodyClassName, hasFooter;

    if (modalType === 'login'){
        modalBodyClassName = "align-center";
        modalContentDisplay = (
            <React.Fragment>
                    <div className="please-login">
                        <p>
                        Please Login.
                        </p>
                        <a className="pui-btn primary" href={(json_loginurl ? json_loginurl : "/login/")}>Login</a>
                    </div>
            </React.Fragment>
        )
    } else if (modalType === 'misuse'){
        modalHeaderDisplay = "Repost Misuse";
        hasFooter = true;
        modalContentDisplay = <ReportMisuseForm isReported={isReported} setIsReported={(e) => setIsReported(true)} product={props.product} member={props.user} closeModal={() => setShowModal(false)} />
    } else if (modalType === 'spam'){
        modalHeaderDisplay = "Report Spam";
        hasFooter = true;
        modalContentDisplay = <ReportSpamForm  isReported={isSpamReported} product={props.product} member={props.user} closeModal={() => setShowModal(false)} setIsReported={(e) => setIsSpamReported(true)}/>
    }

    return (
        <div className="prod-widget-details text-small font-bold link-primary-invert mt4 mb3" id="product-actions">
            <a style={{cursor:"pointer"}} onClick={onReportMisuseClick}><span className="link-primary-invert"></span> Report Misuse</a>
            <a style={{cursor:"pointer"}} onClick={onReportSpamClick}><span className="link-primary-invert"></span> Report SPAM</a>
            <CustomModal 
                isOpen={showModal}
                header={modalHeaderDisplay}
                hasFooter={hasFooter}
                closeModal={() => setShowModal(false)}
                modalBodyClassName={modalBodyClassName}
            >
                {modalContentDisplay}
            </CustomModal>
        </div>
    )
}

function ReportMisuseForm(props){

    const [ loading, setLoading ] = useState(false); 
    const [ text, setText ] = useState('');
    const [ isReported, setIsReported ] = useState(props.isReported);
    const [ error, setError ] = useState('');

    useEffect(() => {
        props.setIsReported(isReported);
    },[isReported])

    function updateText(e){ setText(e.target.value); }

    function onReportMisuseFormSubmit(){
        if (text.length < 5){
            setError('at least 5 chars')
        } else if (!isReported && !loading){
            setError(null);
            setLoading(true);
            jQuery.ajax({
                data:"p="+props.product.project_id+"&t="+text,
                url: "/report/productfraud/",
                type: "POST",
                dataType: "json",
                error: function () { setError("Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.")},
                success: function (data, textStatus) {
                    if (data.redirect) {
                        // data.redirect contains the string URL to redirect to
                        window.location = data.redirect;
                    } else {
                        setLoading(false);
                        setIsReported(true);
                    }
                }
            });
        }
    }

    let formTextDiplay, formTextareaDisplay, fromButtonDisplay;
    if (!isReported){
        formTextDiplay = "Please specify why this product is misused (min 5 chars): ";
        if (!loading ) formTextareaDisplay = <textarea value={text} onChange={(e) => updateText(e)}></textarea>
        else {
            formTextareaDisplay = (
                <div style={{paddingBottom:"10px",textAlign:"center"}}>
                    <span className="right-side-popover-ajax-loader "></span>
                </div>
            )
        }
        fromButtonDisplay = (
            <button onClick={onReportMisuseFormSubmit} type="submit" className="footer-button small">
                <span className="glyphicon glyphicon-share-alt"></span> Report
            </button>
        )
    } else {
        formTextDiplay = (
            <React.Fragment>
                <p>Thank you for reporting the misuse.</p>
                <p>We will try to verify the reason for this case.</p>
            </React.Fragment>
        )
        fromButtonDisplay = <button onClick={props.closeModal} className="footer-button small close" data-dismiss="modal"> Close</button>
    }

    return (
        <React.Fragment>
            <div id="report-product-misuse-form-container">
                <p>{formTextDiplay}</p>
                <p style={{color:"red"}}>{error}</p>
                {formTextareaDisplay}
            </div>
            <div className="custom-modal-footer">
                {fromButtonDisplay}
            </div>
        </React.Fragment>
    )
}

function ReportSpamForm(props){
    
    const [ text, setText ] = useState("Do you really want to report this product?");
    const [ loading, setLoading ] = useState(false);
    const [ isReported, setIsReported ] = useState(props.isReported);

    useEffect(() => {
        props.setIsReported(true);
    },[isReported])

    function onReportSpamFormSubmit(e){
        e.preventDefault();
        setLoading(true);
        if (!isReported){
            jQuery.ajax({
                data:"p="+props.product.project_id,
                url: "/report/product/",
                type: "POST",
                dataType: "json",
                error: function () { setError("Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.")},
                success: function (data, textStatus) {
                    if (data.redirect) {
                        // data.redirect contains the string URL to redirect to
                        window.location = data.redirect;
                    } else {
                        setLoading(false);
                        setText(data.message)
                        setIsReported(true);
                    }
                }
            });
        }
    }

    let formContentDisplay;
    if (loading === true){
        formContentDisplay = (
            <React.Fragment>
                <p>{text}</p>
                <div className="custom-modal-footer">
                    <button className="footer-button small close no-button">
                        <span className="glyphicon glyphicon-refresh spinning"></span>
                    </button>
                </div>
            </React.Fragment>
        )
    } else {
        if (isReported === false){
            formContentDisplay = (
                <React.Fragment>
                    <p>{text}</p>
                    <div className="custom-modal-footer">
                        <button onClick={(e) => onReportSpamFormSubmit(e)} type="submit" className="footer-button small close no-button" style={{opacity:'1'}}>
                            <span className="glyphicon glyphicon-share-alt"></span> yes
                        </button>
                    </div>
                </React.Fragment>
            )
        } else {
            formContentDisplay = (
                <React.Fragment>
                    <div onClick={props.closeModal} style={{height:"20px",overflow:"hidden"}} dangerouslySetInnerHTML={{__html:text}}></div>
                    <div className="custom-modal-footer">
                        <button onClick={props.closeModal} className="footer-button small close"> Close</button>
                    </div>
                </React.Fragment>
            )
        }
    }
    
    return (
        <React.Fragment>
            {formContentDisplay}
        </React.Fragment>
    )
}

function GhnsExcludeForm(props){

    const [ loading, setLoading ] = useState(false);
    let initkeywordValue = props.data.isGhnsExcluded === true  ? "include" : "exclude"; 
    const [ keyword, setKeyword ] = useState(initkeywordValue);
    let initTextDisplay = "Please specify why this product should be "+keyword+"d (min 5 chars) :";
    const [ textDisplay, setTextDisplay ] = useState(initTextDisplay);
    const [ error, setError ] = useState('');
    const [ text, setText ] = useState('');    

    function onSubmitGhnsForm(){
        if (!loading){
            if (text.length < 5) setError("min 5 chars!");
            else {
                setError('');
                setLoading(true);
    
                let ghns_excluded = props.isGhnsExcluded === true ? "0" : "1";
    
                $.ajax({
                    url: '/backend/project/doghnsexclude',
                    method:'POST',
                    data:{'project_id':props.product.project_id,'ghns_excluded':ghns_excluded,'msg':text},
                    success: function (results) {
                        setLoading(false);
                        if (props.isGhnsExcluded === true) {
                            setTextDisplay('Project is successfully included into GHNS');
                            props.onChangeGhnsValue(false);
                        } else {
                            setTextDisplay('Project is successfully excluded into GHNS');
                            props.onChangeGhnsValue(true);
                        }
                        setTimeout(function () {
                            props.closeModal()
                        },500);
                    },
                    error: function () {
                        setError('Service is temporarily unavailable.');
                    }
                });            
            }
        }
    }

    let errorDisplay;
    if (error.length > 0) errorDisplay = <p style={{color:"red"}}>{error}</p>

    let textAreaDisplay;
    if (loading === false){
        textAreaDisplay = <textarea value={text} onChange={(e) => setText(e.target.value)}></textarea>
    } else {
        textAreaDisplay = (
            <p style={{textAlign:"center"}}>
                <span className="right-side-popover-ajax-loader"></span>
            </p>
        )
    }

    return (
        <React.Fragment>
            <div id="ghns-exclude-form-container">
                <p>{textDisplay}</p>
                {errorDisplay}
                {textAreaDisplay}
            </div>
            <div className="custom-modal-footer">
                <button onClick={onSubmitGhnsForm} className="footer-button">ghns {keyword}</button>
            </div>
        </React.Fragment>
    )
}

export default ProductDetailsModule;