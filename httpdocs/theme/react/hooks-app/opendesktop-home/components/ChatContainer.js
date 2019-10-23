import React, { Component } from 'react';

class ChatContainer extends Component {

  constructor(props){
  	super(props);
    this.chatUrl = '/json/chat';
    this.avatarUrl= 'https://chat.opendesktop.org/_matrix/media/v1/thumbnail';
  	this.state = {items:[]};
  }

  componentDidMount() {
    fetch(this.chatUrl)
      .then(response => response.json())
      .then(data => {
        this.setState({items:data});
      });
  }

  render(){
    let container, members;
    if (this.state.items){
      const feedItems = this.state.items.map((fi,index) => {
          if(fi.members){
            let mb=Object.values(fi.members);
            if(mb.length>0){
                members = mb.slice(0,4).map((m,index) => {
                    let imgAvatar;
                    if(m.avatar_url){
                      imgAvatar = <img src={this.avatarUrl+m.avatar_url.substring(5)+'?width=39&height=39&method=crop'}></img>
                    }
                    return (
                            <div className="chatUser">
                              {imgAvatar}
                              <div className="name">
                                {m.display_name}
                              </div>
                            </div>
                            )
                          }
                  );
            }
            if(fi.guest_can_join==false)
            {
              return (
              <li key={index} className="chatMember">
                <a href={this.roomUrl+fi.room_id} >
                  join our chat {(fi.canonical_alias)?fi.canonical_alias.substring(0,fi.canonical_alias.indexOf(':'))+' ('+fi.num_joined_members+')':''}
                </a>
                {members}
              </li>
              )
            }
          }

      });
      container = <ul>{feedItems}</ul>;
    }
    return (
      <div id="chat-container" className="panelContainer">
        <div className="title"><a href="https://chat.opendesktop.org">Riot Chat</a> </div>
        {container}
      </div>
    )
  }
}

export default ChatContainer;
