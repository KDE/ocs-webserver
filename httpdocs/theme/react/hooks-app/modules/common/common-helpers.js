export const FormatDate = (dateString) => {
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const date = dateString.split(' ')[0];
    const year = date.split('-')[0];
    const month = date.split('-')[1];
    const day = date.split('-')[2];
    const monthNameIndex = parseInt(month) - 1;
    const monthName = monthNames[monthNameIndex];
    return monthName + ' ' + day + ' ' + year;
}

export const GenerateToolTipTemplate = (toolTip) => {
    let locationIconDisplay;
    if (toolTip.countrycity !== null){
        locationIconDisplay = <span className="glyphicon glyphicon-map-marker"></span>
    }


    const heartDisplay = (
        <span className="pui-heart active" style={{left:"0",bottom:"0",position:"relative"}}>
            <svg svg="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2">
                <g transform="matrix(1,0,0,1,-238,-365.493)">
                    <path d="M239.99,373.475L246,379.485L252.01,373.475C253.376,372.109 253.376,369.891 252.01,368.525C250.644,367.159 248.427,367.159 247.061,368.525L246,369.586L244.939,368.525C243.573,367.159 241.356,367.159 239.99,368.525C238.624,369.891 238.624,372.109 239.99,373.475Z"></path>
                </g>
            </svg>
        </span>
    )

    return (
        <div className="mytooltip">
            <div className="row">
                <div className="header">
                    <b style={{marginRight:"5px"}}>{toolTip.username}</b> {locationIconDisplay}
                    {toolTip.countrycity}
                </div>
            </div>
            <div className="statistics">
                <div><b className="title">{toolTip.cntProjects}</b> products </div>
                <div><b className="title">{toolTip.totalComments}</b> comments </div>
                <div>Likes <b className="title">{toolTip.cntLikesGave}</b> products </div>
                <div>Got <b className="title">{toolTip.cntLikesGot}</b> Likes {heartDisplay}</div>
                <div>Last time active : {toolTip.lastactive_at}</div>
                <div>Member Since : {toolTip.created_at} </div>
            </div>
        </div>
    )
}

export const GenerateImageUrl = (url,width,height,sizeSuffix) => {
    
    let imageFileType;
    if (url && url !== null && url.split('.').length > 0) imageFileType = url.split('.')[url.split('.').length - 1];

    let fullImageUrl = url;
    if (fullImageUrl && fullImageUrl.indexOf('https://') === -1){
        const urlSuffix = (window.location.hostname.endsWith('com') || window.location.hostname.endsWith('org')) ? 'com' : 'cc';
        let cache = ""
        if (imageFileType !== "gif" && width && height){
            cache = "/cache/" + width + "x" + height + (sizeSuffix ? sizeSuffix : '');
        }
        fullImageUrl = "https://cdn.pling." + urlSuffix + cache + "/img/" + url;
    }
    return fullImageUrl;
}

export const GenerateColorBasedOnRatings = (ratings) => {
    let color;
    if (ratings <= 1) color = "#c82828";
    else if (ratings > 1 && ratings <= 2)  color = "#c85050";
    else if (ratings > 2 && ratings <= 3)  color = "#c87878";
    else if (ratings > 3 && ratings <= 4)  color = "#c8a0a0";
    else if (ratings > 4 && ratings <= 5)  color = "#c8c8c8";
    else if (ratings > 5 && ratings <= 6)  color = "#a0c8a0";
    else if (ratings > 6 && ratings <= 7)  color = "#78c878";
    else if (ratings > 7 && ratings <= 8)  color = "#50c850";
    else if (ratings > 8 && ratings <= 9)  color = "#28c828";
    else if (ratings > 9 && ratings <= 10) color = "#00c800";
    return color;
}

export const GenerateWordBasedOnRatingScore = (score) => {
    const pScore = parseInt(score);
    switch(pScore){
        case 0  :{ return "Add Rating" }
        case 1  :{ return "ugh" }
        case 2  :{ return "really bad" }
        case 3  :{ return "bad" }
        case 4  :{ return "soso" }
        case 5  :{ return "average" }
        case 6  :{ return "okay" }
        case 7  :{ return "good" }
        case 8  :{ return "great" }
        case 9  :{ return "excellent" }
        case 10 :{ return "the best" }
        default :{ return "Add Rating" }
    }
}

export const ConvertObjectToArray = (object) => {
    let newArray = [];
    for (var i in object){
        newArray.push(object[i]);
    }
    return newArray;
}

export const Compare = (a,b,criterion) => {
    if (a[ciretrion] < b[criterion]) {
        return -1;
    }
    if (a[ciretrion] > b[criterion]) {
        return 1;
    }
    return 0;
}