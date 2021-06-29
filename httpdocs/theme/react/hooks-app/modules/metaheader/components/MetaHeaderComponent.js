import "core-js/shim";
import "regenerator-runtime/runtime";
import React , {useEffect, useState, useContext} from 'react'
import MobileLeftMenu from './MobileLeftMenu';
import DomainsMenu from './DomainsMenu';
import UserMenu from './UserMenu';
import SearchForm from "./SearchForm";
import Axios from 'axios';
import {MetaheaderContext} from '../contexts/MetaheaderContext';

const MetaHeaderComponent = (props) => {
  
  const {state, setState} = useContext(MetaheaderContext);

  const [device, setDevice] = useState('large');
  const initialMetaMenuThemeValue = state.metamenuTheme ? state.metamenuTheme : '';
  const [metamenuTheme, setMetamenuTheme] = useState(initialMetaMenuThemeValue);
  const initialSiteThemeValue = state.contentTheme ? state.contentTheme : '';
  const [siteTheme, setSiteTheme ] = useState(initialSiteThemeValue);

  useEffect(() => {   
    updateDimensions(); 
    window.addEventListener("resize", updateDimensions);
    window.addEventListener("orientationchange",updateDimensions);
    //$( "body" ).addClass( "theme" );
    document.body.classList.add("theme");

    if (state.contentTheme === 'content-theme-dark'){
      //$( "body" ).addClass( "dark-theme" );
      document.body.classList.remove("theme-light");
      document.body.classList.add("dark-theme", "theme-dark");
    } else {
      //$( "body" ).removeClass( "dark-theme" );
      document.body.classList.remove("dark-theme", "theme-dark");
      document.body.classList.add("theme-light");
    }
    return () => {
      window.removeEventListener("resize", updateDimensions);
      window.removeEventListener("orientationchange", updateDimensions);
    };
  }, []);

  const updateDimensions = e => {
    
    const width = window.innerWidth;
    let device;
    if (width >= 1015) {
      device = "large";
      // } else if (width < 1015 && width >= 730) {
      //  device = "mid";
      // } else if (width < 730) {
    } else {
      device = "tablet";
    }
    
    setDevice(device);    
  }

  const onSwitchStyle = evt => {        
    let url = "/membersetting/setsettings";           
    const isChecked = evt.target.checked;
    let formData = new FormData();
    formData.set('itemid', 2);
    formData.set('itemvalue',evt.target.checked ? '1' : '0');
    Axios.post(url,formData)
      .then(result => {               
          const newSiteTheme = isChecked ? 'content-theme-dark': '';
          setSiteTheme(newSiteTheme);
          if (newSiteTheme === 'content-theme-dark'){
            document.body.classList.add("dark-theme", "theme-dark");
            document.body.classList.remove("theme-light");
            //$( "body" ).addClass( "dark-theme" );
          } else {
            //$( "body" ).removeClass( "dark-theme" );
            document.body.classList.remove("dark-theme", "theme-dark");
            document.body.classList.add("theme-light");
          }
      });   
  }

  const onSwitchMetaHeaderStyle = evt => {    
    let url ="/membersetting/setsettings";         
    const isChecked = evt.target.checked;    
    let formData = new FormData();
    formData.set('itemid', 1);
    formData.set('itemvalue',evt.target.checked ? '1' : '0');
    Axios.post(url,formData)
      .then(result => {               
        setMetamenuTheme(isChecked?'metamenu-theme-dark':'');
      })
  }

  let domainsMenuDisplay;
    if (device === "tablet") {
      domainsMenuDisplay = (
        <MobileLeftMenu />
      )
    } else {

      domainsMenuDisplay = (
        <DomainsMenu
          device={device}
          onSwitchStyle={onSwitchStyle}
          onSwitchMetaHeaderStyle={onSwitchMetaHeaderStyle}
          onSwitchStyleChecked={metamenuTheme?true:false}
          siteTheme={siteTheme}
          metamenuTheme={metamenuTheme}
          config={props.config}
        />
      )
    }
    const metamenuCls = `metamenu ${metamenuTheme}`;
    let paraChecked = false;
    if (metamenuTheme) {
      paraChecked = true;
    }
  return (    
    
       <nav id="metaheader-nav" className="metaheader" style={{borderBottom:"1px solid #ccc"}}>
          <div style={{ "display": "block" }} className={metamenuCls}>
            {domainsMenuDisplay}
            <UserMenu
              device={device}              
              onSwitchStyle={onSwitchStyle}
              onSwitchStyleChecked={paraChecked}
              onSwitchMetaHeaderStyle={onSwitchMetaHeaderStyle}
              siteTheme={siteTheme}
              metamenuTheme={metamenuTheme}
            />
            <SearchForm />
          </div>
        </nav>
      
  )
}

export default MetaHeaderComponent
