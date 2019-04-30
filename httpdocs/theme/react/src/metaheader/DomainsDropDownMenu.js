import React from 'react';
import DomainsMenuGroup from './DomainsMenuGroup';
class DomainsDropDownMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {};
    this.handleClick = this.handleClick.bind(this);
  }


  componentDidMount() {

    // let menuGroups = [];
    // if(!this.props.domains) return;
    // this.props.domains.forEach(function(domain,index){
    //   if (menuGroups.indexOf(domain.menugroup) === -1){
    //     menuGroups.push(domain.menugroup);
    //   }
    // });
    // this.setState({menuGroups:menuGroups});

    // here is dirty coded because CT want to place other at firt section without modify db sort.
    // and if other then show groupname => Pling. so DomainsMenuGroup also hardcode display groupname
    let menuGroups=[  "Other","Artwork","Desktops", "Applications", "Addons" ];
    this.setState({menuGroups:menuGroups});
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
        if (e.target.className === "domains-menu-link-item"){
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

    let menuGroupsDisplayLeft, menuGroupsDisplayRight,menuGroupsDisplayMiddle;

    if (this.state.menuGroups){
      menuGroupsDisplayLeft = this.state.menuGroups.slice(0,2).map((mg,i) => (
        <DomainsMenuGroup
          key={i}
          domains={this.props.domains}
          menuGroup={mg}
          sName={this.props.sName}
        />
      ));
      menuGroupsDisplayMiddle = this.state.menuGroups.slice(2,3).map((mg,i) => (
        <DomainsMenuGroup
          key={i}
          domains={this.props.domains}
          menuGroup={mg}
          sName={this.props.sName}
        />
      ));

      menuGroupsDisplayRight = this.state.menuGroups.slice(3).map((mg,i) => (
        <DomainsMenuGroup
          key={i}
          domains={this.props.domains}
          menuGroup={mg}
          sName={this.props.sName}
        />
      ));

    }

    return (
      <li ref={node => this.node = node} id="domains-dropdown-menu" className={this.state.dropdownClass}>
        <a className="domains-menu-link-item">Store Listings</a>
        <ul className="dropdown-menu dropdown-menu-right">
          <li className="submenu-container">
            <ul>
              {menuGroupsDisplayLeft}
            </ul>
          </li>
          <li className="submenu-container">
            <ul>
              {menuGroupsDisplayMiddle}
            </ul>
          </li>

          <li className="submenu-container">
            <ul>
              {menuGroupsDisplayRight}
            </ul>
          </li>
        </ul>
      </li>
    );
  }
}

export default DomainsDropDownMenu;
