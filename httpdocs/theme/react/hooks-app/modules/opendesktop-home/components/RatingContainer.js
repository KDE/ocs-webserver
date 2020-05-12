import React, { Component } from 'react';

class RatingContainer extends React.Component {
  render(){
    let ratingContainer;
    if (this.props.votes){
      const votes = this.props.votes.map((cm,index) => (
        <li key={index}>
          <div className="cm-content">
            <span className="cm-userinfo">
              <img src={cm.profile_image_url}/>
              <span className="username"><a href={this.props.baseUrlStore+"/member/"+cm.member_id}>{cm.username}</a></span>
            </span>
            <a className="title" href={this.props.baseUrlStore+"/p/"+cm.project_id}>{cm.title}</a>
            <span className="content">
              Score:{cm.score} {cm.comment_text}
            </span>
            <span className="info-row">
              <span className="date">
                {cm.created_at}

              </span>
            </span>
          </div>
        </li>
      ));
      ratingContainer = <ul>{votes}</ul>
    }
    return (
      <div className="panelContainer">
        <div className="title">{this.props.title}</div>
        {ratingContainer}
      </div>
    )
  }
}

export default RatingContainer;
