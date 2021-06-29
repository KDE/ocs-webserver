
import './style/dummy-user-profile.css';

function DummyUserProfile(){
    return (
        <main id="dummy-user-profile" className="about-me-page">
            <section className="head-wrap">
                <section className="wrapper">
                    <section className="header">
                        <div style={{padding:"0 5px"}} className="col-lg-4 col-lg-offset-4 col-md-4 col-md-offset-4 col-sm-4 col-sm-offset-4 col-xs-12 summary">
                            <article style={{margin:"65px 0",width:"100%"}} className="well">
                                <div className="about-title">
                                    <div style={{overflow:"hidden"}} className="relative inline image-container  dummy-fill dummy-fill-to-white">
                                        <i style={{fontSize:"109px",color:"white"}} className="fa fa-user" aria-hidden="true"></i>
                                    </div>
                                    <h1><span className="dummy-fill dummy-fill-to-white text"></span></h1>
                                    <div className="dummy-fill dummy-fill-to-white text"></div>
                                    <div className="dummy-fill dummy-fill-to-white text"></div>
                                </div>
                                <div className="about-footer"> 
                                    <div className="col-container">
                                        <div className="details info">
                                            <div className="dummy-fill text dummy-fill-to-white"></div>
                                            <div className="dummy-fill text dummy-fill-to-white"></div>
                                            <div className="dummy-fill text dummy-fill-to-white"></div>
                                        </div>                                        
                                        <h1><span className="dummy-fill text  dummy-fill-to-white"></span></h1>
                                        <div className="details stat">
                                            <div className="dummy-fill text dummy-fill-to-white"></div>
                                            <div className="dummy-fill text dummy-fill-to-white"></div>
                                            <div className="dummy-fill text dummy-fill-to-white"></div>
                                            <div className="dummy-fill text dummy-fill-to-white"></div>
                                            <div className="dummy-fill text dummy-fill-to-white"></div>
                                            <div className="dummy-fill text dummy-fill-to-white"></div>
                                            <div className="dummy-fill text dummy-fill-to-white"></div>
                                            <div className="dummy-fill text dummy-fill-to-white"></div>
                                            <div className="dummy-fill text dummy-fill-to-white"></div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </section>
                </section>
            </section>
        </main>
    )
}

export default DummyUserProfile;