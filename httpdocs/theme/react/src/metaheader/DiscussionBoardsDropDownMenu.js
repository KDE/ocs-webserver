import React from 'react';
class DiscussionBoardsDropDownMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {notification: false ,notification_count:0};
    this.handleClick = this.handleClick.bind(this);
    //this.loadNotification = this.loadNotification.bind(this);
  }

  componentWillMount() {
    document.addEventListener('mousedown',this.handleClick, false);
    //this.loadNotification();
  }

  componentWillUnmount() {
    //clearInterval(this.timer);
    document.removeEventListener('mousedown',this.handleClick, false);
  }

  loadNotification(){
    if(this.props.user){
      let url = this.props.baseUrl+'/membersetting/notification';
      //let url = "http://pling.local/membersetting/notification";
      fetch(url,{
                 mode: 'cors',
                 credentials: 'include'
                 })
      .then(response => response.json())
      .then(data => {
          const nots = data.notifications.filter(note => note.read==false);
          if(nots.length>0 && this.state.notification_count !== nots.length)
          {
              this.setState(prevState => ({ notification: true, notification_count:nots.length }))
          }
       });
     }
  }

  // componentDidMount(){
  //
  //   if(this.props.user)
  //   {
  //      this.timer = setInterval(
  //           () => {
  //               this.loadNotification();
  //           },
  //           1000*60*3
  //       );
  //      }
  //  }


  handleClick(e){
    let dropdownClass = "";
    if (this.node.contains(e.target)){
      if (this.state.dropdownClass === "open"){
        if (e.target.className === "discussion-menu-link-item"){
          dropdownClass = "";
        } else {
          dropdownClass = "open";
        }
      } else {
        dropdownClass = "open";
      }
    }
    this.setState({dropdownClass:dropdownClass});
  }

  render(){
    // let badgeNot;
    // if(this.state.notification)
    // {
    //   badgeNot = (<span className="badge-notification">{this.state.notification_count}</span>);
    // }
    return (
      <li ref={node => this.node = node}  id="discussion-boards" className={this.state.dropdownClass}>

        <a className="discussion-menu-link-item">Discussion Boards </a>
        <ul className="discussion-menu dropdown-menu dropdown-menu-right">
          <li><a href={this.props.forumUrl }>General</a></li>
          <li><a href={this.props.forumUrl + "/c/themes"}>Themes</a></li>
          <li><a href={this.props.forumUrl + "/c/apps"}>Apps</a></li>
          <li><a href={this.props.forumUrl + "/c/coding"}>Coding</a></li>
        </ul>
      </li>
    );
  }

}

export default DiscussionBoardsDropDownMenu;
