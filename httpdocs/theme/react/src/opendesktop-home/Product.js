import React, { Component } from 'react';
import TimeAgo from 'react-timeago';

class Product extends React.Component {
  render(){
      const createdDate = this.props.product.changed_at?this.props.product.changed_at:this.props.product.created_at;
      const scoreDisplay=(
          <div className="score-info">
            <div className="score-number">
              score {this.props.product.laplace_score + "%"}
            </div>
            <div className="score-bar-container">
              <div className={"score-bar"} style={{"width":this.props.product.laplace_score + "%"}}></div>
            </div>
          </div>
        );

      const productInfoDisplay = (
        <div className="product-info">
          <span className="product-info-title">{this.props.product.title}</span>
          <span className="product-info-category">{this.props.product.cat_title}</span>
          <span className="product-info-date"><TimeAgo date={createdDate} /></span>
          {scoreDisplay}
        </div>
      );


    let projectUrl = "/p/"+this.props.product.project_id;

    return (
      <div>
        <div>
          <a href={projectUrl} >
            <figure >
              <img className="very-rounded-corners" src={this.props.product.image_small} />
            </figure>
          </a>
        </div>
        <div>
          {productInfoDisplay}
        </div>
      </div>
    )
  }
}

export default Product;
