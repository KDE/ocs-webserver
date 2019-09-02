import React, { Component } from 'react';

class PlingSection extends Component {
  constructor(props){
  	super(props);
  	this.state ={...window.data};
    this.handleClick = this.handleClick.bind(this);
  }

  handleClick(t) {
    this.setState(state => ({
      isToggleOn: !state.isToggleOn,
      sectionSelected:(t==state.sectionSelected)?'':t
    }));
  }
  render() {

    let sectioncontainer, sectiondetail;
    if (this.state.sections){
      const s = this.state.sections.map((section,index) => (
        <li key={section.section_id}
          className={section.section_id==this.state.sectionSelected?'active':''}
          onClick={()=>this.handleClick(section.section_id)}>
           <a>{section.name}</a>
        </li>
      ));
     sectioncontainer =  <div className="pling-nav-tabs">
                        <ul className="nav nav-tabs pling-section-tabs">{s}</ul>
                        </div>     
    }

    if (this.state.details && this.state.sectionSelected){
      const s = this.state.details.map((detail,index) => {
          if(detail.section_id==this.state.sectionSelected) return <li>{detail.title}</li>
      });
      sectiondetail = <div>
                        <h2>Categories</h2>
                        <ul className="pling-section-detail-ul">{s}</ul>
                      </div>
    }

    return (
      <React.Fragment>
       <h1>Section Detail </h1>
       {sectioncontainer}
       {sectiondetail}
      </React.Fragment>
    );
  }
}
export default PlingSection;
