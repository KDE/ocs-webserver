const { Provider, connect } = ReactRedux;
const store = Redux.createStore(reducer);

class App extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      loading:true
    };
  }

  componentDidMount() {
    store.dispatch(setProducts(products));
    store.dispatch(setSupporters(supporters));
    store.dispatch(setUsers(users));
    this.setState({loading:false});
  }

  render(){

    return (
      <div id="app-root">
        <Template/>
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
