import React, { useState } from "react";
import ReactJkMusicPlayer from "react-jinke-music-player";
import {isMobile} from 'react-device-detect';

function MusicPlayerWrapper(props){

  const [ showPlaylist, setShowPlaylist ] = useState(isMobile === true ? false : true);
  let initialPLayedAudioArray = []
  props.slide.items.forEach(function(i,index){
    let pl = 0;
    const pa = {
      ...i,
      played:pl,
      stopped:0
    }
    initialPLayedAudioArray.push(pa);
  })
  const [ playedAudioArray, setPlayedAudioArray ] = useState(initialPLayedAudioArray);
  const [ randomSupporter, setRandomSupporter ] = useState();
  const [ isPlaying, setIsPlaying ] = useState(false);
  const [ isPaused, setIsPaused ] = useState(false);
  const [ playIndex, setPlayIndex ] = useState(0);

  console.log(playedAudioArray);

  React.useEffect(() => {
    getRandomMusicsupporter();
    $('#music-player-wrapper').find('.player-content').prepend($('.music-player-controls'));
  },[])

  function onPlayClick(){
    console.log('play track');
    const playerElement = document.getElementById("music-player-wrapper").getElementsByTagName('audio');
    let currentSrc;
    if (isPaused === false) {
        currentSrc = props.slide.items[playIndex].musicSrc;
        playerElement[0].src = currentSrc;
    }
    playerElement[0].play();
    setIsPlaying(true);
    setIsPaused(false);
    console.log(currentSrc);
    onReportAudioPlay(currentSrc);
}

function onPauseClick(){
    console.log('pause track');
    const playerElement = document.getElementById("music-player-wrapper").getElementsByTagName('audio');
    playerElement[0].pause();
    setIsPlaying(false);
    setIsPaused(true);
    onReportAudioStop(props.slide.items[playIndex].musicSrc)
}

function onPrevTrackPlayClick(){
    let prevTrackIndex;
    if (playIndex === 0){
        prevTrackIndex = props.slide.items.length - 1;
    } else {
        prevTrackIndex = playIndex - 1;
    }
    setPlayIndex(prevTrackIndex);
    onPlayClick(prevTrackIndex);
}

function onNextTrackPlayClick(){
    let nextTrackIndex;
    if (playIndex + 1 === props.slide.items.length){
        nextTrackIndex = 0;
    } else {
        nextTrackIndex = playIndex + 1;
    }
    setPlayIndex(nextTrackIndex);
    onPlayClick(nextTrackIndex);
}

  function getRandomMusicsupporter(){
    const suffix = window.location.host.endsWith('cc') ? 'cc' :  window.location.host.endsWith('com') ||  window.location.host.endsWith('org') ? 'com' : 'cc';
    $.ajax({url: "https://www.pling."+suffix+"/json/fetchrandomsupporter/s/3"}).done(function(res) { 
      setRandomSupporter(res.supporter)
    });    
  }

  function onReportAudioPlay(musicSrc){
    const audioItem = playedAudioArray.find((i => i.musicSrc === musicSrc));
    const audioItemIndex = playedAudioArray.findIndex((i => i.musicSrc === musicSrc));
    const newAudioItem = {
      ...audioItem,
      played:audioItem.played + 1
    }
    const newPLayedAudioArray = [
      ...playedAudioArray.slice(0,audioItemIndex),
      newAudioItem,
      ...playedAudioArray.slice(audioItemIndex + 1, playedAudioArray.length)
    ];
    setPlayedAudioArray(newPLayedAudioArray);

    if (playedAudioArray[audioItemIndex].played === 0){

      let audioStartUrlPrefix = window.location.href;
      if (audioStartUrlPrefix.substr(audioStartUrlPrefix.length - 1) !== "/" ) audioStartUrlPrefix += "/";

      const audioStartUrl = audioStartUrlPrefix + 'startmediaviewajax?collection_id='+audioItem.collection_id+'&file_id='+audioItem.file_id+'&type_id=2';

      $.ajax({url: audioStartUrl}).done(function(res) { 
        console.log(res);
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
    setPlayedAudioArray(newPLayedAudioArray);

    if  (playedAudioArray[audioItemIndex].stopped === 0){

      let audioStopPrefixUrl = window.location.href;
      if (audioStopPrefixUrl.substr(audioStopPrefixUrl.length - 1) !== "/" ) audioStopPrefixUrl += "/";

      const audioStopUrl =  audioStopPrefixUrl + "stopmediaviewajax?media_view_id=" + playedAudioArray[audioItemIndex].mediaViewId;

      console.log(audioStopUrl);

      $.ajax({url: audioStopUrl}).done(function(res) { 
        console.log(res);
      });
    }
  }

  const options = {
      //audio lists model
      audioLists: props.slide.items,
      audioListsPanelVisible:showPlaylist,
      //default play index of the audio player  [type `number` default `0`]
      defaultPlayIndex: 0,
      //if you want dynamic change current play audio you can change it [type `number` default `0`]
      // playIndex: 0,
      //color of the music player theme    [ type `string: 'light' or 'dark'  ` default 'dark' ]
      theme: "dark",
      // Specifies movement boundaries. Accepted values:
      // - `parent` restricts movement within the node's offsetParent
      //    (nearest node with position relative or absolute), or
      // - a selector, restricts movement within the targeted node
      // - An object with `left, top, right, and bottom` properties.
      //   These indicate how far in each direction the draggable
      //   can be moved.
      bounds: "parent",
      //Whether to load audio immediately after the page loads.  [type `Boolean | String`, default `false`]
      //"auto|metadata|none" "true| false"
      preload: false,
      //Whether the player's background displays frosted glass effect  [type `Boolean`, default `false`]
      glassBg: false,
      //The next time you access the player, do you keep the last state  [type `Boolean` default `false`]
      remember: false,
      //The Audio Can be deleted  [type `Boolean`, default `true`]
      remove: false,
      //audio controller initial position    [ type `Object` default '{top:0,left:0}' ]
      defaultPosition: {
        top: 300,
        left: 120
      },
      // play mode text config of the audio player
      playModeText: {
        order: "order",
        orderLoop: "loop",
        singleLoop: "single loop",
        shufflePlay: "shuffle"
      },
      //audio controller open text  [ type `String | ReactNode` default 'open']
      openText: "open",
      //audio controller close text  [ type `String | ReactNode` default 'close']
      closeText: "close",
      //audio theme switch checkedText  [ type `String | ReactNode` default '-']
      checkedText: "dark",      
      //audio theme switch unCheckedText [ type `String | ReactNode` default '-']
      unCheckedText: "light",
      // audio list panel show text of the playlist has no songs [ type `String` | ReactNode  default 'no music']
      notContentText: "No Music",
      panelTitle: props.product.title,
      defaultPlayMode: "order",
      //audio mode        mini | full          [type `String`  default `mini`]
      mode: "full",
        // [ type `Boolean` default 'false' ]
        // The default audioPlay handle function will be played again after each pause, If you only want to trigger it once, you can set 'true'
      once: true,
      //Whether the audio is played after loading is completed. [type `Boolean` default 'true']
      autoPlay: false,
      //Whether you can switch between two modes, full => mini  or mini => full   [type 'Boolean' default 'true']
      toggleMode: false,
      //audio cover is show of the "mini" mode [type `Boolean` default 'true']
      showMiniModeCover: true,   
      //audio playing progress is show of the "mini"  mode
      showMiniProcessBar: false,
      //audio controller is can be drag of the "mini" mode     [type `Boolean` default `true`]
      drag: false,
      //drag the audio progress bar [type `Boolean` default `true`]
      seeked: true,
      //audio controller title [type `String | ReactNode`  default <FaHeadphones/>]
      // controllerTitle: <FaHeadphones />,
      //Displays the audio load progress bar.  [type `Boolean` default `true`]
      showProgressLoadBar: true,
      //play button display of the audio player panel   [type `Boolean` default `true`]
      showPlay: true,
      //reload button display of the audio player panel   [type `Boolean` default `true`]
      showReload: false,
      //download button display of the audio player panel   [type `Boolean` default `true`]
      showDownload: false,
      //loop button display of the audio player panel   [type `Boolean` default `true`]
      showPlayMode: false,
      //theme toggle switch  display of the audio player panel   [type `Boolean` default `true`]
      showThemeSwitch: true,
      //lyric display of the audio player panel   [type `Boolean` default `false`]
      showLyric: false,
      //Extensible custom content       [type 'Array' default '[]' ]
      extendsContent: [],
      //default volume of the audio player [type `Number` default `100` range `0-100`]
      defaultVolume: 50,
      //playModeText show time [type `Number(ms)` default `700`]
      playModeShowTime: 600,
      //Whether to try playing the next audio when the current audio playback fails [type `Boolean` default `true`]
      loadAudioErrorPlayNext: true,
      //Music is downloaded handle
      //onAudioDownload(audioInfo) { console.log("audio download", audioInfo); },
      //audio play handle
      onAudioPlay(audioInfo) {
          $('.play-btn[title="Click to play"]').trigger("click");
          // onReportAudioPlay(audioInfo);
      },
      //audio pause handle
      onAudioPause(audioInfo) { 
        console.log("audio pause", audioInfo); 
        //onReportAudioStop(audioInfo)
      },
      //When the user has moved/jumped to a new location in audio
      onAudioSeeked(audioInfo) { console.log("audio seeked", audioInfo); },
      //When the volume has changed  min = 0.0  max = 1.0
      onAudioVolumeChange(currentVolume) { console.log("audio volume change", currentVolume); },
      //The single song is ended handle
      onAudioEnded(audioInfo) { console.log("audio ended", audioInfo); },
      //audio load abort The target event like {...,audioName:xx,audioSrc:xx,playMode:xx}
      onAudioAbort(e) { console.log("audio abort", e); },
      //audio play progress handle
      onAudioProgress(audioInfo) { /*console.log('audio progress',audioInfo);*/ },
      //audio reload handle
      onAudioReload(audioInfo) { console.log("audio reload:", audioInfo);},
      //audio load failed error handle
      onAudioLoadError(e) { console.log("audio load err", e); },
      //theme change handle
      onThemeChange(theme) { console.log("theme change:", theme); },
      //audio lists change
      onAudioListsChange(currentPlayId, audioLists, audioInfo) {
        console.log("[currentPlayId] audio lists change:", currentPlayId);
        console.log("[audioLists] audio lists change:", audioLists);
        console.log("[audioInfo] audio lists change:", audioInfo);
        console.log(audioInfo)
      },
      onAudioPlayTrackChange(currentPlayId, audioLists, audioInfo) { console.log( "audio play track change:", currentPlayId, audioLists, audioInfo ); },
      onPlayModeChange(playMode) { console.log("play mode change:", playMode); },
      onModeChange(mode) { console.log("mode change:", mode); },
      onAudioListsPanelChange(panelVisible) {
        const newShowPlayListValue = showPlaylist === true ? false : true;
        setShowPlaylist(newShowPlayListValue);
      }, 
      onAudioListsDragEnd(fromIndex, endIndex) {
        console.log("audio lists drag end:", fromIndex, endIndex);
      },
      onAudioLyricChange(lineNum, currentLyric) {
        console.log("audio lyric change:", lineNum, currentLyric);
      }
  };

  let musicPlayerWrapperCssClass = "desktop ";
  let sponsorDetailsDisplay;
  if (isMobile === true) musicPlayerWrapperCssClass = "mobile ";
  if (showPlaylist === true) {
    musicPlayerWrapperCssClass += " show-playlist";
    if (randomSupporter){
      sponsorDetailsDisplay = (
        <div id="music-sponsor-display">
          <span>made possible by supporters like</span>
          <span className="sponsor-avatar">
            <a href={"/u/" + randomSupporter.username}>
              <img src={randomSupporter.profile_image_url}/>
            </a>
          </span>
          <span>
            {randomSupporter.username}
          </span>
        </div>
      )
    }
  }

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
  if (isPlaying === true) playButtonDisplay = <span  onClick={() => onPauseClick()}>{pauseButtonElement}</span>
  else playButtonDisplay = <span  onClick={() => onPlayClick(playIndex)}>{playButtonElement}</span>

  return (
      <div id="music-player-wrapper" className={musicPlayerWrapperCssClass}>
        <ReactJkMusicPlayer {...options} />
        <div className="music-player-controls">
          <span  onClick={() => onPrevTrackPlayClick()}>{prevButtonElement}</span>
          {playButtonDisplay}
          <span   onClick={() => onNextTrackPlayClick()}>{nextButtonElement}</span>
        </div>
        {sponsorDetailsDisplay}
      </div>
  )
}

export default MusicPlayerWrapper;