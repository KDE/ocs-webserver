import React from 'react';
import MobileLeftSidePanel from './MobileLeftSidePanel';
class MobileLeftMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {
      overlayClass:""
    };
    this.toggleLeftSideOverlay = this.toggleLeftSideOverlay.bind(this);
    this.handleClick = this.handleClick.bind(this);
  }

  componentWillMount() {
    window.addEventListener('mousedown',this.handleClick, false);
    window.addEventListener('touchend', this.handleClick, false);
  }

  componentWillUnmount() {
    window.removeEventListener('mousedown',this.handleClick, false);
    window.addEventListener('touchend', this.handleClick, false);
  }

  toggleLeftSideOverlay(){
    let overlayClass = "open";
    if (this.state.overlayClass === "open") {
      overlayClass = "";
    }
    this.setState({overlayClass:overlayClass});
  }

  handleClick(e){
    let overlayClass = "";
    if (this.node.contains(e.target)){
      if (this.state.overlayClass === "open"){
        if (e.target.id === "left-side-overlay" || e.target.id === "menu-toggle-item"){
          overlayClass = "";
        } else {
          overlayClass = "open";
        }
      } else {
        overlayClass = "open";
      }
    }

    const self = this;
    setTimeout(function () {
      console.log('time out');
      self.setState({overlayClass:overlayClass});
    }, 200);
  }

  render(){
    return (
      <div ref={node => this.node = node}  id="metaheader-left-mobile" className={this.state.overlayClass}>
        <a className="menu-toggle" id="menu-toggle-item"></a>
        <div id="left-side-overlay">
          <MobileLeftSidePanel
            domains={this.props.domains}
            baseUrl={this.props.baseUrl}
            blogUrl={this.props.blogUrl}
            forumUrl={this.props.forumUrl}
            isAdmin={this.props.isAdmin}
            user={this.props.user}
            baseUrl={this.props.baseUrl}
            gitlabUrl={this.props.gitlabUrl}
            baseUrlStore={this.props.baseUrlStore}
            riotUrl={this.props.riotUrl}
          />
        </div>
      </div>
    );
  }
}

export default MobileLeftMenu;
