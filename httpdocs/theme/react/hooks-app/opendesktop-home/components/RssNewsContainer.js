import React, { Component } from 'react';
import TimeAgo from 'react-timeago';
class RssNewsContainer extends Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    const self = this;
    //$.getJSON("https://blog.opendesktop.org/?json=1&callback=?", function (res) {
    $.getJSON(`/json/news`, function (res) {
      self.setState({items:res.posts});
    });
  }

  render(){
    let feedItemsContainer;
    if (this.state.items){

      const feedItems = this.state.items.slice(0,5).map((fi,index) => (
        <li key={index}>
          <a className="title" href={fi.url}>
            <span>{fi.title}</span>
          </a>
          <span className="info-row">
            <span className="date">
            <TimeAgo date={fi.date} /></span>
            <span className="comment-counter">{fi.comment_count} comments</span>
          </span>
        </li>
      ));

      feedItemsContainer = <ul>{feedItems}</ul>;
    }
    return (
      <div id="rss-new-container" className="panelContainer">
        <div className="title"><a href="https://blog.opendesktop.org/">News</a></div>
        {feedItemsContainer}
      </div>
    )
  }
}

export default RssNewsContainer;
