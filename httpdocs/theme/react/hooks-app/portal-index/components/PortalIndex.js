import React ,{useState,useEffect} from 'react'
import ForumUserPosts from './ForumUserPosts'
import Gitlab from './Gitlab'
import Riot from './Riot';
import Nextcloud from './Nextcloud';
import Pling from './Pling';

 const PortalIndex = () => {
    const [username, setUsername] = useState(window.data.username);
    return (
        <div>
          <Pling username={username}></Pling>
          <ForumUserPosts username={username}></ForumUserPosts>
          <Gitlab username={username}></Gitlab>
          <Riot username={username}></Riot>
          <Nextcloud username={username}></Nextcloud>

        </div>
    )
}

export default PortalIndex;