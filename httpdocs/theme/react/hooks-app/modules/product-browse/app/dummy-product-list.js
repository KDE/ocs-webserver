import '../style/dummy-product-list.css';
import { isMobile } from 'react-device-detect';

function DummyProductList(props){

    let filtersDisplay;
    if (props.showFilters === true){
        filtersDisplay = (
            <div id="product-browse-top-menu">
                <div className="pling-nav-tabs">
                    <ul className="nav nav-tabs pling-nav-tabs" id="sort">
                        <li className="active">
                            <a>{"Latest"}</a>
                        </li>
                        <li>
                            <a>{"Rating"}</a>
                        </li>
                        <li>
                            <a>{"Plinged"}</a>
                        </li>
                    </ul>
                </div>
            </div>
        )
    }


    return ( 
        <React.Fragment>
            {filtersDisplay}
            <div id="product-browse-item-list" className="dummy-item-list">
                <div id="product-browse-list-container">
                    <DummyProductListItem index={1}/>
                    <DummyProductListItem index={2}/>
                    <DummyProductListItem index={3}/>
                    <DummyProductListItem index={4}/>
                    <DummyProductListItem index={5}/>
                    <DummyProductListItem index={6}/>
                    <DummyProductListItem index={7}/>
                    <DummyProductListItem index={8}/>
                    <DummyProductListItem index={9}/>
                    <DummyProductListItem index={10}/>
                </div>
            </div>
        </React.Fragment>
    )
}

function DummyProductListItem(props){



    let dummyProductTextDisplay;
    let dummyScoreDisplay;
    let circleStyle = {width:"52px",height:"52px",margin:"28px auto"}

    if (isMobile){
        dummyScoreDisplay = '';
        circleStyle = {width:"32px",height:"32px", margin:"0px auto 10px"}
    } else {
        dummyScoreDisplay = <div className="dummy-fill dummy-fill-to-white text"></div>
        dummyProductTextDisplay = (
            <div>
                <div className="dummy-fill dummy-fill-to-white text"></div>
                <div className="dummy-fill dummy-fill-to-white text"></div>
                <div className="dummy-fill dummy-fill-to-white text"></div>
                <div className="dummy-fill dummy-fill-to-white half-text"></div>
            </div>
        )
    }

    return (
        <React.Fragment>
            <div className={"dummy-product-wrapper " + (props.index === 1 ? "first" : "")}>
                <div className="product-browse-item">
                    <div className="dummy-product-browse-item explore-product col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div className="col-lg-1 col-md-1 col-sm-1 col-xs-1">
                            {props.index + '.'}
                        </div>
                        <div className="product-browse-item-wrapper col-lg-2 col-md-2 col-sm-2 col-xs-2 explore-product-imgcolumn">
                            <div className="dummy-fill dummy-fill-to-white img-placeholder"></div>
                        </div>
                        <div className="item-info-main explore-product-details col-lg-7 col-md-7 col-sm-7 col-xs-7">
                            <div className="dummy-fill dummy-fill-to-white title"></div>
                            <div>
                                <div className="dummy-fill dummy-fill-to-white category"></div>
                                <div className="dummy-fill dummy-fill-to-white category"></div>
                            </div>
                            {dummyProductTextDisplay}
                        </div>
                        <div className="item-info-right explore-product-plings col-lg-2 col-md-2 col-sm-2 col-xs-2 text-center">
                            {dummyScoreDisplay}
                            <div className="dummy-circle dummy-fill dummy-fill-to-white dummy-circle" style={circleStyle}></div>
                            <div className="dummy-fill dummy-fill-to-white half-text date-display"></div>
                        </div>
                    </div>
                </div>
            </div>
            <hr className="dummy-fill dummy-fill-to-white dummy-fill dummy-fill-to-white-to-white"></hr>
        </React.Fragment>
    )
}

export default DummyProductList;