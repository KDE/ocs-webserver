import React from 'react';
class UserCommentsTabThreadCommentItem extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    const c = this.props.comment;
    const user = this.props.user;
    let repliedUsernameDisplay;
    if (c.p_comment_member_id){
      repliedUsernameDisplay = ( <p className="replied-user"><span className="glyphicon glyphicon-share-alt"></span><a href={"https://forum.opendesktop.cc/u/"+c.p_username+"/messages"}>{c.p_username}</a></p> )
    }

    let userImage = user.avatar;
    if (this.props.uType === 'search'){
      userImage = user.profile_image_url;
    }

    return (
      <div className="comment-item">
        <figure className="comment-item-user-avatar">
          <img className="th-icon" src={userImage}/>
        </figure>
        <div className="comment-item-header">
          <p className="user"><a href={"https://forum.opendesktop.cc/u/"+user.username+"/messages"}>{user.username}</a></p>
          {repliedUsernameDisplay}
          <p className="date-created"><span>{c.comment_created_at}</span></p>
        </div>
        <div className="comment-item-content">
          <div dangerouslySetInnerHTML={{__html:c.comment_text}}></div>
        </div>
      </div>
    )
  }
}

export default UserCommentsTabThreadCommentItem;
