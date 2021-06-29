function DummyList(props){
    const items = props.items.map((item,index) => (
        <DummyListItem type={props.type} key={index}/>
    ))
    return (
        <React.Fragment>
            {items}
        </React.Fragment>
    )
}

function DummyListItem(props){

    

    let titleDisplay;
    if (props.type === "forum"){
        titleDisplay = (
            <a className=" dummy-fill dummy-fill-to-white" href=""style={{width:"45%",height:"12px",float:"left",marginBottom:"3px"}}>
                <span className="title "></span>
            </a>
        )
    } else if (props.type === "news"){
        titleDisplay = (
            <React.Fragment>
            <a className=" dummy-fill dummy-fill-to-white" href=""style={{width:"850%",height:"12px",float:"left",marginBottom:"3px"}}>
                <span className="title "></span>
            </a>
            <a className=" dummy-fill dummy-fill-to-white" href=""style={{width:"55%",height:"12px",float:"left",marginBottom:"3px"}}>
                <span className="title "></span>
            </a>
            </React.Fragment>
        )       
    }

    return (
        <div className="commentstore" style={{padding:"5px 0"}}>
            {titleDisplay}
            <div className="newsrow" style={{display:"block",float:"left"}}>
                <span className="date dummy-fill dummy-fill-to-white" style={{width:"60px",height:"8px", float:"left"}}></span>
                <span className="date dummy-fill dummy-fill-to-white" style={{width:"50px",height:"8px", float:"right"}}></span>
            </div>
        </div>
    )
}

export default DummyList;