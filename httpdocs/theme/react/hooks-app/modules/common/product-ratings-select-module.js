import { useEffect, useState } from 'react';
import LoadingDot from './loading-dot';
import CustomModal from './modal';

function ProductRatingsSelect(props){
    
    let initialUserRatings = "0";
    let initialIsRatedValue = false;
    if (props.userRatings){
        if (props.userRatings.score){
            initialUserRatings = props.userRatings.score;
            initialIsRatedValue = true;
        }
    }

    const [ userRatings, setUserRatings ] = useState(initialUserRatings);
    const [ isRated, setIsRated ] = useState(initialIsRatedValue);
    const [ showModal, setShowModal] = useState(false);
    
    useEffect(() => {
        if (props.userRatings && props.userRatings.score){
            let newUserRatings, newIsRatedValue;
            newUserRatings = props.userRatings.score;
            newIsRatedValue = true;
            setUserRatings(newUserRatings)
            setIsRated(newIsRatedValue);
        }
    },[props.userRatings])


    function onChangeProductRatings(e){
        if (e.target.value !== initialUserRatings && e.target.value !== "0"){
            if (props.noModal === true){
                props.onUpdateRating(e.target.value);
                let newUserRatings = e.target.value, newIsRatedValue = true;
                if (newUserRatings === "-1"){
                    newUserRatings = null;
                    newIsRatedValue = false;
                }
                setIsRated(newIsRatedValue)
                setUserRatings(newUserRatings);
            } else {
                setUserRatings(e.target.value);
                setShowModal(true);
            }
        }
    }

    function onCancelProductReview(){
        const newUserRatingsValue = props.userRatings !== null ? props.userRatings.score : "0";
        setUserRatings(newUserRatingsValue);
        setShowModal(false);
    }

    function onFinishProductReview(res){
        setShowModal(false);
        let newIsRatedValue = true;
        if (userRatings === -1) newIsRatedValue = false;
        setIsRated(newIsRatedValue);
        props.onUserRatingsAction(res);
    }

    let defaultOption = {value:"0",label:"Add Rating"}
    if (isRated === true) defaultOption = { value:"-1", label:"Remove Rating"}

    const ratingsOptions = [
        defaultOption,
        {value:"10",label:"10 the best"},
        {value:"9",label:"9 excellent"},
        {value:"8",label:"8 great"},
        {value:"7",label:"7 good"},
        {value:"6",label:"6 okay"},
        {value:"5",label:"5 average"},
        {value:"4",label:"4 soso"},
        {value:"3",label:"3 bad"},
        {value:"2",label:"2 really bad"},
        {value:"1",label:"1 ugh"},
    ];

    const ratingsOptionsDisplay = ratingsOptions.map((ro,index) => ( <option key={index} value={ro.value}>{ro.label}</option> ))
    
    let selectedRatingsOption;
    if (userRatings && userRatings !== null){
        if (userRatings === "-1"){
            selectedRatingsOption = "Remove Rating"
        } else {
            const sroIndex = ratingsOptions.findIndex((ro => ro.value === userRatings))
            selectedRatingsOption = ratingsOptions[sroIndex].label;
        }
    }

    let modalBodyDisplay;
    let headerDisplay;
    let modalBodyClassName;
    if (props.user){
        if (props.product && parseInt(props.product.member_id) === props.user.member_id){
            headerDisplay = "Project owner not allowed.";
        } else {
            headerDisplay = 'Add a review to your rating "' + selectedRatingsOption + '" (min. 1 char)"';
            modalBodyDisplay = (
                <AddProductRatingsForm 
                    {...props} 
                    prevUserRatings={props.userRatings}
                    userRatings={userRatings}
                    selectedRatingsOption={selectedRatingsOption}
                    closeModal={onCancelProductReview}
                    onFinishProductReview={onFinishProductReview}
                />
            )
        }
    } else {
        modalBodyClassName = "align-center"
        modalBodyDisplay = (
            <React.Fragment>
                    <div className="please-login">
                        <p>
                        Please Login.
                        </p>
                        <a className="pui-btn primary" href={(json_loginurl ? json_loginurl : "/login/")}>Login</a>
                    </div>
            </React.Fragment>
        )
    }

    return (
        <div className="rating">
            <select className="ratingoptions" value={userRatings} onChange={(e) => onChangeProductRatings(e)}>
                {ratingsOptionsDisplay}
            </select>
            <CustomModal
                isOpen={showModal}
                header={headerDisplay}
                hasFooter={true}
                closeModal={onCancelProductReview}
                onRequestClose={onCancelProductReview}
                modalBodyClassName={modalBodyClassName}>
                {modalBodyDisplay}
            </CustomModal>
        </div>
    )
}

