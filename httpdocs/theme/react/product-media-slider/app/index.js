import React from 'react';
import ReactDOM from 'react-dom';
import StoreContextProvider,{Context} from './context-provider.js';

function App(){

  console.log(window.product);
  console.log(window.filesJson)
  console.log(window.xdgTypeJson)

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

const rootElement = document.getElementById("product-media-carousel-container");
ReactDOM.render(<AppContainer />, rootElement);