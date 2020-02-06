import React ,{useState,useEffect} from 'react'
import ProductsContainer from './ProductsContainer';

const Pling = (props) => {
    const [user, setUser] = useState({});
    const [products, setProducts] = useState([]);
    const [baseUrlStore, setBaseUrlStore] = useState(window.config.baseUrlStore);
    useEffect(() => {                 
        loadData();
    },[]);
    const loadData = async () => {
        const data = await fetch(`/json/pling?username=${props.username}`);
        const items = await data.json();        
        
        if (items && typeof(items.user) != "undefined")   
        {
            setUser(items.user);
            setProducts(items.products);            
        }
        
        
      }

    
    return (
        <div className="sub-system-container">  
        <div className="header">Pling : <a href={baseUrlStore+'/u/'+user.username}>{user.username} </a>
        {
            user.username &&                  
            <img src={user.avatar}></img>            
        }        
        </div>  
        
        <div>
           
        <ProductsContainer baseUrlStore={baseUrlStore} title="Products" products={products}/>
        </div>       
        </div>
    )
}

export default Pling;