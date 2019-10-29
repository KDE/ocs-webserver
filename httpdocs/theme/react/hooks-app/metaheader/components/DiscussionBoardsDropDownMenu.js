import React , {useEffect, useState, useRef,useContext} from 'react'
import CommunityMenuItems from './function/CommunityMenuItems';
import {MetaheaderContext} from '../contexts/MetaheaderContext';

const DiscussionBoardsDropDownMenu = () => {
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
            if (e.target.className === "discussion-menu-link-item"){
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
  return (
      <li ref={toggleEl}  id="discussion-boards" className={dropdownClass}>
        <a className="discussion-menu-link-item">Community &#8964;</a>
        <ul className="discussion-menu dropdown-menu dropdown-menu-right">
          <CommunityMenuItems baseUrl={state.baseUrl}
                              baseUrlStore = {state.baseUrlStore}
                              forumUrl = {state.forumUrl}  />
        </ul>
      </li>
  )
}

export default DiscussionBoardsDropDownMenu


