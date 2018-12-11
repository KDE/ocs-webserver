class App extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentDidMount() {
    console.log('opendesktop app homepage');
    console.log(window.data);
  }

  render() {
    return React.createElement(
      'main',
      { id: 'opendesktop-homepage' },
      React.createElement(
        'p',
        null,
        'test homepage'
      )
    );
  }
}

ReactDOM.render(React.createElement(App, null), document.getElementById('main-content'));
