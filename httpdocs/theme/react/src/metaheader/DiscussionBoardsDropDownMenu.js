import React from 'react';
import CommunityMenuItems from './function/CommunityMenuItems';
class DiscussionBoardsDropDownMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {notification: false ,notification_count:0};
    this.handleClick = this.handleClick.bind(this);
  }

  componentWillMount() {
    document.addEventListener('mousedown',this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('mousedown',this.handleClick, false);
  }

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

    return (
      <li ref={node => this.node = node}  id="discussion-boards" className={this.state.dropdownClass}>
        <a className="discussion-menu-link-item">Community &#8964;</a>
        <ul className="discussion-menu dropdown-menu dropdown-menu-right">
          <CommunityMenuItems baseUrl={this.props.baseUrl}
                              baseUrlStore = {this.props.baseUrlStore}
                              forumUrl = {this.props.forumUrl}  />
        </ul>
      </li>
    );
  }

}

export default DiscussionBoardsDropDownMenu;
