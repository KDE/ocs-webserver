import React, { Component } from 'react';
import TimeAgo from 'react-timeago';
class CommentsContainer extends React.Component {
  render(){
    let commentsContainer;
    if (this.props.comments){
      const comments = this.props.comments.map((cm,index) => (
        <li key={index}>
          <div className="cm-content">
            <span className="cm-userinfo">
              <img src={cm.profile_image_url}/>
              <span className="username"><a href={"/p/"+cm.comment_target_id}>{cm.username}</a></span>
            </span>
            <a className="title" href={"/member/"+cm.member_id}><span>{cm.title}</span></a>
            <span className="content">
              {cm.comment_text}
            </span>
            <span className="info-row">
              <span className="date">
                <TimeAgo date={cm.comment_created_at} />
              </span>
            </span>
          </div>
        </li>
      ));
      commentsContainer = <ul>{comments}</ul>
    }
    return (
      <div className="panelContainer">
        <div className="title">Comments</div>
        {commentsContainer}
      </div>
    )
  }
}

export default CommentsContainer;
