import React, { Component } from 'react';
import IntroductionDetailCode from '../function/IntroductionDetailCode';
import IntroductionDetailPublish from '../function/IntroductionDetailPublish';
import IntroductionDetailCommunity from '../function/IntroductionDetailCommunity';
import IntroductionDetailPersonal from '../function/IntroductionDetailPersonal';

class Introduction extends Component {
  constructor(props){
  	super(props);
    this.state = {isToggleOn: true};
    // This binding is necessary to make `this` work in the callback
   this.handleClick = this.handleClick.bind(this);
  }

  handleClick(t) {
    this.setState(state => ({
      isToggleOn: !state.isToggleOn,
      section:(t==state.section)?'':t
    }));
  }
  render() {
    let introductionDetail=null;
    switch (this.state.section) {
        case 'code':
          introductionDetail = <IntroductionDetailCode />;
          break;
        case 'publish':
          introductionDetail = <IntroductionDetailPublish />;
          break;
        case 'community':
          introductionDetail = <IntroductionDetailCommunity />;
          break;
        case 'personal':
          introductionDetail = <IntroductionDetailPersonal />;
          break;
        default:
          break;
      }
  return (
    <div className="introduction">
      <div className="introduction-head">
        <div className="intro-desc">
          <h1> Welcome to opendesktop.org</h1>
          <h4>
          OpenDesktop is a <a href="#network-sites">network of sites</a> that are all operated by Hive01 and follow the same <a href="#standards">open standards</a>.
          Get one account to use these libre services:

              <a href="#_" className="lightbox" id="network-sites">
              <div className="lightbox-container">
                <h2>Network of sites</h2>
                <ul>
                  <li>openCode.net</li>
                  <li>Pling.com</li>
                  <li>Forums and Chat</li>
                  <li>Personal cloud</li>
                </ul>
              </div>
            </a>

            <a href="#_" className="lightbox" id="standards">
              <div className="lightbox-container">
                <h2>Open standards</h2>
                <ul>
                  <li>100% Libre software</li>
                  <li>We respect users privacy</li>
                  <li>We do not sell data</li>
                </ul>
              </div>
            </a>
          </h4>
        </div>
        <div className="intro-menu">
        <ul>
          <li>
              <div className={this.state.section=='code'?'':''}>
              <a href={this.props.urlCode}><span className="link-code link-image">Code</span></a>
              <span className="showmore"><a className="link-code-showmore" onClick={()=>this.handleClick('code')}>Show more</a></span>
              </div>
          </li>
          <li>
            <div className={this.state.section=='publish'?'':''}>
              <a href={this.props.urlPublish}><span className="link-publish link-image">Store</span></a>
              <span className="showmore"><a className="link-publish-showmore" onClick={()=>this.handleClick('publish')}>Show more</a></span>
              </div>
          </li>
          <li>
              <div className={this.state.section=='community'?'':''}>
              <a href={this.props.urlCommunity}><span className="link-community link-image">Community</span></a>
              <span className="showmore"><a onClick={()=>this.handleClick('community')}>Show more</a></span>
              </div>

          </li>
          <li>
              <div className={this.state.section=='personal'?'':''}>
              <a href={this.props.urlPersonal}><span className="link-personal link-image">Personal Services (Beta)</span></a>
              <span className="showmore"><a onClick={()=>this.handleClick('personal')}>Show more</a></span>
              </div>
          </li>
        </ul>
      </div>
      </div>
      <div className="introduction-detail">
          {introductionDetail}
      </div>
    </div>

  )
  }
}

export default Introduction;
