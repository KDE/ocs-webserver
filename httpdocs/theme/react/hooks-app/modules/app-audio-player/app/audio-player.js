import React, { useState, useEffect, useRef } from 'react';
import Slider from 'rc-slider'; 
import { Scrollbars } from 'react-custom-scrollbars';

import {isMobile} from 'react-device-detect';

import '../style/app-audio-player.css';

// Audio Player Wrapper
class AudioPlayerWrapper extends React.Component {

    constructor(props){
        super(props);
        this.state = { 
            isPlaying:false,
            productImage:null,
            productInfo:null
        }
        this.initAudioPlayerWrapper = this.initAudioPlayerWrapper.bind(this);
        this.localStorageSetHandler = this.localStorageSetHandler.bind(this);
        this.closeAudioPlayer = this.closeAudioPlayer.bind(this);
    }
    
    componentDidMount() {
        this.initAudioPlayerWrapper();
    }

    initAudioPlayerWrapper(){
        var originalSetItem = localStorage.setItem;
        localStorage.setItem = function(key, value) {
          var event = new Event('itemInserted');
          event.value = value; // Optional..
          event.key = key; // Optional..
          document.dispatchEvent(event);
          originalSetItem.apply(this, arguments);
        };
        document.addEventListener("itemInserted", this.localStorageSetHandler, false);
    }
    
    localStorageSetHandler(e) {
        
        if (e.key === "audioTrackIsPlaying"){
            let newAudioTrackIsPlayingVal = e.value
            if (e.value === 'true') newAudioTrackIsPlayingVal = true;
            this.setState({ isPlaying:newAudioTrackIsPlayingVal })
        }
        
        if (e.key === "audioTrackProductImage"){
            this.setState({productImage:e.value});
        }

        if (e.key === "audioTrackProductInfo"){
            this.setState({productInfo:e.value});
        }
    }

    closeAudioPlayer(){ 
        this.setState({isPlaying:false}); 
        window.localStorage.setItem('audioTrackIsPlaying',false);
        const p = JSON.parse(this.state.productInfo);
        const pId = p.project_id;
        $('#music-player-container-'+pId).find('.pause-icon-wrapper').trigger('click');
    }

    render(){

        let audioPlayerDisplay;
        if (this.state.isPlaying === true){
            audioPlayerDisplay = (
                <AudioPlayer
                    audioTrackUrl={this.state.audioTrackUrl}
                    productImage={this.state.productImage}
                    productInfo={this.state.productInfo}
                    closeAudioPlayer={this.closeAudioPlayer}
                />
            )
        }
    
        return (
            <div className="app-audio-player-wrapper">
                {audioPlayerDisplay}
            </div>
        )
    }
}

