import React from 'react';
import UserCommentsTab from './UserCommentsTab';
import UserInfo from './UserInfo';
import UserCommentsTabThreadsContainer from './UserCommentsTabThreadsContainer';
class UserAutoCompleteTabs extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      currentTab:'userinfo',
      odComments:[],
      forumComments:[],
      loading:true
    };
    this.onTabMenuItemClick = this.onTabMenuItemClick.bind(this);
    this.getUserOdComments = this.getUserOdComments.bind(this);
    this.getUserInfo = this.getUserInfo.bind(this);
    //this.getUserForumComments = this.getUserForumComments.bind(this);
  }

  onTabMenuItemClick(val){
    this.setState({currentTab:val});
  }

  // static getDerivedStateFromProps(nextProps, prevState) {
  //   if (nextProps.user.member_id !== prevState.user.member_id) {
  //       return ({ user: nextProps.user,loading:false})
  //   }
  // }

  componentDidMount() {
    this.getUserInfo();
    // this.setState({odComments:[],forumComments:[],loading:true},function(){
    //   this.getUserInfo();
    // });
  }

  getUserInfo(){
    let url = `${this.props.baseUrl}/membersetting/userinfo?member_id=${this.props.user.member_id}`;
     fetch(url,{
                mode: 'cors',
                credentials: 'include'
                })
      .then(response => response.json())
      .then(data => {
        this.setState({userinfo:data,loading:false},function(){
              this.getUserOdComments();
           });
      });
  }

  getUserOdComments(){
      let url = `${this.props.baseUrl}/membersetting/memberjson?member_id=${this.props.user.member_id}`;
       fetch(url,{
                  mode: 'cors',
                  credentials: 'include'
                  })
        .then(response => response.json())
        .then(data => {
          this.setState({odComments:data.commentsOpendeskop,loading:false},function(){
               //this.getUserForumComments();
             });
        });
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
    if (this.state.currentTab === 'userinfo' && this.state.userinfo){

        tabContentDisplay = <UserInfo user={this.props.user} userinfo={this.state.userinfo} />

    }else if(this.state.currentTab === 'comment' && this.state.odComments.length>0){
      tabContentDisplay =
        <UserCommentsTabThreadsContainer
          type={'od'}
          user={this.props.user}
          comments={this.state.odComments}
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
          {tabContentDisplay}
        </div>
      </div>
    );
  }
}

export default UserAutoCompleteTabs;
