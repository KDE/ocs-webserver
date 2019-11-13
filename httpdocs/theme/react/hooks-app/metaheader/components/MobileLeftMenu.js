import React , {useEffect, useState, useRef,useContext} from 'react'
import MobileLeftSidePanel from './MobileLeftSidePanel';

const MobileLeftMenu = (props) => {
  const [overlayClass, setOverlayClass] = useState('');
  const toggleEl = useRef(null);  
  useEffect(() => {        
    
    document.addEventListener('mousedown',handleClick, false);
    document.addEventListener('touchend', handleClick, false);
    return () => {
        
        document.removeEventListener('mousedown',handleClick, false);
        document.removeEventListener('touchend',handleClick, false);
    };
  },[overlayClass])


  const handleClick = e =>{
    let cls = "";
    if (toggleEl.current.contains(e.target)){
      
      if (overlayClass === "open"){
        if (e.target.id === "left-side-overlay" || e.target.id === "menu-toggle-item"){
          cls = "";
        } else {
          cls = "open";
        }
      } else {
        cls = "open";
      }
    }

    setTimeout(function () {
      setOverlayClass(cls);            
    }, 200);
  }

  return (
     <div ref={toggleEl}  id="metaheader-left-mobile" className={overlayClass} style={{'position':'absolute'}}>
        <a className="menu-toggle" id="menu-toggle-item"></a>
        <div id="left-side-overlay">
          <MobileLeftSidePanel />
        </div>
      </div>
  )
}

export default MobileLeftMenu

