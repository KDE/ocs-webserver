
import { CircularProgressbarWithChildren, buildStyles } from 'react-circular-progressbar';
import { GenerateColorBasedOnRatings } from './common-helpers';
// import './style/score-circle-module.css';

function ScoreCircleModule(props){

    const score = props.score;
    const val = (score / 100).toFixed(1);

    let scoreColor = GenerateColorBasedOnRatings(val);
    // if (props.color) scoreColor = props.color;
    // else scoreColor = GenerateColorBasedOnRatings(val);
    
    let size = props.size ? props.size : 48;
    let sizePercentageDifference = size !== 48 ? size / 48 : 1;

    let hideDecimalCssClass = ""
    if (val.toString().split('.')[0] === "10") hideDecimalCssClass = "hide-decimal"
    
    return (
            <div className={"circle-container bmIcXH " + hideDecimalCssClass}
                style={{width:size + "px",fontSize: (26 * sizePercentageDifference) + "px", fontWeight:"600"}}>
                <CircularProgressbarWithChildren 
                    value={val} 
                    minValue={0}
                    maxValue={10} 
                    strokeWidth={props.strokeWidth ? props.strokeWidth : 7}
                    styles={
                        buildStyles({
                        // Whether to use rounded or flat corners on the ends - can use 'butt' or 'round'
                        strokeLinecap: 'butt',
                        // Text size
                        textSize: '24px',
                        // How long animation takes to go from one percentage to another, in seconds
                        // pathTransitionDuration: 0.5,
                        // Can specify path transition in more detail, or remove it entirely
                        // pathTransition: 'none',
                        // Colors
                        pathColor:scoreColor,
                        trailColor: (props.trailColor ? props.trailColor : '#eceded'),
                        // backgroundColor: '#3e98c7',
                        })
                    }>
                    <div className="circle-inner-content-container" style={{color:scoreColor}}>
                        <span>{val.toString().split('.')[0]}</span>
                        <span className="small-number" style={{ fontSize: "58%"}}>.{val.toString().split('.')[1]}</span>
                    </div>
                </CircularProgressbarWithChildren>
            </div>
    )
}

export default ScoreCircleModule;