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
           <Creator creator={creator} baseUrlStore={this.props.baseUrlStore} isAdmin={this.props.isAdmin}/>
         </li>
       ));
      container = <ul>{creators}</ul>
     }
     let title = 'Top 20 Creators Last Month Payout';
     // if(this.props.category){
     //   title = title + ':' +this.props.category.title;
     // }else {
     //   // if(this.props.section){
     //   //   title = this.props.section.name;
     //   // }else {
     //   //   title = 'All';
     //   // }
     // }

     return (
       <div className="panelContainer">
         <div className="title">{title}</div>
         {container}
       </div>
     )
   }
}

export default TopCreators;
