import '../../style/dummy-right-sidebar.css';

function DummyRightSideBar(props){
    let firstDummyElementDisplay = <div className="module-container dummy-module"><div className="dummy-fill"></div></div>
    if (props.view === "browse"){
        firstDummyElementDisplay = (
            <div className="downloadDiv">
                <a>
                    <img src="/images/system/ocsstore-download-button.png" />    
                </a>
            </div>
        )
    }

    return (
        <aside id="explore-sidebar" className={props.view + " dummy-fill dummy-fill-to-white"} style={{minHeight:"800px"}}>
        </aside>
    )
}
export default DummyRightSideBar;