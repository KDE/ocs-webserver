import React, { useState, useRef } from 'react';
import { Player, ControlBar, BigPlayButton, ReplayControl, ForwardControl, VolumeMenuButton, LoadingSpinner } from 'video-react';

class VideoPlayerWrapper extends React.Component {
    constructor(props, context){
        super(props, context);
        this.state = {
            source:this.props.slide.url.replace(/%2F/g,'/').replace(/%3A/g,':'),
            videoStarted:false,
            videoStopped:false,
            videoStartUrl:window.location.href + "startvideoajax?collection_id="+this.props.slide.collection_id+"&file_id="+this.props.slide.file_id,
            videoStopUrl:window.location.href + "stopvideoajax?media_view_id="
        }
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
                    console.log('report stop video' + this.state.videoStopUrl)
                    this.setState({videoStopped:true},function(){
                        $.ajax({url: this.state.videoStopUrl}).done(function(res) {
                            console.log(res)
                        });
                    });
                }
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
            videoPlayerDisplay = (
                <Player
                    ref="player"
                    fluid={false}
                    height={this.props.height}
                    width={this.props.width}
                    playsInline
                    src={this.state.source}>
                        <BigPlayButton position="center" />
                        <LoadingSpinner />
                        <ControlBar autohide={false} className="custom-video-player">
                            <VolumeMenuButton vertical />
                            <a className="cinema-mode-button" onClick={this.onCinemaModeClick} order={8}>cinema</a>
                        </ControlBar>
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