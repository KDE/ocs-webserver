import React from 'react';
import UserCommentsTabThreadsContainer from './UserCommentsTabThreadsContainer';
import UserInfo from './function/UserInfo';
class UserSearchTab extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      loading:true
    };
    this.getUserOdComments = this.getUserOdComments.bind(this);
    this.getUserInfo = this.getUserInfo.bind(this);
    this.getUserForumComments = this.getUserForumComments.bind(this);
  }

  componentDidMount() {
    this.setState({odComments:[],forumComments:[],userinfo:[],loading:true},function(){
      //this.getUserOdComments();
      this.getUserInfo();
    });
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

  getUserForumComments(){
    const user = this.props.user;
    const self = this;
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
    
      if (this.readyState == 4 && this.status == 200) {
        const res = JSON.parse(this.response);
        self.setState({forumComments:res.user_actions,loading:false});
      }
    };
    xhttp.open("GET", "https://forum.opendesktop.cc/user_actions.json?offset=0&username=" + user.username + "&filter=5", true);
    xhttp.send();
  }

  render(){
    let contentDisplay;
    if (!this.state.loading){
      let odCommentsDisplay, forumCommentsDisplay, userinfoDisplay;
      if (this.state.odComments.length > 0){
        odCommentsDisplay = (
          <UserCommentsTabThreadsContainer
            type={'od'}
            user={this.props.user}
            comments={this.state.odComments}
            uType={'search'}
          />
        );
      }
      if(this.state.userinfo)
      {
        userinfoDisplay = <UserInfo userinfo={this.state.userinfo} />
      }
      if (this.state.forumComments.length > 0){
        forumCommentsDisplay = (
          <UserCommentsTabThreadsContainer
            type={'forum'}
            user={this.props.user}
            comments={this.state.forumComments}
            uType={'search'}
          />
        );
      }

      contentDisplay = (
        <div>
          {userinfoDisplay}
          {odCommentsDisplay}
          {forumCommentsDisplay}
        </div>
      )

    } else {
      contentDisplay = (
        <div>loading</div>
      );
    }

    return(
      <div id="user-comments-tab-container">
        {contentDisplay}
      </div>
    )
  }
}

export default UserSearchTab;
