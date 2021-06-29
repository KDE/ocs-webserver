import { useState, useEffect, useRef, Suspense, lazy } from 'react';
import { GenerateImageUrl, GenerateColorBasedOnRatings } from './common-helpers';
import TimeAgo from 'react-timeago';
import ScoreModule from './score-module';
import CustomModal from './modal';
import UserToolTipModule from './user-tooltip-module';
import LoadingDot from './loading-dot';

const Pagination = lazy(() => import('./pagination')) 

// import './style/comments.css';
import SupporterSvg from '../../layout/style/media/supporter.svg';
import CraetorBadgeSVG from '../../layout/style/media/creator.svg';


function CommentsModule(props){
    
    let initComments = [];
    if (props.comments) initComments = props.comments;
    const [ comments, setComments ] = useState(initComments);
    const [ loading, setLoading ] = useState(true);
    let initCurrentPage = 1;
    if (props.currentPage) initCurrentPage = props.currentPage;
    const [ currentPage, setCurrentPage ] = useState(1);

    useEffect(() => {
        if (!props.comments) getComments();
        else setLoading(false);
    },[])

    useEffect(() => {
        const newComments = props.comments;
        setComments(newComments)
    },[props.comments])

    function getComments(url,page){
        if (!url) url = props.featchCommentsUrl;
        if (page) url += "?page=" + page;
        $.ajax({url:url}).done(function(res){
            setComments(res);
            setLoading(false);
        });
    }

    function onCommentsPageChange(page){
        // setLoading(true);
        props.onCommentsPageChange(page)
    }

    let commentFormDisplay;
    if (props.user){

        let showRatings = true;
        if (props.user && props.product && props.user.member_id === parseInt(props.product.member_id)) showRatings = false;

        commentFormDisplay = (
            <CommentForm 
                userRatings={props.userRatings}
                product={props.product}
                user={props.user}
                onPostComment={props.onPostComment}
                showRatings={showRatings}
                type={props.type}
                isDeprecated={props.isDeprecated}
                onUpdateProductIsDeprecated={props.onUpdateProductIsDeprecated}
            />
        )
    } else {
        commentFormDisplay = (
            <div className="no-user-display">
                <span style={{marginRight:"3px"}} className="glyphicon glyphicon-share-alt"></span>
                Please <a href={(json_loginurl ? json_loginurl : "/login/")}>login</a> or <a href="/register/">register</a> to add a comment or rating
            </div>
        )
    }

    let commentListDisplay;
    if (!loading) {
        commentListDisplay = (
            <CommentList 
                product={props.product} 
                comments={props.comments} 
                user={props.user}
                currentPage={props.currentPage}
                onPostComment={() => props.onPostComment(props.currentPage)}
                onChangeUrl={props.onChangeUrl}
                isAdmin={props.isAdmin}
            />
        )
    }
    else {
        commentListDisplay = <LoadingDot/>
    }
    
    let paginationDisplay;
    if (props.commentCount > 25){
        paginationDisplay = (
            <Suspense fallback={''}>
                <Pagination 
                    numberOfPages={Math.ceil(props.commentCount / 25)}
                    onPageChange={onCommentsPageChange}
                    currentPage={props.currentPage}
                />
            </Suspense>
        )
    }



    let loadingDisplay;
    if (props.loading === true){
        loadingDisplay = <LoadingDot/>
    }

    return (
        <div id={props.containerId} className="comments-module">
            <h3 className="mt0">{props.title ? props.title : "Ratings & Comments"}</h3>
            {commentFormDisplay}
            <p className="section-title mt4">{props.commentCount + " Comment" + (props.commentCount !== 1 ? "s" : "")}</p>
            {commentListDisplay}
            <div style={{display:"flex"}} className="bottom-pagination-container">
                {paginationDisplay}
                {loadingDisplay}
            </div>
        </div>
    )
}

