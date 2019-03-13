import React, { Component } from 'react';
import TimeAgo from 'react-timeago';
class BlogFeedContainer extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    const self = this;
    $.ajax("https://forum.opendesktop.org/latest.json").then(function (result) {
      let topics = result.topic_list.topics;
      topics.sort(function(a,b){
        return new Date(b.last_posted_at) - new Date(a.last_posted_at);
      });
      topics = topics.slice(0,5);
      self.setState({items:topics});
    });
  }

  render(){
    let feedItemsContainer;
    if (this.state.items){

      const feedItems = this.state.items.map((fi,index) => (
        <li key={index}>
          <a className="title" href={"https://forum.opendesktop.org//t/" + fi.id}>
            <span>{fi.title}</span>
          </a>
          <span className="info-row">
            <span className="date"><TimeAgo date={fi.created_at} /></span>
            <span className="comment-counter">{fi.reply_count} replies</span>
          </span>
        </li>
      ));

      feedItemsContainer = <ul>{feedItems}</ul>;
    }
    return (
      <div className="panelContainer">
        <div className="title">Forum</div>
        {feedItemsContainer}
      </div>
    )
  }
}

export default BlogFeedContainer;
