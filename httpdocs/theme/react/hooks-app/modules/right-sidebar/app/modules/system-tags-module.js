function SystemTagsModule(props){

    let systemTagsDisplay;
    if (props.tags){
        systemTagsDisplay = props.tags.map((st,index) => (
            <a href={"/find?search="+st.tag_name+"&f=tags"} className="tag-element">
                <span className="pui-pill tag">
                    {st.tag_name}
                </span>
            </a>
        ));
    }

    return (
        <div className="prod-widget-box right details">
            <span className="section-title mt5">System Tags</span>
            <div>
                {systemTagsDisplay}
            </div>
        </div>
    )
}

export default SystemTagsModule;