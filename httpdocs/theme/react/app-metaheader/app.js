const { Provider, connect } = ReactRedux;
const store = Redux.createStore(reducer);

class App extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    return (
      <ul className="nav nav-pills meta-nav-top right" style="margin-right: 30px;">
         <li><a href="/community" > Community </a> </li>
         <li><a href="<?=$url_forum?>" target="_blank"> Forum</a> </li>
         <li><a href="<?=$url_blog?>" target="_blank"> Blog</a> </li>
         <li><a id="plingList" href="/plings" className="popuppanel"> What are Plings? </a></li>
         <li><a id="ocsapiContent" href="/partials/ocsapicontent.phtml" className="popuppanel"> API </a> </li>
         <li><a id="aboutContent" href="/partials/about.phtml" className="popuppanel">About </a></li>
         <li id="user-context-dropdown-container">
           <div id="user-dropdown">
             <button className="btn btn-default dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
               <span className="glyphicon glyphicon-th"></span>
             </button>
             <ul className="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
               <li id="opendesktop-link-item"><a href="http://www.opendesktop.org"></a></li>
               <li id="discourse-link-item"><a href="http://discourse.opendesktop.org/"></a></li>
               <li id="gitlab-link-item"><a href="https://about.gitlab.com/"></a></li>
             </ul>
           </div>
         </li>
         <li id="user-dropdown-container">
           <div id="user-dropdown">
             <button className="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
               <span className="glyphicon glyphicon-user"></span>
             </button>
             <ul className="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu1">
               <li id="user-info-menu-item">
                 <div id="user-info-section">
                   <div className="user-avatar">
                     <div className="no-avatar-user-letter"><img src="<?=$profile_image_url?>"/></div>
                   </div>
                   <div className="user-details">
                     username
                   </div>
                 </div>
               </li>
               <li id="main-seperator" role="separator" className="divider"></li>
               <li className="buttons">
                 <button className="btn btn-default">Add Acount</button>
                 <button className="btn btn-default pull-right">Sign Up</button>
               </li>
             </ul>
           </div>
         </li>
      </ul>
    )
  }
}

class AppWrapper extends React.Component {
  render(){
    return (
      <Provider store={store}>
        <App/>
      </Provider>
    )
  }
}

ReactDOM.render(
    <AppWrapper />,
    document.getElementById('meta-header')
);
