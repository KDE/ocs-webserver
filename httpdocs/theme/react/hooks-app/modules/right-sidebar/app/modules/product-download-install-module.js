import { useState, useEffect, useRef } from 'react';
import filesize from 'filesize';
import DownloadInstallModal from '../../../common/download-install-modal';
import React from 'react';

function ProductDownloadInstallModule(props){

    const ref = useRef();
    const [ showDownloadMenu, setShowDownloadMenu ] = useState(false);
    useOnClickOutside(ref, () => setShowDownloadMenu(false));

    const refInstall = useRef();
    const [ showInstallMenu, setShowInstallMenu ] = useState(false);
    useOnClickOutside(refInstall, () => setShowInstallMenu(false));

    const product = props.product;
    const files = props.files;
    
    let downloadFileListDisplay;
    let installSectionDisplay;
    let downloadButtonCssClass = "btn dropdown-toggle pui-btn ";

    if (files.length > 0){

        downloadButtonCssClass += "primary active btn-primary ";
        downloadFileListDisplay = files.map((file,index) => (
            <FileListItem key={index} product={product} file={file} type={'download'} />
        ));
        
        const ocsCompatibleFiles = files.filter(file => file.isInstall === true);

        if (ocsCompatibleFiles.length > 0){

            const installFileListDisplay = ocsCompatibleFiles.map((file,index) => (
                <FileListItem key={index} product={product} file={file} type={'install'} />
            ));
    
            installSectionDisplay = (
                <div ref={refInstall} id="project_btn_grp_install" style={{position:"relative"}}>
                    <button onClick={() => setShowInstallMenu(showInstallMenu === true ? false : true)} id="project_btn_install" className="btn dropdown-toggle pui-btn primary" type="button" data-toggle="dropdown" aria-expanded="true">
                        Install
                    </button>
                    <ul style={{top:"43px"}} className={"dropdown-menu " + (showInstallMenu === true ? "show" : "")} id="dropdown_installs">
                        {installFileListDisplay}
                    </ul>
                    <p className="text-small">
                        * Works with  <a style={{marginRight:"3px"}} className="link-primary" href="https://www.opendesktop.org/p/1175480/" target="_NEW">pling-store</a> 
                        or <a style={{marginRight:"3px"}} className="link-primary" href="https://www.opendesktop.org/p/1136805/" target="_NEW">ocs-url</a> 
                    </p>
                </div>
            )
        }
    }

    return (
            <div className="prod-widget-box text-center">
                <div ref={ref}>
                        <button  onClick={() => setShowDownloadMenu(showDownloadMenu === true ? false : true)} id="project_btn_download" className={downloadButtonCssClass} type="button"  data-toggle="dropdown" aria-expanded="true">
                            Download
                        </button>
                        <ul style={{top:"43px"}} className={"dropdown-menu " + (showDownloadMenu === true ? "show" : "") + (files.length > 0 ? "" : "hide")} id="dropdown_downloads">
                            {downloadFileListDisplay}
                        </ul>
                </div>
                {installSectionDisplay}
            </div>
    )
}

function FileListItem(props){

    const file = props.file;
    const product = props.product;

    const [ showModal, setShowModal ] = useState(false);

    const fileExtension =  file.name.substring(file.name.lastIndexOf('.')+1);

    let maxFileNameLength = 22;
    if (fileExtension.length > 3) maxFileNameLength = 16 + 3 - fileExtension.length;

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

// Hook
function useOnClickOutside(ref, handler) {
    useEffect(
      () => {
        const listener = (event) => {
          // Do nothing if clicking ref's element or descendent elements
          if (!ref.current || ref.current.contains(event.target)) {
            return;
          }
          handler(event);
        };
        document.addEventListener("mousedown", listener);
        document.addEventListener("touchstart", listener);
        return () => {
          document.removeEventListener("mousedown", listener);
          document.removeEventListener("touchstart", listener);
        };
      },
      // Add ref and handler to effect dependencies
      // It's worth noting that because passed in handler is a new ...
      // ... function on every render that will cause this effect ...
      // ... callback/cleanup to run every render. It's not a big deal ...
      // ... but to optimize you can wrap handler in useCallback before ...
      // ... passing it into this hook.
      [ref, handler]
    );
  }

export default ProductDownloadInstallModule;