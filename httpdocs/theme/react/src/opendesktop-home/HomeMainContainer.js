import React, { Component } from 'react';
import RssNewsContainer from './RssNewsContainer';
import BlogFeedContainer from './BlogFeedContainer';
import CommentsContainer from './CommentsContainer';
import ProductsContainer from './ProductsContainer';

class HomeMainContainer extends Component {
  constructor(props){
  	super(props);
  	this.state ={...window.data};
  }
  render() {
    return (
      <div id="home-main-container">
         <div className="left">

         </div>
         <div className="middle">
          <RssNewsContainer />
          <BlogFeedContainer />
          <CommentsContainer comments={this.state.comments}/>
          <ProductsContainer products={this.state.products}/>
          </div>
         <div className="right"> </div>
      </div>
    );
  }
}
export default HomeMainContainer;
