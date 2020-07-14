import React, { useState, useEffect } from 'react';

function RightSideBar(){

    const [ loading, setLoading ] = useState(true);
    const [ isStartpage, setIsStartpage ] = useState(is_startpage);

    useEffect(() => {
        console.log('init right sidebar');
        console.log(isStartpage);
    },[])

    /* RENDER */

    // download banner display
    const downloadBannerDisplay = (
        <div className="downloadDiv">
            <a href="https://www.pling.com/p/1175480/">
                <img src="/images/system/ocsstore-download-button.png" />    
            </a>
        </div>
    )

    // suppoter div display
    const supporterDivDisplay = (
        <div class="supportDiv">
            <a href="http://pling/support" className="btn btn-primary btn-lg active btn-block" role="button" aria-pressed="true">Thank you for your support!</a>
        </div>
    )

    // forum
    let forumModuleDisplay = <ForumModule/>

    return (
        <aside id="explore-sidebar2">
            {downloadBannerDisplay}
            {forumModuleDisplay}
        </aside>
    )
}

function ForumModule(props){
    console.log('forum module');
    return (
        <div className="module-container">
            <span className="newsTitle"> Forum </span>
            <div class="prod-widget-box right bgwhite " id="blogJson">
                <div class="commentstore">
                    <a href="https://forum.opendesktop.org/t/18544"><span class="title">Dsafdsa</span></a>
                    <div class="newsrow">
                        <span class="date">1 day ago</span>
                        <span class="newscomments">0 Replies</span>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default RightSideBar;