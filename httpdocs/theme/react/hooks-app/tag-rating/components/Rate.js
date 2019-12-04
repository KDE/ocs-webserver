import React from 'react'

const Rate = (props) => {  
   let l = props.values.filter(el=>el.tag_id==props.label.id && el.vote==props.vote);
   let userVoted = false;
   l.map(el=>{
       if(props.user && props.user.member_id==el.member_id){
        userVoted=true;
       }
   });
   const t = userVoted ? '':'o-';
   const iconCls = props.vote==1?"fa fa-thumbs-"+t+"up":"fa fa-thumbs-"+t+"down";

   let content;
   if(props.vote==1)
   {
       content =<>
                    <i className={iconCls}></i>
                    {l.length>0?l.length:''}
                </>
   }else{
        content =<>
                    <i className={iconCls}></i>
                    {l.length>0?l.length:''}      
        </>
   }
return (
    <span style={{display:'inline-block',width:'25px',marginLeft:'5px'}}>
        {content}        
    </span>
)
}

export default Rate;