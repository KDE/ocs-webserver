import React from 'react';
class  UserInfo extends React.Component {
  constructor(props){
  	super(props);
    this.state = {};
  }
  componentDidMount() {    
  }
  render(){
      return(
        <div className="userinfo">
          <div className="header">{this.props.userinfo.username} {this.props.userinfo.countrycity}</div>
          <div className="statistic">
            <div className="statisticRow"><span className="title">{this.props.userinfo.cntProjects} </span> products </div>
            <div className="statisticRow"><span className="title">{this.props.userinfo.totalComments} </span> comments </div>
            <div className="statisticRow">Likes <span className="title">{this.props.userinfo.cntLikesGave} </span> products </div>
            <div className="statisticRow">Got <span className="title">{this.props.userinfo.cntLikesGot} </span>Likes </div>
            <div className="statisticRow">Last time active :{this.props.userinfo.lastactive_at}  </div>
            <div className="statisticRow">Member since : {this.props.userinfo.created_at}  </div>
          </div>
        </div>
      )
  }
}
export default UserInfo;
