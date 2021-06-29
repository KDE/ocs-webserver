import DummyHomepageView from '../../modules/homepage-view/dummy-homepage-view';

function DummyHomepageLayout(){
    return (
        <React.Fragment>
            <div className="GridFlex-cell content" id="main-content">
            <DummyHomepageView/>
            </div>
            <div className="GridFlex-cell sidebar-right" style={{padding:"0", border:"0"}}>
                <div className="project-share-new col-lg-12 col-md-12 col-sm-12 col-xs-12 dummy-fill dummy-fill-to-white" style={{height:"800px"}} id="right-sidebar-container">
                </div>
            </div>
        </React.Fragment>
    )
}

export default DummyHomepageLayout;