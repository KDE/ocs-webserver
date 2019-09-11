import React, { Component } from 'react';
import MobileSiteHeader from './MobileSiteHeader';
import SiteHeaderSearchForm from './SiteHeaderSearchForm';
import Support from './Support';
class SiteHeader extends Component {
  constructor(props){
  	super(props);
  	this.state = {
      baseUrl:window.json_baseurl,
      searchBaseUrl:window.json_searchbaseurl,
      cat_title:window.json_cat_title,
      hasIdentity:window.json_hasIdentity,
      is_show_title:window.json_is_show_title,
      redirectString:window.json_redirectString,
      serverUrl:window.json_serverUrl,
      serverUri:window.json_serverUri,
      store:{
        sName:window.json_sname,
        name:window.json_store_name,
        order:window.json_store_order,
        last_char_store_order:window.json_last_char_store_order,
      },
      user:window.json_member,
      logo:window.json_logoWidth,
      cat_title_left:window.json_cat_title_left,
      tabs_left:window.tabs_left,
      template:window.json_template,
      status:"",
      section:window.json_section,
      url_logout:window.json_logouturl
    };
    this.updateDimensions = this.updateDimensions.bind(this);
  }

  componentWillMount() {
    this.updateDimensions();
  }

  componentDidMount() {
    window.addEventListener("resize", this.updateDimensions);
    window.addEventListener("orientationchange",this.updateDimensions);
  }

  updateDimensions(){
    const width = window.innerWidth;
    let device;
    if (width >= 910){
      device = "large";
    } else if (width < 910 && width >= 610){
      device = "mid";
    } else if (width < 610){
      device = "tablet";
    }
    this.setState({device:device});
  }

  render(){
    let  siteHeaderTopRightCssClass;
    let logoLink = this.state.serverUrl;
    if (this.state.serverUri.indexOf('/s/') > -1){
      logoLink += "/s/" + this.state.store.name;
    }

    let siteHeaderStoreNameDisplay;
    if (this.state.is_show_title === "1"){
      siteHeaderStoreNameDisplay = (
        <div id="site-header-store-name-container">
          <a href={logoLink}>
            {this.state.store.name}
          </a>
        </div>
      );
    }
    let PlingDisplay;
    if(this.state.section && this.state.user)
    {
      if(this.state.user.isAdmin)
      {
        PlingDisplay =
            <div id="siter-header-pling">
            <Support section={this.state.section} amount={this.state.section.amount}
                 goal = {this.state.section.goal} amount_factor={this.state.section.amount_factor}
                 isAdmin = {this.state.user.isAdmin} 
            />
            </div>
      }
    }

    let HeaderDisplay;
    if (this.state.device !== "tablet"){
      HeaderDisplay = (
        <section id="site-header-wrapper" style={{"paddingLeft":this.state.template['header-logo']['width']}}>
          <div id="siter-header-left">
            <div id="site-header-logo-container" style={this.state.template['header-logo']}>
              <a href={logoLink}>
                <img src={this.state.template['header-logo']['image-src']}/>
              </a>
            </div>
            {siteHeaderStoreNameDisplay}
          </div>


          <div id="site-header-right">
            <div id="site-header-right-top" className={siteHeaderTopRightCssClass}>
              <SiteHeaderSearchForm
                baseUrl={this.state.baseUrl}
                searchBaseUrl={this.state.searchBaseUrl}
                store={this.state.store}
                height={this.state.template.header['height']}
              />
                { PlingDisplay }
            </div>

          </div>
        </section>
      );
    } else {
      HeaderDisplay = (
        <MobileSiteHeader
          logoLink={logoLink}
          template={this.state.template}
          user={this.state.user}
          baseUrl={this.state.baseUrl}
          searchBaseUrl={this.state.searchBaseUrl}
          serverUrl={this.state.serverUrl}
          store={this.state.store}
          redirectString={this.state.redirectString}
        />
      )
    }

    let templateHeaderStyle;
    if (this.state.template){
      templateHeaderStyle = {
        "backgroundImage":this.state.template.header['background-image'],
        "backgroundColor":this.state.template.header['background-color'],
        "height":this.state.template.header['height']
      }
    }

    let headerStoreClassName = this.state.store.name.toLowerCase();
    if (headerStoreClassName.indexOf('.') > -1) headerStoreClassName = headerStoreClassName.split('.')[0]

    return (
      <section id="site-header" style={templateHeaderStyle} className={headerStoreClassName}>
        {HeaderDisplay}
      </section>
    )
  }
}

export default SiteHeader;
