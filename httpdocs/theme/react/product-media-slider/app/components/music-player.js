import React, { useState, useEffect, useRef} from "react";
import Slider from 'rc-slider'; 
import { Scrollbars } from 'react-custom-scrollbars';

function MusicPlayerWrapper(props){

  return (
    <div>
      <MusicPlayer 
        product={props.product}
        items={props.slide.items} 
        containerWidth={props.width}
      />
    </div>
  )
}

function MusicPlayer(props){

  /* COMPONENT */

  const [ playIndex, setPlayIndex ] = useState(0);
  const prevIndex = usePrevious(playIndex);
  const [ isPlaying, setIsPlaying ] = useState();
  const [ isPaused, setIsPaused ] = useState();
  const [ audioVolume, setAudioVolume ] = useState(0.5);
  const [ isMuted, setIsMuted ] = useState(false);
  const [ currentTrackTime, setCurrentTrackTime ] = useState(0);
  const [ currentTrackTimeSeconds, setCurrentTrackTimeSeconds ] = useState(0);
  const [ currentTrackDuration, setcurrentTrackDuration ] = useState(0);
  const [ currentTrackProgress, setCurrentTrackProgress ] = useState(0);
  const [ theme, setTheme ] = useState('dark');
  let initialPLayedAudioArray = []
  props.items.forEach(function(i,index){
    let pl = 0;
    if (index === 0) pl = -1;
    const pa = {
      ...i,
      played:0,
      stopped:0
    }
    initialPLayedAudioArray.push(pa);
  })
  const [ playedAudioArray, setPlayedAudioArray ] = useState(initialPLayedAudioArray);
  const [ randomSupporter, setRandomSupporter ] = useState();

  const initialIsMobileValue = props.containerWidth < 600 ? true : false;
  const [ isMobile, setIsMobile ] = useState(initialIsMobileValue);
  const initialShowPlaylistValue = isMobile === true ? false : true;
  const [ showPlaylist, setShowPlaylist ] = useState(initialShowPlaylistValue);

  useEffect(() => {
    getRandomMusicsupporter();
  },[])

  useEffect(() => {
    const playerElement = document.getElementById("music-player-container").getElementsByTagName('audio');
    playerElement[0].volume = audioVolume;
  },[audioVolume])

  useEffect(() => {
    if (isPlaying) onPlayClick(true);
    if (isPaused){
        if (prevIndex === playIndex) onPlayClick();
        else  onPlayClick(true);
    }
    if (isPlaying === true) onReportAudioStop(props.items[prevIndex].musicSrc,playIndex)
  },[playIndex])

  // audio player

  function onPlayClick(reload,newPlayIndex){

    const playerElement = document.getElementById("music-player-container").getElementsByTagName('audio');
    let pi = newPlayIndex ? newPlayIndex : playIndex;
    const currentSrc = props.items[pi].musicSrc;
    
    if (isPaused === false ||  playerElement[0].currentTime && playerElement[0].currentTime === 0 || reload === true){
      playerElement[0].src = currentSrc;
      setCurrentTrackProgress(0);
    }
    playerElement[0].play();
    setIsPlaying(true);
    setIsPaused(false);
    onReportAudioPlay(currentSrc,newPlayIndex);
  }

  function onPauseClick(){
    const playerElement = document.getElementById("music-player-container").getElementsByTagName('audio');
    playerElement[0].pause();
    setIsPlaying(false);
    setIsPaused(true);
    onReportAudioStop(props.items[playIndex].musicSrc)
  }

  function onPrevTrackPlayClick(){
      let prevTrackIndex;
      if (playIndex === 0){
          prevTrackIndex = props.items.length - 1;
      } else {
          prevTrackIndex = playIndex - 1;
      }
      setPlayIndex(prevTrackIndex);
  }

  function onNextTrackPlayClick(){
      let nextTrackIndex;
      if (playIndex + 1 === props.items.length){
          nextTrackIndex = 0;
      } else {
          nextTrackIndex = playIndex + 1;
      }
      setPlayIndex(nextTrackIndex);
  }
 
  function onReportAudioPlay(musicSrc,newPlayIndex){  
    const audioItem = playedAudioArray.find((i => i.musicSrc === musicSrc));
    const audioItemIndex = newPlayIndex ? newPlayIndex : playedAudioArray.findIndex((i => i.musicSrc === musicSrc));
    const newAudioItem = {
      ...audioItem,
      played:audioItem.played + 1
    }
    const newPLayedAudioArray = [
      ...playedAudioArray.slice(0,audioItemIndex),
      newAudioItem,
      ...playedAudioArray.slice(audioItemIndex + 1, playedAudioArray.length)
    ];

    /*console.log('played audio array - ');
    console.log(playedAudioArray);
    console.log('audio item index - ' + audioItemIndex);
    console.log('audio item - ')
    console.log(audioItem);
    console.log( playedAudioArray[audioItemIndex]);
    console.log('is played - ' + playedAudioArray[audioItemIndex].played)*/

    if (playedAudioArray[audioItemIndex].played === 0){

      const audioStartUrl = "https://" + window.location.hostname + "/p/" + props.product.project_id + '/startmediaviewajax?collection_id='+audioItem.collection_id+'&file_id='+audioItem.file_id+'&type_id=2';
      //console.log('audio start url - ' + audioStartUrl);
      $.ajax({url: audioStartUrl}).done(function(res) { 
        //console.log('ajax res - ');
        //console.log(res);
        const newAudioItem = {
          ...audioItem,
          mediaViewId:res.MediaViewId,
          played:audioItem.played + 1
        }
        const newPLayedAudioArray = [
          ...playedAudioArray.slice(0,audioItemIndex),
          newAudioItem,
          ...playedAudioArray.slice(audioItemIndex + 1, playedAudioArray.length)
        ];
        setPlayedAudioArray(newPLayedAudioArray);
      });
    } else {
      setPlayedAudioArray(newPLayedAudioArray);
    }
  }

  function onReportAudioStop(musicSrc){

    const audioItem = playedAudioArray.find((i => i.musicSrc === musicSrc));
    const audioItemIndex = playedAudioArray.findIndex((i => i.musicSrc === musicSrc));
    const newAudioItem = {
      ...audioItem,
      stopped:audioItem.stopped + 1
    }
    const newPLayedAudioArray = [
      ...playedAudioArray.slice(0,audioItemIndex),
      newAudioItem,
      ...playedAudioArray.slice(audioItemIndex + 1, playedAudioArray.length)
    ];

    if  (playedAudioArray[audioItemIndex].stopped === 0){

      const audioStopUrl =   "https://" + window.location.hostname + "/p/" + props.product.project_id + "/stopmediaviewajax?media_view_id=" + playedAudioArray[audioItemIndex].mediaViewId;

      //console.log(audioStopUrl);

      $.ajax({url: audioStopUrl}).done(function(res) { 
        //console.log(res);
        setPlayedAudioArray(newPLayedAudioArray);
      });
    } else {
      setPlayedAudioArray(newPLayedAudioArray);
    }
  }

  function onUpdateCurrentTrackProgress(newTrackProgress){
    console.log(newTrackProgress);
    setCurrentTrackProgress(newTrackProgress);
    const newCurrentTrackTime = (currentTrackTimeSeconds / 100) * newTrackProgress;
    const playerElement = document.getElementById("music-player-container").getElementsByTagName('audio');
    playerElement[0].currentTime = newCurrentTrackTime;
    // playerElement[0].ontimeupdate = function(){ onPlayerTimeUpdate(playerElement[0]) }
    playerElement[0].play();
    setIsPlaying(true);
    setIsPaused(false);
    const currentSrc = props.items[playIndex].musicSrc;
    onReportAudioPlay(currentSrc);
  }

  // random supporter

  function getRandomMusicsupporter(){
    $.ajax({url: "https://"+window.location.hostname +"/json/fetchrandomsupporter/s/3"}).done(function(res) { 
      // console.log(res);
      setRandomSupporter(res.supporter)
    });
  }

  // time progress bar

  function onPlayerTimeUpdate(e){
    const playerElement = e.target;
    const newCurrentTrackTime = millisToMinutesAndSeconds(playerElement.currentTime);
    setCurrentTrackTime(newCurrentTrackTime);
    setCurrentTrackTimeSeconds(playerElement.duration);
    let newcurrentTrackDuration = playerElement.duration;
    if (isNaN(newcurrentTrackDuration)){ newcurrentTrackDuration = 0; }
    newcurrentTrackDuration = millisToMinutesAndSeconds(newcurrentTrackDuration);
    setcurrentTrackDuration(newcurrentTrackDuration );
    const newCurrentTrackProgress = (playerElement.currentTime / playerElement.duration) * 100;
    setCurrentTrackProgress(newCurrentTrackProgress);
    
    if (playerElement.currentTime === playerElement.duration){
      console.log('song ended');
      onNextTrackPlayClick();
    }

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

  // volume

  function toggleAudioMuted(){
    const newIsMuted = isMuted === true ? false : true;
    const playerElement = document.getElementById("music-player-container").getElementsByTagName('audio');
    if (newIsMuted === true) playerElement[0].volume = 0;
    else playerElement[0].volume = audioVolume;
    setIsMuted(newIsMuted);
  }

  // playlist

  function togglePlaylistDisplay(){
    const newShowPlaylistValue = showPlaylist === true ? false : true;
    setShowPlaylist(newShowPlaylistValue);
  }

  // key press 

  function handleKeyPress(e){
    // console.log(e.key)
    if (e.key === 'Space'){
      if (isPlaying === true) onPauseClick();
      else onPlayClick();
    }
  }

  /* RENDER */

  let musicPlayerContainerCssClass = "";
  if (showPlaylist === true) musicPlayerContainerCssClass += "show-playlist ";
  if (isMobile === true) musicPlayerContainerCssClass += " is-mobile";

  const audioElVolume = isMuted === true ? 0.0 : audioVolume;

  const currentSrc = props.items[playIndex].musicSrc;

  return (
    <div id="music-player-container" className={musicPlayerContainerCssClass + " " + theme} onKeyPress={(e) => handleKeyPress(e)}> 
      <audio 
        volume={audioElVolume} 
        onTimeUpdate={(e) => onPlayerTimeUpdate(e)}  
        onLoadedMetadata={(e) => onPlayerTimeUpdate(e)}
        src={currentSrc}
        id="music-player-audio"></audio>
      <MusicPlayerControlPanel 
        playIndex={playIndex}
        isPlaying={isPlaying}
        isPaused={isPaused}
        isMuted={isMuted}
        isMobile={isMobile}
        audioVolume={audioVolume}
        currentTrackTime={currentTrackTime}
        currentTrackDuration={currentTrackDuration}
        currentTrackProgress={currentTrackProgress}
        items={props.items}
        theme={theme}
        setTheme={(val) => setTheme(val)}
        onUpdateCurrentTrackProgress={(val) => onUpdateCurrentTrackProgress(val)}
        onChangeAudioVolume={(val) => setAudioVolume(val)}
        onPlayClick={(reload) => onPlayClick(reload)}
        onPauseClick={onPauseClick}
        onPrevTrackPlayClick={onPrevTrackPlayClick}
        onNextTrackPlayClick={onNextTrackPlayClick}
        togglePlaylistDisplay={togglePlaylistDisplay}
        toggleAudioMuted={() => toggleAudioMuted()}
      />
      <MusicPlayerPlaylist 
        containerWidth={props.containerWidth}
        title={props.product.title}
        randomSupporter={randomSupporter}
        items={props.items}
        playIndex={playIndex}
        isPlaying={isPlaying}
        isPaused={isPaused}
        isMobile={isMobile}
        currentTrackTime={currentTrackTime}
        currentTrackDuration={currentTrackDuration}
        currentTrackProgress={currentTrackProgress}
        togglePlaylistDisplay={togglePlaylistDisplay}
        setPlayIndex={setPlayIndex}
        onPlayClick={(reload) => onPlayClick(reload)}
        onPauseClick={onPauseClick}
      />
    </div>
  )
}

// Hook
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

function MusicPlayerControlPanel(props){

  // console.log(props);

  React.useEffect(() => {
    // console.log('music player controls panel');
    // console.log(props);
  },[])

  /* COMPONENT */

  function onChangeTrackProgressPosition(e){
    console.log(e);
    props.onUpdateCurrentTrackProgress(e);
  }

  function onAfterChangeTrackProgressPosition(e){
    // console.log(e);
  }

  function onChangeVolumeSliderPosition(e){
    if (props.isMuted === false){
      const newVolumeValue = e / 100;
      props.onChangeAudioVolume(newVolumeValue);      
    }
  }

  function onAfterChangeVolumeSliderPosition(e){
    // console.log(e);
  }

  function onVolumeIconClick(){
    props.toggleAudioMuted()
  }

  function onThemeSwitchClick(){
    const newThemeValue = props.theme === "dark" ? "light" : "dark";
    props.setTheme(newThemeValue);
  }

  /* DISPLAY */

  const playIndex = props.playIndex;

  // audio controls display

  const playButtonElement = (
    <svg fill="currentColor" preserveAspectRatio="xMidYMid meet" height="1em" width="1em" viewBox="0 0 40 40" className="play-icon">
        <g><path d="m20.1 2.9q4.7 0 8.6 2.3t6.3 6.2 2.3 8.6-2.3 8.6-6.3 6.2-8.6 2.3-8.6-2.3-6.2-6.2-2.3-8.6 2.3-8.6 6.2-6.2 8.6-2.3z m8.6 18.3q0.7-0.4 0.7-1.2t-0.7-1.2l-12.1-7.2q-0.7-0.4-1.5 0-0.7 0.4-0.7 1.3v14.2q0 0.9 0.7 1.3 0.4 0.2 0.8 0.2 0.3 0 0.7-0.2z"></path></g>
    </svg>
  )

  const pauseButtonElement = (
      <svg fill="currentColor" preserveAspectRatio="xMidYMid meet" height="1em" width="1em" viewBox="0 0 40 40" className="pause-icon">
          <g><path d="m18.7 26.4v-12.8q0-0.3-0.2-0.5t-0.5-0.2h-5.7q-0.3 0-0.5 0.2t-0.2 0.5v12.8q0 0.3 0.2 0.5t0.5 0.2h5.7q0.3 0 0.5-0.2t0.2-0.5z m10 0v-12.8q0-0.3-0.2-0.5t-0.5-0.2h-5.7q-0.3 0-0.5 0.2t-0.2 0.5v12.8q0 0.3 0.2 0.5t0.5 0.2h5.7q0.3 0 0.5-0.2t0.2-0.5z m8.6-6.4q0 4.7-2.3 8.6t-6.3 6.2-8.6 2.3-8.6-2.3-6.2-6.2-2.3-8.6 2.3-8.6 6.2-6.2 8.6-2.3 8.6 2.3 6.3 6.2 2.3 8.6z"></path></g>
      </svg>
  )

  const prevButtonElement = (
      <svg fill="currentColor" preserveAspectRatio="xMidYMid meet" height="1em" width="1em" viewBox="0 0 40 40" className="prev-icon">
          <g><path d="m15.9 20l14.1-10v20z m-5.9-10h3.4v20h-3.4v-20z"></path></g>
      </svg>
  )

  const nextButtonElement = (
      <svg fill="currentColor" preserveAspectRatio="xMidYMid meet" height="1em" width="1em" viewBox="0 0 40 40" className="next-icon">
          <g><path d="m26.6 10h3.4v20h-3.4v-20z m-16.6 20v-20l14.1 10z"></path></g>
      </svg>
  )

  let playButtonDisplay;
  if (props.isPlaying === true){
    if (props.isMobile === true) playButtonDisplay = <span onTouchStart={() => props.onPauseClick()}>{pauseButtonElement}</span>
    else playButtonDisplay = <span onClick={() => props.onPauseClick()}>{pauseButtonElement}</span>
  } else {
    if (props.isMobile === true)  playButtonDisplay = <span onTouchStart={() => props.onPlayClick()}>{playButtonElement}</span>
    else  playButtonDisplay = <span onClick={() => props.onPlayClick()}>{playButtonElement}</span>
  }

  let audioControlsDisplay;

  if (props.isMobile === true){
    audioControlsDisplay = (
      <div className="music-player-audio-control">
        <span onTouchStart={() => props.onPrevTrackPlayClick()}>{prevButtonElement}</span>
        {playButtonDisplay}
        <span onTouchStart={() => props.onNextTrackPlayClick()}>{nextButtonElement}</span>
      </div>
    )
  } else {
    audioControlsDisplay = (
      <div className="music-player-audio-control">
        <span onClick={() => props.onPrevTrackPlayClick()}>{prevButtonElement}</span>
        {playButtonDisplay}
        <span onClick={() => props.onNextTrackPlayClick()}>{nextButtonElement}</span>
      </div>
    )
  }

  // volume control

  const volumeIcon = (
    <svg fill="currentColor" preserveAspectRatio="xMidYMid meet" height="1em" width="1em" viewBox="0 0 40 40" style={{"verticalAlign":"middle"}}>
      <g><path d="m23.4 5.4c6.7 1.5 11.6 7.5 11.6 14.6s-4.9 13.1-11.6 14.6v-3.4c4.8-1.4 8.2-5.9 8.2-11.2s-3.4-9.8-8.2-11.2v-3.4z m4.1 14.6c0 3-1.6 5.5-4.1 6.7v-13.4c2.5 1.2 4.1 3.7 4.1 6.7z m-22.5-5h6.6l8.4-8.4v26.8l-8.4-8.4h-6.6v-10z"></path></g>
    </svg>
  )

  const noVolumeIcon = (
    <svg fill="currentColor" preserveAspectRatio="xMidYMid meet" height="1em" width="1em" viewBox="0 0 40 40"  style={{"verticalAlign":"middle"}}>
      <g><path d="m20 6.6v7.1l-3.5-3.5z m-12.9-1.6l27.9 27.9-2.1 2.1-3.4-3.4c-1.8 1.4-3.9 2.5-6.1 3v-3.4c1.4-0.4 2.6-1.1 3.7-2l-7.1-7.1v11.3l-8.4-8.4h-6.6v-10h7.9l-7.9-7.9z m24.5 15c0-5.3-3.4-9.8-8.2-11.2v-3.4c6.7 1.5 11.6 7.5 11.6 14.6 0 2.5-0.6 4.9-1.7 7l-2.5-2.6c0.5-1.4 0.8-2.8 0.8-4.4z m-4.1 0c0 0.4 0 0.7-0.1 1l-4-4.1v-3.6c2.5 1.2 4.1 3.7 4.1 6.7z"></path></g>
    </svg>
  )

  const volumeIconDisplay = props.isMuted === false ? volumeIcon : noVolumeIcon;
  let musicPlayerVolumeControlCssClass = "music-player-volume-control";
  if (props.isMuted) musicPlayerVolumeControlCssClass += " is-muted";
  
  const volumeControlDisplay = (
      <div className={musicPlayerVolumeControlCssClass}>
        <span className="volume-icon" onClick={onVolumeIconClick}>
          {volumeIconDisplay}
        </span>
        <span className="volume-bar-container progress_bar">
            <Slider 
              min={0}
              max={100}
              value={props.audioVolume * 100}
              vertical={props.isMobile ? false : true}
              onChange={onChangeVolumeSliderPosition}
              onAfterChange={onAfterChangeVolumeSliderPosition}
            />
        </span>
      </div>
    );
  
  // cover 

  const musicPlayerCoverDisplay = (
    <div className="music-player-cover">
      <figure><img src={props.items[playIndex].cover}/></figure>
    </div>
  )

  // title

  const musicPlayerTitleDisplay = (
    <div className="music-player-track-title">
      <h2>{props.items[playIndex].title}</h2>
    </div>
  )

  // time display

  let currentTrackTimeDisplay = props.currentTrackTime;
  if (props.currentTrackTime === 0){
    currentTrackTimeDisplay = '00:00'
  }

  let currentTrackDurationDisplay = props.currentTrackDuration;
  if (props.currentTrackDuration === 0){
    currentTrackDurationDisplay = <span className="infinite">&infin;</span>
  }

  const musicPlayerTimeDisplay = (
    <div className="music-player-time-display">
      <span className="current-track-time">{currentTrackTimeDisplay} </span>
      <span className="current-track-progress">
        <Slider 
          min={0}
          max={100}
          value={props.currentTrackProgress}
          onChange={onChangeTrackProgressPosition}
          onAfterChange={onAfterChangeTrackProgressPosition}
        />
      </span>
      <span className="current-track-duration">{currentTrackDurationDisplay}</span>
    </div>
  )

  // mobile / desktop switch display

  let musicPlayerControlPanelDisplay;
  if (props.isMobile === true){

    musicPlayerControlPanelDisplay = (
      <div className="mobile-control-panel-wrapper">
        {musicPlayerTitleDisplay}
        {musicPlayerCoverDisplay}
        {musicPlayerTimeDisplay}
        <div className="music-player-controls-bar">
          <div className="music-player-controls-wrapper">
            {audioControlsDisplay}
            <div className="bottom-controls">
              {volumeControlDisplay}
              <div className="playlist-toggle-container">
                <span className="playlist-toggle-button" onTouchStart={() => props.togglePlaylistDisplay()}>
                  <svg fill="currentColor" preserveAspectRatio="xMidYMid meet" height="1em" width="1em" viewBox="0 0 40 40" style={{"vertical-align": "middle"}}>
                    <g><path d="m28.4 10h8.2v3.4h-5v15c0 2.7-2.2 5-5 5s-5-2.3-5-5 2.3-5 5-5c0.6 0 1.2 0.1 1.8 0.3v-13.7z m-23.4 16.6v-3.2h13.4v3.2h-13.4z m20-10v3.4h-20v-3.4h20z m0-6.6v3.4h-20v-3.4h20z"></path></g>
                  </svg>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    )
  
  } else {
    
    let themeSwitchCssClass = "theme-switch-container rc-switch ";
    /*<div className="theme-switch-wrapper">
      <span className="theme-switch">
        <button onClick={() => onThemeSwitchClick()} type="button" role="switch" aria-checked="false" className={ themeSwitchCssClass }>
          <span className="rc-switch-inner">{props.theme === "dark" ? "light" : "dark"}</span>
        </button>
      </span>
    </div>*/

    if (props.theme === "light") themeSwitchCssClass += " checked";

    musicPlayerControlPanelDisplay = (
      <div className="desktop-control-panel-wrapper">
        {musicPlayerCoverDisplay}
        {musicPlayerTitleDisplay}
        {musicPlayerTimeDisplay}
        <div className="music-player-controls-bar">
          <div className="music-player-controls-wrapper">
              {audioControlsDisplay}
              {volumeControlDisplay}
              <div className="playlist-toggle-container">
                <span className="playlist-toggle-button" onClick={() => props.togglePlaylistDisplay()}>PL</span>
              </div>
          </div>
        </div>
      </div>
    )
  }

  /* RENDER */

  return (
    <div id="music-player-control-panel">
      {musicPlayerControlPanelDisplay}
    </div>
  )
}

function MusicPlayerPlaylist(props){

  function onMusicPlayerPlaylistItemClick(val){
    if (props.isPlaying === false){
      if (props.playIndex === val) props.onPlayClick();
      // else props.onPlayClick(true,props.playIndex);
    }
    else {
      if (props.playIndex === val) props.onPauseClick();
      // else props.onPlayClick(true,props.playIndex);
    }
    props.setPlayIndex(val);
  }

  const musicPlayerPlaylistItems = props.items.map((item,index) => (
    <MusicPlayerPlaylistItem 
      key={index}
      index={index}
      item={item}
      playIndex={props.playIndex}
      isPlaying={props.isPlaying}
      isPaused={props.isPaused}
      isMobile={props.isMobile}
      onMusicPlayerPlaylistItemClick={(val) => onMusicPlayerPlaylistItemClick(val)}
    />
  ));

  const musicPlayerPlaylistDisplay = <ul>{musicPlayerPlaylistItems}</ul>
  

  let randomSupporterDisplay;
  if (props.randomSupporter && props.randomSupporter !== null){
    randomSupporterDisplay = (
      <div id="music-sponsor-display">
        <span>made possible by supporters like</span>
        <span className="sponsor-avatar">
          <a href={"/u/" + props.randomSupporter.username}>
            <img src={props.randomSupporter.profile_image_url}/>
          </a>
        </span>
        <span>
          {props.randomSupporter.username}
        </span>
      </div>
    )
  }

  let closeButtonDisplay = <a className="toggle-playlist" onClick={props.togglePlaylistDisplay}>X</a>
  if (props.isMobile === true) closeButtonDisplay = <a className="toggle-playlist" onTouchStart={props.togglePlaylistDisplay}>X</a>

  return (
    <div id="music-player-playlist-panel">
      <div id="music-player-playlist-header">
        <h2>{props.title + " / " + props.items.length }</h2>
        {closeButtonDisplay}
      </div>
      <div id="music-player-playlist">
        <Scrollbars
          width={props.containerWidth / 2}
          height={250}
        >
          {musicPlayerPlaylistDisplay}
        </Scrollbars>
      </div>
      <div id="music-player-playlist-footer">
        {randomSupporterDisplay}
      </div>
    </div>
  )
}

function MusicPlayerPlaylistItem(props){

  const playButtonElement = (
    <svg fill="currentColor" preserveAspectRatio="xMidYMid meet" height="1em" width="1em" viewBox="0 0 40 40" className="play-icon">
        <g><path d="m20.1 2.9q4.7 0 8.6 2.3t6.3 6.2 2.3 8.6-2.3 8.6-6.3 6.2-8.6 2.3-8.6-2.3-6.2-6.2-2.3-8.6 2.3-8.6 6.2-6.2 8.6-2.3z m8.6 18.3q0.7-0.4 0.7-1.2t-0.7-1.2l-12.1-7.2q-0.7-0.4-1.5 0-0.7 0.4-0.7 1.3v14.2q0 0.9 0.7 1.3 0.4 0.2 0.8 0.2 0.3 0 0.7-0.2z"></path></g>
    </svg>
  )

  const pauseButtonElement = (
      <svg fill="currentColor" preserveAspectRatio="xMidYMid meet" height="1em" width="1em" viewBox="0 0 40 40" className="pause-icon">
          <g><path d="m18.7 26.4v-12.8q0-0.3-0.2-0.5t-0.5-0.2h-5.7q-0.3 0-0.5 0.2t-0.2 0.5v12.8q0 0.3 0.2 0.5t0.5 0.2h5.7q0.3 0 0.5-0.2t0.2-0.5z m10 0v-12.8q0-0.3-0.2-0.5t-0.5-0.2h-5.7q-0.3 0-0.5 0.2t-0.2 0.5v12.8q0 0.3 0.2 0.5t0.5 0.2h5.7q0.3 0 0.5-0.2t0.2-0.5z m8.6-6.4q0 4.7-2.3 8.6t-6.3 6.2-8.6 2.3-8.6-2.3-6.2-6.2-2.3-8.6 2.3-8.6 6.2-6.2 8.6-2.3 8.6 2.3 6.3 6.2 2.3 8.6z"></path></g>
      </svg>
  )

  const playlistItemPlayButtonDisplay = props.playIndex === props.index ? props.isPlaying === true ? pauseButtonElement : playButtonElement : '';
  const playlistItemCssClass =  props.playIndex === props.index ? props.isPlaying === true ? 'is-playing' : 'is-paused' : '';

  let musicPlayerPlaylistItemDisplay;
  if (props.isMobile === true){
    musicPlayerPlaylistItemDisplay = (
      <a onTouchStart={() => props.onMusicPlayerPlaylistItemClick(props.index)}>
        {playlistItemPlayButtonDisplay}
        {props.item.title}
      </a>
    )
  } else {
    musicPlayerPlaylistItemDisplay = (
      <a onClick={() => props.onMusicPlayerPlaylistItemClick(props.index)}>
        {playlistItemPlayButtonDisplay}
        {props.item.title}
      </a>
    )    
  }

  return (
    <li className={"music-player-playlist-item " + playlistItemCssClass} >
      {musicPlayerPlaylistItemDisplay}
    </li>
  )
}

export default MusicPlayerWrapper;