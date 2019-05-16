import React, { useState, useRef } from 'react';
import { Player, ControlBar, BigPlayButton, ReplayControl, ForwardControl, VolumeMenuButton, LoadingSpinner } from 'video-react';

class VideoPlayerWrapper extends React.Component {
    constructor(props, context){
        super(props, context);
        this.state = {
            source:this.props.source.replace(/%2F/g,'/').replace(/%3A/g,':'),
            videoStarted:false,
            videoStopped:false
        }
        this.onCinemaModeClick = this.onCinemaModeClick.bind(this);
        this.play = this.play.bind(this);
        this.pause = this.pause.bind(this);
    }

    componentDidMount() {
        // subscribe state change
        this.refs.player.subscribeToStateChange(this.handleStateChange.bind(this));
    }

    shouldComponentUpdate(nextProps, nextState){
        if (nextProps.playVideo === false) this.pause()
    }

    handleStateChange(state, prevState) {
        this.setState({ player: state });
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