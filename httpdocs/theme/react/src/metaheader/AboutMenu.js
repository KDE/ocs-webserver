import React from 'react';
class AboutMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {};
    this.handleClick = this.handleClick.bind(this);
  }

  componentWillMount() {
    document.addEventListener('click',this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('click',this.handleClick, false);
  }



  handleClick(e){
    let dropdownClass = "";

    if (this.node.contains(e.target)){

      if(e.target.className === "about-menu-link-item" || "th-icon"===e.target.className)
      {
        // only btn click open dropdown
        if (this.state.dropdownClass === "open"){
          dropdownClass = "";
        }else{
          dropdownClass = "open";
        }
      }else{
        dropdownClass = "";
      }
    }
    this.setState({dropdownClass:dropdownClass});
  }

  render(){

    let faqLinkItem, apiLinkItem, aboutLinkItem,aboutPlingItem, aboutopencodeItem;
    if (this.props.isAdmin ){
      faqLinkItem = (<li><a className="popuppanel" id="faq" href={"/plings"}>Plings (admin only)</a></li>);
    }
    aboutPlingItem = (<li><a id="faq" href={this.props.baseUrl +"/faq-pling"}>FAQ Pling</a></li>);
    aboutopencodeItem = (<li><a id="faq" href={this.props.baseUrl +"/faq-opencode"}>FAQ Opencode</a></li>);

    aboutLinkItem = (<li><a id="about" href={this.props.baseUrl +"/about"}>About</a></li>);
    if (this.props.isExternal === false){
      //faqLinkItem = (<li><a className="popuppanel" id="faq" href={"/plings"}>Plings</a></li>);
      apiLinkItem = (<li><a className="popuppanel" id="api" href={"/partials/ocsapicontent.phtml"}>API</a></li>);
      //aboutLinkItem = (<li><a className="popuppanel" id="about" href={"/partials/about.phtml"}>About</a></li>);
    } else {
      //faqLinkItem = (<li><a className="popuppanel" target="_blank" id="faq" href={this.props.baseUrl + "/#faq"}>FAQ</a></li>);
      apiLinkItem = (<li><a className="popuppanel" target="_blank" id="api" href={this.props.baseUrl + "/#api"}>API</a></li>);
      //aboutLinkItem = (<li><a className="popuppanel" target="_blank" id="about" href={this.props.baseUrl + "/#about"}>About</a></li>);
    }

    return (
      <li ref={node => this.node = node} id="about-dropdown-menu" className={this.state.dropdownClass}>
        <a className="about-menu-link-item"> About &#8964; </a>
        <ul className="dropdown-menu dropdown-menu-right">
          <li><a href={this.props.blogUrl} target="_blank">Blog</a></li>
          {faqLinkItem}
          {apiLinkItem}
          {aboutPlingItem}
          {aboutopencodeItem}
          {aboutLinkItem}
        </ul>
      </li>
    )
  }
}

export default AboutMenu;
