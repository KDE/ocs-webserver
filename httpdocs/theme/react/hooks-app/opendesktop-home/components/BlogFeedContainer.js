import React, { Component } from 'react';
class BlogFeedContainer extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    const self = this;
    var json_url = '/json/forum';
    //$.ajax("https://forum.opendesktop.org/latest.json").then(function (result) {
    $.ajax(json_url).then(function (result) {
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
          <a className="title" href={this.props.urlCommunity+"/t/" + fi.id}>
            <span>{fi.title}</span>
          </a>
          <span className="info-row">
            <span className="date">{fi.timeago}</span>
            <span className="comment-counter">{fi.replyMsg}</span>
          </span>
        </li>
      ));

      feedItemsContainer = <ul>{feedItems}</ul>;
    }
    return (
      <div className="panelContainer">
        <div className="title"><a href={this.props.urlCommunity}>Forum</a></div>
        {feedItemsContainer}
      </div>
    )
  }
}

export default BlogFeedContainer;
