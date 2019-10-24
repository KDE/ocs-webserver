import React from 'react';

function ChatMember(props) {
  return (
    <div>
    <img src={props.avatarUrl} />
    {props.display_name}
    </div>
  )
}

export default ChatMember;
