import React from 'react';
import UserAutoCompleteInput from './UserAutoCompleteInput';

class SearchMenuContainer extends React.Component {
  constructor(props){
    super(props);
    this.state = {};
    this.handleClick = this.handleClick.bind(this);
  }

  componentWillMount() {
    document.addEventListener('mousedown',this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('mousedown',this.handleClick, false);
  }

  handleClick(e){
    let dropdownClass = "";
    if (this.node.contains(e.target)){
      if (this.state.dropdownClass === "open"){
        if (e.target.className === "th-icon" || e.target.className === "btn btn-default dropdown-toggle"){
          dropdownClass = "";
        } else {
          dropdownClass = "open";
        }
      } else {
        dropdownClass = "open";
      }
    }
     this.setState({dropdownClass:dropdownClass});
    // this.setState({dropdownClass:dropdownClass},function(){
    //   if (dropdownClass === "open"){
    //     let el = document.body;
    //     el.classList.add('drawer-open');
    //   } else {
    //     let el = document.body;
    //     el.classList.remove('drawer-open');
    //
    //   }
    // });
  }


  render(){

    return (
      <li id="user-search-menu-container" ref={node => this.node = node}>
        <div className={"user-dropdown " + this.state.dropdownClass}>
        <button
          className="btn btn-default dropdown-toggle" type="button" onClick={this.toggleDropDown}>
          <span className="th-icon"></span>
        </button>
          <div id="background-overlay">
            <ul id="right-panel"  className="dropdown-menu dropdown-menu-right">
              <li id="user-tabs-menu-item">
                <UserAutoCompleteInput baseUrl={this.props.baseUrl}/>
              </li>
            </ul>
          </div>
        </div>
      </li>
    )
  }
}

export default SearchMenuContainer;
