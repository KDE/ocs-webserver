import React ,{useState,useEffect} from 'react'

const Owncloud = (props) => {
    const [users, setUsers] = useState([]);
    const [userdefault, setUserdefault] = useState({'displayname':''});
    const [imgpath,setImgpath] = useState(window.config.myopendesktopUrl+'/avatar/');
    const [isloading, setIsloading] = useState(true);
    useEffect(() => {                 
        loadData();
    },[]);

    const loadData = async () => {
        const data = await fetch(`/json/owncloud?username=${props.username}`);
        const items = await data.json();        
        
        if (items && typeof(items.users) != "undefined")   
        {
            setUsers(items.users);
            setIsloading(false);
            if(items.users)
            {
                setUserdefault(items.users[0]);
                
            }
        }
        
        
      }

    return (
        <div className="sub-system-container">  
        <div className="header">Owncloud : {userdefault.displayname} 
        {
            userdefault.displayname &&
            <>            
            <img src={imgpath+userdefault.id+'/50'}></img>
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
                <img src={imgpath+p.id+'/50'}></img>          
                <div className="title">                
                <span>{'id:'+p.id+' displayname:'+p.displayname+' email:'+p.email+' backend:'+p.backend}
                </span>
                     
                </div>
            </li>
            )
            
        }
        </ul>
        </div>       
        </div>
    )
}

export default Owncloud;
