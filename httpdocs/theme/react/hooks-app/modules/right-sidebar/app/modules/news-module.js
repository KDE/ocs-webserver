import { useState, useEffect } from "react";
import { FormatDate } from '../helpers/right-sidebar-helpers';
import LoadingDot from '../../../common/loading-dot';

function NewsModule(props){

    let xhr;

    const [ loading, setLoading ] = useState(true);
    const [ newsItems, setNewsItems ] = useState();

    useEffect(() => {
        initNewsModule();
        return () => {
            if (xhr && xhr.abort) xhr.abort();
        }
    },[])

    function initNewsModule(){
        xhr = $.getJSON("/json/news", function (res) {
            if (res && res.status === "ok"){
                setNewsItems(res.posts);
                setLoading(false);
            }
        });
    }

    let newsItemsDisplay = <LoadingDot/>
    if (loading === false) {
        newsItemsDisplay = newsItems.map((nItem,index) => (
            <NewsModuleListItem 
                key={index}
                item={nItem}
            />
        ))
    }
    return (
        <React.Fragment>
            <h3 className="mt0 mb3 title-small-upper">
                <a href="#">News ›</a>
            </h3>
            <div className="link-primary-invert">
                {newsItemsDisplay}
            </div>
            <hr className="hr-dark"/>
        </React.Fragment>
    )
}

function NewsModuleListItem(props){
    const item = props.item;
    let commentTagDisplay = " Comments";
    if (item.comment_count === 1) commentTagDisplay = " Comment";
    return (
        <div className="aside-post">
            <p className="post-title">
                <a href={item.url} dangerouslySetInnerHTML={{__html:item.title}}></a>
            </p>
            <p className="post-meta">
                <span>{FormatDate(item.date)}</span>
                {item.comment_count}{commentTagDisplay}
           </p>
        </div>
    )
}

export default NewsModule;