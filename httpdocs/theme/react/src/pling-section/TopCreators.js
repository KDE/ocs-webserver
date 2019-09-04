import React from 'react';
import Creator from './function/Creator';
class TopCreators extends React.Component {
  constructor(props){
    super(props);
    this.state = {};

  }
   render(){
     let container;
     if (this.props.creators){
       const creators = this.props.creators.map((creator,index) => (
         <li key={index}>
           <Creator creator={creator} baseUrlStore={this.props.baseUrlStore}/>
         </li>
       ));
      container = <ul>{creators}</ul>
     }
     let title;
     if(this.props.category){
       title = this.props.category.title;
     }else {
       if(this.props.section){
         title = this.props.section.name;
       }else {
         title = 'All';
       }
     }

     return (
       <div className="panelContainer">
         <div className="title">Top 20 Creators Last Month</div>
         {container}
       </div>
     )
   }
}

export default TopCreators;
