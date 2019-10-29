import React , {useEffect, useState, useRef, useContext}from 'react'
import {MetaheaderContext} from '../contexts/MetaheaderContext';

const DomainsMenu_subdomain = () => {
    const {state} = useContext(MetaheaderContext);
    const [dropdownClass, setDropdownClass] = useState('test');    
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
            if (e.target.className === "domains-menu-link-item"){
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
       <div ref={toggleEl} id="domains-dropdown-menu" className={dropdownClass}>
        <a className="domains-menu-link-item">{state.storeConfig.name} &#8964;</a>
        <ul className="dropdown-menu dropdown-menu-right">
         {state.domains.filter(domain=>domain.is_show_in_menu==1)
                       .sort((a, b) => a.name > b.name)
                       .map((domain, index)=><li key={index}>
                           <a href={domain.is_show_real_domain_as_url==1?'https://'+domain.host:state.baseUrlStore+'/s/'+domain.name}>{domain.name}</a></li>)}        
        </ul>
      </div>
    )
}

export default DomainsMenu_subdomain
