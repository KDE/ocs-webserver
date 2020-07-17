import React, { useState, useEffect } from 'react';

function RightSideBar(){

    const [ loading, setLoading ] = useState(true);
    const [ isStartpage, setIsStartpage ] = useState();

    useEffect(() => {
        console.log('init right sidebar');
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
            <a href="http://pling/support" className="btn btn-primary btn-lg active btn-block" role="button" aria-pressed="true">
                Thank you for your support!
            </a>
        </div>
    )

    // forum
    let forumModuleDisplay = <ForumModule/>

    // comments 
    let commentsModuleDisplay = <CommentsModule/>

    return (
        <aside id="explore-sidebar2">
            {downloadBannerDisplay}
            {supporterDivDisplay}
            {forumModuleDisplay}
            {commentsModuleDisplay}
        </aside>
    )
}

function ForumModule(props){

    const forumItems = [{
        title:"Dsafdsa",
        created_at:"1 day ago",
        link:"https://forum.opendesktop.org/t/18544",
        num_replies:"0"
    }]

    const items = forumItems.map((fItem, index) => (
        <div class="commentstore">
            <a href={fItem.link}>
                <span class="title">{fItem.title}</span>
            </a>
            <div class="newsrow">
                <span class="date">{fItem.created_at}</span>
                <span class="newscomments">{fItem.num_replies}</span>
            </div>
        </div>
    ));

    return (
        <div className="module-container row sidebar-right-info" id="forum-module">
            <span className="newsTitle"> Forum </span>
            <div class="prod-widget-box right bgwhite " id="blogJson">
                {items}
            </div>
        </div>
    )
}

function CommentsModule(props){
    return (
        <div className="moduel-container row sidebar-right-info" id="comments-module">
            <span className="newsTitle"> Comments </span>
        </div>
    )
}

export default RightSideBar;