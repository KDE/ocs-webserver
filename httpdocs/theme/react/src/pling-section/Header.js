import React, { Component } from 'react';
class Header extends Component {
  constructor(props){
  	super(props);
  }
  render(){

    function compare(el,idx,array) {
      for (let i = 0; i < array.length; i++) {
        if (array[i].member_id == el.member_id)
        {
           if(idx==i){
             return el;
           }else
           {
             break;
           }
        }
      }
    }
    const s = this.props.supporters.filter(compare).length;
    let goal = Math.ceil((s/50))*50;
    if(goal==0)
    {
      goal = 50;
    }
    return(
      <div className="pling-section-header">
          <div className="header-title">
            <span>{this.props.section ? this.props.section.name:''}</span>
          </div>
          <div className="score-container">
            <span>Goal:</span>
            <div className="score-bar-container">
              <div className={"score-bar"} style={{"width":(s/goal)*100 + "%"}}>
                {s}
              </div>
            </div>
            <span>{goal}</span>

          </div>
      </div>
    )
  }
}

export default Header;
