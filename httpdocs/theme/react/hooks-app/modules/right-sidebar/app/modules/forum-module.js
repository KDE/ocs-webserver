import React, { useEffect, useState } from 'react';
import TimeAgo from 'react-timeago';
import LoadingDot from '../../../common/loading-dot';
import DummyList from './dummy-list';

function ForumModule(props){

    const [ loading, setLoading ] = useState(true);
    const [ forumItems, setForumItems ] = useState();

    useEffect(() => {
        const xhr = $.ajax({url:'/json/forum'}).done(function (result) {
            setForumItems(result.topic_list.topics.slice(0,3))
            setLoading(false);
        });
        return () => { xhr.abort(); }
    },[])
    
    let itemsDisplay = <LoadingDot/>
    if (loading === false){
        itemsDisplay = forumItems.map((fItem, index) => (
            <ForumModuleListItem 
                key={index}
                item={fItem}
            />
        ));
    }

    return (
        <React.Fragment>
            <h3 className="mt0 mb3 title-small-upper">
                <a href="#">Forum ›</a>
            </h3>
            <div className="link-primary-invert">
                {itemsDisplay}
            </div>
            <hr className="hr-dark"/>
        </React.Fragment>
    )
}

function ForumModuleListItem(props){

    const item = props.item;
    let replyTagDisplay = " Replies";
    if (item.reply_count === 1) replyTagDisplay = " Reply";

    return (
        <div className="aside-post">
            <p className="post-title">
                <a href={"https://forum.opendesktop.org/t/"+item.id}>
                    {item.title}
                </a>
            </p>
            <div className="post-meta">
                <span><TimeAgo date={item.created_at}></TimeAgo></span>
                {item.reply_count} 
                {replyTagDisplay}
            </div>
        </div>
    )
}

export default ForumModule;