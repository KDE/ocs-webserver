import DummyProductView from "../../modules/product-view/app/dummy-product-view"

function DummyProductViewLayout(){
    return (
        <React.Fragment>
            <div className="col-lg-8 col-md-8 col-sm-8 col-xs-12" id="product-main">
                <div id="product-view-container">
                    <DummyProductView/>
                </div>               
            </div>
            <div className="col-lg-2 col-md-2 col-sm-12 col-xs-12 flex-right dummy-fill dummy-fill-to-white" id="product-maker" style={{minHeight:"800px"}}></div>
        </React.Fragment>
    )
}

export default DummyProductViewLayout;