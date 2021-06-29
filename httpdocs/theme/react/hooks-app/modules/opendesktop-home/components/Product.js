import React, { Component } from 'react';
import Score from './Score';
class Product extends React.Component {
  render(){
      let projectUrl = this.props.baseUrlStore+"/p/"+this.props.product.project_id;
      const scoreDisplay =(
        <Score score={this.props.product.laplace_score} r="30" fontSize="18"/>
      );
      const productInfoDisplay = (
        <div className="product-info">
          <span className="product-info-title"><a href={projectUrl} >{this.props.product.title}</a></span>
          <span className="product-info-category">{this.props.product.cat_title}</span>
          <span className="product-info-date">{this.props.product.updated_at}</span>
        </div>
      );


    return (
      <div className="productrow row">
        <div className="col-lg-2">
          <a href={projectUrl} >
            <figure >
              <img className="productimg" src={this.props.product.image_small} />
            </figure>
          </a>
        </div>
        <div className="col-lg-7">
          {productInfoDisplay}
        </div>
        <div className="col-lg-3">
            {scoreDisplay}
        </div>
      </div>
    )
  }
}

export default Product;
