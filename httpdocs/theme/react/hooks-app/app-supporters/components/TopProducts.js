import React from 'react';
import Product from './Product';
const TopProducts = (props) => {
  return (
    <div className="panelContainer">
         <div className="title">Most Plinged Products</div>
         <ul>{props.products.map((product,index) => (
                <li key={index}>
                <Product product={product} baseUrlStore={props.baseUrlStore} isAdmin={props.isAdmin}/>
                </li>
              ))}</ul>
       </div>
  )
}

export default TopProducts
