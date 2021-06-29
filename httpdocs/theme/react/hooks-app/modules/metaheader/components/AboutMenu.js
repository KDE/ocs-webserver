import React , {useEffect, useState, useRef} from 'react'
import AboutMenuItems from './function/AboutMenuItems';

const AboutMenu = () => {
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
            if (e.target.className === "about-menu-link-item"){
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
      <li ref={toggleEl} id="about-dropdown-menu" className={dropdownClass}>
        <a className="about-menu-link-item"> About &#8964; </a>
        <ul className="dropdown-menu dropdown-menu-right">
          <AboutMenuItems />
        </ul>
      </li>
  )
}

export default AboutMenu
