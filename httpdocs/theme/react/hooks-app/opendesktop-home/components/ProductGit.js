import React, { Component } from 'react';
import TimeAgo from 'react-timeago';
import TextTruncate from 'react-text-truncate';
class ProductGit extends React.Component {
  constructor(props){
  	super(props);
    this.gitBaseUrl='https://opencode.net/';
  }
  render(){

      const userDisplay =(
        <span className="cm-userinfo">
          <img src={this.props.product.user_avatar_url}/>
          <span className="username">
            <a href={this.gitBaseUrl+this.props.product.namespace.path}>
            {this.props.product.namespace.name}
            </a>
          </span>
        </span>
      );
      const productInfoDisplay = (
        <div className="product-info">
          <span className="product-info-title"><a href={this.props.product.web_url} >{this.props.product.name}</a></span>
          <span className="product-info-desc">

          <TextTruncate
                line={3}
                truncateText="…"
                text={this.props.product.description}

            />
          </span>
          <span className="product-info-date">{this.props.product.timeago}</span>
        </div>
      );

      let imageProject;
      if(this.props.product.avatar_url)
      {
        imageProject =(
          <figure>
            <img className="productimg" src={this.props.product.avatar_url} />
          </figure>
        );
      }else {
        imageProject =(
          <figure>
            <div className="defaultProjectAvatar">{this.props.product.name.substr(0,1)}</div>
          </figure>
        );
      }

    return (
      <div className="productrow row cm-content">
        <div className="col-lg-2">
          <a href={this.props.product.web_url} >
          {imageProject}
          </a>
        </div>
        <div className="col-lg-7">
          {productInfoDisplay}
        </div>
        <div className="col-lg-3">
            {userDisplay}
        </div>
      </div>
    )
  }
}

export default ProductGit;
