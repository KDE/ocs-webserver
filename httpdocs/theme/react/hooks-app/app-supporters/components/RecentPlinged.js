import React from 'react'
import RecentPlingedProduct from './RecentPlingedProduct';
const RecentPlinged = (props) => {
    
    return (
         
         <div id="recentplinged-list">       
            {props.products.map((product,index) => (                
                <RecentPlingedProduct key={index} product={product} baseUrlStore={props.baseUrlStore}/>                
              ))}
        </div>

    )
}

export default RecentPlinged