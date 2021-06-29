import React, { useEffect, useState } from "react";
import LoadingDot from './loading-dot';
import './style/tags.css';

function TagsModule(props){

    let initTagsValue = [];
    if (props.tags) initTagsValue = props.tags.split(',');
    const [ tags, setTags ] = useState(initTagsValue)
    const [ showTagInterface, setShowTagInterface ] = useState(false);
    const [ tagText, setTagText ] = useState('');
    const [ loadingSuggestions, setLoadingSuggestions ] = useState(false);
    const [ suggestions, setSuggestions ] = useState([]);
    const [ error, setError ] = useState('');
    const [ isSaving, setIsSaving ] = useState(false);
    const [ tagIsSaved, setTagIsSaved ] = useState(false);

    function toggleTagsMenuVisibility(){
        let newShowTagInterfaceValue = true;
        if (showTagInterface === true) newShowTagInterfaceValue = false;
        setShowTagInterface(newShowTagInterfaceValue);
    }

    function updateTagText(e) {
        let key = e.keyCode || e.charCode;
        if (key !== 13 || key !== 32) {
            if (loadingSuggestions !== true) setTagText(e.target.value.trim()); 
        }
    }

    function onKeyDown(e){
        let key = e.keyCode || e.charCode;
        if (key === 32 || key === 13){
            const text = e.target.value;
            if (text.length >= 3) addTag(text)
        }
    }


    useEffect(() => {
        if (tagText.length > 2 && loadingSuggestions !== true){
            setLoadingSuggestions(true);
            getSuggestions(tagText); 
        }
        else if (tagText < 3){
            setLoadingSuggestions(false)
        }
    },[tagText])

    function getSuggestions(value) {
        if (loadingSuggestions !== true){
            const url = '/tag/filter?term=' + value + '&_type=query&q=' + value;
            $.ajax({ url:url, dataType: 'json', type: "GET" }).done(function(res){
                let tags = [{text:value}];
                if (res.data && res.data.tags.length > 0) tags = res.data.tags;
                setSuggestions(tags);
                setLoadingSuggestions(false);
            });
        }
    }      
    
    function addTag(suggestion){

        const newTags = [ ...tags, suggestion ]
        setTags(newTags);
        setTagText('');
        setSuggestions([]);
        setIsSaving(true)

        $.post( "/tag/add", { p: props.product.project_id, t: suggestion }).done(function( data ) {
            setIsSaving(false)
            if (data.status=='error'){
                setError(data.message);
            }
            else{
                setTagIsSaved(true);
            }
        });      
    }

    function removeTag(t){
        
        const newTags = []
        tags.forEach(function(tag,index){
            if (tag !== t) newTags.push(tag);
        })
        setTags(newTags);
        setIsSaving(true)

        $.post( "/tag/del", { p: props.product.project_id, t: t }).done(function( data ) {
            setIsSaving(false)
            if (data.status=='error') setError(data.message);
            else {
                setTagIsSaved(true);
            }
        });
    }

    useEffect(() => {
        setTimeout(() => { setTagIsSaved(false); }, 2000);
    },[tagIsSaved])

    let manageTagsDisplay;
    if (props.user && props.user.member_id === parseInt(props.product.member_id)){
        let buttonTextDisplay = "Manage tags";
        if (showTagInterface === true) buttonTextDisplay = "Done";
        manageTagsDisplay = (
            <div className="manage-tags-container" style={{float:"right"}}>
                <button style={{padding:"5px 10px", fontSize:"12px", color:"white", textDecoration:"none"}} onClick={toggleTagsMenuVisibility} className="btn-link topic-tags-btn">{buttonTextDisplay}</button>
            </div>
        )
    }

    let tagsContainerCss = "tags-container"
    let tagsDisplay =  tags.map((tag,index) => (
        <a key={index} rel="nofollow" href={"/find?search="+tag+"&f=tags"}>
        <span className="pui-pill tag"> {tag} </span>   
        </a>
    ))

    if (showTagInterface === true){
        
        tagsContainerCss += " edit-mode";
        let suggestionsContainerDisplay;

        if (tagText.length < 3){
            suggestionsContainerDisplay = <div style={{padding:"5px"}}>{"Please enter " + ( 3 - tagText.length) + " or more characters"}</div>
        } else {
            if (loadingSuggestions){
                suggestionsContainerDisplay = (
                    <div className="loading-suggestions-container">
                        <span className="glyphicon glyphicon-refresh spinning"></span> 
                        <span>Searching...</span>
                    </div>
                )
            }
            else if (error.length > 0) suggestionsContainerDisplay = <span className="error" style={{ color: "red" }}>{error}</span>
            else {
                suggestionsContainerDisplay = suggestions.map((suggestion,index) => (
                    <span style={{cursor:"pointer"}} className="pui-pill tag" key={index}>
                        <a onClick={e => addTag(suggestion.text)}>{suggestion.text}</a>
                    </span>
                ))
            }
        }

        let tagContainerStyle = {
            display: "block",
            left: "100%",
            marginLeft: "-200px",
            top:"45px"
        }

        let tagIsSavedDisplay;
        if (tagIsSaved === true) {
            tagIsSavedDisplay = (
                <div className="tag-is-saved-container success">
                    <i className="bi bi-file-check"></i> Saved
                </div>
            )
        }
        
        let tagIsSavingDisplay;
        if (isSaving === true ){
            tagIsSavingDisplay = (
                <div className="tag-is-saved-container success">
                    <LoadingDot/> Saving
                </div>
            )
        }


        tagsDisplay = (
            <div style={tagContainerStyle} className="manage-tags-menu-container pui-popup-body">
                <ul style={{padding:"0",margin:"0",listStyleType:"none"}}>
                    <li>
                    {
                        tags.map((tag,index) => (
                            <span className="pui-pill tag" key={index} title={tag}>
                                <span style={{cursor:"pointer",marginRight:"3px"}} className="remove-x" role="presentation" onClick={e => removeTag(tag)}>
                                    <i className="bi bi-x-circle"></i>
                                </span>
                                {tag}
                            </span>
                        ))
                    }
                    </li>
                    <li>
                        {tagIsSavingDisplay}
                        {tagIsSavedDisplay}
                    </li>
                    <li className="input-container">
                        <input style={{marginBottom:"5px"}} placeholder="Enter text..." type="text" value={tagText} onChange={e => updateTagText(e)} onKeyDown={e => onKeyDown(e)}/>
                    </li>
                </ul>
                <div className="suggestions-container" style={{fontSize:"12px"}}>
                    {suggestionsContainerDisplay}
                </div>
            </div>
        )
    }

    return (
        <React.Fragment>
            {manageTagsDisplay}
            {tagsDisplay}
        </React.Fragment>
    )

}

export default TagsModule;