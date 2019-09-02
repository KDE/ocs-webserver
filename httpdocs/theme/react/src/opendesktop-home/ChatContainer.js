import React, { Component } from 'react';

class ChatContainer extends Component {

  constructor(props){
  	super(props);
    this.access_token = 'MDAyMmxvY2F0aW9uIGNoYXQub3BlbmRlc2t0b3Aub3JnCjAwMTNpZGVudGlmaWVyIGtleQowMDEwY2lkIGdlbiA9IDEKMDAzM2NpZCB1c2VyX2lkID0gQG1hZ2dpZWRvbmc6Y2hhdC5vcGVuZGVza3RvcC5vcmcKMDAxNmNpZCB0eXBlID0gYWNjZXNzCjAwMjFjaWQgbm9uY2UgPSBnMSxnRUA2c3AuKyxtYSx4CjAwMmZzaWduYXR1cmUgc3LtmFDiz7wU0TVOdGS7EbEg0wnXVKwXxNqkqe5qpCAK';
    this.avatarUrl= 'https://chat.opendesktop.org/_matrix/media/v1/thumbnail';
    this.roomPublicUrl='https://chat.opendesktop.org/_matrix/client/unstable/publicRooms';
    this.roomsUrl= 'https://chat.opendesktop.org/_matrix/client/unstable/rooms/';
    this.roomUrl='https://chat.opendesktop.org/#/room/';
  	this.state = {items:[]};
  }

  componentDidMount() {
    const urlRooms = `${this.roomPublicUrl}?access_token=${this.access_token}`;
    const urlMembers = this.roomsUrl;
    fetch(urlRooms)
      .then(response => response.json())
      .then(data => {
        data.chunk.map((fi,index) => {
            let url = urlMembers+fi.room_id+`/joined_members?access_token=${this.access_token}`;
            return fetch(url)
                   .then(response => response.json())
                   .then(data => {
                      var arr = [];
                      for (var key in data.joined) {
                       arr.push(data.joined[key]);
                      }
                      fi.members = arr;
                      let items = this.state.items.concat(fi);
                      this.setState({items:items});
                   })
        });
      });


    // $.getJSON("https://chat.opendesktop.org/_matrix/client/unstable/publicRooms?access_token=MDAyMmxvY2F0aW9uIGNoYXQub3BlbmRlc2t0b3Aub3JnCjAwMTNpZGVudGlmaWVyIGtleQowMDEwY2lkIGdlbiA9IDEKMDAzN2NpZCB1c2VyX2lkID0gQGtpbWltcG9zc2libGUyOmNoYXQub3BlbmRlc2t0b3Aub3JnCjAwMTZjaWQgdHlwZSA9IGFjY2VzcwowMDIxY2lkIG5vbmNlID0gV2RFWmEuZ343eGx2a1czMQowMDJmc2lnbmF0dXJlICcmEgjLFNY7i2wHGT84mPr1eH4F6vNjTm3s8zZ4ZQVkCg", function (res) {
    //   self.setState({items:res.chunk});
    // });
  }

  render(){
    let container;
    if (this.state.items){
      const feedItems = this.state.items.map((fi,index) => {
          let members;
          if(fi.members){
              members = fi.members.slice(0,4).map((m,index) => {
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
