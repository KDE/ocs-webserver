import UserToolTipModule from '../../../common/user-tooltip-module';
import { FormatDate } from '../helpers/right-sidebar-helpers';

function CommentsModule(props){

    const comments = props.comments.map((cItem,index) => (
        <CommentListItem 
            key={index}
            index={index}
            item={cItem}
            onChangeUrl={props.onChangeUrl}
        />
    ));

    return (
        <React.Fragment>
            <h3 className="mt0 mb3 title-small-upper">
                <a href="#">Comments {'>'}</a>
            </h3>
            <div className="pui-coments-container">
                {comments}
            </div>
        </React.Fragment>
    )
}

function CommentListItem(props){

    const item = props.item;
    const commentUserLink = "/u/"+item.username;
    const commentProductLink = "/p/"+item.comment_target_id+"/";

    /*function onCommentItemClick(e,commentProductLink){
        e.preventDefault();
        props.onChangeUrl(commentProductLink,item.title,item.project_category_id);
    }

    function onCommentUserNameClick(e){
        e.preventDefault();
        props.onChangeUrl(commentUserLink, item.username);
    }*/

    let itemProfileImageUrl = item.profile_image_url;
    if (itemProfileImageUrl.indexOf('hive/') > -1){
        itemProfileImageUrl = `https://cdn.pling.${ window.location.host.endsWith('com') ? 'com' : 'cc'}/cache/40x40/img/${item.profile_image_url}`;
    }

    return(
        <div className="pui-comment">
            <a href={commentProductLink} title="product page link">
                <div class="pui-comment-title">
                    <p>{item.title}</p>
                    <p>{item.catTitle}</p>
                </div>
                <div class="pui-comment-body">
                    <p dangerouslySetInnerHTML={{__html:item.comment_text}}></p>
                </div>
            </a>
            <a href={commentUserLink} title="user profile link">
                <div className="pui-comment-author">
                    <figure>
                        <img src={itemProfileImageUrl}/>
                    </figure>
                    <p>{item.username}</p>
                    <p><span>{FormatDate(item.comment_created_at)}</span></p>
                </div>
            </a>
        </div>
    )
}

export default CommentsModule;