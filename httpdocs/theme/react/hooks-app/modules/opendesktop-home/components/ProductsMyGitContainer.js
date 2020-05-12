import React, { Component } from 'react';
import ProductGit from './ProductGit';

class ProductsMyGitContainer extends React.Component {
  constructor(props){
  	super(props);
    this.gitUrl = `/json/gitlab?username=${props.user.username}`;
    this.gitUserUrl = '/json/gitlabfetchuser?username=';
    this.state = {items:[],user:null};
  }

  componentDidMount() {
    fetch(this.gitUrl)
      .then(response => response.json())
      .then(data => {

        if(data.user)
        {
          this.setState({user:data.user});
        }
        if(data.projects)
        {
          data.projects.map((fi,index) => {
            fi.user_avatar_url = data.user.avatar_url;            
          });
          this.setState({items:data.projects});
        }
        
      });
  }

  render(){
    let container;
    container = <div> <h1> Projects </h1></div>
    if (this.state.items){
      const items = this.state.items.sort(function(a,b){
                return new Date(b.created_at) - new Date(a.created_at);
        });
      const products = items.map((product,index) => (
        <li key={index}>
          <ProductGit product={product}/>
        </li>
      ));
     container = <ul>{products}</ul>
    }
    return (
      <div className="panelContainer">
        <div className="title"> Projects</div>
        {container}
      </div>
    )
  }
}

export default ProductsMyGitContainer;