function AddProductRatingsForm(props){

    const initTextValue = props.selectedRatingsOption;
    const [ text, setText ] = useState(initTextValue);
    const [ error, setError ] = useState(false);
    const [ loading, setLoading ] = useState(false);

    function onSubmitUserProductReview(){

        setLoading(true);
        
        let oTextVal = "",
            userScoreVal = "-1"
        if (props.prevUserRatings && props.prevUserRatings !== null){
            if (props.prevUserRatings.comment_text) oTextVal = props.prevUserRatings.comment_text.replace(" ", "%20");
            if (props.prevUserRatings.score) userScoreVal = props.prevUserRatings.score;
        }

        const serializedString = "p="  + props.product.project_id + "&" +
                                 "m="  + props.user.member_id + "&" + 
                                 "v="  + ( props.userRatings > 5 || props.userRatings === "-1" ? "1" : "2" ) + "&" +
                                 "pm=" + props.product.member_id + "&" +
                                 "otext=" + oTextVal + "&" + 
                                 "userscore=" + userScoreVal + "&" +
                                 "s=" + props.userRatings + "&" + 
                                 "msg=" + text.split(" ").join("%20");

        $.ajax({
            data:serializedString,
            url:"/productcomment/addreplyreviewnew/",
            type:"POST",
            error: function () {
                setError(true);
                setLoading(false);
            },
            success: function (results) {
                /*const newRatingOfUser = {
                    comment_id: null,
                    comment_text: text,
                    created_at: Date.now(),
                    member_id: props.user.member_id,
                    project_id: props.product.project_id,
                    rating_active: "1",
                    rating_id: null,
                    score: props.userRatings,
                    score_test: null,
                    source_id: null,
                    source_pk: null,
                    user_dislike:  ( props.userRatings < 5 ? "1" : "0" ),
                    user_like: ( props.userRatings > 5 ? "1" : "0" )
                }*/
                //props.onFinishProductReview(newRatingOfUser);
                //setLoading(false);
                location.reload();
            }
        })

    }
    

    let formBodyDisplay, rateNowBtnDisplay;
    const rateNowBtnText = props.userRatings === "-1" ? "Remove Rating" : "Rate Now";
    formBodyDisplay = <textarea style={{backgroundColor:loading === true ?  "#efefef" : ""}} disabled={loading === true ? "disabled" : false} value={text} onChange={(e)=>setText(e.target.value)}></textarea>
    if (error) formBodyDisplay = <span class='error'>Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.</span>
    if (loading === true){
        rateNowBtnDisplay = (
            <button className="footer-button">
                <LoadingDot/>
                {rateNowBtnText}
            </button>
        )
    } else rateNowBtnDisplay = <button onClick={onSubmitUserProductReview} className="footer-button">{rateNowBtnText}</button>

    return (
        <React.Fragment>
            <div id="add-product-ratings-form">
                {formBodyDisplay}
            </div>
            <div className="custom-modal-footer">
                <button onClick={props.closeModal} className="footer-button float-left">Cancel</button>
                {rateNowBtnDisplay}
            </div>
        </React.Fragment>
    )
}

export default ProductRatingsSelect;