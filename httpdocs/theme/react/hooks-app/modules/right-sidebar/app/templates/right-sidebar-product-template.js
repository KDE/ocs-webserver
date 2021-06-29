
import React, { useEffect, useState }  from 'react';
import ProductDownloadInstallModule from '../modules/product-download-install-module';
import SupportBoxModule from '../modules/support-box-module';
import MoreProductsModule from '../modules/more-product-module';
import ProductMakerModule from '../modules/product-maker-module';
import ProjectPlingsModule from '../modules/project-plings-module';
import ProductDetailsModule from '../modules/product-details-module';
import SystemTagsModule from '../modules/system-tags-module';
import UserActionModule from '../modules/user-actions-module';

export default function RightSideBarProductTemplate(props){

    const dataRightSideBar = props.dataRightSideBar;
    const product = props.product;
    
    let identity = false,
        isAdmin = false;

    if (props.user){
        if (props.user.member_id) identity = true;
        if (props.user.isAdmin === true) isAdmin = true;
    }

    const [ storeAbout, setStoreAbout ] = useState();
    const [ catAbout, setCatAbout ] = useState();

    useEffect(() => {
        if (dataRightSideBar.catabout){
            fetch('/partials/' + dataRightSideBar.catabout.split('/partials/')[1]).then(res => res.text()).then(res => {
                setCatAbout(res);
            })
        }
        if (dataRightSideBar.storeabout){
            fetch('/partials/' + dataRightSideBar.storeabout.split('/partials/')[1]).then(res => res.text()).then(res => {
                setStoreAbout(res);
            })
        }
    },[])

    let viewBasedModulesBlockDisplay;
    let downloadInstallModuleDisplay;

    // more products of user
    let moreProductsOfUserModuleDisplay;
    if (dataRightSideBar.moreProductsOfUser.length > 0){
        moreProductsOfUserModuleDisplay = (
                <MoreProductsModule
                    type={'user'}
                    items={dataRightSideBar.moreProductsOfUser}
                    product={product}
                    onChangeUrl={props.onChangeUrl}
                    isCollectionView={props.isCollectionView}
                />
        )
    }
    
    // more products of category
    let moreProductsOfCategoryModuleDisplay;
    if (dataRightSideBar.moreProductsOfOtherUsers.length > 0){
        moreProductsOfCategoryModuleDisplay = (
            <MoreProductsModule
                type={'category'}
                items={dataRightSideBar.moreProductsOfOtherUsers}
                product={product}
                onChangeUrl={props.onChangeUrl}
                isCollectionView={props.isCollectionView}
            />
        )
    }
    
    if (!props.isCollectionView){

        downloadInstallModuleDisplay = (
            <ProductDownloadInstallModule
                product={product}
                files={props.files}
            />
        )

        // project afiliates 
        const pac = dataRightSideBar.cntProjectaffiliates;
        const projectAffiliatesModuleDisplay = (            
            <div className="projectdtailHeart">
                <div id={"container-affiliate"+product.project_id} className="container-pling" style={{float:"right"}}>
                    <div className="plingbartext">
                        <span className="plingnum"> {pac} Affiliate{pac == 1 ? '' : 's'} </span>
                    </div>
                </div>
            </div>
        )

        // is part of collections
        let moreProductsCollectionModuleDisplay;
        if (dataRightSideBar.moreCollectionProducts.length > 0){
            moreProductsCollectionModuleDisplay = (
                <MoreProductsModule
                    type={'collection'}
                    items={dataRightSideBar.moreCollectionProducts}
                    product={product}
                    onChangeUrl={props.onChangeUrl}
                />
            )
        }

        viewBasedModulesBlockDisplay = (
            <React.Fragment>
                <div className="prod-widget-box prod-widget-col-2">
                    <ProjectPlingsModule 
                        user={props.user}
                        isAdmin={isAdmin}
                        product={product}
                        data={dataRightSideBar}
                        identity={identity}
                    />
                    {projectAffiliatesModuleDisplay}
                </div>
                <SupportBoxModule
                    user={props.user}
                    isAdmin={isAdmin}
                    projectId={product.project_id}
                    projectTitle={product.project_title}
                    data={dataRightSideBar.dataSupportbox}
                    onChangeUrl={props.onChangeUrl}
                />
                <ProductDetailsModule 
                    isAdmin={isAdmin}
                    product={product}
                    data={dataRightSideBar}
                    user={props.user}
                />
                <UserActionModule 
                    isAdmin={isAdmin}
                    product={product}
                    data={dataRightSideBar}
                    user={props.user}
                    onChangeUrl={props.onChangeUrl}
                />
                <hr className="hr-dark"/>
                <SystemTagsModule tags={dataRightSideBar.systemTags} />
                <hr className="hr-dark"/>
                {moreProductsCollectionModuleDisplay}
                {moreProductsOfUserModuleDisplay}
                {moreProductsOfCategoryModuleDisplay}
            </React.Fragment>
        )
    } else {
        viewBasedModulesBlockDisplay = (
            <React.Fragment>
                <ProductDetailsModule 
                    isAdmin={isAdmin}
                    product={product}
                    data={dataRightSideBar}
                    user={props.user}
                />
                <hr className="hr-dark"/>
                <SystemTagsModule tags={dataRightSideBar.systemTags} />
                <hr className="hr-dark"/>
                {moreProductsOfUserModuleDisplay}
                {moreProductsOfCategoryModuleDisplay}
            </React.Fragment>
        )
    }
    
    // product maker
    let productMakerModuleDisplay;
    if (product.claimable !== true){
        productMakerModuleDisplay = (
            <ProductMakerModule
                product={product}
                user={props.user}
                maker={props.maker}
                isCollectionView={props.isCollectionView}
                onChangeUrl={props.onChangeUrl}
            />
        )
    }

    let catAboutdisplay;
    if (catAbout){
        catAboutdisplay = (
            <React.Fragment>
                <hr className="hr-dark"/>
                <div className="prod-widget-box" dangerouslySetInnerHTML={{__html:catAbout}}></div>
            </React.Fragment>
        )
    }

    let storeAboutDisplay;
    if (storeAbout){
        storeAboutDisplay = (
            <React.Fragment>
                <hr className="hr-dark"/>
                <div className="prod-widget-box" dangerouslySetInnerHTML={{__html:storeAbout}}></div>
            </React.Fragment>
        )
    }

    return (
        <React.Fragment>
            {downloadInstallModuleDisplay}
            {productMakerModuleDisplay}
            {viewBasedModulesBlockDisplay}
            {catAboutdisplay}
            {storeAboutDisplay}
        </React.Fragment>
    )

}
