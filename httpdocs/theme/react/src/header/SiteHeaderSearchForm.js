import React from 'react';

class SiteHeaderSearchForm extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      searchText:''
    };
    this.onSearchTextChange = this.onSearchTextChange.bind(this);
    this.onSearchFormSubmit = this.onSearchFormSubmit.bind(this);
  }

  onSearchTextChange(e){
    this.setState({searchText:e.target.value});
  }

  onSearchFormSubmit(e){
    e.preventDefault();
    window.location.href = this.props.searchBaseUrl  + this.state.searchText;
  }

  render(){

    let siteHeaderSearchFormStyle;

    if (this.props.store.name.toLowerCase().indexOf("appimagehub") > -1) {
      let tHeight = parseInt(this.props.height.split('px')[0]);
      siteHeaderSearchFormStyle = {
        "marginTop": (tHeight / 2) - 19 + "px"
      }
    }

    return (
      <div id="site-header-search-form" style={siteHeaderSearchFormStyle}>
        <form id="search-form" onSubmit={this.onSearchFormSubmit}>
          <input onChange={this.onSearchTextChange} value={this.state.searchText} type="text" name="projectSearchText" />
          <a onClick={this.onSearchFormSubmit}></a>
        </form>
      </div>
    )
  }
}

export default SiteHeaderSearchForm;
