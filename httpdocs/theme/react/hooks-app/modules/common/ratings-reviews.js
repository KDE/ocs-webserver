
import { useState } from 'react';
import { GenerateImageUrl, GenerateColorBasedOnRatings, GenerateWordBasedOnRatingScore } from './common-helpers';
import TimeAgo from 'react-timeago';

import './style/ratings-reviews.css';

function RatingsReviewsModule(props){

    const [ selectedRatings, setSelectedRatings ] = useState(null);

    const ratingsNumbersDisplay = [1,2,3,4,5,6,7,8,9,10].map((rn,index) => {
        const ratingsColor = GenerateColorBasedOnRatings(rn);
        let ratingsCounterDisplay;
        const ratingsCounter = props.ratings.filter(r => parseInt(r.score) === rn).length;
        if (ratingsCounter > 0) ratingsCounterDisplay = <span style={{color:ratingsColor}} className="ratings-counter">{ratingsCounter}</span>
        return (
            <div key={index} onClick={(e) => setSelectedRatings(selectedRatings === rn ? null : rn)} className="ratings-number-container">
                <span className={"pui-badge pui-score-tag-" + parseInt(rn) + " " + (selectedRatings === parseInt(rn) ? "selected number-display":"number-display")}>
                    {rn}
                </span>
                {ratingsCounterDisplay}
            </div>
        )
    });

    let ratingsReviewsDisplay;
    if (props.ratings && props.ratings.length > 0){
        let ratings;
        if (selectedRatings !== null) ratings = props.ratings.filter(r => parseInt(r.score) === selectedRatings);
        else ratings = props.ratings;
        ratingsReviewsDisplay = ratings.map((rating,index) => (
            <RatingsReviewsListItem
                key={index} 
                rating={rating}
                onRatingsItemClick={props.onRatingsItemClick}
            />
        ))
    }

    return (
        <React.Fragment>
            <div id="products-ratings-container">
                <div id="product-ratings-summary-container">
                    <div id="product-ratings-summary">
                        {ratingsNumbersDisplay}
                    </div>
                </div>
            </div>
            <div id="product-ratings-list">
                {ratingsReviewsDisplay}
                <RatingsReviewsListItem
                    type={'info'}
                />
            </div>
        </React.Fragment>
    )

}

function RatingsReviewsListItem(props){

    const r = props.rating;

    function onRatingsItemClick(e){
        e.preventDefault();
        props.onRatingsItemClick("/u/"+r.username,r.username);
    }

    let ratingsReviewsListItemDisplay;
    if (props.type === "info"){

        ratingsReviewsListItemDisplay = (
            <div className="product-ratings-list-item" id={"ratings-list-item-info"}>
                <div className="rating-container">
                    <div className="rating-header">
                        <a className="rating-profile-image">
                            <figure><img src={"https://cdn.pling.cc/cache/40x40/img/hive/user-pics/nopic.png"}/></figure>
                        </a>
                        <span style={{backgroundColor:GenerateColorBasedOnRatings(5)}} className="rating-number">{"Base: 5 x 5.0 Ratings"}</span>
                    </div>
                </div>
            </div>            
        )

    } else {
             
        const ratingsItemProfileImage = GenerateImageUrl(r.profile_image_url,40,40);
        const scoreWordDisplay = GenerateWordBasedOnRatingScore(r.score);

        ratingsReviewsListItemDisplay = (
            <div className="product-ratings-list-item" id={"ratings-list-item-"+r.rating_id}>
                <div className="rating-container">
                    <div className="rating-header">
                        <a onClick={e => onRatingsItemClick(e)} className="rating-profile-image" href={"/u/"+r.username}>
                            <figure><img src={ratingsItemProfileImage}/></figure>
                        </a>
                        <span className="rating-username">
                            <a onClick={e => onRatingsItemClick(e)} href={"/u/"+r.member_id}>{r.username}</a>
                        </span>
                        <span class="delimiter">•</span>
                        <span className="small light lightgrey product-update-date rating-created-at">
                            <TimeAgo date={r.created_at}></TimeAgo>
                        </span>
                        <span class="delimiter">•</span>
                        <span className={"pui-badge pui-score-tag-" + parseInt(r.score)}>{r.score}</span>
                        <span className="rating-number-word">{scoreWordDisplay}</span>
                    </div>
                    <div className="rating-text">{r.comment_text}</div>
                </div>
            </div>
        )
    }
    
    return (
        <React.Fragment>
            {ratingsReviewsListItemDisplay}
        </React.Fragment>
    )

}

export default RatingsReviewsModule;