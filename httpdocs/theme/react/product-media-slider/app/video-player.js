import React, { useState } from 'react';
import ReactPlayer from 'react-player'

function VideoPlayerWrapper(props){
    return (
        <ReactPlayer url={props.source}playing />
    )
}

export default VideoPlayerWrapper;