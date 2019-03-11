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
        <div className="row" key={index}><Product product={product}/></div>
      ));
     container = <div>{products}</div>
    }
    return (
      <div className="panelContainer">
        <div className="title">Products</div>
        {container}
      </div>
    )
  }
}

export default ProductsContainer;
