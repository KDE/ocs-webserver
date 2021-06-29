import React, { useContext, useState, useEffect } from 'react';
import { Context } from '../context/context-provider';
import { GenerateImageUrl } from '../../../common/common-helpers';
import ScoreCircleModule from '../../../common/score-circle-module';
import UserToolTipModule from '../../../common/user-tooltip-module';
import filesize from 'filesize';
import DownloadInstallModal from '../../../common/download-install-modal';
import parser from 'bbcode-to-react';
import '../../style/listing-page.css';

function CollectionProductsList(props){

    const { productViewState } = useContext(Context);
    const collectionProductsList = productViewState.listing_projects.map((cp,index) => {
        const productFiles = productViewState.listing_dls.filter(f => f.collection_id === cp.ppload_collection_id)
        return (
            <CollectionProductListItem 
                key={index} 
                index={index}
                product={cp} 
                file={productFiles}
                onChangeUrl={props.onChangeUrl}
            />
        )
    });

    return (
        <div className="collectionProductsContainer">
            {collectionProductsList}
        </div>
    )
}

function CollectionProductListItem(props){

    const product = props.product;
    const [ showDetails, setShowDetails ] = useState(false);

    function toggleCollectionDetailsVisiblity(){
        const newShowDetailsValue = showDetails === true ? false : true;
        setShowDetails(newShowDetailsValue);
    }

    function onProductLinkClick(e){
        e.preventDefault();
        props.onChangeUrl('/p/'+product.project_id,product.title)
    }

    function onUserNameClick(e){
        e.preventDefault();
        props.onChangeUrl('/u/'+product.username,product.username)
    }

    const imgUrl = GenerateImageUrl(product.image_small,80,80)

    let arrowClassName = "bi bi-chevron-compact-right";
    if (showDetails === true) arrowClassName = "bi bi-chevron-compact-down";

    let detailsRowDisplay;
    //if (showDetails === true){
        let scoreDisplay;
        if (product.laplace_score){
            scoreDisplay = (
                <ScoreCircleModule 
                    score={product.laplace_score}
                />
            )
        }
        detailsRowDisplay = (
            <React.Fragment>
                <div dangerouslySetInnerHTML={{__html:product.description}}></div>
                {scoreDisplay}
            </React.Fragment>
        )
    //}

    return (
            <div className="rowcollection">
                <div className="rownum font-bold">
                    {props.index + 1}
                </div>
                <div>
                    <figure>
                        <a href={'/p/'+product.project_id}>
                            <img className="downloadhistory-image" width="96" src={imgUrl}/>
                        </a>
                    </figure>
                </div>
                <div>
                    <h3 className="m0 font-bold">
                        <a href={'/p/'+product.project_id}>
                            {product.title}
                        </a>
                    </h3>
                    <span className="font-bold">
                        <UserToolTipModule 
                            showBy={true}
                            username={product.username} 
                            memberId={product.member_id} 
                            toolTipId={"pling-tool-tip-user-"+product.member_id}
                        />
                    </span>
                </div>
                <div className="tag-sub-menu-accordion accordion accordion-flush" id={"accordionFlushExample5" + product.project_id}>
                    <div className="accordion-item">
                        <h2 className="accordion-header" id={"flush-headingOne"+product.project_id}>
                            <button onClick={() => setShowDetails(showDetails === true ? false : true)} className={showDetails === true ? "accordion-button " : "accordion-button collapsed"} type="button" data-bs-toggle="collapse" data-bs-target={"#flush-collapseOne"+product.project_id } aria-expanded="false" aria-controls={"flush-collapseOne"+product.project_id }>
                                <span className="title-small-upper">More info</span>
                            </button>
                        </h2>
                        <div id={"flush-collapseOne"+product.project_id } className={"accordion-collapse collapse " + (showDetails === true ? "show" : "")} aria-labelledby={"flush-headingOne"+product.project_id} data-bs-parent={"#accordionFlushExample5" + product.project_id}>
                            <div className="accordion-body">
                                {detailsRowDisplay}
                            </div>
                        </div>
                    </div>
                </div>
                <div className="text-small mt3 mb0">
                    {product.cat_title}
                </div>
                <CollectionProductDownloadInstallButtons 
                    product={product}
                    files={props.file}
                />
            </div>
    )
}

