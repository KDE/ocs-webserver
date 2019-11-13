import React, { useState } from "react";
import ReactJkMusicPlayer from "react-jinke-music-player";
import {isMobile} from 'react-device-detect';

function MusicPlayerWrapper(props){

  const [ showPlaylist, setShowPlaylist ] = useState(isMobile === true ? false : true);
  let initialPLayedAudioArray = []
  props.slide.items.forEach(function(i,index){
    let pl = 0;
    if (index === 0) pl = -1;
    const pa = {
      ...i,
      played:pl,
      stopped:0
    }
    initialPLayedAudioArray.push(pa);
  })
  const [ playedAudioArray, setPlayedAudioArray ] = useState(initialPLayedAudioArray);
  const [ randomSupporter, setRandomSupporter ] = useState();

  React.useEffect(() => {
    getRandomMusicsupporter();
  },[])

  function getRandomMusicsupporter(){
    const suffix = window.location.host.endsWith('cc') ? 'cc' :  window.location.host.endsWith('com') ||  window.location.host.endsWith('org') ? 'com' : 'cc';
    $.ajax({url: "https://www.pling."+suffix+"/json/fetchrandomsupporter/s/3"}).done(function(res) { 
      setRandomSupporter(res.supporter)
    });    
  }

  function onReportAudioPlay(audioInfo){
    const audioItem = playedAudioArray.find((i => i.musicSrc === audioInfo.musicSrc));
    const audioItemIndex = playedAudioArray.findIndex((i => i.musicSrc === audioInfo.musicSrc));
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
      console.log(window);
      const audioStartUrl = 'startmediaviewajax?collection_id='+audioItem.collection_id+'&file_id='+audioItem.file_id+'&type_id=2';
      console.log(audioStartUrl);
      $.ajax({url: audioStartUrl}).done(function(res) { 

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

  function onReportAudioStop(audioInfo){
    const audioItem = playedAudioArray.find((i => i.musicSrc === audioInfo.musicSrc));
    const audioItemIndex = playedAudioArray.findIndex((i => i.musicSrc === audioInfo.musicSrc));
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
    // console.log('stppped - ' + playedAudioArray[audioItemIndex].stopped)
    if  (playedAudioArray[audioItemIndex].stopped === 0){
      const audioStopUrl = "stopmediaviewajax?media_view_id=" + playedAudioArray[audioItemIndex].mediaViewId;
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
      showPlayMode: true,
      //theme toggle switch  display of the audio player panel   [type `Boolean` default `true`]
      showThemeSwitch: true,
      //lyric display of the audio player panel   [type `Boolean` default `false`]
      showLyric: false,
      //Extensible custom content       [type 'Array' default '[]' ]
      extendsContent: [],
      //default volume of the audio player [type `Number` default `100` range `0-100`]
      defaultVolume: 100,
      //playModeText show time [type `Number(ms)` default `700`]
      playModeShowTime: 600,
      //Whether to try playing the next audio when the current audio playback fails [type `Boolean` default `true`]
      loadAudioErrorPlayNext: true,
      //Music is downloaded handle
      //onAudioDownload(audioInfo) { console.log("audio download", audioInfo); },
      //audio play handle
      onAudioPlay(audioInfo) {
          $('.play-btn[title="Click to play"]').trigger("click");
          onReportAudioPlay(audioInfo);
      },
      //audio pause handle
      onAudioPause(audioInfo) { 
        console.log("audio pause", audioInfo); 
        onReportAudioStop(audioInfo)
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
          <span>This music is sponsored by</span>
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

  return (
      <div id="music-player-wrapper" className={musicPlayerWrapperCssClass}>
        <ReactJkMusicPlayer {...options} />
        {sponsorDetailsDisplay}
      </div>
  )
}

export default MusicPlayerWrapper;