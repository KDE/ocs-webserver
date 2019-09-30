import React from 'react';
import SiteHeaderSearchForm from './SiteHeaderSearchForm';
import Support from './Support';
class MobileSiteHeader extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      status:"switch"
    };
    this.showMobileUserMenu = this.showMobileUserMenu.bind(this);
    this.showMobileSearchForm = this.showMobileSearchForm.bind(this);
    this.showMobileSwitchMenu = this.showMobileSwitchMenu.bind(this);

  }

  showMobileUserMenu(){
    this.setState({status:"user"});
  }

  showMobileSearchForm(){
    this.setState({status:"search"});
  }

  showMobileSwitchMenu(){
    this.setState({status:"switch"});
  }

  render(){
    const menuItemCssClass = {
      "borderColor":this.props.template['header-nav-tabs']['border-color'],
      "backgroundColor":this.props.template['header-nav-tabs']['background-color']
    }

    const closeMenuElementDisplay = (
      <a className="menu-item"  onClick={this.showMobileSwitchMenu}>
        <span className="glyphicon glyphicon-remove"></span>
      </a>
    );

    let PlingDisplay;
    if(this.props.section )
    {
        PlingDisplay =
            <div id="siter-header-pling">
            <Support section={this.props.section}
              headerStyle={this.props.template['header']['header-supporter-style']}
            />
            </div>
    }

    let mobileMenuDisplay;
    if (this.state.status === "switch"){
      mobileMenuDisplay = (
        <div id="switch-menu">
          <a className="menu-item" onClick={this.showMobileSearchForm} id="user-menu-switch">
            <span className="glyphicon glyphicon-search"></span>
          </a>
          {/*
          <a className="menu-item" onClick={this.showMobileUserMenu} id="search-menu-switch">
            <span className="glyphicon glyphicon-option-horizontal"></span>
          </a>
          */}
          { PlingDisplay }
        </div>
      );
    } else if (this.state.status === "user"){
      mobileMenuDisplay = (
        <div id="mobile-user-menu">
          <div className="menu-content-wrapper">
            <MobileUserContainer
              user={this.props.user}
              baseUrl={this.props.baseUrl}
              serverUrl={this.state.serverUrl}
              template={this.props.template}
              redirectString={this.props.redirectString}
            />
          </div>
          {closeMenuElementDisplay}
        </div>
      )
    } else if (this.state.status === "search"){
      mobileMenuDisplay = (
        <div id="mobile-search-menu">
          <div className="menu-content-wrapper">
            <SiteHeaderSearchForm
              baseUrl={this.props.baseUrl}
              searchBaseUrl={this.props.searchBaseUrl}
              store={this.props.store}
            />
          </div>
          {closeMenuElementDisplay}
        </div>
      )
    }

    let logoElementCssClass = this.props.store.name;
    if (this.state.status !== "switch"){
      logoElementCssClass += " mini-version";
    }

    return(
      <section id="mobile-site-header">
        <div id="mobile-site-header-logo" className={logoElementCssClass}>
          <a href={this.props.logoLink}>
            <img src={this.props.template['header-logo']['image-src']}/>
          </a>
        </div>
        <div id="mobile-site-header-menus-container">
          {mobileMenuDisplay}
        </div>
      </section>
    );
  }
}

export default MobileSiteHeader;
