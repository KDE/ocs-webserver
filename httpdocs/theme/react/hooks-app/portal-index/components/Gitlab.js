import React ,{useState,useEffect} from 'react'

const Gitlab = (props) => {
    const [projects, setProjects] = useState([]);
    const [user, setUser] = useState({'username':''});
    const [gitlabUrl, setGitlabUrl] = useState(window.config.gitlabUrl);
    

    useEffect(() => {                 
        loadData();
    },[]);

    const loadData =  () => {       
        
        fetch(`/json/gitlab?username=${props.username}`, {
            mode: 'cors',
            credentials: 'include'
          })
          .then(response => response.json())
          .then(data => {
                let items = data;
                if (items && typeof(items.projects) != "undefined")        
                {            
                    setProjects(items.projects);      
                }
                
                if (items && typeof(items.user) != "undefined")   
                {
                    setUser(items.user);
                }
          }); 
        
      }

    return (
        <div className="sub-system-container">  
        <div className="header">Opencode :<a href={gitlabUrl+'/'+user.username}> {user.username} </a>
        {
            user.avatar_url &&
            <>            
            <img src={user.avatar_url}></img>
            </>
        }
        
        </div>  
        
        <div>
        <ul>{

                projects.slice(0, 5).map((p,index) =>       
            <li key={index}>                
                <div className="title">
                {p.avatar_url ? (
                    <img src={p.avatar_url} style={{width:'40px', height:'40px'}}></img>
                ) : (
                    <div style={{width:'40px',height:'40px',background:'#EEEEEE',fontSize:'16px'
                                ,lineHeight:'38px', textAlign:'center',color:'#555555'
                                ,display: 'block', float: 'left',marginRight: '10px'
                                }}>
                    {p.name.substr(0,1)}</div>
                )}

                <a href={p.http_url_to_repo}>
                {p.name+' '+p.description+p.last_activity_at}
                </a>
                </div>
            </li>
            )
            
        }
        </ul>
        </div>       
        </div>
    )
}

export default Gitlab;