import "core-js/shim";
import "regenerator-runtime/runtime";
import React from 'react';
import MobileLeftMenu from './MobileLeftMenu';
import DomainsMenu from './DomainsMenu';
import UserMenu from './UserMenu';
import SearchForum from "./SearchForum";

class MetaHeader extends React.Component {
  constructor(props){
    super(props);
    this.state = {...this.props.config};
    this.initMetaHeader = this.initMetaHeader.bind(this);
    this.updateDimensions = this.updateDimensions.bind(this);
    this.onSwitchStyle = this.onSwitchStyle.bind(this);

    //this.getUser = this.getUser.bind(this);
  }


  componentWillMount() {
    this.updateDimensions();
  }

   componentDidMount() {

    this.initMetaHeader();

  }

  componentWillUnmount(){
    window.removeEventListener("resize", this.updateDimensions);
    window.removeEventListener("orientationchange",this.updateDimensions);

  }

  initMetaHeader(){
    window.addEventListener("resize", this.updateDimensions);
    window.addEventListener("orientationchange",this.updateDimensions);
  }

  // change metamenu class
  onSwitchStyle(evt){
     let url = 'https://www.opendesktop.org/membersetting/setsettings/itemid/1/itemvalue/';
     if(this.state.isExternal)
     {
       if (this.props.hostname.endsWith('cc') || this.props.hostname.endsWith('local')) {
         url = 'https://www.opendesktop.cc/membersetting/setsettings/itemid/1/itemvalue/';
       }
     }else
     {
       url = '/membersetting/setsettings/itemid/1/itemvalue/';
     }
     url = url +(evt.target.checked?'1':'0');
     const isChecked = evt.target.checked;
     fetch(url,{
                mode: 'cors',
                credentials: 'include'
                })
      .then(response => response.json())
      .then(data => {
          this.setState({metamenuTheme:`${isChecked?'metamenu-theme-dark':''}`});
      });
  }

  getUser(){
    const decodedCookie = decodeURIComponent(document.cookie);
    let ocs_data = decodedCookie.split('ocs_data=')[1];
    if (ocs_data){
      if (ocs_data.indexOf(';') > -1){ ocs_data = ocs_data.split(';')[0]; }
      const user = JSON.parse(ocs_data);
      this.setState({user:user});
    }
  }

  updateDimensions(){
    const width = window.innerWidth;
    let device;
    if (width >= 1015){
      device = "large";
    } else if (width < 1015 && width >= 730){
      device = "mid";
    } else if (width < 730){
      device = "tablet";
    }
    this.setState({device:device});
  }

  render(){

    let domainsMenuDisplay;
    if (this.state.device === "tablet"){
      domainsMenuDisplay = (
        <MobileLeftMenu
          device={this.state.device}
          domains={this.state.domains}
          user={this.state.user}
          baseUrl={this.state.baseUrl}
          baseUrlStore={this.state.baseUrlStore}
          blogUrl={this.state.blogUrl}
          forumUrl={this.state.forumUrl}
          sName={this.state.sName}
          isAdmin={this.state.isAdmin}
          isExternal={this.state.isExternal}
          gitlabUrl={this.state.gitlabUrl}
          riotUrl={this.state.riotUrl}
        />
      )
    } else {

      domainsMenuDisplay = (
        <DomainsMenu
          device={this.state.device}
          domains={this.state.domains}
          user={this.state.user}
          baseUrl={this.state.baseUrl}
          baseUrlStore={this.state.baseUrlStore}
          blogUrl={this.state.blogUrl}
          forumUrl={this.state.forumUrl}
          sName={this.state.sName}
          loginUrl={this.state.loginUrl}
          logoutUrl={this.state.logoutUrl}
          gitlabUrl={this.state.gitlabUrl}
          myopendesktopUrl={this.state.myopendesktopUrl}
          cloudopendesktopUrl={this.state.cloudopendesktopUrl}
          musicopendesktopUrl={this.state.musicopendesktopUrl}
          docsopendesktopUrl={this.state.docsopendesktopUrl}
          isAdmin={this.state.isAdmin}
          onSwitchStyle={this.onSwitchStyle}
          onSwitchStyleChecked={paraChecked}
          isExternal={this.state.isExternal}
          riotUrl={this.state.riotUrl}
        />
      )
    }
    const metamenuCls = `metamenu ${this.state.metamenuTheme}`;
    let paraChecked = false;
    if(this.state.metamenuTheme){
        paraChecked=true;
    }

    return (
      <nav id="metaheader-nav" className="metaheader">
        <div style={{"display":"block"}} className={metamenuCls}>
          {domainsMenuDisplay}

          <UserMenu
            device={this.state.device}
            user={this.state.user}
            baseUrl={this.state.baseUrl}
            baseUrlStore={this.state.baseUrlStore}
            blogUrl={this.state.blogUrl}
            forumUrl={this.state.forumUrl}
            loginUrl={this.state.loginUrl}
            logoutUrl={this.state.logoutUrl}
            gitlabUrl={this.state.gitlabUrl}
            myopendesktopUrl={this.state.myopendesktopUrl}
            cloudopendesktopUrl={this.state.cloudopendesktopUrl}
            musicopendesktopUrl={this.state.musicopendesktopUrl}
            docsopendesktopUrl={this.state.docsopendesktopUrl}
            isAdmin={this.state.isAdmin}
            onSwitchStyle={this.onSwitchStyle}
            onSwitchStyleChecked={paraChecked}
            isExternal={this.state.isExternal}
            riotUrl={this.state.riotUrl}
          />
          <SearchForum searchBaseUrl={this.state.baseUrlStore+'/search/projectSearchText/'}/>
          

        </div>
      </nav>
    )
  }
}

export default MetaHeader;
