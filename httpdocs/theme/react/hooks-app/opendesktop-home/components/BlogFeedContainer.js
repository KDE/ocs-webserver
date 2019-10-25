import React, { useState, useEffect } from 'react';
import Axios from 'axios';

const BlogFeedContainer = (props) => {

  const [items, setItems] = useState([]);

  useEffect(() => {
    Axios.get(`/json/forum`)
      .then(result => {
        let topics = result.data.topic_list.topics;
        topics.sort(function (a, b) {
          return new Date(b.last_posted_at) - new Date(a.last_posted_at);
        });
        topics = topics.slice(0, 5);
        setItems(topics);
      })
  }, []);

  return (
    <div className="panelContainer">
      <div className="title"><a href={props.urlCommunity}>Forum</a></div>
      <ul>
        {items.map((fi, index) => (
          <li key={index}>
            <a className="title" href={props.urlCommunity + "/t/" + fi.id}>
              <span>{fi.title}</span>
            </a>
            <span className="info-row">
              <span className="date">{fi.timeago}</span>
              <span className="comment-counter">{fi.replyMsg}</span>
            </span>
          </li>
        )
        )}
      </ul>
    </div>
  )
}

export default BlogFeedContainer


