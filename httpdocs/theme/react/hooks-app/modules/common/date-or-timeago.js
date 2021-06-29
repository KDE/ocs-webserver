import React from 'react';
import TimeAgo from 'react-timeago';
import { FormatDate } from './common-helpers';

function DateOrTimeAgoModule(props){

    let dateDiplay;
    if (props.layout === true){
        const date = FormatDate(props.date);
        dateDiplay = (
            <React.Fragment>
                <span>{date.split(' ')[0] + ' ' + date.split(' ')[1]}</span>
                {date.split(' ')[2]}
            </React.Fragment>
        )
    } else {
        
        const now = new Date();
        const dateTime = Date.parse(props.date);
        const timeDifference = now - dateTime;
        const oneday = 60 * 60 * 24 * 1000;

        if (timeDifference < oneday * (props.numDays ? props.numDays : 1)) dateDiplay = <TimeAgo date={props.date}></TimeAgo>
        else dateDiplay = FormatDate(props.date);
    }

    return (
        <React.Fragment>
            {dateDiplay}
        </React.Fragment>
    )
}

export default DateOrTimeAgoModule;