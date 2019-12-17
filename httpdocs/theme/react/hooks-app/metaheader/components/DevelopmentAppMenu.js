import React , {useEffect, useState, useRef,useContext} from 'react'
import MyButton from './function/MyButton';
import {MetaheaderContext} from '../contexts/MetaheaderContext';


const DevelopmentAppMenu = () => {
  const {state} = useContext(MetaheaderContext);
  const [dropdownClass, setDropdownClass] = useState('');  
  const [notification, setNotification] = useState(false);  
  const [notification_count, setNotification_count] = useState(0);
  const toggleEl = useRef(null);  
  
  useEffect(() => {     
    document.addEventListener('mousedown',handleClick, false);
    return () => {        
        document.removeEventListener('mousedown',handleClick, false);
    };
  },[dropdownClass])

  
  useEffect(() => {
    if(state.user){
        let url = state.baseUrl+'/membersetting/notification';
        fetch(url,{
                  mode: 'cors',
                  credentials: 'include'
                  })
        .then(response => response.json())
        .then(data => {            
            if(data.notifications){
              const nots = data.notifications.filter(note => note.read==false);
              if(nots.length>0 && notification_count !== nots.length)
              {
                  setNotification(true);
                  setNotification_count(nots.length);                
              }
            }
        });
     }     
  }, [])
  

  const handleClick= e => {          
    let cls = "";
    if (toggleEl.current.contains(e.target)){                 
      if (dropdownClass === "open"){              
        if (e.target.className === "btn btn-default dropdown-toggle" || e.target.className === "th-icon"){
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
    <li ref={toggleEl} id="development-app-menu-container">
        <div className={"user-dropdown " + dropdownClass}>
          <button
            id="developmentDropdownBtn"
            className="btn btn-default dropdown-toggle" type="button" >
            <span className="th-icon"></span>            
            {notification && 
              <span className="badge-notification">{notification_count}</span>
            }            
          </button>
          <ul id="user-context-dropdown" className="dropdown-menu dropdown-menu-right">                          
          
              <MyButton id="storage-link-item"
                      url={state.myopendesktopUrl}
                      label="Storage" />
              <MyButton id="calendar-link-item"
                      url={state.myopendesktopUrl+"/index.php/apps/calendar/"}
                      label="Calendar" />
              <MyButton id="contacts-link-item"
                      url={state.myopendesktopUrl+"/index.php/apps/contacts/"}
                      label="Contacts" />
                <li id="messages-link-item">
                    <a href={state.forumUrl+"/u/"+state.user.username+"/messages"}>
                      <div className="icon"></div>
                      <span>PM</span>
                      {notification && 
                          <span className="badge-notification">{notification_count}</span>
                        } 
                    </a>
                </li>
                  
                <MyButton id="music-link-item"
                              url={state.musicopendesktopUrl}
                              label="Music" />
             
          </ul>
          
        </div>
      </li>
  )
}

export default DevelopmentAppMenu


