import React, { Component } from 'react';

function dechex (number) {
  if (number < 0) {
    number = 0xFFFFFFFF + number + 1
  }
  return parseInt(number, 10)
    .toString(16)
}

function getScoreColor($score2)
{    
    let $blue2 = 200;
    let $red2 = 200;
    let $green2 = 200;
    let $default2 = 200;
    if ($score2 >= 5) {
        $red2 = dechex($default2 - (($score2 * 10 - 50) * 4));
        $green2 = dechex($default2);
        $blue2 = dechex($default2 - (($score2 * 10 - 50) * 4));
    } else if ($score2 < 5) {
        $red2 = dechex($default2);
        $green2 = dechex($default2 - ((50 - $score2 * 10) * 4));
        $blue2 = dechex($default2 - ((50 - $score2 * 10) * 4));
    }
    if ($green2.length == 1)
        $green2 = '0' + $green2;
    if ($red2.length == 1)
        $red2 = '0' + $red2;
    if ($blue2.length == 1)
        $blue2 = '0' . $blue2;
    return '#' +$red2 + $green2 + $blue2;
}

class Score extends React.Component {

  constructor(props){
    super(props);
    this.width = parseInt(props.r)+2;
    this.height = parseInt(props.r)+2;
    this.viewBox = "0 0 "+parseInt(props.r)*2+" "+parseInt(props.r)*2;
    this.strokeDasharray = 2*3.14159*parseInt(props.r);
    this.strokeDashoffset = this.strokeDasharray*(1-props.score/1000);
    this.scoreArray = (props.score/100).toFixed(1).split(".");
    this.scoreColor = getScoreColor(props.score/100);   
  }
  

  render(){      
  
    return (      
      <div className="bmIcXH" >
        <svg xmlns="http://www.w3.org/2000/svg" width={this.width} height={this.height} viewBox={this.viewBox} className="donMiK">
            <circle fill="#fff" cx="50%" cy="50%" r={this.props.r}></circle>
            <circle stroke="#eceded" fill="transparent" cx="50%" cy="50%" r={this.props.r} strokeWidth="14"></circle>
            <circle fill="transparent" cx="50%" cy="50%" r="30" 
                    strokeDasharray={this.strokeDasharray} 
                    strokeDashoffset={this.strokeDashoffset}
                    strokeWidth="14" 
                    stroke = {this.scoreColor}
                    className="cnGKWN"></circle>
        </svg>
        <div className="kaLHBk" style={{"color":this.scoreColor, "fontSize":(this.props.fontSize?this.props.fontSize:"28")+'px'}}>
            <div className="kkSWyw" >
                <span>{this.scoreArray[0]}</span>
                <span className="jdsHLZ">.{this.scoreArray[1]}</span>
            </div>
        </div>
    </div>
    )
  }
}

export default Score;
