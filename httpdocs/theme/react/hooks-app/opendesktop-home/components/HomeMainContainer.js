import React, { Component } from 'react';
import RssNewsContainer from './RssNewsContainer';
import CommentsContainer from './CommentsContainer';
import RatingContainer from './RatingContainer';
import ProductsContainer from './ProductsContainer';
import MySpamContainer from './MySpamContainer';
import ChatContainer from './ChatContainer';
import ProductsGitContainer from './ProductsGitContainer';
import Introduction from './Introduction';
import PersonalLinksContainer from '../function/PersonalLinksContainer';
import AdminLinksContainer from '../function/AdminLinksContainer';
import BlogFeedContainer from './BlogFeedContainer';
import WatchlistContainer from '../function/WatchlistContainer';
import StatisticsContainer from '../function/StatisticsContainer';
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
       supporterinfo =   <div>Thanks for supporting Pling.com</div>
    }else{
      supporterinfo = <div><a href={this.state.baseUrlStore+"/support"}>Become a Supporter</a></div>
    }
    if(this.state.user)
    {
      content = (
          <div id="home-main-container">
            <div className="top">
                <div className="personal-container-top">

                  <div className="userinfo">
                    <img className="image-profile" src={this.state.user.avatar}></img>
                    {supporterinfo}
                  </div>
                  <h1>
                    Hi {this.state.user.username}, good to see you!</h1>
                  go <a href={this.state.baseUrl+'/start'}>here</a> for the startpage.

                  <PersonalLinksContainer myopendesktopUrl={this.state.url_myopendesktop}
                                          docsopendesktopUrl={this.state.url_docsopendesktop}
                                          riotUrl = {this.state.riotUrl}
                                          forumUrl ={this.state.forumUrl}
                                          musicopendesktopUrl ={this.state.url_musicopendesktop}
                                          user ={this.state.user}

                    />
                </div>
            </div>
            <div className="middle"> 
                     
                <CommentsContainer title="Last 10 comments received" baseUrlStore={this.state.baseUrlStore} comments={this.state.comments}/>
                <CommentsContainer title="Last 10 moderation" baseUrlStore={this.state.baseUrlStore} comments={this.state.commentsmoderation} type='moderation'/>
                <RatingContainer title="Last 10 ratings received" baseUrlStore={this.state.baseUrlStore} votes={this.state.votes}/>
                { this.state.spams.length>0 && 
                <MySpamContainer title="Spam" baseUrlStore={this.state.baseUrlStore} spams={this.state.spams}/>
                }
                  { this.state.user.isAdmin &&
                  <>
                    <div className="panelContainer">
                      <div className="title">Watchlist(Admin only)</div>
                      <WatchlistContainer  user={this.state.user} baseUrlStore={this.state.baseUrlStore}/>
                    </div>
                    <div className="panelContainer">
                      <div className="title">Admin(Admin only)</div>
                      <AdminLinksContainer  user={this.state.user} baseUrlStore={this.state.baseUrlStore}/>
                    </div>
                    <div className="panelContainer">
                      <div className="title">Statistics(Admin only)</div>
                      <StatisticsContainer  user={this.state.user} baseUrlStore={this.state.baseUrlStore}/>
                    </div>
                  </>
                  }
               
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
