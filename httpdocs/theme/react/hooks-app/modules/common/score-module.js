import React, { useState } from 'react';
import ScoreCircleModule from './score-circle-module';
import ProductRatingsSelect from './product-ratings-select-module';
import { GenerateColorBasedOnRatings } from './common-helpers';

function ScoreModule(props){
    
    const product = props.product;

    let initScore, initScoreColor;
    if (props.userScore === true && props.userRatings !== null){
        if (props.userRatings.score){
            initScore = parseInt(props.userRatings.score) * 100;
            initScoreColor = GenerateColorBasedOnRatings(score);
        }
    } else if (props.product) {
        initScore = product.score ? product.score : product.laplace_score;
        initScoreColor = score > "500" ? GenerateColorBasedOnRatings(initScore) : "#c8c8c8";        
    }
    const [ score, setScore ] = useState(initScore !== null ? initScore : "0");
    const [ scoreColor, setScoreColor ] = useState(initScoreColor);

    function onUpdateRating(res){
        if (res === "-1"){
            setScore(null)
            setScoreColor(null);
        } else {
            setScore(res * 100);
            const newScoreColor = GenerateColorBasedOnRatings(res * 100);
            setScoreColor(newScoreColor);
        }
        props.onUpdateRating(res);
    }

    let scoreCircleDisplay;
    if (props.type === "circle"){
        if (props.userScore === true && props.userRatings === null || props.userRatings === "-1"){
            // console.log('dont show circle');
        } else {
            scoreCircleDisplay = (
                <ScoreCircleModule 
                    score={score} 
                    color={scoreColor}
                    size={props.circleSize}
                />
            )
        }
    }

    let selectDisplay;
    if (props.select === true){
        selectDisplay = (
            <ProductRatingsSelect 
                userRatings={props.userRatings}
                product={product}
                user={props.user}
                commentSelect={true}
                onUpdateRating={onUpdateRating}
                noModal={props.noModal}
            />
        )    
    }

    if (props.type === "circle" && props.select === true){
        return (
            <div id="widget-rating" className="prod-widget-box right" style={{border:"0 !important;"}}>
                {selectDisplay}
                {scoreCircleDisplay}
            </div>
        )
    } else if (props.select === true ){
        return (
            <React.Fragment>
                {selectDisplay}
            </React.Fragment>
        )
    }
}

/*
    USAGE:

    <ScoreModule 
        userRatings={userRatings}
        product={props.product}
        user={props.user}   
        select={true} // show select
        type={"circle"} // circle / bar
        circleSize={42} // default 54
        userScore={true} // if it shows general PRODUCT score ( false ) or is supposed to reflect the user score ( true )
        onUpdateScore={onSetUserRatings} // the function to run on props.onUpdateScore
        noModal={true} // show add ratings form modal on select click or not
    />

*/

export default ScoreModule;