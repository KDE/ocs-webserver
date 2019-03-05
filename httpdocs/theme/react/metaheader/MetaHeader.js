import "core-js/shim";
import "regenerator-runtime/runtime";
import React from 'react';
import MobileLeftMenu from './MobileLeftMenu';
import DomainsMenu from './DomainsMenu';
import UserMenu from './UserMenu';

class MetaHeader extends React.Component {
  constructor(props){
    super(props);
    this.state = {};
    this.initMetaHeader = this.initMetaHeader.bind(this);
    this.updateDimensions = this.updateDimensions.bind(this);
    this.onSwitchStyle = this.onSwitchStyle.bind(this);

    //this.getUser = this.getUser.bind(this);
  }


  componentWillMount() {
    this.updateDimensions();
    this.setState({...this.props.config});
  }

   componentDidMount() {

    this.initMetaHeader();

    //this.initMetamenuTheme();
    //this.fetchState();

  }

  componentWillUnmount(){
    window.removeEventListener("resize", this.updateDimensions);
    window.removeEventListener("orientationchange",this.updateDimensions);

  }

  initMetaHeader(){
    window.addEventListener("resize", this.updateDimensions);
    window.addEventListener("orientationchange",this.updateDimensions);
    //this.getUser();
  }

  // async fetchState()
  // {
  //         let url = `https://www.opendesktop.org/home/metamenubundlejs?
  //               target=${this.props.target}
  //               &url=${this.props.redirect}`;
  //               try {
  //                 const response = await fetch(url, {
  //                   mode: 'cors',
  //                   credentials: 'include'
  //                 });
  //                 if (!response.ok) {
  //                   throw new Error('Network response error');
  //                 }
  //                 let config = await response.json();
  //                 config.isAdmin = config.json_isAdmin;
  //                 this.setState({...config});
  //                 return true;
  //               }
  //               catch (error) {
  //                 console.error(error);
  //                 return false;
  //               }
  //
  //
  // }

  // fetchMetaheaderThemeSettings(){
  //
  //    let url = 'https://www.opendesktop.org/membersetting/getsettings';
  //    if (location.hostname.endsWith('cc') || location.hostname.endsWith('local')) {
  //      url = 'https://www.opendesktop.cc/membersetting/getsettings';
  //    }
  //
  //    fetch(url,{
  //               mode: 'cors',
  //               credentials: 'include'
  //               })
  //     .then(response => response.json())
  //     .then(data => {
  //       const results = data.results;
  //       if(results.length>0)
  //       {
  //         const theme = results.filter(r => r.member_setting_item_id == 1);
  //         if(theme.length>0 && theme[0].value==1)
  //         {
  //            this.setState({metamenuTheme:'metamenu-theme-dark'});
  //         }
  //       }
  //     });
  //
  //
  // }

  // change metamenu class
  onSwitchStyle(evt){

     let url = 'https://www.opendesktop.org/membersetting/setsettings/itemid/1/itemvalue/';     
     if(this.props.isExternal)
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
          blogUrl={this.state.blogUrl}
          forumUrl={this.state.forumUrl}
          sName={this.state.sName}
          isAdmin={this.state.isAdmin}

          gitlabUrl={this.state.gitlabUrl}
        />
      )
    } else {

      domainsMenuDisplay = (
        <DomainsMenu
          device={this.state.device}
          domains={this.state.domains}
          user={this.state.user}
          baseUrl={this.state.baseUrl}
          blogUrl={this.state.blogUrl}
          forumUrl={this.state.forumUrl}
          sName={this.state.sName}
          isAdmin={this.state.isAdmin}
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
            blogUrl={this.state.blogUrl}
            forumUrl={this.state.forumUrl}
            loginUrl={this.state.loginUrl}
            logoutUrl={this.state.logoutUrl}
            gitlabUrl={this.state.gitlabUrl}
            isAdmin={this.state.isAdmin}
            onSwitchStyle={this.onSwitchStyle}
            onSwitchStyleChecked={paraChecked}
          />
        </div>
      </nav>
    )
  }
}

export default MetaHeader;


