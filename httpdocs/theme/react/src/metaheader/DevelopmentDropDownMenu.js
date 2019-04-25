import React from 'react';
class DevelopmentDropDownMenu extends React.Component {
  constructor(props){
    super(props);    
    this.state = {
      /*gitlabLink:this.props.gitlabUrl+"/dashboard/issues?assignee_id="*/
    };
    this.handleClick = this.handleClick.bind(this);
  }

  componentWillMount() {
    document.addEventListener('mousedown',this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('mousedown',this.handleClick, false);
  }

  componentDidMount() {
    /*const self = this;
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        const res = JSON.parse(this.response);
        const gitlabLink = self.state.gitlabLink + res[0].id;
        self.setState({gitlabLink:gitlabLink,loading:false});
      }
    };
    xhttp.open("GET", this.props.gitlabUrl+"/api/v4/users?username="+this.props.user.username, true);
    xhttp.send();*/
  }

  handleClick(e){
    let dropdownClass = "";
    if (this.node.contains(e.target)){
      if (this.state.dropdownClass === "open"){
        if (e.target.className === "admins-menu-link-item"){
          dropdownClass = "";
        } else {
          dropdownClass = "open";
        }
      } else {
        dropdownClass = "open";
      }
    }
    this.setState({dropdownClass:dropdownClass});
  }

  render(){
    let issuesMenuItem;

   
    if (this.props.isAdmin){
      issuesMenuItem = (
        <li><a href={this.props.gitlabUrl + "/dashboard/issues?milestone_title=No+Milestone&state=all"}>Issues</a></li>
      )
    }

    let gitfaqLinkItem;
    if (this.props.isExternal === false){
      gitfaqLinkItem = (<li><a className="popuppanel" id="gitfaq" href={"/gitfaq"}>FAQ</a></li>);
    } else {
      gitfaqLinkItem = (<li><a className="popuppanel" target="_blank" id="faq" href={this.props.baseUrl + "/#gitfaq"}>Git FAQ</a></li>);
    }

    return (
      <li ref={node => this.node = node} id="admins-dropdown-menu" className={this.state.dropdownClass}>
        <a className="admins-menu-link-item">Development</a>
        <ul className="dropdown-menu dropdown-menu-right">
          <li><a href={this.props.gitlabUrl + "/explore/projects"}>Opencode.net</a></li>
          {issuesMenuItem}
          {gitfaqLinkItem}
        </ul>
      </li>
    )
  }
}

export default DevelopmentDropDownMenu;
