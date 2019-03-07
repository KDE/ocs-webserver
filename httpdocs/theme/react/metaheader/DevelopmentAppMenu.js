import React from 'react';
class DevelopmentAppMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {
      gitlabLink:this.props.gitlabUrl+"/dashboard/issues?assignee_id="
    };
    this.handleClick = this.handleClick.bind(this);
  }

  componentWillMount() {
    //document.addEventListener('mousedown',this.handleClick, false);
    document.addEventListener('click',this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('click',this.handleClick, false);
  }

  componentDidMount() {
    const self = this;
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState === 4 && this.status === 200) {
        const res = JSON.parse(this.response);
        const gitlabLink = self.state.gitlabLink + res[0].id;
        self.setState({gitlabLink:gitlabLink,loading:false});
      }
    };
    xhttp.open("GET", this.props.gitlabUrl+"/api/v4/users?username="+this.props.user.username, true);
    xhttp.send();
  }

  handleClick(e){
    let dropdownClass = "";
    if (this.node.contains(e.target)){
      if(e.target.className === "btn btn-default dropdown-toggle")
      {
        // only btn click open dropdown
        if (this.state.dropdownClass === "open"){
          dropdownClass = "";
        }else{
          dropdownClass = "open";
        }
      }else{
        dropdownClass = "";
      }

      // if (this.state.dropdownClass === "open"){
      //   if (e.target.className === "th-icon" || e.target.className === "btn btn-default dropdown-toggle"){
      //     dropdownClass = "";
      //   } else {
      //     dropdownClass = "open";
      //   }
      // } else {
      //   dropdownClass = "open";
      // }
    }
    this.setState({dropdownClass:dropdownClass});
  }

  render(){

    const urlEnding = this.props.baseUrl.split('opendesktop.')[1];

    return (
      <li ref={node => this.node = node} id="development-app-menu-container">
        <div className={"user-dropdown " + this.state.dropdownClass}>
          <button
            className="btn btn-default dropdown-toggle" type="button" onClick={this.toggleDropDown}>
            <span className="th-icon"></span>
          </button>
          <ul id="user-context-dropdown" className="dropdown-menu dropdown-menu-right">
            <li id="addproduct-link-item">
              <a href={this.props.baseUrl+"/product/add"}>
                <div className="icon"></div>
                <span>Add Product</span>
              </a>
            </li>
            <li id="listproduct-link-item">
              <a href={this.props.baseUrl + "/u/" + this.props.user.username + "/products"}>
                <div className="icon"></div>
                <span>Products</span>
              </a>
            </li>
            <li id="plings-link-item">
              <a href={this.props.baseUrl + "/u/" + this.props.user.username + "/plings"}>
                <div className="icon"></div>
                <span>Plings</span>
              </a>
            </li>
            <li id="addproduct-link-item">
              <a href={this.props.gitlabUrl+"/projects/new"}>
                <div className="icon"></div>
                <span>Add Project</span>
              </a>
            </li>
            <li id="opencode-link-item">
              <a href={this.props.gitlabUrl+"/dashboard/projects"}>
                <div className="icon"></div>
                <span>Projects</span>
              </a>
            </li>
            <li id="issues-link-item">
              <a href={this.state.gitlabLink}>
                <div className="icon"></div>
                <span>Issues</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
    )
  }
}

export default DevelopmentAppMenu;
