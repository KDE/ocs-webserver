import { useContext } from 'react';
import { Context } from '../context/context-provider';

import parser from 'bbcode-to-react';

import ScoreCircleModule from '../../../common/score-circle-module';
import UserToolTipModule from '../../../common/user-tooltip-module';
import { FormatDate } from '../../../common/common-helpers';
import { isMobile } from 'react-device-detect';


function StandardBrowseListItem(props){

    const p = props.product;
    
    const { productBrowseState } = useContext(Context);

    let oldScoreDisplay;
    if (!isMobile && productBrowseState.authMember && productBrowseState.authMember.isAdmin === true){
        oldScoreDisplay = <p>{"Score_old:"+p.laplace_score_old+"%/Score_test:"+p.laplace_score_test}</p>
    }

    let plingsDisplay;
    if (parseInt(p.count_plings) > 0) plingsDisplay = <p className="plings-display" style={{marginTop:"10px"}}>Plings: {p.count_plings}</p>
    
    let packageNamesDisplay;
    if (p.package_names){
        packageNamesDisplay = p.package_names.split(',').map((pn,index) => (
            <span key={index} className="packagetypeos"> {pn} </span>
        ))
    }

    let descriptionDisplay;
    if (props.showDescription !== 0 && !isMobile ){
        let desc = p.description;
        if (p.description.length > 291) desc = p.description.substr(0,291) + '...';
        descriptionDisplay = (
            <div className="description">
                <div>{parser.toReact(desc)}</div>
            </div>
        )
    }

    let commentsDisplay;
    if (p.count_comments !== "0"){
        commentsDisplay = (
            <div className="productInfo">
                <span className="cntComments">{p.count_comments + " Comment" + (p.count_comments !== "1" ? "s" : "")} </span>
            </div>
        )
    }

    return (
        <React.Fragment>
            <div className="item-info-main explore-product-details col-lg-7 col-md-7 col-sm-7 col-xs-7">
                <h3>
                    <a href={"/p/"+p.project_id} onClick={props.onBrowseItemClick}>
                        {p.title}
                        <span className="version">{p.version}</span>
                    </a>
                </h3>
                <div className="title">
                    <b>{p.cat_title}</b>
                    <b className="username small">
                        <UserToolTipModule 
                            showBy={true}
                            memberId={p.member_id}
                            username={p.username}
                            toolTipId={"product-browse-list-item-user-tooltip"+p.project_id}
                            onUserNameClick={props.onUserNameClick}
                        />
                    </b>
                </div>
                {descriptionDisplay}
                <div className="packagetypes">
                    {packageNamesDisplay}
                </div>
                {commentsDisplay}
            </div>
            <div className="item-info-right explore-product-plings col-lg-2 col-md-2 col-sm-2 col-xs-2 text-center">
                {oldScoreDisplay}
                <ScoreCircleModule 
                    score={p.laplace_score}
                    size={isMobile ? 32 : 52}
                />
                {plingsDisplay}
                <p className="date-display">{FormatDate(p.changed_at)}</p>
            </div>
        </React.Fragment>
    )
}

export default StandardBrowseListItem;