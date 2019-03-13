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
        <Product product={product}/>
        </li>
      ));
     container = <ul>{products}</ul>
    }
    return (
      <div className="panelContainer">
        <div className="title">{this.props.title}</div>
        {container}
      </div>
    )
  }
}

export default ProductsContainer;
