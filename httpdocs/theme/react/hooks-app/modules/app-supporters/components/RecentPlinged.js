import React, { useEffect , useState}from 'react'
import RecentPlingedProduct from './RecentPlingedProduct';
const RecentPlinged = (props) => {
        
    useEffect(() => {                 
        TooltipUserPlings.setup("tooltipuserplings", "right");      
        TooltipUser.setup('tooltipuser','right');           
    },[props.products]);

    return (
         
         <div id="recentplinged-list">       
            {props.products.map((product,index) => (                
                <RecentPlingedProduct key={index} product={product} baseUrlStore={props.baseUrlStore}/>                
              ))}
        </div>

    )
}

export default RecentPlinged