
function Pagination(props){
    
    function onPaginationButtonClick(page){
        props.onPageChange(page)
    }
    
    let firstPageNumber = 0, lastPageNumber = props.numberOfPages;
    if (props.numberOfPages > 10){
        lastPageNumber = 10;
        if ((props.currentPage - 1) > 5){
            firstPageNumber = ((props.currentPage) - 5);
            lastPageNumber = ((props.currentPage) + 5);
            if (lastPageNumber > props.numberOfPages){
                lastPageNumber = lastPageNumber + (props.numberOfPages - lastPageNumber);
                firstPageNumber = firstPageNumber - (5 - (props.numberOfPages - props.currentPage));
            }
        }
    }

    let paginationArray = [];
    for (var i = firstPageNumber; i < lastPageNumber; i++){ paginationArray.push(i) }

    const paginationDisplay = paginationArray.map((page,index) => (
        <PaginationButton type={props.type} onPaginationButtonClick={onPaginationButtonClick} key={index} currentPage={props.currentPage} page={page} />
    ))


    return (
        <div className={"pagination-container " + props.type}>
            <ul className="pagination">
                <PaginationButton 
                    onPaginationButtonClick={onPaginationButtonClick}
                    currentPage={props.currentPage}
                    numberOfPages={props.numberOfPages}
                    page={"-1"}
                    type={props.type}
                />
                {paginationDisplay}
                <PaginationButton 
                    onPaginationButtonClick={onPaginationButtonClick}
                    currentPage={props.currentPage}
                    numberOfPages={props.numberOfPages}
                    page={"+1"}
                    type={props.type}
                />
            </ul>
        </div>
    )
}

function PaginationButton(props){

    let pageLink = props.page + 1,
        pageDisplay = props.page + 1,
        buttonCssStyle = {cursor:"pointer", padding:"4px 8px"};
        if (props.type === "browse") buttonCssStyle.padding = "4px";

    if (props.page === "-1"){ 

        pageLink = props.currentPage - 1;
        pageDisplay = <i class="bi bi-chevron-left"></i>
        if (props.type === "browse"){
            pageDisplay = (
                <React.Fragment>
                    <i class="bi bi-chevron-left"></i> Prev    
                </React.Fragment>
            )
        }

        if (props.currentPage === 1){
            buttonCssStyle.cursor = "no-drop"
            if (props.type === "browse") pageDisplay = "";
        }

    } else if (props.page === "+1"){
        
        pageLink = props.currentPage + 1
        pageDisplay = <i class="bi bi-chevron-right"></i>
        
        if (props.type === "browse"){
            pageDisplay = ( 
                <React.Fragment>
                    Next <i class="bi bi-chevron-right"></i> 
                </React.Fragment>
            )
        }

        if (pageLink > props.numberOfPages){
            buttonCssStyle.cursor = "no-drop";
            if (props.type === "browse") pageDisplay = "";
        }
    }

    function onPaginationButtonClick(){
        let loadPage = true;
        if (props.page === "-1" && props.currentPage === 1 || props.page === "+1" && pageLink > props.numberOfPages) loadPage = false;
        if (loadPage === true) props.onPaginationButtonClick(pageLink)
    }

    return (
        <li className={props.currentPage === ( props.page + 1 ) ? "active" : ""}>
            <a style={buttonCssStyle} onClick={onPaginationButtonClick}>
                <span>{pageDisplay}</span>
            </a>
        </li>
    )
}

export default Pagination;