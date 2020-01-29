import React ,{useState,useEffect} from 'react'
import ForumUserPosts from './ForumUserPosts'
import Gitlab from './Gitlab'
import Riot from './Riot';

 const PortalIndex = () => {
    const [username, setUsername] = useState(window.data.username);
    return (
        <div>
          <ForumUserPosts username={username}></ForumUserPosts>
          <Gitlab username={username}></Gitlab>
          <Riot username={username}></Riot>
        </div>
    )
}

export default PortalIndex;