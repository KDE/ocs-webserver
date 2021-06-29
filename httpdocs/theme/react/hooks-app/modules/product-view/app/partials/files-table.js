import { useEffect, useState } from 'react';
import CustomModal from '../../../common/modal';
import filesize from 'filesize';
import DownloadInstallModal from '../../../common/download-install-modal';

import { detect } from 'detect-browser';
import LoadingDot from '../../../common/loading-dot';
import LoadingSpinner from '../../../common/loading-spinners';
const browser = detect();

function FilesTable(props){

    const [ showArchived, setShowArchived ] = useState(false);

    function onSetShowArchived(val){
        setShowArchived(val);
    }

    return (
        <table className="table table-ocs-file">
            <FilesTableHeader 
                showArchived={showArchived} 
                {...props}
            />
            <FilesTableBody 
                showArchived={showArchived} 
                {...props}            
            />
            <FilesTableFooter
                showArchived={showArchived}
                {...props}
                onSetShowArchived={onSetShowArchived}
            />
        </table>
    )
}

function FilesTableHeader(props){

    const showArchived = props.showArchived;
    const [ showInstallInfoModal, setShowInstallInfoModal ] = useState(false);

    function checkIfGroupTagIsInFilesArray(tcgt){
        let a = false;

        props.files.forEach(function(file){
            let checkFile = true;
            if (file.active === "0"){
                checkFile = false;
                if (showArchived === true) checkFile = true;
            }
            if (checkFile === true){
                if (file.tag_groups && file.tag_groups.length > 0){
                    file.tag_groups.forEach(function(tg){
                        if (tg.group_display_name === tcgt){
                            a = true;
                        }
                    });
                }
            }
        });
        if (a == true) return true;
        else return false;
    }
    
    const tableColumns = [
        {label:'File (click to download)'},
        {label:'Version'},
        {label:'Description'},
        {
            label:'Packagetype',
            groupTag:true
        },
        {
            label:'Architecture',
            groupTag:true
        },
        {
            label:'Release Channel',
            groupTag:true
        },
        {
            label:'Downloads Old',
            isAdmin:true,
            style:{textAlign:'right',whiteSpace:'nowrap'}
        },
        {
            label:'Downloads',
            style:{textAlign:'right'}
        },
        {label:'Date'},
        {
            label:'Filesize',
            style:{textAlign:'right'}
        },
        {
            label:'DL',
            style:{textAlign:'right'}
        },
        {
            label:'OCS-install',
            isInstall:true,
            style:{
                textAlign:'right',
                whiteSpace:'nowrap'
            }
        },
        {
            label:'MD5SUM',
            style:{minWidth:"250px"}
        },
        {
            label:'Compatible',
            isAdmin:true
        }
    ];

    const tableHeaderDisplay = tableColumns.map((tc,index) => {
        
        let showColumn = true;
        if (tc.groupTag === true){
            showColumn = false;
            const groupTagIsInFilesArray = checkIfGroupTagIsInFilesArray(tc.label)
            if (groupTagIsInFilesArray === true) showColumn = true;
        } else if (tc.isAdmin === true){
            showColumn = false;
            if (props.isAdmin === true) showColumn = true;
        }

        if (showColumn === true){

            let titleDisplay;
            if (tc.isAdmin === true && props.isAdmin === true) titleDisplay = <i>{tc.label}</i>
            else if (tc.isInstall){
                titleDisplay = (
                    <React.Fragment>
                        {tc.label}
                        <a onClick={() => setShowInstallInfoModal(true)}>
                            <i style={{cursor:"pointer",color:"#5551FF",marginLeft:"2px",fontSize:"16px"}} className="bi bi-patch-question-fill" aria-hidden="true"></i>
                        </a>
                        <CustomModal
                            isOpen={showInstallInfoModal}
                            closeModal={() => setShowInstallInfoModal(false)}
                            header={"Installation Instructions"}
                            onRequestClose={() => setShowInstallInfoModal(false)}>
                            <InstallInfoModal/>
                        </CustomModal>
                    </React.Fragment>
                )
            } else titleDisplay = tc.label
            return ( <th style={tc.style} key={index}>{titleDisplay}</th> )
        }
    });

    return (
        <thead>
            <tr>{tableHeaderDisplay}</tr>
        </thead>
    )
}

