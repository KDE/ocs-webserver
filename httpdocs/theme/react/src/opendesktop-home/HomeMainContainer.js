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
                                  urlPublish={this.state.baseUrlStore}
                                  urlCommunity={this.state.forumUrl}
                                  urlPersonal={this.state.url_myopendesktop}
                                  />
                  </div>
                  <div className="middle">
                     <ProductsGitContainer />
                     <ProductsContainer baseUrlStore={this.state.baseUrlStore} title="Apps and Addons" cat="152" products={this.state.products}/>
                     <ProductsContainer baseUrlStore={this.state.baseUrlStore} title="Themes" cat="148" products={this.state.productsThemes}/>
                     <ProductsContainer baseUrlStore={this.state.baseUrlStore} title="Multimedia" cat="586" products={this.state.productsMultimedia}/>
                     <ChatContainer />
                     <CommentsContainer baseUrlStore={this.state.baseUrlStore} comments={this.state.comments}/>
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
