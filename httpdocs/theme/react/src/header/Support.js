import React, { Component } from 'react';
class Support extends Component {
  constructor(props){
  	super(props);
    this.state = {selected:'month'};
    this.handleClick = this.handleClick.bind(this);
    this.tabSwitch = this.tabSwitch.bind(this);
  }

  handleClick(){
    let dropdownClass = "";
    if (this.state.dropdownClass === "open"){
      dropdownClass = "";
    }else{
      dropdownClass = "open";
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
                                       <a>Months</a>
                                    </li>
                                    <li key={'amount'}
                                      className={(this.state.selected=="amount")?'active':''}
                                      onClick={()=>this.tabSwitch('amount')}
                                      >
                                       <a>Amount</a>
                                    </li>
                                  </ul>
                                  </div>

        let content;
        if(this.state.selected=="amount")
        {
          content = this.props.section.supporters.sort((a, b) => Number(a.sum_support) < Number(b.sum_support)).map((mg,i) => (
                  <div className="section"><div className="section-name"><a href={"/u/"+mg.username}><img src={mg.profile_image_url}></img></a><span>{mg.username}</span></div>
                  <div className="section-value"> ${mg.sum_support}</div></div>
                  ))
        }else{
          // default show month panel
          content = this.props.section.supporters.sort((a, b) => Number(a.active_months) < Number(b.active_months)).map((mg,i) => (
                  <div className="section"><div className="section-name"><a href={"/u/"+mg.username}><img src={mg.profile_image_url}></img></a><span>{mg.username}</span></div>
                  <div className="section-value"> {mg.active_months+' months'}</div></div>
                  ))
        }

        const t = (
                        <div className="user-pling-section-container">
                        {sectioncontainer}
                        { content }
                        </div>
                  )

        supporters = <ul className="dropdown-menu dropdown-menu-right">{t}</ul>
      }

    return(
      <div className="pling-section-header">
          <div className="header-title">
            <span>{this.props.section ? this.props.section.name:''}</span>
          </div>
          <div className="header-body">
            <div className="score-container">
              <span>Goal:</span>
              <div className="score-bar-container">
                <div className={"score-bar"} style={{"width":(this.props.amount_factor/this.props.goal)*100 + "%"}}>
                  {this.props.amount_factor+(this.props.isAdmin?'('+this.props.amount+')':'')}
                </div>
              </div>
              <span>{ this.props.goal}</span>
            </div>
            <div className={'supporter-container '+this.state.dropdownClass }>
              <a className="header-supporters" onClick={this.handleClick} > Supporters &#8964; </a>
              {supporters}
            </div>
          </div>
      </div>
    )
  }
}

export default Support;