// Audio Player
function AudioPlayer(props){

    const [ sliderPosition, setSliderPosition ] = useState(0);
    const initAudioTrackUrl = window.localStorage.getItem('audioTrackUrl').replace(/"/g, '');
    const [ audioTrackUrl, setAudioTrackUrl ] = useState(initAudioTrackUrl);
    const [ currentTrackTime, setCurrentTrackTime ] = useState(0);
    const [ currentTrackTimeSeconds, setCurrentTrackTimeSeconds ] = useState(0);
    const [ currentTrackDuration, setCurrentTrackDuration ] = useState(0);
    const [ currentTrackProgress, setCurrentTrackProgress ] = useState(0);

    useEffect(() => {
        initAudioPlayer();
    },[])

    useEffect(() => {
        initAudioPlayer();
    },[props.audioTrackUrl])

    function initAudioPlayer(){
        console.log('init audio player');
        const playerElement = document.getElementById("app-audio-player").getElementsByTagName('audio');
        playerElement[0].src = "https://dllb2.pling.com/api/files/download/j/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjE1OTQxMDQwNjgiLCJ1IjoiMjQiLCJsdCI6ImZpbGVwcmV2aWV3IiwicyI6IjMxZTEwYTM5ZDg3MTc3MjljYzQ2MGM4MmNmZDkzMTE1NGE5ZWNmOThlMjVjODM5ODU1ODdlMGI5YmU1MWNkNDUzNTgzZWY3NDlmNmQzYTBlYjNlMTQ1ZWQyMTA2YTc1ZTk5NzBmZWQ0NGMwNjY3YmJhZDRkZmJhMTgwZmZmNjM5IiwidCI6MTU5NDIwNTYzMCwic3RmcCI6IjNjZDBlMjRlNjlkMDFjM2FlYWE4ZGJkZDcxMjE5M2RhIiwic3RpcCI6IjgyLjU0LjE3NC4yMjUifQ.cHK19mb_nLwJDUNRa3CatRAs09_WpwP9RUenOo-8LLE/7Dance+rock+mp3.mp3";
        playerElement[0].currentTime = 0;
        playerElement[0].volume = 0.5;
        playerElement[0].play();
    }

    function onPlayerTimeUpdate(e){
        const playerElement = e.target;
        const newCurrentTrackTime = millisToMinutesAndSeconds(playerElement.currentTime);
        setCurrentTrackTime(newCurrentTrackTime);
        setCurrentTrackTimeSeconds(playerElement.duration);
        let newcurrentTrackDuration = playerElement.duration;
        if (isNaN(newcurrentTrackDuration)){ newcurrentTrackDuration = 0; }
        newcurrentTrackDuration = millisToMinutesAndSeconds(newcurrentTrackDuration);
        setCurrentTrackDuration(newcurrentTrackDuration );
        const newCurrentTrackProgress = (playerElement.currentTime / playerElement.duration) * 100;
        setCurrentTrackProgress(newCurrentTrackProgress);
        /*if (playerElement.currentTime === playerElement.duration){
          console.log('song ended');
          onNextTrackPlayClick();
        }*/
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
        const newCurrentTrackTime = (currentTrackTimeSeconds / 100) * newTrackProgress;
        const playerElement = document.getElementById("app-audio-player").getElementsByTagName('audio');
        playerElement[0].currentTime = newCurrentTrackTime;
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

    const productInfo = JSON.parse(props.productInfo);

    return (
        <div id="app-audio-player">
            <a className="pause-player" onClick={props.closeAudioPlayer}>{pauseButtonDisplay}</a>
            <div className="track-player">
                <audio 
                    volume={1} 
                    onTimeUpdate={(e) => onPlayerTimeUpdate(e)}  
                    onLoadedMetadata={(e) => onPlayerTimeUpdate(e)}
                    src={audioTrackUrl}>
                </audio>
                <span className="player-time time-elapsed">{currentTrackTime}</span>
                <div className="slider-wrapper">
                    <Slider 
                        min={0}
                        max={100}
                        value={currentTrackProgress}           
                        onChange={(e) => onChangeVolumeSliderPosition(e)}
                        railStyle={{"backgroundColor":"#babec2","height":"2px"}}
                        trackStyle={{"backgroundColor":"#2d3339","height":"2px"}}
                        handleStyle={{"display":"none"}}
                    />
                </div>
                <span className="player-time time-total">{currentTrackDuration}</span>
            </div>
            <div className="track-info">
                <div className="info-wrapper">
                    <img src={props.productImage.replace(/"/g, '')}/>
                    <span className="title">{productInfo.title}</span>
                    <span>{productInfo.username}</span>
                </div>
            </div>
            <a className="close-player" onClick={props.closeAudioPlayer}>{closePlayerButtonDisplay}</a>
        </div>
    )
}

// Hook Use Local Storage
function useLocalStorage(key, initialValue) {
    // State to store our value
    // Pass initial state function to useState so logic is only executed once
    const [storedValue, setStoredValue] = useState(() => {
      try {
        // Get from local storage by key
        const item = window.localStorage.getItem(key);
        // Parse stored json or if none return initialValue
        return item ? JSON.parse(item) : initialValue;
      } catch (error) {
        // If error also return initialValue
        console.log(error);
        return initialValue;
      }
    });
  
    // Return a wrapped version of useState's setter function that ...
    // ... persists the new value to localStorage.
    const setValue = value => {
      try {
        // Allow value to be a function so we have same API as useState
        const valueToStore =
          value instanceof Function ? value(storedValue) : value;
        // Save state
        setStoredValue(valueToStore);
        // Save to local storage
        window.localStorage.setItem(key, JSON.stringify(valueToStore));
      } catch (error) {
        // A more advanced implementation would handle the error case
        console.log(error);
      }
    };
  
    return [storedValue, setValue];
}  

// Hook use Previous
function usePrevious(value) {
    // The ref object is a generic container whose current property is mutable ...
    // ... and can hold any value, similar to an instance property on a class
    const ref = useRef();
    
    // Store current value in ref
    useEffect(() => {
      ref.current = value;
    }, [value]); // Only re-run if value changes
    
    // Return previous value (happens before update in useEffect above)
    return ref.current;
}


export default AudioPlayerWrapper;