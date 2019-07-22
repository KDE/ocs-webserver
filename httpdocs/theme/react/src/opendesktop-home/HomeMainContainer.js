import React, { Component } from 'react';
import RssNewsContainer from './RssNewsContainer';
import BlogFeedContainer from './BlogFeedContainer';
import CommentsContainer from './CommentsContainer';
import RatingContainer from './RatingContainer';
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
    let supporterinfo;
    if(this.state.supporterinfo && this.state.supporterinfo.member_id)
    {
      supporterinfo = "Thanks for supporting Pling.com";
    }else{
      supporterinfo = <div><a href={this.state.baseUrlStore+'/support'}>Become a Supporter</a></div>
    }
    if(this.state.user)
    {
      content = (
          <div id="home-main-container">
            <div className="top">
                <div className="personal-container-top">
                    <h1>
                      Hi {this.state.user.username}, welcome to your personal start page!
                    </h1>
                  <div className="userinfo">
                    <img className="image-profile" src={this.state.user.avatar}></img>
                    {supporterinfo}
                  </div>

                </div>
            </div>
            <div className="middle">
                <PersonalActivityContainer  user={this.state.user.member_id}/>
                <CommentsContainer title="Last 10 comments received" baseUrlStore={this.state.baseUrlStore} comments={this.state.comments}/>
                <RatingContainer title="Last 10 ratings received" baseUrlStore={this.state.baseUrlStore} votes={this.state.votes}/>
            </div>
          </div>
          )
    }else{
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
                     <ProductsGitContainer urlCode={this.state.gitlabUrl}/>
                     <ProductsContainer baseUrlStore={this.state.baseUrlStore} title="Apps and Addons" cat="152" products={this.state.products}/>
                     <ProductsContainer baseUrlStore={this.state.baseUrlStore} title="Themes" cat="148" products={this.state.productsThemes}/>
                     <ProductsContainer baseUrlStore={this.state.baseUrlStore} title="Multimedia" cat="586" products={this.state.productsMultimedia}/>
                     <ChatContainer />
                     <CommentsContainer title="Comments" baseUrlStore={this.state.baseUrlStore} comments={this.state.comments}/>
                     <BlogFeedContainer urlCommunity={this.state.forumUrl}/>
                     <RssNewsContainer />
                  </div>
             </div>
           )
    }

    return (
      <React.Fragment>
       {content}
      </React.Fragment>
    );
  }
}
export default HomeMainContainer;
