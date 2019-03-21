import React from 'react';
class ChatMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {};
  }

  render(){
    return (
      <li ref={node => this.node = node} id="about-dropdown-menu" className={this.state.dropdownClass}>
        <a className="about-menu-link-item"> About &#8964; </a>
        <ul className="dropdown-menu dropdown-menu-right">
          <li><a href={this.props.blogUrl} target="_blank">Blog</a></li>
          {faqLinkItem}
          {apiLinkItem}
          {aboutLinkItem}
        </ul>
      </li>
    )
  }
}

export default ChatMenu;