export const CommentForm = (props) => {

    let initUserRatings = null
    if (props.showRatings === false) initUserRatings = null;
    else if (props.userRatings !== null) initUserRatings = props.userRatings;
    
    const [ userRatings, setUserRatings ] = useState(initUserRatings);
    const [ text, setText ] = useState('');
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState('');

    function onCommentTextUpdate(e) {
        setText(e.target.value);
    }

    function onUpdateRating(res){
        setUserRatings(res)
    }

    function onPostComment(){

        if (!loading){
            
            let condition = text.length === 0;
            if (userRatings === null) condition = text.length < 5;
            else if (parseInt(userRatings) <= 5) condition = text.length < 5;
        
            if (condition){
                const newError = "minimum " + (userRatings === null || parseInt(userRatings) < 5 ? "5" : "1") + " chars";
                setError(newError);
            } else {
                setError('');
                setLoading(true);

                let parentCommentIdDisplay = ""
                if (props.commentParent) parentCommentIdDisplay = "i="+props.commentParent.comment_id+"&";
                
                let commentRatingOptionDisplay = "";
                if (userRatings !== null) commentRatingOptionDisplay = "&r=" + userRatings;
    
                let tValueDisplay = "";
                if (props.type === "licensing") tValueDisplay = "t=50&";
                else if (props.type === "moderaion") tValueDisplay = "t=30&";
        
                const serializedData =  parentCommentIdDisplay + 
                                        "p="+props.product.project_id+"&"+
                                        "m="+props.user.member_id+"&"+
                                        tValueDisplay+
                                        "msg="+ text +
                                        commentRatingOptionDisplay;
                
                $.ajax({
                    data:serializedData,
                    url:"/productcomment/addreply",
                    method:"POST"
                }).done(function(res){
                    setLoading(false)
                    setText('');
                    props.onPostComment();
                })
            }
        }
    }

    let sendIconDisplay = <span className="glyphicon glyphicon-send"></span>
    if (loading === true){
        sendIconDisplay = <LoadingDot/>
    }


    let scoreModuleDisplay;    
    if (props.showRatings === true && props.type === "comments"){
        scoreModuleDisplay = (
            <ScoreModule 
                userRatings={userRatings}
                user={props.user}
                select={true}
                circleSize={24}
                userScore={true}
                onUpdateRating={onUpdateRating}
                noModal={true}
            />
        )
    }

    let addCommentDisplay;
    if (!props.commentParent) addCommentDisplay = <small style={{fontSize:"12px"}}>Add a comment</small>

    let productDeprecatedInputDisplay;
    if (props.type === "moderation") productDeprecatedInputDisplay = <ProductDeprecatedInput onUpdateProductIsDeprecated={props.onUpdateProductIsDeprecated} isDeprecated={props.isDeprecated} projectId={props.product.project_id} />
    
    let errorDisplay;
    if (error.length > 0) errorDisplay = <p className="composer-warning">{error}</p>

    return (
        <div className="comment-composer">
            <div className="product-add-comment">
                {productDeprecatedInputDisplay}
                {addCommentDisplay}
                <div className="comment-composer-focus">
                    <textarea style={{backgroundColor:loading === true ? "#efefef" : ""}} disabled={loading} value={text} onChange={(e) => onCommentTextUpdate(e)}></textarea>
                </div>
                {errorDisplay}
                <div className="comment-composer-options">
                    <div className="rating">
                        {scoreModuleDisplay}
                    </div>
                    <button onClick={(e) => onPostComment(text)} className="comment-form-submit-button">
                        {sendIconDisplay}
                        <span>Send</span>
                    </button>
                </div>
            </div>
        </div>
    )
}

function ProductDeprecatedInput(props){
    let initialIsDeprecated = props.isDeprecated;
    const [ isDeprecated, setIsDeprecated ] = useState(initialIsDeprecated);
    const [ loading, setLoading ] = useState(false);

    function onProductIsDeprecatedClick(e){
        setLoading(true)
        e.preventDefault();
        const checked = isDeprecated === true ? false : true;
        let status = 0;
        if (checked === true) status = 1;
        var target = "/backend/project/dodeprecated?project_id="+props.projectId+"&product_deprecated=" + status;
        $.ajax({
            url: target,
            success: function (results) {
                let value;
                if (status == 0) {
                    alert('Project deprecated is successfully removed');
                    value = false;
                } else {
                    alert('Project is successfully marked as deprecated');
                    value = true;
                }
                props.onUpdateProductIsDeprecated(value)
                setIsDeprecated(value);
                setLoading(false);
            },
            error: function () { alert('Service is temporarily unavailable.'); }
        });
            
    }

    let loadingDisplay;
    if (loading === true){
            loadingDisplay = <LoadingDot/>
    }

    return (
        <div>
            <input type="checkbox" onChange={e => onProductIsDeprecatedClick(e)} checked={isDeprecated === true ? 'checked' : ''} /> product-deprecated {loadingDisplay}
        </div>
    )
}

const CommentList = (props) => {

    const [ refreshToolTips, setRefreshToolTips ] = useState(false);
    const prevComments = usePrevious(props.comments);

    useEffect(() => {
        if (props.comments && props.comments !== prevComments) setRefreshToolTips(true);
    },[props.comments])

    useEffect(() => {
        if (refreshToolTips === true) setRefreshToolTips(false);
    },[refreshToolTips])

    let commentListDiplay;
    if (props.comments && props.comments.length > 0){
        commentListDiplay = props.comments.map((c,index) => {

            if (c.comment.comment_active === "1"){
                return (
                    <CommentListItem 
                        key={c.comment_id}
                        comment={c}
                        comments={props.comments}
                        product={props.product}
                        user={props.user}
                        onPostComment={props.onPostComment}
                        onChangeUrl={props.onChangeUrl}
                        refreshToolTips={refreshToolTips}
                        isAdmin={props.isAdmin}
                    />
                )
            }
        })
    } else {
        commentListDiplay = <div>Be the first to comment</div>
    }
    return (
        <div className="comments-list">
            {commentListDiplay}
        </div>
    )
}

