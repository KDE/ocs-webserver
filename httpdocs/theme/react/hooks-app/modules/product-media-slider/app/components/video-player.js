import React from 'react';
import { 
    Player, 
    ControlBar, 
    BigPlayButton,
    LoadingSpinner,
    VolumeMenuButton,
    CurrentTimeDisplay,
    DurationDisplay } from 'video-react';

import LoadingSpinners from '../../../common/loading-spinners';
import './../../style/video-react.css';

class VideoPlayerWrapper extends React.Component {
    constructor(props, context){
        super(props, context);

        let hostLocation = window.location.href;
        if (!hostLocation.endsWith('/')) hostLocation += "/";

        this.state = {
            source:this.props.slide.url_preview,
            width:this.props.width,
            videoStarted:false,
            videoStopped:false,
            videoStartUrl:hostLocation + "startvideoajax?collection_id="+this.props.slide.collection_id+"&file_id="+this.props.slide.file_id,
            videoStopUrl:hostLocation + "stopvideoajax?media_view_id=",
            initialVolumeSet:false
        }

        this.onCinemaModeClick = this.onCinemaModeClick.bind(this);
        this.onProgressBarClick = this.onProgressBarClick.bind(this);
        this.onVolumeBarClick = this.onVolumeBarClick.bind(this);

        this.play = this.play.bind(this);
        this.pause = this.pause.bind(this);
    }

    componentDidMount() {
        if (this.refs.player){
            this.refs.player.subscribeToStateChange(this.handleStateChange.bind(this));
            const parent = document.getElementById('video-'+this.props.slide.file_id);
            parent.getElementsByClassName('video-react-progress-holder video-react-slider-horizontal video-react-slider')[0].addEventListener('click', this.onProgressBarClick);
            parent.getElementsByClassName('video-react-volume-bar')[0].addEventListener('click', this.onVolumeBarClick);

            
        }
    }

    onProgressBarClick(params) {
        const divWidth = document.getElementById('video-'+this.props.slide.file_id).getElementsByClassName('video-react-progress-holder video-react-slider-horizontal video-react-slider')[0].offsetWidth;
        const precentage = (params.offsetX / divWidth ) * 100;
        const currentPlayerState = this.refs.player.getState();
        const currentTime = currentPlayerState.player.currentTime;
        const expectedTime = (currentPlayerState.player.duration / 100) * precentage;
        if (currentTime !== expectedTime) this.refs.player.seek(expectedTime);
    }

    onVolumeBarClick(params){
        const divWidth = document.getElementById('video-'+this.props.slide.file_id).getElementsByClassName('video-react-volume-bar')[0].offsetWidth;
        const precentage = (params.offsetX / divWidth ) * 100;
        const currentPlayerState = this.refs.player.getState();
        const currentVolume = currentPlayerState.player.volume;
        const expectedVolume = precentage / 100;
        if (currentVolume !== expectedVolume) this.refs.player.volume = expectedVolume;
    }

    handleStateChange(state, prevState) {
        this.setState({ player: state },function(){
            if (this.state.player){
                if (this.state.initialVolumeSet === false){
                    const { player } = this.refs.player.getState();
                    this.refs.player.volume = 0.33;
                    this.setState({initialVolumeSet:true})
                }
                if (typeof this.state.player.videoWidth !== undefined) {
                    setTimeout(() => {
                        this.setState({videoRenderMask:false}) 
                    }, 1000);
                }
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
        if (nextProps.playVideo === false){
            this.pause()
        }
        return true;
    }

    onCinemaModeClick(){
        this.props.onCinemaModeClick()
    }

    play() {
        if (this.refs.player) this.refs.player.play();
    }
    
    pause() {
        if (this.refs.player) this.refs.player.pause();
    }
    
    render(){   

        let videoPlayerDisplay;

        if (this.state.source){
            let width = this.props.width;
            if (this.props.cinemaMode === false){
                if (this.state.player){
                    const dimensionsRatio =  this.props.height / this.state.player.videoHeight;
                    if ((this.state.player.videoWidth * dimensionsRatio) < this.props.width) width = this.state.player.videoWidth * dimensionsRatio;
                }
            }
            videoPlayerDisplay = (
                <Player
                    ref="player"
                    fluid={false}
                    height={this.props.height}
                    width={width}
                    preload={"auto"}
                    src={this.state.source}>
                        <BigPlayButton position="center" />
                        <LoadingSpinner />
                            <ControlBar className="custom-video-player">
                                <VolumeMenuButton order={4.2} />
                                <CurrentTimeDisplay order={4.3} />
                                <DurationDisplay order={7.1} />
                                <a className="cinema-mode-button" onClick={this.onCinemaModeClick} order={7.3}><span></span></a>
                            </ControlBar>
                </Player>            
            )
        }
        let videoRenderMask = <div className="video-render-mask" style={{paddingTop:"140px"}}><LoadingSpinners type={"ripples"}/></div>
        if (this.state.videoRenderMask === false){ videoRenderMask = ''; }
        return (
            <div className={"react-player-container"} id={'video-'+this.props.slide.file_id}>
                {videoRenderMask}
                {videoPlayerDisplay}
            </div>
        )
    }
}

export default VideoPlayerWrapper;