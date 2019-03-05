import React from 'react';
import UserCommentsTabThreadCommentItem from './UserCommentsTabThreadCommentItem';
class UserCommentsTabThread extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
    this.filterCommentsByThread = this.filterCommentsByThread.bind(this);
  }

  filterCommentsByThread(comment){
    if (comment.title === this.props.thread.title){
      return comment;
    }
  }

  render(){
    let commentsDisplay;
    if (this.props.comments){
      const user = this.props.user;
      commentsDisplay = this.props.comments.filter(this.filterCommentsByThread).map((c,index) => (
        <UserCommentsTabThreadCommentItem
          key={index}
          comment={c}
          user={user}
          uType={this.props.uType}
        />
      ));
    }
    return (
      <div className="user-comments-thread">
        <div className="thread-title">
          <h2><a href={"https://www.opendesktop.cc/p/" + this.props.thread.id}>{this.props.thread.title}</a></h2>
        </div>
        <div className="thread-comments">
          {commentsDisplay}
        </div>
      </div>
    );
  }
}

export default UserCommentsTabThread;
