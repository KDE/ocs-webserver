import React from 'react';
import { 
    Player, 
    ControlBar, 
    BigPlayButton,
    VolumeMenuButton, 
    LoadingSpinner,
    CurrentTimeDisplay,
    DurationDisplay } from 'video-react';
import {isMobile} from 'react-device-detect';

class VideoPlayerWrapper extends React.Component {
    constructor(props, context){
        super(props, context);

        let hostLocation = window.location.href;
        if (!hostLocation.endsWith('/')) hostLocation += "/";

        this.state = {
            source:this.props.slide.url_preview,
            videoStarted:false,
            videoStopped:false,
            videoStartUrl:hostLocation + "startvideoajax?collection_id="+this.props.slide.collection_id+"&file_id="+this.props.slide.file_id,
            videoStopUrl:hostLocation + "stopvideoajax?media_view_id="
        }
        console.log(this.state);
        this.onCinemaModeClick = this.onCinemaModeClick.bind(this);
        this.play = this.play.bind(this);
        this.pause = this.pause.bind(this);
    }

    componentDidMount() {
        this.refs.player.subscribeToStateChange(this.handleStateChange.bind(this));
    }

    handleStateChange(state, prevState) {
        this.setState({ player: state },function(){
            if (this.state.player){
                if (this.state.player.hasStarted && this.state.videoStarted === false){
                    this.setState({videoStarted:true},function(){
                        const self = this;
                        $.ajax({url: this.state.videoStartUrl}).done(function(res) {
                            self.setState({videoStopUrl:self.state.videoStopUrl + res.MediaViewId})
                        });
                    });
                } 
                if (this.state.player.paused && this.state.videoStarted === true && this.state.videoStopped === false){
                    this.setState({videoStopped:true},function(){
                        $.ajax({url: this.state.videoStopUrl}).done(function(res) {
                            // console.log(res)
                        });
                    });
                }
                if (state.isFullscreen === false && prevState.isFullscreen === true) this.props.onUpdateDimensions()
                if (state.isFullscreen !== prevState.isFullscreen) this.props.onFullScreenToggle(state.isFullscreen)
            }
        });
    }

    shouldComponentUpdate(nextProps, nextState){
        if (nextProps.playVideo === false) this.pause()
        return true;
    }

    onCinemaModeClick(){
        this.props.onCinemaModeClick()
    }

    play() {
        this.refs.player.play();
    }
    
    pause() {
        this.refs.player.pause();
    }
    
    render(){   
        let videoPlayerDisplay;
        if (this.state.source){
            let controlBarDisplay;
            if (isMobile){
                controlBarDisplay = (
                    <ControlBar disabled/>
                )  
            } else {
                controlBarDisplay = (
                    <ControlBar className="custom-video-player">
                        <CurrentTimeDisplay order={4.1} />
                        <DurationDisplay order={7.1} />
                        <VolumeMenuButton vertical order={7.2} />
                        <a className="cinema-mode-button" onClick={this.onCinemaModeClick} order={7.3}><span></span></a>
                    </ControlBar>
                )
            }
            videoPlayerDisplay = (
                <Player
                    poster={this.props.slide.url_thumb}
                    ref="player"
                    fluid={false}
                    height={this.props.height}
                    width={this.props.width}
                    preload={"auto"}
                    src={this.state.source}>
                        <BigPlayButton position="center" />
                        <LoadingSpinner />
                        {controlBarDisplay}
                </Player>            
            )
        }
    
        return (
            <div className="react-player-container">
                {videoPlayerDisplay}
            </div>
        )
    }
}

export default VideoPlayerWrapper;