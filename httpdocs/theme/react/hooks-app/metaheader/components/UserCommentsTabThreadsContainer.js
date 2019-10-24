import React from 'react';
import UserCommentsTabThread from './UserCommentsTabThread';
class UserCommentsTabThreadsContainer extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    let siteInfo;
    if (this.props.type === 'od'){
      siteInfo = {
        address:'openDesktop.org',
        url:'https://www.opendesktop.org'
      }
    } else if (this.props.type === 'forum'){
      siteInfo = {
        address:'forum',
        url:'https://forum.opendesktop.org'
      }
    }

    let threads = [];
    this.props.comments.forEach(function(c,index){
      let pos = threads.map(function(e) { return e.id; }).indexOf(c.project_id);
      if(pos===-1)
      {
        const thread = {
            title:c.title,
            id:c.project_id
          }
        threads.push(thread)
      }
    });

    this.setState({siteInfo:siteInfo,comments:this.props.comments,threads:threads});
  }

  render(){
    const t = this.state.siteInfo;
    const comments = this.state.comments;
    const user = this.props.user;
    let headerDisplay, threadsDisplay, threadCommentsDisplay;
    if (this.state.threads){
  
      threadsDisplay = this.state.threads.map((tr,index) => (
        <UserCommentsTabThread
          key={index}
          thread={tr}
          comments={comments}
          user={user}
          uType={this.props.uType}
        />
      ));
      headerDisplay = (
        <div className="thread-header">
          <div className="thread-subtitle">
            <p>Discussion on <b><a href={this.state.siteInfo.url}>{this.state.siteInfo.address}</a></b></p>
            <p><span>{this.state.comments.length} comments</span></p>
          </div>
        </div>
      );
    }

    return (
      <div className="user-comments-thread-container">
        {headerDisplay}
        <div className="thread-comments">
          {threadsDisplay}
        </div>
      </div>
    )
  }
}
export default UserCommentsTabThreadsContainer;
