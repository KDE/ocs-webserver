class MetaHeader extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    return (
      <ul className="nav nav-pills meta-nav-top right">
         <li><a href="/community" > Community </a> </li>
         <li><a href="<?=$url_forum?>" target="_blank"> Forum</a> </li>
         <li><a href="<?=$url_blog?>" target="_blank"> Blog</a> </li>
         <li><a id="plingList" href="/plings" className="popuppanel"> What are Plings? </a></li>
         <li><a id="ocsapiContent" href="/partials/ocsapicontent.phtml" className="popuppanel"> API </a> </li>
         <li><a id="aboutContent" href="/partials/about.phtml" className="popuppanel">About </a></li>
         <li id="user-context-dropdown-container" className="metaheader-menu-item">
           <div id="user-dropdown">
             <button className="btn btn-default dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
               <span className="glyphicon glyphicon-th"></span>
             </button>
             <ul className="dropdown-menu three-column dropdown-menu-right" aria-labelledby="dropdownMenu2">
               <li id="opendesktop-link-item">
                 <a href="http://www.opendesktop.org">
                   <div className="icon"></div>
                   <span>OpenDesktop</span>
                 </a>
               </li>
               <li id="discourse-link-item">
                 <a href="http://discourse.opendesktop.org/">
                   <div className="icon"></div>
                   <span>Discourse</span>
                 </a>
               </li>
               <li id="gitlab-link-item">
                 <a href="https://about.gitlab.com/">
                   <div className="icon"></div>
                   <span>Gitlab</span>
                 </a>
               </li>
               <li id="opencode-link-item">
                 <a href="https://www.opencode.net/">
                   <div className="icon"></div>
                   <span>OpenCode</span>
                 </a>
               </li>
             </ul>
           </div>
         </li>
         <li id="user-dropdown-container" className="metaheader-menu-item">
           <div id="user-dropdown">
             <button className="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
               <img src="<?=$profile_image_url?>"/>
             </button>
             <ul className="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu1">
               <li id="user-info-menu-item">
                 <div id="user-info-section">
                   <div className="user-avatar">
                     <div className="no-avatar-user-letter">
                       <img src="<?=$profile_image_url?>"/>
                       <a className="change-profile-pic">
                         Change
                       </a>
                     </div>
                   </div>
                   <div className="user-details">
                     <ul>
                       <li><b>username</b></li>
                       <li>$loginMember->mail</li>
                       <li></li>
                       <li><a>Profile</a> - <a>Privacy</a></li>
                       <li><button className="btn btn-default btn-blue">Account</button></li>
                     </ul>
                   </div>
                 </div>
               </li>
               <li id="main-seperator" role="separator" className="divider"></li>
               <li className="buttons">
                 <button className="btn btn-default btn-metaheader">Add Account</button>
                 <button className="btn btn-default pull-right btn-metaheader">Sign Up</button>
               </li>
             </ul>
           </div>
         </li>
         <li id="user-signin-button" className="metaheader-menu-item"><button className="btn btn-default btn-blue"><a href="/login">Login</a></button></li>
      </ul>
    )
  }
}

ReactDOM.render(
    <MetaHeader />,
    document.getElementById('metaheader-menu')
);
