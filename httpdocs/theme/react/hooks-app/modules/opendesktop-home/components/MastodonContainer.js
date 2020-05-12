import React, { useState, useEffect } from 'react';
import Axios from 'axios';

const MastodonContainer = (props) => {
    const [items, setItems] = useState([]);

  useEffect(() => {
    Axios.get(`/json/socialtimeline`)
      .then(result => {              
        setItems(result.data);
      })
  }, []);

    return (
        <div className="panelContainer">
        <div className="title"><a href={props.url_mastodon}>Mastodon</a></div>
        <ul>
          {items.map((fi, index) => (
            <li key={index}>
                <div style={{display:'block',width:'60px',float:'left'}}>
                    <img src={fi.account.avatar} style={{width:'48px',height: '48px', borderRadius:'10px'}}></img>
                </div>
                <div style={{width:'100%'}}>
                    <span style={{display:'inline-block'}}>{fi.account.username}</span>
                    <a className="title" href={fi.url}>
                        <span>{fi.content.replace(/(<([^>]+)>)/ig,"")}</span>
                    </a>
                    <span className="date">{fi.created_at}</span>                
                </div>
              
            </li>
          )
          )}
        </ul>
      </div>
    )
}

export default MastodonContainer;