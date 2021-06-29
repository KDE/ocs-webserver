import React from 'react'; 

function Footer(){
    return (
            <div className="footer container-wide">
                    <div className="footer-col container-normal link-primary-invert">
                        <div className="footer-store-info">
                            <h3>
                                
                            {
                                (headerData && headerData.ocsStoreTemplate.footer_heading ? headerData.ocsStoreTemplate.footer_heading + " - " : "")
                                +
                                (headerData && headerData.ocsStoreTemplate.domain ? headerData.ocsStoreTemplate.domain : "pling.com")
                            }

                            </h3>
                            <p className="footer-text"><span>© 2021 {(headerData && headerData.ocsStoreTemplate.domain ? headerData.ocsStoreTemplate.domain : "pling.com") + " " + (headerData && headerData.ocsStoreTemplate.footer_heading ?  "- " + headerData.ocsStoreTemplate.footer_heading : "")}</span> 
                            <br/>All rights reserved. All trademarks are copyright by their respective owners. All contributors are responsible for their uploads.</p>
                        </div>
                        <div className="footer-grid text-small m0">
                            <ul className="clean-list">
                                <li className="text-normal font-bold uppercase">Services</li>
                                <li><a href="https://www.opendesktop.org">Opendesktop.org</a></li>
                                <li><a href="https://opencode.net">Opencode.net</a></li>
                                <li><a href="https://pling.com">Pling.com</a></li>
                            </ul>
                            <ul className="clean-list">
                                <li className="text-normal font-bold uppercase">Community</li>
                                <li><a href="https://www.pling.com/community">Community</a></li>
                                <li><a href="https://forum.opendesktop.org">Discussion</a></li>
                                <li><a href="https://www.pling.com/supporters">Community Funding</a></li>
                                <li><a href="https://www.pling.com/support">Become a Supporter</a></li>
                            </ul>
                            <ul className="clean-list">
                                <li className="text-normal font-bold uppercase">Documents</li>
                                <li><a href="https://www.pling.com/faq-pling">FAQ Pling</a></li>
                                <li><a href="https://www.opendesktop.org/faq-opencode">FAQ Opencode</a></li>
                                <li><a href="https://www.opendesktop.org/ocs-api">API</a></li>
                            </ul>
                            
                            <ul className="clean-list">
                                <li className="text-normal font-bold uppercase">About</li>
                                <li><a href="https://www.opendesktop.org/about">About</a></li>
                                <li><a href="/terms">Terms &amp; Conditions</a></li>
                                <li><a href="/privacy">Privacy Policy</a></li>
                                <li><a href="/contact">Contact</a></li>
                                <li><a href="/imprint">Imprint</a></li>
                            </ul>
                        </div>
                        <div className="footer-links link-primary-invert" style={{"display": "none"}}>
                            <p><a className="pui-pill" href="#">Terms</a></p>
                            <p><a className="pui-pill" href="#">Privacy</a></p>
                            <p><a className="pui-pill" href="#">Imprint</a></p>
                            <p><a className="pui-pill" href="#">Contact</a></p>
                            <p><a className="pui-pill facebook-pill" href="#">Facebook</a></p>
                            <p><a className="pui-pill twitter-pill" href="#">Twitter</a></p>
                        </div>
                    </div>
                    <div className="container-wide">
                        <div className="footer-grid-links">
                            <div><p><a className="pui-pill blog-pill" href={"https://blog.opendesktop.org"} target="_blank">Blog</a></p></div>
                            <div><p><a className="pui-pill pling-app-pill" href="https://www.pling.com/p/1175480/">Download the app</a></p></div>
                            <div><p><a href="https://chat.opendesktop.org/#/room/#opendesktop:chat.opendesktop.org" class="pui-pill element-app-pill">Join our chat</a></p></div>
                            <div><p><span className="pui-pill">Part of <a href="https://www.opendesktop.org">Opendesktop.org</a> Platform</span></p></div>
                        </div>
                    </div>
            </div>
    )
}

export default Footer;