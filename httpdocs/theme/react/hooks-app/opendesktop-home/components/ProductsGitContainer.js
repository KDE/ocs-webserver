import React, { Component } from 'react';
import ProductGit from './ProductGit';

class ProductsGitContainer extends React.Component {
  constructor(props){
  	super(props);
    this.gitUrl = '/json/gitlabnewprojects';
    this.gitUserUrl = '/json/gitlabfetchuser?username=';
    this.state = {items:[]};
  }

  componentDidMount() {
    fetch(this.gitUrl)
      .then(response => response.json())
      .then(data => {
        data.map((fi,index) => {
            let url = this.gitUserUrl+fi.namespace.name;
            return fetch(url)
                   .then(response => response.json())
                   .then(data => {
                      fi.user_avatar_url = data[0].avatar_url;
                      let items = this.state.items.concat(fi);
                      this.setState({items:items});
                   })
        });
      });
  }

  render(){
    let container;
    container = <div> <h1> git-container </h1></div>
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
        <div className="title"> <a href={this.props.urlCode+"/explore/projects"}>Git-Projects</a></div>
        {container}
      </div>
    )
  }
}

export default ProductsGitContainer;
