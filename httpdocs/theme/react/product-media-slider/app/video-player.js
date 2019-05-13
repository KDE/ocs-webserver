import React, { useState } from 'react';
import { Player } from 'video-react';

function VideoPlayerWrapper(props){

    const [ source, setSource ] = useState();
    
    React.useEffect(() => { convertStringToUrl(); }, [props.source])
    
    function convertStringToUrl(){
        let newSource = props.source.replace(/%2F/g,'/').replace(/%3A/g,':');
        setSource(newSource);
    }

    let videoPlayerDisplay;
    if (source){
        videoPlayerDisplay = (
            <Player
                height={props.height}
                width={props.width}
                playsInline
                src={source}
            />            
        )
    }
    return (
        <div className="react-player-container">
            {videoPlayerDisplay}
        </div>
    )
}

export default VideoPlayerWrapper;