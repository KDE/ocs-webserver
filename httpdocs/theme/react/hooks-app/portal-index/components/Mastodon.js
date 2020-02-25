import React ,{useState,useEffect} from 'react'
import TimeAgo from 'react-timeago'
const Mastodon = (props) => {
    const [posts, setPosts] = useState([]);
    const [user, setUser] = useState({'username':''});
    const [mastodonUrl, setMastodonUrl] = useState(window.config.mastodonUrl);
    const [baseUrl, setBaseUrl] = useState(window.config.baseUrl);
    useEffect(() => {                 
        loadData();
    },[]);

    const loadData =  () => {       
        fetch(`/json/socialuserstatuses?username=${props.username}`, {
            mode: 'cors',
            credentials: 'include'
          })
          .then(response => response.json())
          .then(data => {
                let items = data;
                console.log(items.user.account);

                if ( items && typeof(items.statuses) != "undefined")        
                {                                
                    setPosts(items.statuses);      
                }
                
                if (items && typeof(items.user.account) != "undefined")   
                {
                    setUser(items.user.account);
                }
          }); 
        
      }

    return (
        <div className="sub-system-container">  
        <div className="header">Mastodon : <a href={user.url}>{user.username} </a>
        {
            user.avatar &&                    
            <img src={user.avatar} style={{width:'48px',height: '48px', borderRadius:'10px'}}></img>         
        }
        
        </div>  
        <div>
        <ul>{
            posts.slice(0, 5).map((p,index) =>       
            <li key={index}>                
                <div className="title">
                    <a className="title" href={p.url}>
                        <span>{p.content.replace(/(<([^>]+)>)/ig,"")}</span>                                              
                    </a> <TimeAgo date={p.created_at} />
                </div>
            </li>
            )
        }
        </ul>
        </div>   
             
        </div>
    )
}

export default Mastodon;