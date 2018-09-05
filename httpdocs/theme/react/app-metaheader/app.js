class MetaHeader extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    console.log('component did mount');

  }

  render(){
    return (
      <nav id="metaheader-nav">
        <DomainsMenu/>
        <UserMenu
          user={this.state.user}
        />
      </nav>
    )
  }
}

class DomainsMenu extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    return (
      <ul className="metaheader-menu left" id="domains-menu">
        <li><a href="#">test</a></li>
        <li><a href="#">test</a></li>
        <li><a href="#">test</a></li>
        <li><a href="#">test</a></li>
        <li><a href="#">test</a></li>
        <li><a href="#">test</a></li>
      </ul>
    )
  }
}

class UserMenu extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }
  render(){
    let userDropdownDisplay;
    if (this.props.user){
      userDropdownDisplay = (
        <li>User</li>
      );
    } else {
      userDropdownDisplay = (
        <li id="user-login-container"><a className="btn btn-metaheader">Login</a></li>
      )
    }
    return (
      <div id="user-menu-container" className="right">
        <ul className="metaheader-menu right" id="user-menu">
          <li><a href="/community">Community</a></li>
          <li><a href="<?=$url_blog?>" target="_blank">Blog</a></li>
          <li><a id="plingList" className="popuppanel" href="/plings">What are Plings?</a></li>
          <li><a id="ocsapiContent" className="popuppanel" href="/partials/ocsapicontent.phtml">API</a></li>
          <li><a id="aboutContent" className="popuppanel" href="/partials/about.phtml" >About</a></li>
          <li><span className="glyphicon glyphicon-th"></span></li>
          {userDropdownDisplay}
        </ul>
      </div>
    )
  }
}

ReactDOM.render(
    <MetaHeader />,
    document.getElementById('metaheader')
);
