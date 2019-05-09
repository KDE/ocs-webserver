import React, { useState } from 'react';
import ReactPlayer from 'react-player'

function VideoPlayerWrapper(props){
    function convertStringToUrl(string){
        let newSource = string.replace('%2F','/').replace('%3A',':');
        return newSource;
    }
    return (
        <ReactPlayer url={() => convertStringToUrl(props.source)} playing />
    )
}

export default VideoPlayerWrapper;