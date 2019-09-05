import React, { Component } from 'react';

class Support extends React.Component {

  constructor(props){
  	super(props);
  	this.state ={};
    this.onChangeFreeamount = this.onChangeFreeamount.bind(this);
  }

  onChangeFreeamount(event){
      this.setState({typed: event.target.value});
  }

  render(){

    let tiers=[0.99,2,5,10,20,50];
    const container = tiers.map((t,index) => {
          let c;
          let tmp = t;
          const result = this.props.supporters.filter(s => s.section_support_tier==tmp);
          const x = result.map((s,index) => {
                  return (
                    <li key={index}>
                      <a href={this.props.baseUrlStore+'/u/'+s.username}><img src={s.profile_image_url}></img></a>
                    </li>
                  )
             }
           );
           c = <ul>{x}</ul>
           let url = this.props.baseUrlStore+'/support-predefined?section_id='+this.props.section.section_id;
           url = url+'&amount_predefined='+tmp;

          return (
              <div className="tier-container">
                <span>the following people chose ${t} tier to support this section:</span>
                {c}
              <div className="join">
              <a href={url}>Join ${t} Tier</a>
              </div>
            </div>
          );
      }
    );

    let result = this.props.supporters;
    tiers.forEach(function(element) {
       result = result.filter(s => s.section_support_tier!=element);
    });
    let othertiers;
    let o;
    if(result.length>0)
    {
      const x = result.map((s,index) => {
              return (
                <li key={index}>
                  <a href={this.props.baseUrlStore+'/u/'+s.username}><img src={s.profile_image_url}></img></a>
                </li>
              )
         }
       );
       o = <ul>{x}</ul>
    }
    let url = this.props.baseUrlStore+'/support-predefined?section_id='+this.props.section.section_id;
       url = url+'&amount_predefined='+this.state.typed;

    othertiers = (
         <div className="tier-container">
           { o &&
             <span>the following people chose other tier to support this section:</span>
            }
             {o}
           <div className="join">
             <div>
             $<input className="free-amount" onChange={this.onChangeFreeamount.bind(this)}></input><span>100 or more</span>
            </div>
             <a  href={url} id="free-amount-link" >Join </a>
           </div>
         </div>
    )


    return (
      <div className="support-container">
        <div className="tiers">
          <h5>Tiers</h5>
        </div>
        {container}
        {othertiers}
      </div>
    )
  }
}

export default Support;
