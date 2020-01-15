import React from 'react';
import {filterDuplicated} from './util';
const Supporters = (props) => {

  return (
    <div className="supporters-container">
      <ul>{
        props.supporters.filter(filterDuplicated).map((s,index) =>       
          <li key={index}>
            <a href={props.baseUrlStore+'/u/'+s.username}><img src={s.profile_image_url}></img></a>
            <div className="username">{s.username}</div>
          </li>
        )
      }
      </ul>
      </div>
  )
}

export default Supporters

