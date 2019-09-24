import React from 'react';
import AboutMenuItems from './function/AboutMenuItems';

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
    return (
      <li ref={node => this.node = node} id="about-dropdown-menu" className={this.state.dropdownClass}>
        <a className="about-menu-link-item"> About &#8964; </a>
        <ul className="dropdown-menu dropdown-menu-right">
          <AboutMenuItems baseUrl={this.props.baseUrl}
                          baseUrlStore={this.props.baseUrlStore}
                          isAdmin={this.props.isAdmin}
                          blogUrl={this.props.blogUrl}
                          />
        </ul>
      </li>
    )
  }
}

export default AboutMenu;
