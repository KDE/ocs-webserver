import React from 'react';
class MoreDropDownMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {};
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
        if (e.target.className === "more-menu-link-item"){
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

    let faqLinkItem, apiLinkItem, aboutLinkItem;
    if (this.props.isExternal === false){
      faqLinkItem = (<li><a className="popuppanel" id="faq" href={"/plings"}>FAQ</a></li>);
      apiLinkItem = (<li><a className="popuppanel" id="api" href={"/partials/ocsapicontent.phtml"}>API</a></li>);
      aboutLinkItem = (<li><a className="popuppanel" id="about" href={"/partials/about.phtml"}>About</a></li>);
    } else {
      faqLinkItem = (<li><a className="popuppanel" target="_blank" id="faq" href={this.props.baseUrl + "/#faq"}>FAQ</a></li>);
      apiLinkItem = (<li><a className="popuppanel" target="_blank" id="api" href={this.props.baseUrl + "/#api"}>API</a></li>);
      aboutLinkItem = (<li><a className="popuppanel" target="_blank" id="about" href={this.props.baseUrl + "/#about"}>About</a></li>);
    }

    return(
      <li ref={node => this.node = node} id="more-dropdown-menu" className={this.state.dropdownClass}>
        <a className="more-menu-link-item">More</a>
        <ul className="dropdown-menu">
          <li><a href={this.props.baseUrl + "/community"}>Community</a></li>
          <li><a href={this.props.baseUrl + "/support"}>Support</a></li>
          <li><a href={this.props.blogUrl} target="_blank">Blog</a></li>
          {faqLinkItem}
          {apiLinkItem}
          {aboutLinkItem}
        </ul>
      </li>
    )
  }
}
export default MoreDropDownMenu;
