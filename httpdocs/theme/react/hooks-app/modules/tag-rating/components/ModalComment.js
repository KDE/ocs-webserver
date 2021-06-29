import React from 'react'

 const ModalComment = (props) => {
    const l = props.labels.filter(el=>el.id==props.tid);       
    const iconCls = props.vote == 1 ? "fa fa-thumbs-up" : "fa fa-thumbs-down";
    const iconColor = props.vote == 1 ? "#4CAF50" : "#FF0000";
    let name;
    let removeTitle;
    if(props.tid && l && l.length==1)
    {
        name=l[0].name;
        let v = props.values.filter(el=>el.tag_id==props.tid && el.vote==props.vote && el.member_id==props.user.member_id);
        
        if(v && v.length==1)
        {
            removeTitle='Remove ';
        }else{
            removeTitle='Add ';
        }
    }
    
    
    return (
        <div className="modal fade " id="tag-voting-comment-modal" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="tag-voting-comment-modal-label" aria-hidden="true">
                <div className="modal-dialog " role="document" style={{width:'500px'}}>
                <form name="form-tag-voting-comment" onSubmit={props.handleSubmit}>
                <div className="modal-content">
                    <div className="modal-header">              
                    <button type="button" className="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h5 className="modal-title" id="tag-voting-comment-modal-label">
                       {removeTitle} <i className={iconCls} style={{ color: iconColor }}> </i> on {name}
                    </h5>
                    </div>
                    <div className="modal-body">
                        <div style={{display:'flex'}}>
                        
                        <div>                    
                            <div className="container" style={{width:'450px'}}>                                                                              
                                <div className="row">
                                        <div className="col-lg-12">
                                            <textarea name="comment" id="comment" value={props.comment} 
                                                onChange={props.handleChangeComment} 
                                                required="required" 
                                            style={{width:'400px',height:'120px'}}/>
                                        </div>
                                </div>                                                           
                            </div>
                                                                                
                        </div>
                        </div>
                        

                    </div>
                    <div className="modal-footer">
                        {props.errmsg &&
                            <span style={{color:'#ff0000'}}>{props.errmsg}</span>
                        }                                           
                    <button type="button" className="btn btn-primary active" data-dismiss="modal">Close</button>                                  
                    <button type="submit" className="btn btn-primary active" >Save</button>                                 
                    </div>
                </div>
                </form>
                </div>
            </div>
    )
}

export default ModalComment;