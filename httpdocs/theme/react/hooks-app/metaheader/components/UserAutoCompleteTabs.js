import React from 'react';
import UserCommentsTab from './UserCommentsTab';
import UserInfo from './function/UserInfo';
import UserCommentsTabThreadsContainer from './UserCommentsTabThreadsContainer';
class UserAutoCompleteTabs extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      currentTab:'userinfo',
      loading:true
    };
    this.onTabMenuItemClick = this.onTabMenuItemClick.bind(this);
  }

  onTabMenuItemClick(val){
    this.setState({currentTab:val});
  }

  // getUserForumComments(){
  //   const user = this.props.user;
  //   const self = this;
  //   const xhttp = new XMLHttpRequest();
  //   xhttp.onreadystatechange = function() {
  //     console.log('this ');
  //     if (this.readyState == 4 && this.status == 200) {
  //       const res = JSON.parse(this.response);
  //       self.setState({forumComments:res.user_actions,loading:false});
  //     }
  //   };
  //   xhttp.open("GET", "https://forum.opendesktop.cc/user_actions.json?offset=0&username=" + user.username + "&filter=5", true);
  //   xhttp.send();
  // }

  render(){

    let tabContentDisplay;
    if (this.state.currentTab === 'userinfo'){

        tabContentDisplay = <UserInfo  userinfo={this.props.userinfo} />

    }else if(this.state.currentTab === 'comment' && this.props.odComments.length>0){
      tabContentDisplay =
        <UserCommentsTabThreadsContainer
          type={'od'}
          user={this.props.user}
          comments={this.props.odComments}
          uType={'search'}
        />
    }

    return(
      <div id="user-tabs-container">
        <div id="user-tabs-menu">
          <ul>
            <li>
              <a className={this.state.currentTab === "userinfo" ? "active" : ""}
                onClick={() => this.onTabMenuItemClick('userinfo')} >
                Userinfo
              </a>
            </li>
            <li>
              <a className={this.state.currentTab === "comment" ? "active" : ""}
                onClick={() => this.onTabMenuItemClick('comment')} >
                Comment
              </a>
            </li>

          </ul>
        </div>
        <div id="user-tabs-content">
          <div class="user-comments-tab-container">
          {tabContentDisplay}
          </div>
        </div>
      </div>
    );
  }
}

export default UserAutoCompleteTabs;
