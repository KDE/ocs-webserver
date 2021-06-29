import React, { Component } from 'react';
import MobileSiteHeader from './MobileSiteHeader';
import Support from './Support';

import './../style/header.css';

class SiteHeader extends Component {
  constructor(props){
  	super(props);
  	this.state = {
      baseUrl:headerData.baseurl,
      searchBaseUrl:headerData.searchbaseurl, // ?
      cat_title:headerData.catTitle, // ?
      // hasIdentity:headerData.hasIdentity, // ?
      is_show_title:headerData.ocsStoreConfig.is_show_title, // ?
      // redirectString:headerData.redirectString, // ?
      // serverUrl:headerData.serverUrl, //?
      // serverUri:headerData.serverUri, //?
      store:{
        sName:headerData.ocsStoreConfig.name, // ?
        name:headerData.ocsStoreConfig.name, // ?
        order:headerData.store_order, // ?
        last_char_store_order:headerData.last_char_store_order, // ?
      },
      // user:headerData.member, // ?
      // cat_title_left:headerData.cat_title_left,
      // tabs_left:headerData.tabs_left,
      template:headerData.ocsStoreTemplate,
      status:"",
      section:headerData.sectiondata,
      // url_logout:headerData.logouturl,
      // cat_id:headerData.cat_id,
      // isShowAddProject: headerData.isShowAddProduct,
      // baseurlStore:headerData.baseurlStore,
      header_links:headerData.header_links
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
    const storeName = this.state.store.name.toLowerCase();

    let headerStoreClassName = storeName;
    if (headerStoreClassName.indexOf('.') > -1) headerStoreClassName = headerStoreClassName.split('.')[0];
    let headerLinksStyleBottom = "0px";
    if (headerStoreClassName === "all" || headerStoreClassName === "pling_new_layout" || headerStoreClassName === "kde" || headerStoreClassName === "gnome"){
      headerStoreClassName += " w-padding";
      headerLinksStyleBottom = "-20px";
    }
    
    let  siteHeaderTopRightCssClass;

    let logoLink = headerData.ocsStoreConfig.host;
    if (logoLink.indexOf('https://') === -1) logoLink = "https://" + headerData.ocsStoreConfig.host;
    if (this.state.serverUri && this.state.serverUri.indexOf('/s/') > -1) logoLink += "/s/" + this.state.store.name;

    let headerLinksDisplay;
    if (this.state.header_links){
      headerLinksDisplay = (
        <div className="header-links-display" dangerouslySetInnerHTML={{__html:this.state.header_links}}/>
      )
    } else {
      headerLinksDisplay = (
        <div className="header-links-display">{this.state.cat_title}</div>
      ) 
    }

    let siteTitleDisplay;
    if (this.state.is_show_title === "1") {
      siteTitleDisplay = (
        <a href={logoLink}>
          {this.state.store.name}
        </a>
      )
    }

    const siteHeaderStoreNameDisplay = (
        <div id="site-header-store-name-container" style={{"margin-left":"240px", bottom:headerLinksStyleBottom}}>
          {siteTitleDisplay}
          {headerLinksDisplay}
        </div>
      );
    
    let HeaderDisplay;
    if (this.state.device !== "tablet"){     
      
      let logoStyle = headerData.ocsStoreTemplate['header-logo'];
      if (!logoStyle.left) logoStyle.left = "80px";

      let plingDisplay;
      if(this.state.section && this.state.cat_title && this.state.section.supporters && this.state.section.supporters.length > 0) {
          plingDisplay = (
            <div id="site-header-right">
              <div id="site-header-right-top" className={siteHeaderTopRightCssClass}>
                <div id="siter-header-pling">
                  <Support section={this.state.section} />
                </div>
              </div>
            </div>
          )
      }
      
      let headlineDisplay;
      if (this.state.template.homepage && this.state.template.homepage.headline){
        /*let headlineStyle = {
            left: "240px",
            position: "absolute",
            top: "0"
        }
        headlineDisplay = <div style={headlineStyle} dangerouslySetInnerHTML={{__html:this.state.template.homepage.headline}}></div>
        */
      }
      HeaderDisplay = (
        <section id="site-header-wrapper" >
          <div id="site-header-left">
            <div id="site-header-logo-container" style={logoStyle}>
              <a href={logoLink}>
                <img src={headerData.ocsStoreTemplate['header-logo']['image-src']}/>
              </a>
            </div>
          </div>
          {siteHeaderStoreNameDisplay}
          {headlineDisplay}
          {plingDisplay}
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
          section={this.state.section}
          jsonHeaderLinks={this.state.header_links}
        />
      )
    }

    let templateHeaderStyle = { "height":"140px" }
    if (this.state.template){
      if (this.state.template.header){
        const th = this.state.template.header
        let bgImage = th['background-image'] !== "" ? th['background-image'] : th['background-color'] ? "" : "url(/theme/react/hooks-app/layout/style/media/rounded-rain-cover.png)";
        templateHeaderStyle = {
          backgroundImage:bgImage,
          backgroundColor:this.state.template.header['background-color'],
          height:this.state.template.header.height,
          color:this.state.template.header.color
        }
      }
    }

    return (
      <section id="site-header" style={templateHeaderStyle} className={"pui-s-header " + headerStoreClassName}>
        {HeaderDisplay}
      </section>
    )
  }
}

export default SiteHeader;