class App extends React.Component {
  constructor(props){
  	super(props);
  	this.state = { };
  }

  componentDidMount() {
    console.log('opendesktop app homepage');
    console.log(window.data);
  }

  render(){
    return (
      <main id="opendesktop-homepage">
        <p>test homepage</p>
      </main>
    )
  }
}

ReactDOM.render(
    <App />,
    document.getElementById('main-content')
);
