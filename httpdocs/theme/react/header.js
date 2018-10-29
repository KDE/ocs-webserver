class SiteHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }
  render() {
    return React.createElement(
      "section",
      { id: "site-header" },
      "site header"
    );
  }
}

ReactDOM.render(React.createElement(SiteHeader, null), document.getElementById('site-header-container'));
