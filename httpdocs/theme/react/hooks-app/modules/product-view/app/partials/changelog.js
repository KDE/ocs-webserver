import TimeAgo from 'react-timeago';

function Changelog(props){

    const update = props.update
    let updateTextDisplay = update.text;
    if (update.text.indexOf('<br>') > -1){
        const updateTextArray = update.text.split('<br>');
        updateTextDisplay = updateTextArray.map((ut) => (
            <React.Fragment>
                <span dangerouslySetInnerHTML={{__html:ut}}></span>
                <br/>
            </React.Fragment>
        ))
    }    

    return (
        <React.Fragment>
            <h3 className="product-heading product-update-heading">
                {update.title}
                <span className="small light lightgrey product-update-date">
                    <TimeAgo date={update.created_at}></TimeAgo>
                </span>
            </h3>
            <p>{updateTextDisplay}</p>
        </React.Fragment>
    )
}

export default Changelog;