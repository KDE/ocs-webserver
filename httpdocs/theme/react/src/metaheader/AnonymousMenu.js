import React from 'react';
class AnonymousMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {};
    this.handleClick = this.handleClick.bind(this);
    this.loadAnonymousDl = this.loadAnonymousDl.bind(this);
  }

  componentWillMount() {
    document.addEventListener('click',this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('click',this.handleClick, false);
  }

  componentDidMount(){
    this.loadAnonymousDl();
   }

   loadAnonymousDl(){
       let url = this.props.baseUrlStore+'/json/anonymousdl';
       fetch(url,{
                  mode: 'cors',
                  credentials: 'include'
                  })
       .then(response => response.json())
       .then(data => {
          this.setState(prevState => ({ anonymousdl: data.dls , section:data.section}))
        });
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

  }

  render(){

    let contextMenuDisplay;

    if (this.state.section){
      contextMenuDisplay = this.state.section.map((mg,i) => (
        <div className="section">{mg.name}: {mg.dls}</div>
      ));
    }



    return (
      <li ref={node => this.node = node} id="anonymous-dropdown-menu-container" >
        <div className={"user-dropdown " + this.state.dropdownClass}>
        <button
          className="btn btn-default dropdown-toggle" type="button" onClick={this.toggleDropDown}>
          <span className="th-icon"></span>{this.state.anonymousdl}
        </button>
        <ul className="dropdown-menu dropdown-menu-right">
          <li className="user-context-menu">
          <div className="user-pling-section-container">
              <div className="title">Download Pling Section</div>
              {contextMenuDisplay}
          </div>
        </li>
        </ul>
      </div>
      </li>
    )
  }
}

export default AnonymousMenu;
