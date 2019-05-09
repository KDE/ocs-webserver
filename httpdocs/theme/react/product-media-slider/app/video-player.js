import React, { useState } from 'react';
import ReactPlayer from 'react-player'

function VideoPlayerWrapper(props){
    const [ source, setSource ] = useState();
    React.useEffect(() => { convertStringToUrl(); }, [props.source])
    function convertStringToUrl(){
        let newSource = props.source.replace(/%2F/g,'/').replace(/%3A/g,':');
        setSource(newSource);
    }
    let videoPlayerDisplay;
    if (source) videoPlayerDisplay = <ReactPlayer url={source} playing={false} controls={true} /> 
    return (
        <div className="react-player-container">
            {videoPlayerDisplay}
        </div>
    )
}

export default VideoPlayerWrapper;