import React from 'react'

const RatingBar = (props) => {

    const getVoteLabel = (vote) => {
        let l = props.values.filter(el => el.tag_id == props.label.id && el.vote == vote);
        let userVoted = false;
        l.map(el => {
            if (props.user && props.user.member_id == el.member_id) {
                userVoted = true;
            }
        });
        const t = userVoted ? '' : 'o-';
        const iconCls = vote == 1 ? "fa fa-thumbs-" + t + "up" : "fa fa-thumbs-" + t + "down";

        let content;
        if (vote == 1) {
            content =
                <span style={{display:'inline-block',width:'35px',marginLeft:'5px'}}>
                <i className={iconCls} style={l.length > 0 ? { color: '#4CAF50' } : {}}></i>
                <span style={{paddingLeft:'10px'}}>{l.length > 0 ? l.length : ''}</span>
                </span>
        } else {
            content =
             <span style={{display:'inline-block',width:'35px',marginRight:'5px',textAlign:'right'}}>
                <span style={{paddingRight:'10px'}}>{l.length > 0 ? l.length : ''}</span>
                <i className={iconCls} style={l.length > 0 ? { color: '#FF0000' } : {}}></i>
                
                </span>
            
        }
       
        return content;
    }

    let mystyle = {
        marginRight: '3px'
        , width: '120px'
        , display: 'inline-block'
        , padding: '3px'
        , border: '1px solid #ccc'
        , textAlign: 'center'
    };
    /*
    const getGreenToRedColor= () =>{
        if(props.values && props.values.length>0)
        {
            let lt = props.values.filter(el => el.tag_id == props.label.id && el.vote == -1);
            let rt = props.values.filter(el => el.tag_id == props.label.id && el.vote == 1);
            const percent = Math.floor(rt.length/(rt.length+lt.length))*100;            
            const r = percent<50 ? 255 : Math.floor(255-(percent*2-100)*255/100);
            const g = percent>50 ? 255 : Math.floor((percent*2)*255/100);
        return 'rgb('+r+','+g+',0)';
        }else{
            return 'rgb(255,255,255)';
        }
    }
    
    mystyle.backgroundColor=getGreenToRedColor();
    */
    return (
        <>
        <a onClick={() => props.handleVote(-1, props.label.id)} style={{ color: '#4e4e4e' , cursor:'pointer'}}>
            {getVoteLabel(-1)}
        </a>   
        <div className="bar" style={{display:'inline'}}>
            <span className="taglabel"
                style={mystyle}>
                {props.label.name}
            </span>
        </div>             
        <a onClick={() => props.handleVote(1, props.label.id)} style={{ color: '#4e4e4e', cursor:'pointer' }}>
            {getVoteLabel(1)}
        </a> 
        </>
    )
}

export default RatingBar;
