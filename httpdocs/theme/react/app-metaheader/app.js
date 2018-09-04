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
        <UserMenu/>
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
    return (
      <ul className="metaheader-menu right" id="user-menu">
        <li><a href="/community">Community</a></li>
        <li><a href="<?=$url_blog?>" target="_blank">Blog</a></li>
        <li><a id="plingList" className="popuppanel" href="/plings">What are Plings?</a></li>
        <li><a id="ocsapiContent" className="popuppanel" href="/partials/ocsapicontent.phtml">API</a></li>
        <li><a id="aboutContent" className="popuppanel" href="/partials/about.phtml" >About</a></li>
      </ul>
    )
  }
}

ReactDOM.render(
    <MetaHeader />,
    document.getElementById('metaheader')
);
