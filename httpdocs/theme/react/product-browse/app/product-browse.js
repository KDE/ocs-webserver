import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import {isMobile} from 'react-device-detect';
import {ProductBrowseItem} from './product-browse-item';
import {getNumberOfItemsPerRow, getImageHeight, chunkArray, getItemWidth, ConvertObjectToArray} from './product-browse-helpers';

function ProductBrowse(){
    return (
        <div id="product-browse">
            <ProductTagGroupFilterContainer/>
            <ProductBrowseFilterContainer/>
            <ProductBrowseItemList />
        </div>
    )
}

function ProductBrowseFilterContainer(){

    let filtersBaseUrl = json_serverUrl;
    filtersBaseUrl += json_store_name === "ALL" ? "/" : "/s/" + json_store_name + "/";
    filtersBaseUrl += "browse/";
    if (typeof filters.category === 'number') filtersBaseUrl += "cat/" + filters.category + "/";

    return (
        <div id="product-browse-top-menu">
            <div className="pling-nav-tabs">
                <ul className="nav nav-tabs pling-nav-tabs" id="sort">
                    <li className={filters.order === "latest" ? "active" : ""}>
                        <a href={filtersBaseUrl + "ord/latest/" + window.location.search}>Latest</a>
                    </li>
                    <li className={filters.order === "rating" ? "active" : ""}>
                        <a href={filtersBaseUrl + "ord/rating/" + window.location.search}>Score</a>
                    </li>
                    <li className={filters.order === "plinged" ? "active" : ""}>
                        <a href={filtersBaseUrl + "ord/plinged/" + window.location.search}>Plinged</a>
                    </li>
                </ul>
            </div>
        </div>
    )
}

function ProductTagGroupFilterContainer(){
 
    const [ tagGroups, setTagGroups ] = useState([]);
    const [ tagGroupIds, setTagGroupIds ] = useState([]);
    const [ selectedTags, setSelectedTags ] = useState([]);

    React.useState(() => {
        renderTagGroups();
    },[])

    function renderTagGroups(){
        console.log(tag_group_filter);
        for ( var i in tag_group_filter){
            const newTagGroupIds = tagGroupIds;
            newTagGroupIds.push(i); 
            setTagGroupIds(newTagGroupIds);
            const tagGroup = tag_group_filter[i];
            for (var ii in tagGroup){
                if (ii === "selected_tag"){
                    const newSelectedTags = selectedTags;
                    newSelectedTags.push(tagGroup[ii]);
                    setSelectedTags(newSelectedTags);
                } else {
                    const newArray = ConvertObjectToArray(tagGroup[ii],ii);
                    let newTagGroupsArray = tagGroups;
                    newTagGroupsArray.push(newArray);
                    setTagGroups(newTagGroupsArray);
                }
            }
        }
    }

    let tagGroupsDropdownDisplay;
    if (tagGroups.length > 0){
        tagGroupsDropdownDisplay = tagGroups.map((tagGroup,index) => (
            <TagGroupDropDownMenu 
                key={index}
                tagGroup={tagGroup}
                tagGroupId={tagGroupIds[index]}
                selectedTag={selectedTags[index]}
            />
        ));
    }

    return (
        <div id="product-tag-filter-container" style={{"width":140 * tagGroups.length + 1}}>
            {tagGroupsDropdownDisplay}
        </div>
    )
}

function TagGroupDropDownMenu(props){

    console.log(props);

    function onSelectTag(e){
        const serverUrl = json_serverUrl.split('://')[1];
        const ajaxUrl = "https://"+ serverUrl + "/explore/savetaggroupfilter?group_id="+props.tagGroupId+"&tag_id="+e.target.value;
        $.ajax({url: ajaxUrl}).done(function(res) { 
            window.location.reload();
        });
    }

    const tagsDisplay = props.tagGroup.map((tag,index) => (
        <option key={index} selected={tag.id === props.selectedTag} value={tag.id}>{tag.tag}</option>
    ));

    return (
        <div className="product-tag-group-dropdown">
            <select onChange={e => onSelectTag(e)}>
                <option></option>
                {tagsDisplay}
            </select>
        </div>
    )
}

