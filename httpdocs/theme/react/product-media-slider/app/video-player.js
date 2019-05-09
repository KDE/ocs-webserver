import React, { useState } from 'react';
import ReactPlayer from 'react-player'

function VideoPlayerWrapper(props){
    console.log(props);
    return (
        <ReactPlayer url={props.source} playing />
    )
}

export default VideoPlayerWrapper;