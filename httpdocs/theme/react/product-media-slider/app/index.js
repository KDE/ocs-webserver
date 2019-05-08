import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import StoreContextProvider,{Context} from './context-provider';

function App(){

  const [ appState, appDispatch ] = React.useContext(Context);
  const { loading, setLoading } = useState()

  React.useEffect(() => { initProductMediaSlider() },[])

  // init product media slider
  function initProductMediaSlider(){
    appDispatch({type:'SET_PRODUCT',product:window.product});
    setLoading(false);
  }

  let appDisplay;
  if (loading === false) appDisplay = <div>media player</div>

  console.log(appState);

  return (
    <main id="media-player">
      {appDisplay}
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