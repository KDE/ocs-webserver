import React, { useState, useEffect, useRef, useContext } from 'react';
import { AppContext } from '../../context/context-provider';
import Slider from 'rc-slider'; 
import { GenerateImageUrl, usePrevious } from '../../../modules/common/common-helpers';

import './style/app-audio-player.css';

// Audio Player
function AudioPlayer(props){

    const { appState, appDispatch } = useContext(AppContext)
    const [ hidePlayer, setHidePlayer ] = useState(true);
    const mediaPlayer = appState.mediaPlayer;

    useEffect(() => {
        if (appState.mediaPlayer && appState.mediaPlayer.isPlaying === true){
            const playerElement = document.getElementById("app-audio-player").getElementsByTagName('audio');
            playerElement[0].play();
            setHidePlayer(false);
        } else {
            /*setTimeout(() => {
                setHidePlayer(true);
            }, 5000);*/
        }
    },[appState.mediaPlayer])

    function onPlayerTimeUpdate(e){

        const playerElement = e.target;
        const newCurrentTrackTime = millisToMinutesAndSeconds(playerElement.currentTime);
        let newcurrentTrackDuration = playerElement.duration;
        if (isNaN(newcurrentTrackDuration)){ newcurrentTrackDuration = 0; }
        newcurrentTrackDuration = millisToMinutesAndSeconds(newcurrentTrackDuration);
        const newCurrentTrackProgress = (playerElement.currentTime / playerElement.duration) * 100;
        appDispatch({type:'SET_TRACK_DURATION',value:newcurrentTrackDuration});
        appDispatch({type:'SET_TRACK_PROGRESS',value:newCurrentTrackProgress});
        appDispatch({type:'SET_TRACK_TIME',value:newCurrentTrackTime});
        appDispatch({type:'SET_TRACK_TIME_SECONDS',value:playerElement.duration});
    }

    function millisToMinutesAndSeconds(time) {
        let minutes = Math.floor(time / 60);
        let seconds = time - minutes * 60;
        seconds = Math.floor(seconds);
        if (minutes < 10) minutes = "0" + minutes;
        if (seconds < 10) seconds = "0" +  seconds;
        const timestamp = minutes + ":" + seconds;
        return timestamp;
    }

    function onChangeVolumeSliderPosition(e){
        const newTrackProgress = e;
        const newCurrentTrackTime = (appState.mediaPlayer.trackTimeSeconds / 100) * newTrackProgress;
        const playerElement = document.getElementById("app-audio-player").getElementsByTagName('audio');
        playerElement[0].currentTime = newCurrentTrackTime;
    }

    function onPlayClick(){
        const playerElement = document.getElementById("app-audio-player").getElementsByTagName('audio');
        playerElement[0].play();
        appDispatch({type:'SET_IS_PLAYING',value:true});
        appDispatch({type:'SET_IS_PAUSED',value:false});
    }

    function onPauseClick(){
        const playerElement = document.getElementById("app-audio-player").getElementsByTagName('audio');
        playerElement[0].pause();
        appDispatch({type:'SET_IS_PLAYING',value:false});
        appDispatch({type:'SET_IS_PAUSED',value:true});
        // onReportAudioStop(props.items[playIndex].musicSrc)
    }

    function closeAudioPlayer(){
        onPauseClick();
        setHidePlayer(true);
    }

    function onTrackNameClick(e){
        e.preventDefault();
        props.onChangeUrl("/p/"+mediaPlayer.productId,mediaPlayer.productTitle)
    }

    function onUserNameClick(e){
        e.preventDefault();
        props.onChangeUrl("/u/"+mediaPlayer.username,mediaPlayer.username)
    }

    const pauseButtonDisplay = (
        <svg viewBox="0 0 24 24" className="icon-pause" xmlns="http://www.w3.org/2000/svg">
            <path d="M7.25 4C6.56 4 6 4.56 6 5.25v13.5a1.25 1.25 0 102.5 0V5.25C8.5 4.56 7.94 4 7.25 4zm9.5 0c.69 0 1.25.56 1.25 1.25v13.5a1.25 1.25 0 11-2.5 0V5.25c0-.69.56-1.25 1.25-1.25z" clipRule="evenodd" fillRule="evenodd"></path>
        </svg>
    )

    const closePlayerButtonDisplay = (
        <svg className="icon-close" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
            <path d="m11.2928932 3.29289322c.3905243-.39052429 1.0236893-.39052429 1.4142136 0s.3905243 1.02368927 0 1.41421356l-8.00000002 8.00000002c-.39052429.3905243-1.02368927.3905243-1.41421356 0s-.39052429-1.0236893 0-1.4142136zm-7.99999998 1.41421356c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l8.00000002 7.99999998c.3905243.3905243.3905243 1.0236893 0 1.4142136s-1.0236893.3905243-1.4142136 0z"></path>
        </svg>
    )

    // const productInfo = JSON.parse(props.productInfo);


    let audioPlayerCssClass = "";
    if (hidePlayer === true) audioPlayerCssClass = "hide-player";


    
    let audioVolume , src, trackTime,trackProgress,trackDuration, trackTitle, username, imageDisplay,buttonDisplay;
    if (mediaPlayer){
        src = mediaPlayer.src;
        audioVolume = mediaPlayer.audioVolume;
        trackTime = mediaPlayer.trackTime;
        trackProgress = mediaPlayer.trackProgress;
        trackDuration = mediaPlayer.trackDuration;
        trackTitle = mediaPlayer.productTitle;
        username = mediaPlayer.username;

        if (mediaPlayer.productImage){
            const imgUrl = GenerateImageUrl(mediaPlayer.productImage);
            imageDisplay = <img src={imgUrl}/>
        }
        if (mediaPlayer.isPlaying === true){
            buttonDisplay = <a className="pause-button" onClick={onPauseClick}>{pauseButtonDisplay}</a>
        } else {
            buttonDisplay = <a className="play-button" onClick={onPlayClick}>PLAY</a>   
        }
    }

    return (
        <div className="app-audio-player-wrapper">
            <div id="app-audio-player" className={audioPlayerCssClass}>
                {buttonDisplay}
                <div className="track-player">
                    <audio 
                        volume={audioVolume} 
                        onTimeUpdate={(e) => onPlayerTimeUpdate(e)}  
                        onLoadedMetadata={(e) => onPlayerTimeUpdate(e)}
                        src={src}>
                    </audio>
                    <span className="player-time time-elapsed">{trackTime}</span>
                    <div className="slider-wrapper">
                        <Slider 
                            min={0}
                            max={100}
                            value={trackProgress}           
                            onChange={(e) => onChangeVolumeSliderPosition(e)}
                            railStyle={{"backgroundColor":"#babec2","height":"2px"}}
                            trackStyle={{"backgroundColor":"#2d3339","height":"2px"}}
                            handleStyle={{"display":"none"}}
                        />
                    </div>
                    <span className="player-time time-total">{trackDuration}</span>
                </div>
                <div className="track-info">
                    <div className="info-wrapper">
                        {imageDisplay}
                        <a style={{display:"block",color:"black",fontWeight: "bold"}} onClick={e => onTrackNameClick(e)} href={"/p/"+( mediaPlayer ? mediaPlayer.productId : "" )} className="title">{trackTitle}</a>
                        <a style={{display:"block",color:"#605f68"}} onClick={e => onUserNameClick(e)}>{username}</a>
                    </div>
                </div>
                <a className="close-player" onClick={closeAudioPlayer}>{closePlayerButtonDisplay}</a>
            </div>
        </div>
    )
}

export default AudioPlayer;