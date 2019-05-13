import React, { useState, useRef } from 'react';
import { Player, ControlBar } from 'video-react';

function VideoPlayerWrapper(props){

    const playerEl = useRef(null)
    const [ source, setSource ] = useState();

    console.log(playerEl)
    
    React.useEffect(() => { convertStringToUrl(); }, [props.source])
    React.useEffect(() => { console.log(playerEl)},[playerEl])
    
    function convertStringToUrl(){
        let newSource = props.source.replace(/%2F/g,'/').replace(/%3A/g,':');
        setSource(newSource);
    }

    let videoPlayerDisplay;
    if (source){
        videoPlayerDisplay = (
            <Player
                ref={playerEl}
                fluid={false}
                height={props.height}
                width={props.width}
                playsInline
                src={source}>
                    <ControlBar autoHide={false} className="custom-video-player">
                        <a className="cinema-mode-button" onClick={props.onCinemaModeClick} order={8}>cinema</a>
                    </ControlBar>
            </Player>            
        )
    }

    return (
        <div className="react-player-container">
            {videoPlayerDisplay}
        </div>
    )
}

export default VideoPlayerWrapper;