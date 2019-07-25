import React from 'react';
import SwitchItem from './SwitchItem';
import MyButton from './MyButton';

class UserLoginMenuContainer extends React.Component {
  constructor(props){
    super(props);
    this.state = {};
    this.handleClick = this.handleClick.bind(this);
    this.loadNotification = this.loadNotification.bind(this);
    this.loadAnonymousDl = this.loadAnonymousDl.bind(this);
  }

  componentWillMount() {
    document.addEventListener('click',this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('click',this.handleClick, false);
  }

  componentDidMount(){
    this.loadNotification();
    this.loadAnonymousDl();
   }

   loadAnonymousDl(){
       let url = this.props.baseUrlStore+'/json/anonymousdl';
       fetch(url,{
                  mode: 'cors',
                  credentials: 'include'
                  })
       .then(response => response.json())
       .then(data => {
          this.setState(prevState => ({ anonymousdl: data.dls , section:data.section}));
        });
   }
  loadNotification(){
    if(this.props.user){
      let url = this.props.baseUrl+'/membersetting/notification';
      fetch(url,{
                 mode: 'cors',
                 credentials: 'include'
                 })
      .then(response => response.json())
      .then(data => {
          if(data.notifications){
            const nots = data.notifications.filter(note => note.read==false);
            if(nots.length>0 && this.state.notification_count !== nots.length)
            {
                this.setState(prevState => ({ notification: true, notification_count:nots.length }))
            }
          }
       });
     }
  }

  handleClick(e){
    let dropdownClass = "";

    if (this.node.contains(e.target)){
      if (this.state.dropdownClass === "open"){
        if (e.target.className === "th-icon" || e.target.className === "btn btn-default dropdown-toggle"){
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
    const theme = this.props.onSwitchStyleChecked?"Metaheader theme dark":"Metaheader theme light";
    let badgeNot;
    if(this.state.notification)
    {
      badgeNot = (<span className="badge-notification">{this.state.notification_count}</span>);
    }

    let plingSectionDisplay;
    if (this.state.section){
      let sections = this.state.section.map((mg,i) => (
        <div className="section">{mg.name}: {mg.dls}</div>
      ));

      plingSectionDisplay = (<li className="user-pling-section-container">
                            <div className="title">Download Pling Section</div>
                              {sections}
                            </li>
                          );
    }

    // let contextMenuDisplay;
    //
    // contextMenuDisplay = (
    //   <ul className="user-context-menu-container">
    //     <MyButton id="addproduct-link-item" url={this.props.baseUrlStore+"/product/add"} label="Add Product" />
    //     <MyButton id="addproduct-link-item" url={this.props.baseUrlStore+"/collection/add"} label="Add Collection" />
    //     <MyButton id="addproduct-link-item" url={this.props.baseUrlStore+"/projects/new"} label="Add Project" />
    //
    //     <MyButton id="listproduct-link-item" url={this.props.baseUrlStore + "/u/" + this.props.user.username + "/products"} label="Products" />
    //     <MyButton id="listproduct-link-item" url={this.props.baseUrlStore + "/u/" + this.props.user.username + "/collections"} label="Collections" />
    //     <MyButton id="opencode-link-item" url={this.props.gitlabUrl+"/dashboard/projects"} label="Projects" />
    //
    //     <MyButton id="plings-link-item" url={this.props.baseUrlStore + "/u/" + this.props.user.username + "/payout"} label="Payout" />
    //     <MyButton id="issues-link-item" url={this.state.gitlabLink} label="Issues" />
    //   </ul>
    // );

    // if (this.props.isAdmin){
    //   contextMenuDisplay = (
    //     <ul className="user-context-menu-container">
    //        <MyButton id="storage-link-item"
    //                url={this.props.myopendesktopUrl}
    //                label="Storage" />
    //        <MyButton id="calendar-link-item"
    //                url={this.props.myopendesktopUrl+"/index.php/apps/calendar/"}
    //                label="Calendar" />
    //        <MyButton id="contacts-link-item"
    //                url={this.props.myopendesktopUrl+"/index.php/apps/contacts/"}
    //                label="Contacts" />
    //        <MyButton id="messages-link-item"
    //                url={this.props.forumUrl+"/u/"+this.props.user.username+"/messages"}
    //                label="Messages"
    //                badge={badgeNot}
    //                 />
    //       <MyButton id="docs-link-item"
    //               url={this.props.docsopendesktopUrl}
    //               label="Docs" />
    //       <MyButton id="music-link-item"
    //               url={this.props.musicopendesktopUrl}
    //               label="Music" />
    //     </ul>
    //   );
    // } else {
    //   contextMenuDisplay = (
    //     <ul  className="user-context-menu-container">
    //       <MyButton id="storage-link-item"
    //               url={this.props.myopendesktopUrl}
    //               label="Storage" />
    //       <MyButton id="calendar-link-item"
    //               url={this.props.myopendesktopUrl+"/index.php/apps/calendar/"}
    //               label="Calendar" />
    //       <MyButton id="contacts-link-item"
    //               url={this.props.myopendesktopUrl+"/index.php/apps/contacts/"}
    //               label="Contacts" />
    //       <MyButton id="messages-link-item"
    //               url={this.props.forumUrl+"/u/"+this.props.user.username+"/messages"}
    //               label="Messages"
    //               badge={badgeNot}
    //                />
    //     </ul>
    //   );
    // }

    return (
      <li id="user-login-menu-container" ref={node => this.node = node}>
        <div className={"user-dropdown " + this.state.dropdownClass}>
          <button
            className="btn btn-default dropdown-toggle"
            type="button"
            id="userLoginDropdown">
            <img className="th-icon" src={this.props.user.avatar}/>
            {badgeNot}
          </button>
          <ul className="dropdown-menu dropdown-menu-right">
            <li id="user-info-menu-item">
              <div id="user-info-section">
                <div className="user-avatar">
                  <div className="no-avatar-user-letter">
                    <img src={this.props.user.avatar}/>
                  </div>
                </div>
                <div className="user-details">
                  <ul>
                    <li id="user-details-username"><b>{this.props.user.username}</b></li>
                    <li id="user-details-email">{this.props.user.mail}</li>
                    {this.props.user.isSupporter ? (
                      <li id="user-is-supporter">Thanks for being a supporter!</li>
                    ) : (
                      <li id="user-is-supporter"> <a className="become-supporter" href={this.props.baseUrl+"/support"}>Become a supporter</a> </li>
                    )}

                  </ul>
                </div>
              </div>
            </li>

            {plingSectionDisplay}

            {
              /*
            <li className="user-context-menu">
              {contextMenuDisplay}
            </li>*/
            }
            <li className="user-settings-item">
             <span className="user-settings-item-title">Metaheader theme light</span>
               <SwitchItem onSwitchStyle={this.props.onSwitchStyle}
                        onSwitchStyleChecked={this.props.onSwitchStyleChecked}/>
              <span className="user-settings-item-title">dark</span>
            </li>
            <li className="buttons">
              <a href={this.props.baseUrl + "/settings/"} className="btn btn-default btn-metaheader"><span>Settings</span></a>
              <a href={this.props.baseUrl + "/settings/profile"} className="btn btn-default btn-metaheader"><span>Profile</span></a>
              <a href={this.props.logoutUrl} className="btn btn-default pull-right btn-metaheader"><span>Logout</span></a>
            </li>

          </ul>
        </div>
      </li>
    )
  }
}

export default UserLoginMenuContainer;
