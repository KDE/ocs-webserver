import React, { Component } from 'react';

class Product extends React.Component {
  render(){
      let projectUrl = this.props.baseUrlStore+"/p/"+this.props.product.project_id;
      const scoreDisplay=(
          <div className="score-info">
            <div className="score-number">
               {(this.props.product.laplace_score/10).toFixed(1) + "%"}
            </div>
            <div className="score-bar-container">
              <div className={"score-bar"} style={{"width":this.props.product.laplace_score/10 + "%"}}></div>
              <span>${this.props.product.probably_payout_amount_factor} {this.props.product.probably_payout_amount}</span>
            </div>
          </div>
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
