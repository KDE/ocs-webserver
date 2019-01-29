class CommunityPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      jsonData: window.json_data
    };
  }

  componentDidMount() {
    console.log(this.state);
  }

  render() {
    return React.createElement(
      "div",
      { id: "community-page" },
      "react community"
    );
  }
}

ReactDOM.render(React.createElement(CommunityPage, null), document.getElementById('community-page-container'));
