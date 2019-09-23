import React, { Component } from 'react';

class Supporters extends React.Component {

  constructor(props){
  	super(props);
  	this.state ={};
  }

  render(){

    function compare(el,idx,array) {
      for (let i = 0; i < array.length; i++) {
        if (array[i].member_id == el.member_id)
        {
           if(idx==i){
             return el;
           }else
           {
             break;
           }
        }
      }
    }

    const x = this.props.supporters.filter(compare).map((s,index) => {
            return (
              <li key={index}>
                <a href={this.props.baseUrlStore+'/u/'+s.username}><img src={s.profile_image_url}></img></a>
                <div className="username">{s.username}</div>
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