function CollectionProductDownloadInstallButtons(props){

    const product = props.product;
    const files = props.files;
    
    let downloadButtonDisplay, installButtonDisplay;

    let downloadFileListDisplay;
    let installFileListDisplay;
    let downloadButtonCssClass = "btn dropdown-toggle pui-btn-smaller";

    if (files.length > 0){
        downloadButtonCssClass += " active btn-primary";
        downloadFileListDisplay = files.map((file,index) => (
            <FileListItem key={index} product={product} file={file} type={'download'} />
        ));
        downloadButtonDisplay = (
            <div className=" button-container-cell">
                <button id="project_btn_download" className={downloadButtonCssClass} type="button" data-toggle="dropdown">
                    Download
                    <span className="caret"></span>
                </button>
                <ul className={files.length > 0 ? "dropdown-menu" : "dropdown-menu hide"} id="dropdown_downloads">
                    {downloadFileListDisplay}
                </ul>
            </div>
        )

        const ocsCompatibleFiles = files.filter(file => file.ocs_compatible === "1" /*&& file.xdgType  && file.xdgType !== null*/);

        if (ocsCompatibleFiles.length > 0){
            installFileListDisplay = ocsCompatibleFiles.map((file,index) => (
                <FileListItem key={index} product={product} file={file} type={'install'} />
            ));

            installButtonDisplay = (
                <div className="button-container-cell">
                    <button id="project_btn_install" className="btn btn-primary active pui-btn-smaller dropdown-toggle" type="button" data-toggle="dropdown">
                        Install
                        <span className="caret"></span>
                    </button>
                    <ul className={files.length > 0 ? "dropdown-menu" : "dropdown-menu hide"} id="dropdown_installs">
                        {installFileListDisplay}
                    </ul>
                </div>
            )
        }   
    }

    return (
        <div>
            {installButtonDisplay}
            {downloadButtonDisplay}
        </div>
    )
}

function FileListItem(props){

    const file = props.file;
    const product = props.product;

    const [ showModal, setShowModal ] = useState(false);

    const fileExtension =  file.name.substring(file.name.lastIndexOf('.')+1);

    let maxFileNameLength = 10;
    if (fileExtension.length > 3) maxFileNameLength = 9 + 3 - fileExtension.length;

    let fileName = file.name.split('.').slice(0, -1).join('.');
    if (fileName.length > maxFileNameLength) fileName = fileName.substring(0,maxFileNameLength) + '...';
    fileName = fileName + file.name.substring(file.name.lastIndexOf('.')+1);

    const url = "/dl?"+
                "file_id="+file.id+
                "&file_type="+file.type+
                "&file_name="+file.name+
                "&file_size="+file.size+
                "&has_torrent="+(file.has_torrent ? file.has_torrent : "0")+
                "&project_id="+product.project_id+
                "&link_type="+props.type+
                "&is_external="+(file.is_external ? file.is_external : false)+
                "&external_link="+(file.external_link ? file.external_link : null);

    return (
        <li className={"file-"+props.type+"-list-item"} style={{fontSize:"12px"}}>
            <a style={{cursor:"pointer"}} onClick={e => setShowModal(true)} className={"dropdown-item open"+props.type+"file"}>
                <span className={"file-"+props.type+"-list-item-name"} id={props.type+"-link-filename"+file.id}>{fileName}</span>
                <span style={{float:"right"}}>&nbsp;{filesize(file.size, {exponent: 2})}&nbsp;</span>
            </a>
            <DownloadInstallModal
                isOpen={showModal}
                url={url}
                type={props.type}
                fileSize={filesize(file.size, {exponent: 2})}
                closeModal={e => setShowModal(false)}
                onRequestClose={e => setShowModal(false)}
            />
        </li>
    )
}

export default CollectionProductsList;