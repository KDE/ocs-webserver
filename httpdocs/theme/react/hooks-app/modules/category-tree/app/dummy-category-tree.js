function DummyCategoryTree(){
    return (
        <div id="category-tree">
            <input type="text" value="" />
            <div id="category-tree-header">
                <a className="disabled" id="back-button"><span className="glyphicon glyphicon-chevron-left"></span></a>
                <a className="disabled" id="forward-button"><span className="glyphicon glyphicon-chevron-right"></span></a>
                <a className="dummy-fill dummy-fill-to-white" style={{width:"70px",height:"15px", display:"block"}}></a>
            </div>
            <div id="category-panles-container" className="visible">
                <div id="category-panels-slider" style={{width:"100%"}}>
                    <div className="category-panel"  style={{width:"100%"}}>
                        <ul>
                            <li>
                                <a>
                                    <span className="cat-title">
                                        <span className="dummy-fill dummy-fill-to-white" style={{width:"70px",height:"15px"}}></span>
                                    </span>
                                    <span className="dummy-fill dummy-fill-to-white cat-product-counter" style={{width:"30px",height:"15px"}}></span>
                                </a>
                            </li>
                            <li>
                                <a>
                                    <span className="cat-title">
                                        <span className="dummy-fill dummy-fill-to-white" style={{width:"55px",height:"15px"}}></span>
                                    </span>
                                    <span  className="dummy-fill dummy-fill-to-white cat-product-counter" style={{width:"25px",height:"15px"}}></span>
                                </a>
                            </li>
                            <li>
                                <a>
                                    <span className="cat-title">
                                        <span className="dummy-fill dummy-fill-to-white" style={{width:"80px",height:"15px"}}></span>
                                    </span>
                                    <span  className="dummy-fill dummy-fill-to-white cat-product-counter" style={{width:"30px",height:"15px"}}></span>
                                </a>
                            </li>
                            <li>
                                <a>
                                    <span className="cat-title">
                                        <span className="dummy-fill dummy-fill-to-white" style={{width:"75px",height:"15px"}}></span>
                                    </span>
                                    <span  className="dummy-fill dummy-fill-to-white cat-product-counter" style={{width:"30px",height:"15px"}}></span>
                                </a>
                            </li>
                            <li>
                                <a>
                                    <span className="cat-title">
                                        <span className="dummy-fill dummy-fill-to-white" style={{width:"35px",height:"15px"}}></span>
                                    </span>
                                    <span  className="dummy-fill dummy-fill-to-white cat-product-counter" style={{width:"10px",height:"15px"}}></span>
                                </a>
                            </li>
                            <li>
                                <a>
                                    <span className="cat-title">
                                        <span className="dummy-fill dummy-fill-to-white" style={{width:"65px",height:"15px"}}></span>
                                    </span>
                                    <span  className="dummy-fill dummy-fill-to-white cat-product-counter" style={{width:"20px",height:"15px"}}></span>
                                </a>
                            </li>
                            <li>
                                <a>
                                    <span className="cat-title">
                                        <span className="dummy-fill dummy-fill-to-white" style={{width:"55px",height:"15px"}}></span>
                                    </span>
                                    <span  className="dummy-fill dummy-fill-to-white cat-product-counter" style={{width:"35px",height:"15px"}}></span>
                                </a>
                            </li>
                            <li>
                                <a>
                                    <span className="cat-title">
                                        <span className="dummy-fill dummy-fill-to-white" style={{width:"75px",height:"15px"}}></span>
                                    </span>
                                    <span  className="dummy-fill dummy-fill-to-white cat-product-counter" style={{width:"25px",height:"15px"}}></span>
                                </a>
                            </li>
                            <li>
                                <a>
                                    <span className="cat-title">
                                        <span className="dummy-fill dummy-fill-to-white" style={{width:"45px",height:"15px"}}></span>
                                    </span>
                                    <span  className="dummy-fill dummy-fill-to-white cat-product-counter" style={{width:"35px",height:"15px"}}></span>
                                </a>
                            </li>
                            <li>
                                <a>
                                    <span className="cat-title">
                                        <span className="dummy-fill dummy-fill-to-white" style={{width:"45px",height:"15px"}}></span>
                                    </span>
                                    <span  className="dummy-fill dummy-fill-to-white cat-product-counter" style={{width:"35px",height:"15px"}}></span>
                                </a>
                            </li>
                            <li>
                                <a>
                                    <span className="cat-title">
                                        <span className="dummy-fill dummy-fill-to-white" style={{width:"75px",height:"15px"}}></span>
                                    </span>
                                    <span  className="dummy-fill dummy-fill-to-white cat-product-counter" style={{width:"25px",height:"15px"}}></span>
                                </a>
                            </li>
                            <li>
                                <a>
                                    <span className="cat-title">
                                        <span className="dummy-fill dummy-fill-to-white" style={{width:"25px",height:"15px"}}></span>
                                    </span>
                                    <span  className="dummy-fill dummy-fill-to-white cat-product-counter" style={{width:"25px",height:"15px"}}></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default DummyCategoryTree;