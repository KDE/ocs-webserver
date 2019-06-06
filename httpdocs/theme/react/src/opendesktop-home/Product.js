import React, { Component } from 'react';
import TimeAgo from 'react-timeago';

class Product extends React.Component {
  render(){
      let projectUrl = "/p/"+this.props.product.project_id;
      const createdDate = this.props.product.changed_at?this.props.product.changed_at:this.props.product.created_at;
      const scoreDisplay=(
          <div className="score-info">
            <div className="score-number">
               {this.props.product.laplace_score/10 + "%"}
            </div>
            <div className="score-bar-container">
              <div className={"score-bar"} style={{"width":this.props.product.laplace_score/10 + "%"}}></div>
            </div>
          </div>
        );

      const productInfoDisplay = (
        <div className="product-info">
          <span className="product-info-title"><a href={projectUrl} >{this.props.product.title}</a></span>
          <span className="product-info-category">{this.props.product.cat_title}</span>
          <span className="product-info-date"><TimeAgo date={createdDate} /></span>
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