function FilesTableBody(props){
    
    const showArchived = props.showArchived;

    function sortFilesByDate(a,b){
        const dateA = a.created_timestamp;
        const dateB = b.created_timestamp;
        const intDateA =  new Date(dateA).getTime();
        const intDateB =  new Date(dateB).getTime();
        let comparison = 0;
        if (intDateA > intDateB) comparison = -1
        else if (intDateB < intDateA) comparison = 1;
        return comparison;
    }

    let files = props.files.sort(sortFilesByDate);
    if (showArchived === false) files = props.files.filter(file => file.active === "1").sort(sortFilesByDate);
    if (browser.name === "firefox") files.reverse();

    const tableBodyDisplay = files.map((file,index) => {
        const displayFile = showArchived === true ? true : file.active === "1" ? true : false;
        if (displayFile){
            const trCssClass = file.active === "1" ? "activerows" : "archived activerows";
            
            const fileDownloadUrl = "/dl?"+
                                    "file_id="+file.id+
                                    "&file_type="+file.type+
                                    "&file_name="+file.name+
                                    "&file_size="+file.size+
                                    "&has_torrent="+(file.has_torrent ? file.has_torrent : "0")+
                                    "&project_id="+props.product.project_id+
                                    "&link_type=download&is_external="+(file.is_external ? file.is_external : false)+
                                    "&external_link="+(file.external_link ? file.external_link : null);

            const fileInstallUrl =  "/dl?file_id="+file.id+
                                    "&file_name="+file.name+
                                    "&file_type="+file.type+
                                    "&file_size="+file.size+
                                    "&has_torrent="+file.has_torrent+
                                    "&project_id="+props.product.project_id+
                                    "&link_type=install&is_external="+(file.is_external ? file.is_external : false) +
                                    "&external_link="+(file.external_link ? file.external_link : null);

            let packageTypeDisplay, architectureDisplay, releaseChannelDisplay;
            if (file.tag_groups && file.tag_groups.length > 0){
                file.tag_groups.forEach(function(tg){
                    if (tg.group_display_name === "Packagetype"){
                        const packageTypes = tg.selected_tags.map((st,index) => (
                            <FilesTableRowPackageTypeModule 
                                index={index}
                                tag={st}
                            />
                        ))
                        packageTypeDisplay = <td>{packageTypes}</td>
                    } else if (tg.group_display_name === "Architecture"){
                        const archiTypes = tg.selected_tags.map((st,index) => ( <span key={index}>{st.tag_fullname}</span> ))
                        architectureDisplay = <td>{archiTypes}</td>                        
                    } else if (tg.group_display_name === "Release Channel"){
                        const releaseChannels = tg.selected_tags.map((st,index) => ( <span key={index}>{st.tag_fullname}</span> ))
                        releaseChannelDisplay = <td>{releaseChannels}</td>
                    }
                });
            }

            let downloadOldDisplay, ocsCompatibleDisplay;
            if (props.isAdmin === true){
                downloadOldDisplay = <td style={{textAlign:"right"}}><span id={"download_counter_"+file.id}>{file.downloaded_count}</span></td>
                if (file.active === "1"){
                    ocsCompatibleDisplay = <input readOnly type="checkbox" id={"data-checkbox-compatible-"+file.id} checked={file.ocs_compatible === "1" ? "checked" : ""} />                }
            }
            
            let downloadButtonDisplay, installButtonDisplay;
            if (file.active === "1"){
                downloadButtonDisplay = <FilesTableDownloadInstallButton fileSize={filesize(file.size, {exponent: 2})} type="download" url={fileDownloadUrl}/>
                if (file.isInstall === true){
                    installButtonDisplay = <FilesTableDownloadInstallButton fileSize={filesize(file.size, {exponent: 2})} type="install" url={fileInstallUrl}/>
                }
            }
                            
            let filesTableMd5SumCellDisplay;
            if (file.md5sum !== null){
                filesTableMd5SumCellDisplay = (
                    <FilesTableMD5SUMCell
                        md5sum={file.md5sum}
                    />
                )
            }

            let fileDownloadTextLinkDisplay = file.name
            if (file.active === "1") fileDownloadTextLinkDisplay = <FilesTableDownloadInstallButton fileSize={filesize(file.size, {exponent: 2})} type="text" name={file.name} url={fileDownloadUrl} />

            return (
                <tr key={index} data-ppload-file-id={file.id} className={trCssClass}>
                    <td>{fileDownloadTextLinkDisplay}</td>
                    <td>{file.version}</td>
                    <td>{file.description}</td>
                    {packageTypeDisplay}
                    {architectureDisplay}
                    {releaseChannelDisplay}
                    {downloadOldDisplay}
                    <td style={{textAlign:"right"}}>{file.downloaded_count_uk}</td>
                    <td style={{whiteSpace:"nowrap"}}>{file.updated_timestamp ? file.updated_timestamp.split(' ')[0] : file.created_timestamp.split(' ')[0] }</td>
                    <td style={{textAlign:"right",whiteSpace:"nowrap"}}>{filesize(file.size, {exponent: 2})}</td>
                    <td style={{textAlign:"right"}}>
                        {downloadButtonDisplay}
                    </td>
                    <td style={{textAlign:"right"}}>
                        {installButtonDisplay}  
                    </td>
                    <td>{filesTableMd5SumCellDisplay}</td>
                    <td style={{textAlign:"center"}}>{ocsCompatibleDisplay}</td>
                </tr>
            )
        }
    });

    return (
        <tbody>
            {tableBodyDisplay}
        </tbody>
    )
}