function CommentListItem(props){

    const comment = props.comment.comment;
    const commentLevel = props.comment.level;
    
    const [ showReplyForm, setShowReplyForm ] = useState(false);

    function toggleShowReplyForm(){
        let newShowReplyFormValue = true;
        if (showReplyForm === true) newShowReplyFormValue = false;
        setShowReplyForm(newShowReplyFormValue)
    }

    function onPostComment(){
        props.onPostComment();
        setShowReplyForm(false);
    }

    function onCommentUserNameClick(e){
        if (props.onChangeUrl){
            e.preventDefault();
            props.onChangeUrl("/u/"+comment.username);
        } else window.location.href = "/u/"+comment.username
    }

    // profile image url
    const profileImageUrl = GenerateImageUrl(comment.profile_image_url,60,60);

    // ratings badge 
    const commentRating = comment.rating_member;
    let ratingsBadgeDisplay;
    if (commentRating){
        ratingsBadgeDisplay = (
            <div className={"rating-badge"}>
                <span className={"pui-badge pui-score-tag-"+parseInt(commentRating)}>{commentRating}</span>
            </div>
        )
    }

    let commentTextRatingDisplay;
    if (comment.rating){
        commentTextRatingDisplay = <span className={"pui-badge pui-score-tag-"+parseInt(commentRating)}>{comment.rating}</span>
    }

    // supporter badge
    let supporterBadge;
    if (comment.issupporter !== "0"){
        const activeCssClass = comment.issupporterActive === true ? "" : "inactive";
        supporterBadge = (
            <div className={"supporter-badge supporter-badge-"+(commentLevel > 1 ? "small" : "large") + " " + activeCssClass} title="Supporters">
                <span> S{comment.issupporter} </span>
                <img src={SupporterSvg}/>
            </div>
        )
    }

    // creator badge
    let creatorBadge;
    if (comment.member_id === props.product.member_id){
        creatorBadge = (
            <span className={"creator-badge creator-badge-"+(commentLevel > 1 ? "small" : "large")}>
                <span></span>
                <img src={CraetorBadgeSVG}/>
            </span> 
        )
    }

    // moderator badge
    let moderatorBadge;
    if (comment.roleid === "400") moderatorBadge = <div className={"mod-badge mod-badge-"+commentLevel}>MOD</div>

    // is admin css class
    const isAdminCssClass = comment.roleid === "400" ? " is-mod" : "";

    let replyButtonDisplay;
    if (props.user) replyButtonDisplay =  <a onClick={toggleShowReplyForm} style={{cursor:"pointer"}} className="action-reply"> Reply </a>

    // show comment form display
    let replyFormDisplay;
    if (showReplyForm === true){
        replyFormDisplay = (
            <CommentForm 
                product={props.product}
                user={props.user}
                showRatings={false}
                commentParent={comment}
                onPostComment={onPostComment}
            />
        )
    }

    let deleteCommentDisplay;
    if (props.isAdmin){
        deleteCommentDisplay = (
            <CommentListITemDeleteComment 
                product={props.product}
                user={props.user}
                comment={comment}
            />
        )
    }

    let commentCssClass = "comment-input ",
        userAvatarCssClass = "us-user-64";
    if (commentLevel >= 2){
        
        commentCssClass += " comment-input-reply ";
        userAvatarCssClass = "us-user-48";
        if (commentLevel > 2) {

            commentCssClass += "reply-lvl-"+(commentLevel-1);
            // userAvatarCssClass = "us-user-40";
            // if (commentLevel > 3) userAvatarCssClass = "us-user-32";
        }
    }

    return (
        <div className={commentCssClass + " " + isAdminCssClass}>
            <div className="comment-input-grid">
                <div className={userAvatarCssClass}>
                    <a href={"/u/"+comment.username}>
                        <figure>
                            {supporterBadge}
                            {ratingsBadgeDisplay}
                            {creatorBadge}
                            {moderatorBadge}
                            <img src={profileImageUrl}/>
                        </figure>
                    </a>
                </div>
                <div className="comment-input-body">
                    <div>
                        <span className="comment-input-user">
                            <UserToolTipModule
                                showBy={false} 
                                place="right"
                                effect="solid"
                                type="light"
                                backgroundColor="#ededed"
                                borderColor="#ccc"
                                border={true}
                                username={comment.username}
                                memberId={comment.member_id}
                                toolTipId={"comment-item-popover-container-"+comment.comment_id}
                                userNameClassName={"comment-list-item-username"}
                                onUserNameClick={onCommentUserNameClick}
                                refreshToolTips={props.refreshToolTips}
                            />
                        </span>
                        <span className="delimiter">•</span>
                        <span className="comment-input-date">
                                <TimeAgo date={comment.comment_created_at}></TimeAgo>
                        </span>
                    </div>
                    <div>
                        <p>
                            {commentTextRatingDisplay} {comment.comment_text}
                        </p>
                    </div>
                    <div className="comment-input-respond">
                            {replyButtonDisplay}
                            <CommentListItemReportComment
                                comment={comment}
                                product={props.product}
                                user={props.user}
                            />
                            {deleteCommentDisplay}
                    </div>
                    {replyFormDisplay}
                </div>                
            </div>        
        </div>
    )
}

