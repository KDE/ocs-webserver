import React, { Component } from 'react';
class Support extends Component {
  constructor(props){
  	super(props);
    this.state = {selected:'month'};
    this.handleClick = this.handleClick.bind(this);
    this.tabSwitch = this.tabSwitch.bind(this);
  }

  componentWillMount() {
    document.addEventListener('click',this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('click',this.handleClick, false);
  }


  handleClick(e){

    let dropdownClass = "";
    if (this.node.contains(e.target)){
      if(e.target.className === "header-supporters" )
      {
        // only btn click open dropdown
        if (this.state.dropdownClass === "open"){
          dropdownClass = "";
        }else{
          dropdownClass = "open";
        }
      }else{
        dropdownClass = "open";
      }
    }
    this.setState({dropdownClass:dropdownClass});
  }

  tabSwitch(selected)
  {
    this.setState({selected:selected});
  }

  render(){
      let supporters;
      if(this.props.section.supporters)
      {
        const sectioncontainer =  <div className="pling-nav-tabs">
                                  <ul className="nav nav-tabs pling-section-tabs">
                                    <li key={'month'}
                                      className={(this.state.selected=="month")?'active':''}
                                      onClick={()=>this.tabSwitch('month')}
                                      >
                                       <a className="cls-tab-month">Months</a>
                                    </li>
                                  </ul>
                                    <a href="/support" role="button" className="btn btn-primary active">Become a Supporter</a>
                                  </div>

          // default show month panel
        let  content = this.props.section.supporters.sort((a, b) => Number(a.active_months) < Number(b.active_months)).map((mg,i) => (
                  <div className="section"><div className="section-name"><a href={"/u/"+mg.username}><img src={mg.profile_image_url}></img></a>
                  <span><a href={"/u/"+mg.username}>{mg.username}</a></span></div>
                  <div className="section-value"> {mg.active_months+' months'}</div></div>
                  ))


        const t = (
                        <div className="user-pling-section-container">
                        {sectioncontainer}
                        { content }
                        </div>
                  )

        supporters = <ul className="dropdown-menu dropdown-menu-right">{t}</ul>
      }
      const s = this.props.section.supporters?this.props.section.supporters.length:0;
    return(
      <div className={"pling-section-header "+this.props.headerStyle}>
          <div className="header-body">

            <div ref={node => this.node = node} className={'supporter-container '+this.state.dropdownClass }>
              <a className="header-supporters" >{this.props.section ? this.props.section.name:''} has {s} active Supporters &#8964; </a>
              {supporters}
            </div>
          </div>
      </div>
    )
  }
}

export default Support;
