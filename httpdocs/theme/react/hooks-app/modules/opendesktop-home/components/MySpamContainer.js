import React, { Component } from 'react';
import Product from './Product';
class MySpamContainer extends React.Component {
  constructor(props){
  	super(props);
  }
  render(){
    let container;
    if (this.props.spams){
      const products = this.props.spams.map((product,index) => (
        <li key={index}>
        <Product product={product} baseUrlStore={this.props.baseUrlStore}/>
        </li>
      ));
     container = <ul>{products}</ul>
    }
    return (
      <div className="panelContainer">
        <div className="title">Spam</div>
        {container}
      </div>
    )
  }
}

export default MySpamContainer;
