import React from 'react';
import UserCommentsTab from './UserCommentsTab';
import UserSearchTab from './UserSearchTab';
class UserTabs extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      currentTab:'comments',
      searchPhrase:''
    };
    this.onTabMenuItemClick = this.onTabMenuItemClick.bind(this);
    this.onUserSearchInputChange = this.onUserSearchInputChange.bind(this);
    this.getUsersAutocompleteList = this.getUsersAutocompleteList.bind(this);
    this.selectUserFromAutocompleteList = this.selectUserFromAutocompleteList.bind(this);
  }

  onTabMenuItemClick(val){
    this.setState({currentTab:val});
  }

  onUserSearchInputChange(e){
    const searchPhrase = e.target.value;
    this.setState({searchPhrase:e.target.value},function(){
      let showUserList;
      if (searchPhrase.length > 2){
        showUserList = true;
      } else {
        showUserList = false;
      }
      this.setState({showUserList:showUserList,selectedUser:''},function(){
        this.getUsersAutocompleteList(searchPhrase);
      });
    });
  }

  getUsersAutocompleteList(searchPhrase){
      const self = this;
      const xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          const res = JSON.parse(this.response);
          self.setState({usersList:res,showUserList:true});
        }
      };
      xhttp.open("GET", "https://www.opendesktop.cc/home/searchmember?username="+searchPhrase, true);
      xhttp.send();
  }

  selectUserFromAutocompleteList(user){
    this.setState({selectedUser:user,searchPhrase:user.username,showUserList:false});
  }

  render(){

    let usersAutocompleteList;
    if (this.state.usersList && this.state.showUserList){
      const users = this.state.usersList.map((u,index) => (
        <li onClick={() => this.selectUserFromAutocompleteList(u)} key={index}>
          {u.username}
        </li>
      ));
      usersAutocompleteList = (
        <ul className="autcomplete-list">
          {users}
        </ul>
      );
    }


    let tabContentDisplay;
    if (this.state.currentTab === 'comments'){
      tabContentDisplay = (
        <UserCommentsTab
          user={this.props.user}
        />
      );
    } else if (this.state.currentTab === 'search'){
      if (this.state.selectedUser){

        tabContentDisplay = (
          <UserSearchTab
            user={this.state.selectedUser}
          />
        );
      } else {
        tabContentDisplay = (
          <p>search user</p>
        );
      }
    }

    return(
      <div id="user-tabs-container">
        <div id="user-tabs-menu">
          <ul>
            <li>
              <a className={this.state.currentTab === "comments" ? "active" : ""}
                onClick={() => this.onTabMenuItemClick('comments')}>
                Comments
              </a>
            </li>
            <li id="search-form-container">
              <a className={this.state.currentTab === "search" ? "active" : ""}
                onClick={() => this.onTabMenuItemClick('search')}>
                <input value={this.state.searchPhrase} type="text" onChange={this.onUserSearchInputChange}/>
              </a>
              {usersAutocompleteList}
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

export default UserTabs;
