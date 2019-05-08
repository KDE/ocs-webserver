import React from 'react';
import ReactDOM from 'react-dom';
import StoreContextProvider,{Context} from './context-provider.js';

function App(){
  return (
    <main id="media-player">
        <div>media player</div>
    </main>
  )
}

function AppContainer(){
  return (
    <StoreContextProvider>
      <App/>
    </StoreContextProvider>
  );
}

const rootElement = document.getElementById("media-player-container");
ReactDOM.render(<AppContainer />, rootElement);