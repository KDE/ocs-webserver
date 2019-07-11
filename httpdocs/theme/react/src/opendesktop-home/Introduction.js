import React, { Component } from 'react';
import IntroductionDetailCode from './IntroductionDetailCode';
import IntroductionDetailPublish from './IntroductionDetailPublish';
import IntroductionDetailCommunity from './IntroductionDetailCommunity';
import IntroductionDetailPersonal from './IntroductionDetailPersonal';

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
            OpenDesktop is a portal providing 100% libre services for coding, publishing, messenging and personal data storage.
          </h4>
        </div>
        <div className="intro-menu">
        <ul>
          <li>
              <div className={this.state.section=='code'?'arrowbox':''}>
              <a href="https://opencode.net"><span className="link-code link-image">Code</span></a>
              <span className="showmore"><a className="link-code-showmore" onClick={()=>this.handleClick('code')}>Show more</a></span>
              </div>
          </li>
          <li>
            <div className={this.state.section=='publish'?'arrowbox':''}>
              <a href="https://pling.com"><span className="link-publish link-image">Publish</span></a>
              <span className="showmore"><a className="link-publish-showmore" onClick={()=>this.handleClick('publish')}>Show more</a></span>
              </div>
          </li>
          <li>
              <div className={this.state.section=='community'?'arrowbox':''}>
              <a href="https://forum.opendesktop.org"><span className="link-community link-image">Community</span></a>
              <span className="showmore"><a onClick={()=>this.handleClick('community')}>Show more</a></span>
              </div>

          </li>
          <li>
              <div className={this.state.section=='personal'?'arrowbox':''}>
              <a href="https://my.opendesktop.org"><span className="link-personal link-image">Personal</span></a>
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
