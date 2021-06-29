import DummyProductList from '../../modules/product-browse/app/dummy-product-list';

function DummyProductBrowseLayout(){
    return (
        <React.Fragment>
            <div className="GridFlex-cell content">
                <div id="product-browse-container">
                    <DummyProductList showFilters={true}/>
                </div>
            </div>
            <div className="GridFlex-cell sidebar-right" style={{padding:"0",marginTop:"-20px"}}>
                <div className="project-share-new col-lg-12 col-md-12 col-sm-12 col-xs-12 dummy-fill dummy-fill-to-white" style={{height:"800px"}} id="right-sidebar-container">
                </div>
            </div>
        </React.Fragment>
    )   
}

export default DummyProductBrowseLayout;