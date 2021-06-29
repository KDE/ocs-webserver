import React, {useEffect, useState } from 'react';
import CategoryBlocks from '../common/category-blocks';
import './style/app-image-hub.css';

function AppImageHubHomePage(props){
    return (
        <React.Fragment>
            <div className="container-wide aih-welcome">
                <Introduction
                    count={props.data.totalProjects}
                />
            </div>
            <CategoryBlocks/>
        </React.Fragment>
    )
}

class Introduction extends React.Component {
    render(){
  
    let siteTitle = "AppImageHub";
    let introductionText = (
        <React.Fragment>
        <p className="mt7 mb4 lead">This catalog has <span className="aih-underline">{this.props.count}</span> AppImages and counting.</p>
        <p>AppImages are self-contained apps which can simply be downloaded & run on any Linux distribution. For easy integration, download</p>
        <p>
            <a href="/p/1228228" className="pui-btn-aih">
                AppImage Launcher
            </a>
        </p>
        </React.Fragment>
    );
    let buttonsContainer = (
        <p>
            <a href="/browse" className="pui-btn">Browse all apps</a>
            <a href="https://chat.opendesktop.org/#/room/#appimagehub:chat.opendesktop.org" className="pui-btn">Join our chat #AppImageHub</a>
        </p>
    );

    if (window.page === "libreoffice"){
        siteTitle = "LibreOffice";
        introductionText = (
          <p>
            Extensions add new features to your LibreOffice or make the use of already existing ones easier.
            Currently there are {this.props.count} project(s) available.
          </p>
        );
        buttonsContainer = (
          <div className="actions green">
            <a href={window.baseUrl+"product/add"} className="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary">Add Extension</a>
            <a href={window.baseUrl+"browse/"} className="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary">Browse all Extensions</a>
          </div>
        );
    }
  
        
        return (
            <div  className="container-narrow text-center">
                <h1>Welcome to {siteTitle}</h1>
                {introductionText}
                {buttonsContainer}
            </div>
        )
    }
}

export default AppImageHubHomePage;