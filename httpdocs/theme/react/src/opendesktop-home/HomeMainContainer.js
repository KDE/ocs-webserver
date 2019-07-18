import React, { Component } from 'react';
import RssNewsContainer from './RssNewsContainer';
import BlogFeedContainer from './BlogFeedContainer';
import CommentsContainer from './CommentsContainer';
import ProductsContainer from './ProductsContainer';
import ChatContainer from './ChatContainer';
import ProductsGitContainer from './ProductsGitContainer';
import Introduction from './Introduction';
import PersonalActivityContainer from './PersonalActivityContainer';

class HomeMainContainer extends Component {
  constructor(props){
  	super(props);
  	this.state ={...window.data};
  }
  render() {
    let content;
    // if(this.state.user)
    // {
    //   content = (
    //       <div id="home-main-container">
    //         <div className="top">
    //             <h1>
    //               Hi {this.state.user.username}, welcome to your personal start page!
    //             </h1>
    //         </div>
    //         <div className="middle">
    //             <PersonalActivityContainer  user={this.state.user.member_id}/>
    //         </div>
    //       </div>
    //       )
    // }else{
      content = (
                <div id="home-main-container">
                  <div className="top">
                      <Introduction urlCode={this.state.gitlabUrl}
                                  urlPublish={this.state.baseUrl}
                                  urlCommunity={this.state.forumUrl}
                                  urlPersonal={this.state.url_myopendesktop}
                                  />
                  </div>
                  <div className="middle">
                     <ProductsGitContainer />
                     <ProductsContainer title="Themes" cat="381" products={this.state.products}/>
                     <ProductsContainer title="Apps and Addons" cat="282" products={this.state.products}/>
                     <ProductsContainer title="Multimedia" cat="282" products={this.state.productsMultimedia}/>
                     <ChatContainer />
                     <CommentsContainer comments={this.state.comments}/>
                     <BlogFeedContainer />
                     <RssNewsContainer />
                  </div>
             </div>
           )
    // }

    return (
      <React.Fragment>
       {content}
      </React.Fragment>
    );
  }
}
export default HomeMainContainer;
