import React , {useEffect, useState, useRef, useContext} from 'react'
import SwitchItem from './function/SwitchItem';
import {MetaheaderContext} from '../contexts/MetaheaderContext';

const UserLoginMenuContainer = (props) => {
  const {state} = useContext(MetaheaderContext);
  
  const [dropdownClass, setDropdownClass] = useState('');    
  const toggleEl = useRef(null);    
  const [gitlabLink, setGitlabLink] = useState(state.gitlabUrl+"/dashboard/issues?assignee_id=");

  useEffect(() => {        
    document.addEventListener('mousedown',handleClick, false);
    return () => {
        
        document.removeEventListener('mousedown',handleClick, false);
    };
  },[dropdownClass])
  
  useEffect(() => {   
    //componentDidMount     
    /*
    Axios.get(state.gitlabUrl+"/api/v4/users?username="+state.user.username)
      .then(result => {       
        setGitlabLink(gitlabLink+result.data[0].id);
      })
      */
     loadData();
  },[]);

  function onUserThemeSwitch(e){
    props.onSwitchStyleChecked(e)
  }

  function onMetaHeaderThemeSwitch(e){
    console.log('on meta header theme switch');
  }

  const loadData = async () => {
    const data = await fetch(`${state.gitlabUrl}/api/v4/users?username=${state.user.username}`);
    const items = await data.json();
    setGitlabLink(gitlabLink+items[0].id);
  }

  const handleClick= e => {          
        let cls = "";
        if (toggleEl.current.contains(e.target)){                 
          if (dropdownClass === "open"){              
            if (e.target.className === "th-icon" || e.target.className === "btn btn-default dropdown-toggle"){
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
    <li id="user-login-menu-container" ref={toggleEl}>
        <div className={"user-dropdown " + dropdownClass}>
          <button
            className="btn btn-default dropdown-toggle"
            type="button"
            id="userLoginDropdown">
            <img className="th-icon" src={state.user.avatar}/>
          </button>
          <ul className="dropdown-menu dropdown-menu-right">
            <li id="user-info-menu-item">
              <div id="user-info-section">
                <div className="user-avatar">
                  <div className="no-avatar-user-letter">
                    <img src={state.user.avatar}/>
                  </div>
                </div>
                <div className="user-details">
                  <ul>
                    <li id="user-details-username"><b>{state.user.username}</b></li>
                    <li id="user-details-email">{state.user.mail}</li>
                  </ul>
                </div>
              </div>
            </li>

            <li id="user-menu" className="buttons user-info-payout">
                <ul className="payout">
                  <li><a href={state.baseUrlStore + "/u/" + state.user.username + "/products"}><div className="icon"></div>Products</a></li>
                  <li><a href={state.gitlabUrl+"/dashboard/projects"}><div className="icon"></div>Projects</a></li>
                  <li><a href={gitlabLink}><div className="icon"></div>Issues</a></li>
                </ul>
            </li>

            <li id="user-menu-products" className="buttons user-info-payout">
                <ul className="payout">
                  <li><a href={state.baseUrlStore+"/product/add"}><div className="icon iconAdd"></div>Add Product</a></li>  
                  <li><a href={state.baseUrlStore+"/my-favourites"}><div className="icon iconFav"></div>My Favourites</a></li>                    
                </ul>
            </li>

            <li id="user-info-payout" className="buttons user-info-payout">
                <ul className="payout">
                  <li><a href={state.baseUrlStore+'/u/'+state.user.username+'/payout'}><div className="icon"></div>My Payout</a></li>
                  <li><a href={state.baseUrlStore+'/u/'+state.user.username+'/funding'}><div className="icon"></div>My Funding</a></li>
                </ul>
            </li>

            <li className="user-settings-item">
             <span className="user-settings-item-title">Theme</span>
               <SwitchItem 
                onSwitchStyle={e => props.onSwitchStyle(e)}
                onSwitchStyleChecked={e => onUserThemeSwitch(e)}
              />
              <span className="user-settings-item-title">dark</span>
            </li>

            <li className="user-settings-item">
             <span className="user-settings-item-title">Metaheader</span>
               <SwitchItem 
                onSwitchStyle={e => onMetaHeaderThemeSwitch(e)}
              />
              <span className="user-settings-item-title">dark</span>
            </li>

            <li className="buttons">
              <a href={state.baseUrl + "/settings/"} className="btn btn-default btn-metaheader"><span>Settings</span></a>
              <a href={state.baseUrl + "/settings/profile"} className="btn btn-default btn-metaheader"><span>Profile</span></a>
              <a href={state.logoutUrl} className="btn btn-default pull-right btn-metaheader"><span>Logout</span></a>
            </li>

          </ul>
        </div>
      </li>
  )
}

export default UserLoginMenuContainer
