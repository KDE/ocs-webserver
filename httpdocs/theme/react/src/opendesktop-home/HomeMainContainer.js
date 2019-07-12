import React, { Component } from 'react';
import RssNewsContainer from './RssNewsContainer';
import BlogFeedContainer from './BlogFeedContainer';
import CommentsContainer from './CommentsContainer';
import ProductsContainer from './ProductsContainer';
import ChatContainer from './ChatContainer';
import ProductsGitContainer from './ProductsGitContainer';
import Introduction from './Introduction';

class HomeMainContainer extends Component {
  constructor(props){
  	super(props);
  	this.state ={...window.data};
  }
  render() {
    return (
      <div id="home-main-container">
         <div className="top">
           <Introduction urlCode={this.state.gitlabUrl}
                         urlPublish={this.state.baseUrl}
                         urlCommunity={this.state.forumUrl}
                         urlPersonal={this.state.url_myopendesktop}
                         />
         </div>
         <div className="middle">
          <ProductsContainer title="Themes" cat="381" products={this.state.products}/>
          <ProductsContainer title="Apps and Addons" cat="282" products={this.state.products}/>
          <ProductsContainer title="Multimedia" cat="282" products={this.state.productsMultimedia}/>
          <ProductsGitContainer />
          <CommentsContainer comments={this.state.comments}/>
          <BlogFeedContainer />
          <ChatContainer />
          <RssNewsContainer />
          </div>
      </div>
    );
  }
}
export default HomeMainContainer;
