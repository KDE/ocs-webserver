class CommunityPage extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      jsonData:window.json_data
    };
  }

  componentDidMount() {
    console.log(this.state);
  }

  render(){
    return(
      <div id="community-page">
        react community
      </div>
    );
  }
}

ReactDOM.render(
    <CommunityPage />,
    document.getElementById('community-page-container')
);
