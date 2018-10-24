class SiteHeader extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }
  render(){
    return (
      <section id="site-header">
        site header
      </section>
    )
  }
}


ReactDOM.render(
    <SiteHeader />,
    document.getElementById('site-header-container')
);
