import React, { useState, useEffect } from 'react'
import Axios from 'axios';
import Rate from './Rate';
import Bar from './Bar';
import ModalComment from './ModalComment';
import RateComment from './RateComment';
const TagRating = () => {
    const [labels, setLabels] = useState([]);
    const [values, setValues] = useState([]);   
    const [comment, setComment] = useState('');   
    const [user, setUser] = useState(window.config.user); 
    const [project_id, setProject_id] = useState(window.product.project_id);   
    const [vote, setVote] = useState(null);   
    const [tid, setTid] = useState(null);   
    const [errmsg, setErrmsg] = useState('');
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
            setVote(vote);
            setTid(tid);         
            $('#tag-voting-comment-modal').modal('show');                        
        }else{            
            $('#like-product-modal').modal('show');
        }        
    }

    const handleSubmitVote= event =>{        
        event.preventDefault();
        if(comment.length<=2)
        {
            setErrmsg('Please fill out comment at least 3 chars.');
            return;
        }

        const url = window.config.baseUrlStore + '/p/' + project_id + '/votetagrating';
        const params = new URLSearchParams();
        params.append('vote', vote);
        params.append('tid', tid);
        params.append('msg', comment);
        Axios.post(url,params)
        .then(function (response) {
            setComment('');
            $('#tag-voting-comment-modal').modal('hide');      
            loadTagRatings();            
        })
        .catch(function (error) {
          console.log(error);
        });
    }

    
    const handleChangeComment = event =>{
        setComment(event.target.value);
        if(event.target.value.length>=2)
        {
            setErrmsg('');
        }
      }

   
    return (
        <div className="tag-rating-container">
            {labels && labels.length>0 &&
                <h4 style={{marginTop:'20px',borderBottom:'1px solid #ccc',fontWeight:'bold'}}>
                    {labels[0].group_display_name}
                </h4>
            }            
            <div style={{display:'flex'}}>
            <ul style={{listStyle:'none',paddingLeft:'0px',marginTop:'20px'}}>{
                labels.map((l, index) => 
                    <li key={index} style={{margin:'3px'}}>                        
                        <a onClick={()=>handleVote(-1,l.id)} style={{color:'#4e4e4e'}}>                            
                            <Rate values={values} vote="-1" label={l} user={user}/>
                            </a> 
                        <Bar title={l.name}></Bar>                     
                        <a onClick={()=>handleVote(1,l.id)} style={{color:'#4e4e4e'}}>                            
                            <Rate values={values} vote="1" label={l} user={user}/>
                             </a> 
                    </li>                                     
                )
            }
            </ul>
            <div style={{borderLeft:'1px solid #ccc', maxHeight:'300px', overflow:'auto', marginLeft:'20px',paddingLeft:'20px'}}>
            <ul style={{listStyle:'none',paddingLeft:'0px',marginTop:'20px'}}>
            {   
                    values.map((v, index) => 
                    <li key={index} style={{margin:'3px'}}>                                                
                        <RateComment labels={labels} vote={v} user={user}/>
                    </li>                                     
                    )
            }
            </ul>
            </div>
            </div>

            <ModalComment handleSubmit={handleSubmitVote} 
                        comment={comment} 
                        handleChangeComment={handleChangeComment}
                        errmsg ={errmsg}
                        ></ModalComment>
        </div>
    )
}
export default TagRating;