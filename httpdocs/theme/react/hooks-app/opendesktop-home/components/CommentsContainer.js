import React, { Component } from 'react';

class CommentsContainer extends React.Component {
  render(){
    let commentsContainer;
    if (this.props.comments){
      const comments = this.props.comments.map((cm,index) => (
        <li key={index}>
          <div className="cm-content">
            <span className="cm-userinfo">
              <img src={cm.profile_image_url}/>
              <span className="username"><a href={this.props.baseUrlStore+"/member/"+cm.member_id}>{cm.username}</a></span>
            </span>
            <a className="title" href={this.props.baseUrlStore+"/p/"+cm.comment_target_id}>{cm.title}</a>
            <span className="content">
              {cm.comment_text}
            </span>
            <span className="info-row">
              <span className="date">
                {cm.comment_created_at}

              </span>
            </span>
          </div>
        </li>
      ));
      commentsContainer = <ul>{comments}</ul>
    }
    return (
      <div className="panelContainer">
        <div className="title">{this.props.title}</div>
        {commentsContainer}
      </div>
    )
  }
}

export default CommentsContainer;
