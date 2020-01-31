import React ,{useState,useEffect} from 'react'

const ForumUserPosts = (props) => {
    const [posts, setPosts] = useState([]);
    const [user, setUser] = useState({'username':''});
    const [forumUrl, setForumUrl] = useState(window.config.forumUrl);
    const [baseUrl, setBaseUrl] = useState('https://www.opendesktop.cc');
    useEffect(() => {                 
        loadData();
    },[]);

    const loadData = async () => {
        const data = await fetch(`/json/forumposts?username=${props.username}`);
        const items = await data.json();
        
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
        
      }

    return (
        <div className="sub-system-container">  
        <div className="header">Forum : {user.username} 
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