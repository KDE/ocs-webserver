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
     if(!this.props.user){
       let url = this.props.baseUrl+'/membersetting/anonymousdl';
      
       fetch(url,{
                  mode: 'cors',
                  credentials: 'include'
                  })
       .then(response => response.json())
       .then(data => {
          this.setState(prevState => ({ anonymousdl: data.dls }))
        });
      }
   }


  handleClick(e){
    // let dropdownClass = "";
    //
    // if (this.node.contains(e.target)){
    //
    //   if(e.target.className === "about-menu-link-item" || "th-icon"===e.target.className)
    //   {
    //     // only btn click open dropdown
    //     if (this.state.dropdownClass === "open"){
    //       dropdownClass = "";
    //     }else{
    //       dropdownClass = "open";
    //     }
    //   }else{
    //     dropdownClass = "";
    //   }
    // }
    // this.setState({dropdownClass:dropdownClass});
  }

  render(){

    return (
      <li ref={node => this.node = node} id="anonymous-dropdown-menu" className={this.state.dropdownClass}>
        <a className="anonymous-menu-link-item"> Anonymous dl:{this.state.anonymousdl}</a>
      </li>
    )
  }
}

export default AnonymousMenu;