function FilesTableDownloadInstallButton(props){

    const [ showModal, setShowModal ] = useState(false);

    let cssClass = "opendownloadfile btn btn-native btn-min-width";
    let btnContentDisplay = <img src="/images/system/download.svg" alt="download" style={{width:"20px",height:"20px"}}/>
    if (props.type === "install") btnContentDisplay = "Install"
    else if (props.type === "text"){ 
        cssClass = "";
        btnContentDisplay = props.name
    }
    
    return (
        <React.Fragment>
            <a onClick={e => setShowModal(true)} style={{cursor:"pointer",whiteSpace:"nowrap"}} className={cssClass}>{btnContentDisplay}</a>
            <DownloadInstallModal
                isOpen={showModal}
                url={props.url}
                type={props.type}
                fileSize={props.fileSize}
                closeModal={e => setShowModal(false)}
                onRequestClose={e => setShowModal(false)}
            />
        </React.Fragment>

    )
}

function FilesTableMD5SUMCell(props){

    const [ isHover, setIsHover ] = useState(false);

    let md5SumDisplay;
    if (isHover === true){
        md5SumDisplay = props.md5sum;
    } else {
        md5SumDisplay = props.md5sum.slice(0,4) + "..." + props.md5sum.slice(props.md5sum.length - 5, props.md5sum.length - 1)
    }

    return (
        <React.Fragment>
            <span onMouseEnter={e => setIsHover(true)} onMouseLeave={e => setIsHover(false)}>
                {md5SumDisplay}
            </span>
        </React.Fragment>
    )
}

