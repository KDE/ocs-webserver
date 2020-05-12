import React from 'react';
import Product from './Product';
const TopProducts = (props) => {
  return (
    <div className="panelContainer">
         <div className="title">Top 20 Products Last Month Payout</div>
         <ul>{props.products.map((product,index) => (
                <li key={index}>
                <Product product={product} baseUrlStore={props.baseUrlStore} isAdmin={props.isAdmin}/>
                </li>
              ))}</ul>
       </div>
  )
}

export default TopProducts
