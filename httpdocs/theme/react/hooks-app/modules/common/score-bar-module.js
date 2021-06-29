import { Line } from 'rc-progress';
import { GenerateColorBasedOnRatings } from './common-helpers';

function ScoreBarModule(props){

    const val = (props.score / 10);
    const scoreColor = GenerateColorBasedOnRatings(props.score / 100)
    return (
        <div className="score-bar-module">
            <div className="score-bar-container">
                <span style={{color:props.textColor}}>
                    Score: {val.toFixed(1)}%
                </span>
                <Line 
                    prefixCls="progress-line-container"
                    percent={val} 
                    strokeLinecap="square"
                    trailWidth="8"
                    trailColor={props.trailColor ? props.trailColor : "#eeeeee"}
                    strokeWidth="6" 
                    strokeColor={scoreColor} />
            </div>
        </div>
    )
}

export default ScoreBarModule;