function FilesTableRowPackageTypeModule(props){
    const [ showPackageInfoModal, setShowPackageInfoModal ] = useState(false);
    const tag = props.tag;

    let moduleBodyDisplay;
    switch (tag.tag_fullname) {
        default:
            moduleBodyDisplay = (
                <div className="info" style={{paddingBottom:"15px"}}>
                    For easy appimage use, install appimage launcher :
                    <p><a style={{ color:" #0d6efd", textDecoration: "underline"}} target="_blank" href="https://www.opendesktop.org/p/1228228">www.opendesktop.org/p/1228228</a></p>
                    <p>More info: <br/><a style={{ color:" #0d6efd", textDecoration: "underline"}} target="_blank" href="https://www.linuxuprising.com/2018/04/easily-run-and-integrate-appimage-files.html">www.linuxuprising.com/2018/04/easily-run-and-integrate-appimage-files.html</a>  
                    </p>
                </div>
            )
            break;
    }

    const iconStyle = {
        cursor: "pointer",
        marginLeft: "5px",
        display: "inline-block",
        position: "absolute"
    }

    return (
        <div style={{position:"relative"}}>
            <span>{tag.tag_fullname}</span>
            <i style={iconStyle} onClick={(e) => setShowPackageInfoModal(true)} className="bi bi-patch-question-fill"></i>
            <CustomModal
                isOpen={showPackageInfoModal}
                header={tag.tag_fullname + " info"}
                closeModal={(e) => setShowPackageInfoModal(false)}
                onRequestClose={(e) => setShowPackageInfoModal(false)}>
                {moduleBodyDisplay}
            </CustomModal>
            <br/>
        </div>
    )
}

function FilesTableFooter(props){

    const showArchived = props.showArchived;
    
    function toggleShowArchived(){
        let newShowArchivedValue = showArchived === true ? false : true;
        props.onSetShowArchived(newShowArchivedValue);
    }

    // FOOTER

    let archivedCount = 0, 
        totleFilesSize = 0, 
        downloadsOldCount = 0, 
        downloadsCount = 0,
        packageTypePlaceholder,
        architecturePlaceholder,
        releaseChannelsPlaceholder;

    props.files.forEach(function(file){
        totleFilesSize += parseInt(file.size);
        downloadsOldCount += parseInt(file.downloaded_count);
        downloadsCount += file.downloaded_count_uk;
        if (file.active !== "1") archivedCount += 1;
        if (file.tag_groups && file.tag_groups.length > 0){
            file.tag_groups.forEach(function(tg){
                if (tg.group_display_name === "Packagetype") packageTypePlaceholder = <th></th>
                if (tg.group_display_name === "Architecture") architecturePlaceholder = <th></th>
                if (tg.group_display_name === "Release Channel") releaseChannelsPlaceholder = <th></th>
            });
        }
    });

    let colSpanCount = "3", downloadsOldDisplay;
    if (props.isAdmin === true){
        colSpanCount = "3"
        downloadsOldDisplay = <th style={{textAlign:"right"}}>{downloadsOldCount ? downloadsOldCount : 0}</th>
    }

    return (
        <tfoot>
            <tr>
                <th colSpan={colSpanCount}>{props.countFiles} files ( <a style={{cursor:"pointer"}} onClick={toggleShowArchived}>{archivedCount} archived</a> )</th>
                {packageTypePlaceholder}
                {architecturePlaceholder}
                {releaseChannelsPlaceholder}
                {downloadsOldDisplay}
                <th style={{textAlign:"right"}}>{downloadsCount}</th>
                <th></th>
                <th style={{textAlign:"right",whiteSpace:"nowrap"}}>{filesize(totleFilesSize,{exponent:2})}</th>
                <th colSpan="4"></th>
            </tr>
        </tfoot>
    )
}

function InstallInfoModal(props){

    const [ info, setInfo ] = useState(null);

    useEffect(() => {
        getInstallInfo();
    },[])

    function getInstallInfo(){
        $.ajax({url:window.location.href + "/loadinstallinstruction"}).done(function(res){
            setInfo(res.data);
        });
    }

    let infoDisplay = <div style={{width:"100%",textAlign:"center"}}><LoadingSpinner type={"ripples"}/></div>
    if (info !== null) infoDisplay = <div className="info" dangerouslySetInnerHTML={{__html:info}}></div>

    return (
        <div id="install-info" style={{height:(info !== null ? "85vh" : "85px")}}>
            {infoDisplay}
        </div>
    )
}

export default FilesTable;