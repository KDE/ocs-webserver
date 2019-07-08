
import React, { Component } from 'react';
import ProductsGitContainer from './ProductsGitContainer';
import BlogFeedContainer from './BlogFeedContainer';
import CommentsContainer from './CommentsContainer';
import ProductsContainer from './ProductsContainer';
import ChatContainer from './ChatContainer';

class Introduction extends Component {
    constructor(props){
    	super(props);
    	this.state ={...window.data};
    }
  render() {
  return (
    <div className="introduction">
      <h1> Welcome to opendesktop</h1>
      <h4> Opendesktop is community based portal, dedicated to libre content, opencode, free software and operating system.</h4>
      <div className="intro-container">
        <div className="intro-table-row">
          <div className="code-container sub-container">
          <ProductsGitContainer />
          </div>
          <div className="pub-container sub-container">
          <ProductsContainer title="Apps and Addons" products={this.state.products}/>
          </div>
        </div>
        <div className="intro-table-row">
        <div className="comm-container sub-container">
          <BlogFeedContainer />
          <ChatContainer />

        </div>
        <div className="personal-container sub-container"><a href="https://my.opendesktop.org">Personal</a></div>
        </div>
      </div>

    </div>
  )
  }
}

/*
<ul>
  <li><a href="https://opendesktop.org">Pling.com</a></li>
  <li><a href="https://opencode.net">Opencode</a></li>
  <li><a href="https://forum.opendesktop.org">Discourse</a></li>
  <li><a href="https://chat.opendesktop.org">Riot</a></li>
  <li>Mastodon</li>
  <li><a href="https://my.opendesktop.org">Nextcloud</a></li>
</ul>
*/
export default Introduction;
