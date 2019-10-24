import React from 'react';
import UserCommentsTab from './UserCommentsTab';
import UserSearchTab from './UserSearchTab';
import UserAutoCompleteInput from './UserAutoCompleteInput';
class UserTabs extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      currentTab:'autocompletetest',
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

    let url = this.props.baseUrl+'/membersetting/searchmember?username='+searchPhrase;
     fetch(url,{
                mode: 'cors',
                credentials: 'include'
                })
      .then(response => response.json())
      .then(data => {
        this.setState({usersList:data,showUserList:true});
      });
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
            baseUrl={this.props.baseUrl}
          />
        );
      } else {
        tabContentDisplay = (
          <p>search user</p>
        );
      }
    }else if(this.state.currentTab === 'autocompletetest'){
      tabContentDisplay = <UserAutoCompleteInput />
    }

    return(
      <div id="user-tabs-container">
        <div id="user-tabs-menu">
          <ul>
            <li>
              <a className={this.state.currentTab === "autocompletetest" ? "active" : ""}
                onClick={() => this.onTabMenuItemClick('autocompletetest')} >
                Search User
              </a>
            </li>
            {/*
            <li id="search-form-container">
              <a className={this.state.currentTab === "search" ? "active" : ""}
                onClick={() => this.onTabMenuItemClick('search')}>
                Member Search: <input className="searchInput" value={this.state.searchPhrase} type="text" onChange={this.onUserSearchInputChange}/>
              </a>
              {usersAutocompleteList}
            </li>
            */}
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
