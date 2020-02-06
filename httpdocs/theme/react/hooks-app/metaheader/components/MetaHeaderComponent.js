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
    $( "body" ).addClass( "theme" );

    if (state.contentTheme === 'content-theme-dark'){
      $( "body" ).addClass( "dark-theme" );
    } else {
      $( "body" ).removeClass( "dark-theme" );
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
    let url = "https://" + window.location.hostname +"/membersetting/setsettings/itemid/2/itemvalue/"+ (evt.target.checked ? '1' : '0');    
    console.log(url);
    const isChecked = evt.target.checked;    
    Axios.get(url)
      .then(result => {
        console.log(result); 
        const newSiteTheme = isChecked ? 'content-theme-dark': '';
        setSiteTheme(newSiteTheme);
        if (newSiteTheme === 'content-theme-dark'){
          $( "body" ).addClass( "dark-theme" );
        } else {
          $( "body" ).removeClass( "dark-theme" );
        }
    })
  }

  const onSwitchMetaHeaderStyle = evt => {     
    let url = "https://" + window.location.hostname +"/membersetting/setsettings/itemid/1/itemvalue/"+ (evt.target.checked ? '1' : '0');
    console.log(url);  
    const isChecked = evt.target.checked;    
    Axios.get(url)
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
        />
      )
    }
    const metamenuCls = `metamenu ${metamenuTheme}`;
    let paraChecked = false;
    if (metamenuTheme) {
      paraChecked = true;
    }
  return (    
    
       <nav id="metaheader-nav" className="metaheader">
          <div style={{ "display": "block" }} className={metamenuCls}>
            {domainsMenuDisplay}
            <UserMenu
              device={device}              
              onSwitchStyle={onSwitchStyle}
              onSwitchStyleChecked={paraChecked}
              onSwitchMetaHeaderStyle={onSwitchMetaHeaderStyle}
            />
            <SearchForm />
          </div>
        </nav>
      
  )
}

export default MetaHeaderComponent
