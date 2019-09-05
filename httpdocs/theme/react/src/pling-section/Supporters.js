import React, { Component } from 'react';

class Supporters extends React.Component {

  constructor(props){
  	super(props);
  	this.state ={};
  }

  render(){

    const x = this.props.supporters.map((s,index) => {
            return (
              <li key={index}>
                <a href={this.props.baseUrlStore+'/u/'+s.username}><img src={s.profile_image_url}></img></a>
              </li>
            )
       }
     );
    const c = <ul>{x}</ul>

    return (
      <div className="supporters-container">
      {c}
      </div>
    )
  }
}
export default Supporters;
