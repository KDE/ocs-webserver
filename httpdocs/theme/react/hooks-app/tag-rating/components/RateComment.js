import React from 'react'
const RateComment = (props) => {
   
    let l = props.labels.filter(el=>el.id==props.vote.tag_id);
    let userVoted = false;
    if(props.user && props.user.member_id==props.vote.member_id){
        userVoted=true;
       }
    const t = userVoted ? '':'o-';
    const iconCls = props.vote.vote==1?"fa fa-thumbs-"+t+"up":"fa fa-thumbs-"+t+"down";
    
    return (
        <div>
            {l[0].name} <i className={iconCls} style={props.vote.vote==1?{color:'#4CAF50'}:{color:'#FF0000'}}></i> : {props.vote.comment_text}        
        </div>
    )
}

export default RateComment;