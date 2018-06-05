const { Provider, connect } = ReactRedux;
const store = Redux.createStore(reducer);

class App extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      loading:true,
      version:1
    };
  }

  componentDidMount() {
    store.dispatch(setProducts(products));
    store.dispatch(setSupporters(supporters));
    store.dispatch(setUsers(users));
    this.setState({loading:false});
  }

  render(){
    let templateDisplay;
    if (this.state.version === 1){
      templateDisplay = <HomePageTemplateOne/>;
    } else if (this.state.version === 2) {
      templateDisplay = <HomePageTemplateTwo/>;
    }
    return (
      <div id="app-root">
        {templateDisplay}
      </div>
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
    document.getElementById('explore-content')
);
