import React, { useState } from 'react';
import ReactPlayer from 'react-player'

function VideoPlayerWrapper(props){

    const [ source, setSource ] = useState();
    console.log(source);
    
    React.useEffect(() => { convertStringToUrl(); }, [props.source])

    function convertStringToUrl(){
        let newSource = props.source.replace('%2F','/').replace('%3A',':');
        setSource(newSource);
    }

    let videoPlayerDisplay;
    if (source) videoPlayerDisplay = <ReactPlayer url={() => convertStringToUrl(source)} playing /> 
    return (
        <div className="react-player-container">
            {videoPlayerDisplay}
        </div>
    )
}

export default VideoPlayerWrapper;