import React ,{useState,useEffect} from 'react'

const Gitlab = (props) => {
    const [projects, setProjects] = useState([]);
    const [user, setUser] = useState({'username':''});
    const [gitlabUrl, setGitlabUrl] = useState(window.config.gitlabUrl);
    

    useEffect(() => {                 
        loadData();
    },[]);

    const loadData = async () => {
        const data = await fetch(`/json/gitlab?username=${props.username}`);
        const items = await data.json();

        if (items && typeof(items.projects) != "undefined")        
        {            
            setProjects(items.projects);      
        }
        
        if (items && typeof(items.user) != "undefined")   
        {
            setUser(items.user);
        }
        
        
      }

    return (
        <div className="sub-system-container">  
        <div className="header">Opencode : {user.username} 
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