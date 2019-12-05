import React, { useState, useEffect } from 'react'
import Axios from 'axios';
import Rate from './Rate';
const TagRating = () => {
    const [labels, setLabels] = useState([]);
    const [values, setValues] = useState([]);   
    const [user, setUser] = useState(window.config.user); 
    useEffect(() => {
        loadTagRatings();
    }, []);

    const loadTagRatings=()=>{
        const url = window.config.baseUrlStore + '/p/' + window.product.project_id + '/loadtagrating?gid=' + window.product.project_category_id;
        Axios.get(url)
            .then(result => {
                setLabels(result.data.labels);
                setValues(result.data.values);                                
            })
    }
    const handleVote =(vote,tid)=>{
        if(user)
        {
            const url = window.config.baseUrlStore + '/p/' + window.product.project_id + '/votetagrating?vote='+vote+'&tid='+tid;
            Axios.get(url)
            .then(result => {
                loadTagRatings();        
            })
        }else{            
            $('#like-product-modal').modal('show');
        }
        
    }
    return (
        <div className="tag-rating-container">
            {labels && labels.length>0 &&
                <h4 style={{marginTop:'20px',borderBottom:'1px solid #ccc',fontWeight:'bold'}}>
                    {labels[0].group_display_name}
                </h4>
            }            
            <ul style={{listStyle:'none',paddingLeft:'0px',marginTop:'20px'}}>{
                labels.map((l, index) => 
                    <li key={index} style={{margin:'3px'}}>                        
                        <a onClick={()=>handleVote(-1,l.id)} style={{color:'#4e4e4e'}}>                            
                            <Rate values={values} vote="-1" label={l} user={user}/>
                            </a> 
                        <span className="taglabel label label-info" style={{marginRight: '3px',width:'120px',display:'inline-block',padding:'3px'}}>{l.name}</span>                        
                        <a onClick={()=>handleVote(1,l.id)} style={{color:'#4e4e4e'}}>                            
                            <Rate values={values} vote="1" label={l} user={user}/>
                             </a> 
                    </li>                                     
                )
            }
            </ul>
        </div>
    )
}
export default TagRating;