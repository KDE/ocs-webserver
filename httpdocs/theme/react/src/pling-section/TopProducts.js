import React from 'react';
//import Product from '../opendesktop-home/Product';
import Product from './Product';
class TopProducts extends React.Component {
  constructor(props){
    super(props);
    this.state = {};

  }
   render(){
     let container;
     if (this.props.products){
       const products = this.props.products.map((product,index) => (
         <li key={index}>
         <Product product={product} baseUrlStore={this.props.baseUrlStore}/>
         </li>
       ));
      container = <ul>{products}</ul>
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
         <div className="title">Top 20 Products Last Month Payout</div>
         {container}
       </div>
     )
   }
}

export default TopProducts;
