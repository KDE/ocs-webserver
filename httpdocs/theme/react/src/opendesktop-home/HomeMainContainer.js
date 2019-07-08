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
           <Introduction />
         </div>

      </div>
    );
  }
}
export default HomeMainContainer;