function  CommentListITemDeleteComment(props) {

    const [ showModal, setShowModal ] = useState(false);

    function onDeleteComment() {
        $.ajax({
            url:`/productcomment/delcomment?p=${props.product.project_id}&c=${props.comment.comment_id}`,
            method:'POST'
        }).done(function(res) {
            console.log(res);
            window.location.href = res.redirect;
        })
    }
  
    return (
        <React.Fragment>
            <a onClick={() => setShowModal(true)} style={{cursor:"pointer",fontWeight:"bold"}} className="action-report"> DELETE </a>
            <CustomModal
                isOpen={showModal}
                header={'Report Comment'}
                hasFooter={true}
                closeModal={e => setShowModal(false)}
                onRequestClose={e => setShowModal(false)}
                modalBodyClassName="mini">
                <React.Fragment>
                    <p>{'Are you sure to remove this comment?'}</p>
                    <div className="custom-modal-footer">
                        <a style={{cursor:"pointer"}} className="pui-btn primary" onClick={() => setShowModal(false)}>Cancel</a>
                        <a style={{cursor:"pointer"}} className="pui-btn primary" onClick={onDeleteComment}>Yes</a>
                    </div>
                </React.Fragment>
            </CustomModal>
        </React.Fragment>        
    )
}

function CommentListItemReportComment(props){
    
    const [ showModal, setShowModal ] = useState(false);
    const [ isReported, setIsReported ] = useState(false);
    const [ loading, setLoading ] = useState(false); 

    function onReportCommentClick(){
        if (!isReported && !loading){
            setLoading(true);
            const serializedData = 'i='+props.comment.comment_id+'&p='+props.product.project_id;
            $.ajax({
                url:'/report/comment/',
                data:serializedData,
                method:'POST'
            }).done(function(res){
                setIsReported(true);
                setLoading(false);
                // setShowModal(false);
            })
        }
    }

    let modalTextDisplay = (
        <div className="please-login">
            <p>
            Please Login.
            </p>
            <a className="pui-btn primary" href={(json_loginurl ? json_loginurl : "/login/")}>Login</a>
        </div>
    )
    if (props.user) modalTextDisplay =  "Do you really want to report this comment?"
    if (isReported === true) modalTextDisplay = "Thank you for helping us to keep these sites SPAM-free."

    let buttonDisplay;
    if (props.user){
        if (loading === true){
            buttonDisplay = (            
                <button style={{opacity:"1"}} className="footer-button small close">
                    <React.Fragment>
                        <span className="glyphicon glyphicon-refresh spinning"></span>
                        Reporting...
                    </React.Fragment>
                </button>
            )
        } else {
            if (isReported === true){
                buttonDisplay = (
                    <button onClick={e => setShowModal(false)} className="footer-button small close">
                        <span>Close</span>
                    </button>
                )
            } else {
                buttonDisplay = (
                    <button  style={{opacity:"1"}} onClick={onReportCommentClick} className="footer-button small close">
                            <span>Yes</span>
                    </button>
                )
            }
        }
    }

    return (
        <React.Fragment>
            <a onClick={() => setShowModal(true)} style={{cursor:"pointer"}} className="action-report"> Report </a>
            <CustomModal
                isOpen={showModal}
                header={'Report Comment'}
                hasFooter={true}
                closeModal={e => setShowModal(false)}
                onRequestClose={e => setShowModal(false)}
                modalBodyClassName="mini">
                <React.Fragment>
                    <p>{modalTextDisplay}</p>
                    <div className="custom-modal-footer">
                        {buttonDisplay}
                    </div>
                </React.Fragment>
            </CustomModal>
        </React.Fragment>

    )
}

// Hook
function usePrevious(value) {
    // The ref object is a generic container whose current property is mutable ...
    // ... and can hold any value, similar to an instance property on a class
    const ref = useRef();
    
    // Store current value in ref
    useEffect(() => {
      ref.current = value;
    }, [value]); // Only re-run if value changes
    
    // Return previous value (happens before update in useEffect above)
    return ref.current;
  }
  

export default CommentsModule;