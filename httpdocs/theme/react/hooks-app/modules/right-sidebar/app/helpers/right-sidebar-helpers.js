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

export const GenerateProductDetailsKeyArray = () => {
    const productDetailsKeyArray = [
        {
            title:"license",
            key:"project_license_title"
        },{
            title:"version",
            key:"project_version"
        },{
            title:"updated",
            key:"project_changed_at",
            isDate:true
        },{
            title:"major updated",
            key:"project_major_updated_at",
            isAdmin:true,
            isDate:true
        },{
            title:"added",
            key:"project_created_at",
            isDate:true
        },{
            title:"downloads 24h old",
            key:"countDownloadsTodayUk",
            isAdmin:true,
            isData:true
        },{
            title:"downloads 24h",
            key:"countDownloadsToday",
            isData:true
        },{
            title:"mediaviews 24h",
            key:"countMediaViewsToday",
            isData:true
        },{
            title:"mediaviews total",
            key:"countMediaViewsAlltime",
            isAdmin:true,
            isData:true
        },{
            title:"pageviews 24h",
            key:"countPageviews24h",
            isData:true
        },{
            title:"pageviews 24h",
            key:"countPageviewsTotal",
            isData:true,
            isAdmin:true
        },{
            title:"spam reports",
            key:"countSpamReports",
            isAdmin:true,
            isData:true
        },{
            title:"misuse reports",
            key:"countMisuseReports",
            isAdmin:true,
            isData:true
        }
    ]
    return productDetailsKeyArray;
}

export const GenerateArrayFromObject = (filesJson) => {
    const files = [];
    for (var i in filesJson){
        files.push(filesJson[i]);
    }
    return files;
}

export const GenerateToolTipTemplate = (toolTip) => {
    return (
        <div className="mytooltip">
            <div className="row">
                <div className="header">
                    {toolTip.username}
                    <span className="glyphicon glyphicon-map-marker"></span>
                    {toolTip.countrycity}
                </div>
            </div>
            <div className="statistics">
                <div className="row"><span className="title">{toolTip.cntProjects}</span> products </div>
                <div className="row"><span className="title">{toolTip.totalComments}</span> comments </div>
                <div className="row">Likes <span className="title">{toolTip.cntLikesGave}</span> products </div>
                <div className="row">Got <span className="title">{toolTip.cntLikesGot}</span> Likes <i className="fa fa-heart myfav" aria-hidden="true"></i>  </div>
                <div className="row">Last time active : {toolTip.lastactive_at}</div>
                <div className="row">Member Since : {toolTip.created_at} </div>
            </div>
        </div>
    )
}