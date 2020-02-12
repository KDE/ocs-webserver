import React ,{useState,useEffect} from 'react'

const ForumUserPosts = (props) => {
    const [posts, setPosts] = useState([]);
    const [user, setUser] = useState({'username':''});
    const [forumUrl, setForumUrl] = useState(window.config.forumUrl);
    const [baseUrl, setBaseUrl] = useState(window.config.baseUrl);
    useEffect(() => {                 
        loadData();
    },[]);

    const loadData =  () => {       
        fetch(`/json/forumposts?username=${props.username}`, {
            mode: 'cors',
            credentials: 'include'
          })
          .then(response => response.json())
          .then(data => {
                let items = data;
                if ( items && typeof(items.posts) != "undefined")        
                {            
                    let posts = Object.keys(items.posts).map(function (i) {
                        return items.posts[i];
                    });
                    posts.sort(function(a, b) {
                    return a.created_at < b.created_at;
                    });
                    setPosts(posts);      
                }
                
                if (items && typeof(items.user) != "undefined")   
                {
                    setUser(items.user.user);
                }
          }); 
        
      }

    return (
        <div className="sub-system-container">  
        <div className="header">Forum : <a href={forumUrl+'/u/'+user.username}>{user.username} </a>
        {
            user.avatar_template &&
            <>            
            <img src={forumUrl+user.avatar_template.replace('{size}','50')}></img>
            </>
        }
        
        </div>  
        
        <div>
        <ul>{
            posts.slice(0, 5).map((p,index) =>       
            <li key={index}>                
                <div className="title">
                <a href={forumUrl+'/p/'+p.post_id}>{p.excerpt} {p.created_at}</a>
                </div>
            </li>
            )
        }
        </ul>
        </div>       
        </div>
    )
}

export default ForumUserPosts;