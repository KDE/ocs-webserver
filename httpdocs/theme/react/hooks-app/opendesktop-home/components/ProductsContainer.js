import React, { Component } from 'react';
import Product from './Product';
class ProductsContainer extends React.Component {
  constructor(props){
  	super(props);
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
    return (
      <div className="panelContainer">
        <div className="title"><a href={this.props.baseUrlStore+"/browse/cat/"+this.props.cat+"/order/latest/"}>{this.props.title}</a></div>
        {container}
      </div>
    )
  }
}

export default ProductsContainer;
