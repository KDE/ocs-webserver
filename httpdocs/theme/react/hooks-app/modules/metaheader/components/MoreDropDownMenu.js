import React,{useContext, useState,useRef,useEffect} from 'react';
import {MetaheaderContext} from '../contexts/MetaheaderContext';

const MoreDropDownMenu = () => {
  const {state} = useContext(MetaheaderContext);
  const [dropdownClass, setDropdownClass] = useState('');    
  const toggleEl = useRef(null);  
  useEffect(() => {        
    document.addEventListener('mousedown',handleClick, false);
    return () => {
        
        document.removeEventListener('mousedown',handleClick, false);
    };
  },[dropdownClass])

  const handleClick= e => {          
    let cls = "";
    if (toggleEl.current.contains(e.target)){                 
      if (dropdownClass === "open"){              
        if (e.target.className === "more-menu-link-item"){
            cls = "";
        } else {
            cls = "open";
        }
      } else {
        cls = "open";
      }
    }        
    setDropdownClass(cls);              
  }


  let faqLinkItem, apiLinkItem, aboutLinkItem,aboutPlingItem, aboutopencodeItem;

    aboutPlingItem = (<li><a id="faq" href={state.baseUrl +"/faq-pling"}>FAQ Pling</a></li>);
    aboutopencodeItem = (<li><a id="faq" href={state.baseUrl +"/faq-opencode"}>FAQ Opencode</a></li>);
    aboutLinkItem = (<li><a id="about" href={state.baseUrl +"/about"}>About</a></li>);
    apiLinkItem = (<li><a  href={state.baseUrl +"/ocs-api"}>API</a></li>);
    if (state.isAdmin ){
      faqLinkItem = (<li><a className="popuppanel" id="faq" href={"/plings"}>Plings (admin only)</a></li>);
    }
  return (
    <li ref={toggleEl} id="more-dropdown-menu" className={dropdownClass}>
        <a className="more-menu-link-item">More</a>
        <ul className="dropdown-menu">   
            <li><a href={state.baseUrl + "/community"}>Members</a></li>
            <li><a href={state.forumUrl}>Discussion</a></li>
            <li><a href={state.baseUrlStore + "/support"}>Become a Supporter</a></li>
            <li><a href={state.blogUrl} target="_blank">Blog</a></li>
            {faqLinkItem}
            {apiLinkItem}
            {aboutPlingItem}
            {aboutopencodeItem}
            {aboutLinkItem}
        </ul>
      </li>
  )
}

export default MoreDropDownMenu