function ProductBrowseItemList(props){


    if (window.location.search === "?index=7") {
        window.browseListType === "favorites";
        browseListType = "favorites";
    }

    const [ containerWidth, setContainerWidth ] = useState($('#product-browse-container').width() + 14);
    const [ itemsInRow, setItemsInRow ] = useState(getNumberOfItemsPerRow(browseListType,isMobile,containerWidth));
    const [ itemWidth, setItemWidth ] = useState(getItemWidth(browseListType,containerWidth,itemsInRow));
    const [ imgHeight, setImgHeight ] = useState(getImageHeight(browseListType,itemWidth));

    
    React.useEffect(() => {
        window.addEventListener("resize", function(event){ updateDimensions() });
        window.addEventListener("orientationchange",  function(event){ updateDimensions() });
    },[])

    function updateDimensions(){
        const newContainerWidth = $('#product-browse-container').width() + 14;
        setContainerWidth(newContainerWidth);
        const newItemsInRow = getNumberOfItemsPerRow(browseListType,isMobile,newContainerWidth);
        setItemsInRow(newItemsInRow);
        const newItemWidth = getItemWidth(browseListType,newContainerWidth,newItemsInRow)
        setItemWidth(newItemWidth);
        const newImgHeight = getImageHeight(browseListType,newItemWidth);
        setImgHeight(newImgHeight);
    }

    let productsRowsDisplay;
    if (itemsInRow){   
        productsRowsDisplay = chunkArray(products,itemsInRow).map((ac,index) => (
            <ProductBrowseItemsRow
                key={index} 
                rowIndex={index}
                products={ac}
                itemWidth={itemWidth}
                imgHeight={imgHeight}
            />
        ))
    }

    return (
        <div id="product-browse-item-list" className={isMobile ? "mobile" : ""}>
            <div id="product-browse-list-container">
                {productsRowsDisplay}
                <ProductBrowsePagination/>
            </div>
        </div>
    )
}

function ProductBrowseItemsRow(props){

    const productsDisplay = props.products.map((p,index) => (
        <ProductBrowseItem
            key={index} 
            index={index}
            rowIndex={props.rowIndex}
            product={p}
            itemWidth={props.itemWidth}
            imgHeight={props.imgHeight}
        />
    ));

    return (
        <div className={"product-browse-item-row " + ( browseListType ? browseListType + "-row" : "")}>
            {productsDisplay}
        </div>
    )
}

function ProductBrowsePagination(){
    
    const [ totalItems, setTotalItems ] = useState(pagination.totalcount);
    const [ itemsPerPage, setItemsPerPage ] = useState(50);
    const [ currentPage, setCurrentPage ] = useState(pagination.page);
    const [ totalPages, setTotalPages ] = useState(Math.ceil(totalItems / itemsPerPage));

    const minPage = currentPage - 5 > 0 ? currentPage - 5 : 0;
    const maxPage = minPage + 10 < totalPages ? minPage + 10 : totalPages;

    let paginationArray = [];
    for (var i = minPage; i < maxPage; i++){ paginationArray.push(i + 1); }
    
    let pageLinkBase = json_serverUrl;
    pageLinkBase += is_show_real_domain_as_url === 1 ? "/" : "/s/" + json_store_name + "/";
    pageLinkBase += "browse/page/";

    let pageLinkSuffix = "/" 
    if (typeof filters.category === 'number') pageLinkSuffix += "cat/" + filters.category + "/";
    pageLinkSuffix += "ord/" + filters.order + "/";
    if (filters.original !== null) pageLinkSuffix += "filteroriginal/" + filters.original + window.location.search;

    let previousButtonDisplay;
    if (currentPage > 1) previousButtonDisplay = <li><a href={pageLinkBase + (currentPage - 1) + pageLinkSuffix}><span className="glyphicon glyphicon-chevron-left"></span> Previous</a></li>

    let nextButtonDisplay;
    if (currentPage < totalPages) nextButtonDisplay = <li><a href={pageLinkBase + (currentPage + 1) + pageLinkSuffix}>Next <span className="glyphicon glyphicon-chevron-right"></span></a></li>

    const paginationDisplay = paginationArray.map((p,index) => {
        let pageLinkDisplay;
        if (currentPage === p) pageLinkDisplay = <span className="no-link">{p}</span>
        else pageLinkDisplay = <a href={pageLinkBase + p + pageLinkSuffix}>{p}</a>
        return (                
            <li key={index}>
                {pageLinkDisplay}
            </li>
        )
    });

    return (
        <div id="product-browse-pagination">
            <ul>
                {previousButtonDisplay}
                {paginationDisplay}
                {nextButtonDisplay}
            </ul>
        </div>
    )
}

const rootElement = document.getElementById("product-browse-container");
ReactDOM.render(<ProductBrowse />, rootElement);