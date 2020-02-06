import React ,{useState,useEffect} from 'react'

const Riot = (props) => {
    
    const [user, setUser] = useState({'username':''});
    const [userPresence, setUserPresence] = useState(null);
    const [imgpath,setImgpath] = useState('https://chat.opendesktop.org/_matrix/media/r0/thumbnail/');

    useEffect(() => {                 
        loadData();
    },[]);

    const loadData = async () => {
        const data = await fetch(`/json/riot?username=${props.username}`);
        const items = await data.json();                
        if (items && typeof(items.user) != "undefined")   
        {
            setUser(items.user);            
        }                
        if (items && items.status.presence)   
        {
            setUserPresence(items.status.presence);            
        }
      }

    return (
        <div className="sub-system-container">  
        <div className="header">Chat : {user.displayname} 
        {
            user.avatar_url &&
            <>            
            <img src={imgpath+user.avatar_url.replace('mxc://','')+'?width=50&height=50&method=crop'}></img>
            </>
        }
        
        {userPresence}
        </div>  
            
        </div>
    )
}

export default Riot;