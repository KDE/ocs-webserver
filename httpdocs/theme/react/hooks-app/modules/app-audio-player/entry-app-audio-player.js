import React from 'react';
import ReactDOM from 'react-dom';
import AudioPlayerWrapper from './app/audio-player';

const rootElement = document.getElementById("app-audio-player-container");
ReactDOM.render(<AudioPlayerWrapper />, rootElement);