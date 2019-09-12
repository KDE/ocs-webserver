import React, { Component } from 'react';
class Support extends Component {
  constructor(props){
  	super(props);
    this.state = {};
    this.handleClick = this.handleClick.bind(this);
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

  render(){



      let supporters;
      if(this.props.section.supporters)
      {
        const t = (
                        <div className="user-pling-section-container">
                        <div className="title">Supporters this month</div>

                        { this.props.section.supporters.map((mg,i) => (
                            <div className="section"><div className="section-name">{mg.username}:</div><div className="section-value"> {mg.active_months+' months'}</div></div>
                          ))
                        }
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
