import React ,{useState,useEffect} from 'react'

const Nextcloud = (props) => {
    const [users, setUsers] = useState([]);
    const [userdefault, setUserdefault] = useState({'displayname':''});
    const [imgpath,setImgpath] = useState(window.config.myopendesktopUrl+'/avatar/');
    const [isloading, setIsloading] = useState(true);
    useEffect(() => {                 
        loadData();
    },[]);

    const loadData =  () => {        
        
        fetch(`/json/owncloud?username=${props.username}`, {
            mode: 'cors',
            credentials: 'include'
          })
          .then(response => response.json())
          .then(data => {
                let items = data;
                if (items && typeof(items.users) != "undefined")   
                {
                    setUsers(items.users);
                    setIsloading(false);
                    if(items.users)
                    {
                        setUserdefault(items.users[0]);
                        
                    }
                }
          }); 
        
      }

    return (
        <div className="sub-system-container">  
        <div className="header">Nextcloud : {userdefault.displayname} 
        {
            userdefault.displayname &&
            <>            
            <img  src={imgpath+userdefault.id+'/50'}></img>
            </>
        }
        
        </div>  
        
        <div>
            {
                isloading &&
                <span>loading...</span>
            }
        <ul>{

            users.map((p,index) =>       
            <li key={index}>          
                <img className="icon" src={imgpath+p.id+'/50'}></img>          
                <div className="title">                
                <ul>
                    <li>{'id:'+p.id}</li>
                    <li>{'email:'+p.email}</li>
                    <li>{'displayname:'+p.displayname}</li>
                    <li>{'backend:'+p.backend}</li>
                </ul>
          
                </div>
            </li>
            )
            
        }
        </ul>
        </div>       
        </div>
    )
}

export default Nextcloud;
