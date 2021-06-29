import { useState, useEffect } from "react";
import DummyList from './dummy-list';

function GitProjectsModule(){

    let xhr = null;

    const [ loading, setLoading ] = useState(true);
    const [ gitItems, setGitItems ] = useState();
    
    useEffect(() => {
        initGitProjectsModule();
        return () => {
            if (xhr && xhr.abort) xhr.abort();
        }
    },[])

    function initGitProjectsModule(){
        xhr = $.ajax('/json/gitlabnewprojects').then(function (result) {
            setGitItems(result);
            setLoading(false);
        });
    }

    let gitProjectsDisplay = "LOADING...";
    if (loading === false){
        gitProjectsDisplay = gitItems.map((gItem,index) => (
            <GitProjectsModuleListItem
                key={index}
                item={gItem}
            />    
        ))
    }

    return (
        <React.Fragment>
            <h3 className="mt0 mb3 title-small-upper">
                Git Projects
            </h3>
            <div className="pui-coments-container">
                {gitProjectsDisplay}
            </div>
            <hr/>
        </React.Fragment>
    )
}

function GitProjectsModuleListItem(props){
    
    const item = props.item;
    const initGitUrlVal = "https://git.opendesktop." + ( window.location.hostname.endsWith('com') || window.location.hostname.endsWith('org')  ? 'org' : 'cc' );
    const [ gitUrl, setGitUrl ] = useState(initGitUrlVal);
    const [ userAvatarUrl, setUserAvatarUrl ] = useState('')

    useEffect(() => {
        const json_url = 'https://git.opendesktop.org/api/v4/users?username=' + item.namespace.name;
        $.ajax(json_url).then(function (result) {
            setUserAvatarUrl(result[0].avatar_url);
        });
    },[])

    let avatarDisplay;
    if (item.avatar_url){
        avatarDisplay = (
            <img className="avatar project-avatar s40 js-lazy-loaded" src={item.avatar_url} width="40" height="40"/>
        )
    } else {
        avatarDisplay = (
            <div className="avatar project-avatar s40 identicon bg2">{ item.namespace.name.substr(0,1) }</div>
        )
    }

    return (
        <div className="pui-comment">
            <a href={item.web_url} title="product page link">
            <div className="pui-comment-title">
                <p>{item.name}</p>
                <p>{avatarDisplay} </p>
            </div>
            <div className="pui-comment-body">
                <p>Use https://store.kde.org/p/1181039/ for showing all active windows. I probably won't add a search icon/textfield in the panel since pressing the menu button does the exact same thing.</p>
            </div>
            </a>
            <a  href={gitUrl+"/"+item.namespace.name} title="user profile link">
                <div className="pui-comment-author">
                    <figure>
                        <img id={"avatar_" + item.namespace.name + "_" + item.id} src={userAvatarUrl}/>    
                    </figure>
                    <p>{item.namespace.name}</p>
                    <p><span>1 hour ago</span></p>
                </div>
            </a>
        </div>
    )
}

export default GitProjectsModule;