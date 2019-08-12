export function SortByCurrentFilter(a,b){
    let aComparedValue, bComparedValue;
    if (filters.order === "latest"){
        const aDate = typeof a.changed_at !== undefined ? a.changed_at : a.created_at
        aComparedValue = new Date(aDate);
        const bDate = typeof b.changed_at !== undefined ? b.changed_at : b.created_at
        bComparedValue = new Date(bDate);
    } else if (filters.order === "rating"){
        aComparedValue = parseInt(a.laplace_score);
        bComparedValue = parseInt(b.laplace_score);
    } else if (filters.order === "plinged"){
        aComparedValue = parseInt(a.count_plings) !== null ? parseInt(a.count_plings) : 0;
        bComparedValue = parseInt(b.count_plings) !== null ? parseInt(b.count_plings) : 0;
    }
    return aComparedValue - bComparedValue;
}

export function getNumberOfItemsPerRow(browseListType,isMobile){
    let itemsPerRow;
    if (isMobile) itemsPerRow = 2;
    else {
        if (browseListType === "music") itemsPerRow = 6;
        else if (browseListType === "phone-pictures") itemsPerRow = 5;
        else itemsPerRow = 3;
    }
    return itemsPerRow;
}

export function getImageHeight(browseListType,itemWidth){
    
    let itemHeightDivider, imgHeight;

    if (browseListType === "music"){
        itemHeightDivider = 1;
        imgHeight = itemWidth / itemHeightDivider;
    } 
    else if (browseListType === "phone-pictures"){
        itemHeightDivider = .5;
        imgHeight = itemWidth / itemHeightDivider;
    } 
    else {
        itemHeightDivider = 1.85;
        imgHeight = ( itemWidth - 14) / itemHeightDivider;
    }

    return imgHeight;
}

export function getImageUrl(p,itemWidth,imgHeight){
    let imgUrl = "";
    if (p.image_small && p.image_small.indexOf('https://') > -1 || p.image_small && p.image_small.indexOf('http://') > -1 ) imgUrl = p.image_small;
    else {
        imgUrl = "https://cn.opendesktop.";
        imgUrl += window.location.host.endsWith('org') === true || window.location.host.endsWith('com') === true  ? "org" : "cc";
        imgUrl += "/cache/" + Math.ceil(itemWidth * 2) + "x" + Math.ceil(imgHeight * 2) + "/img/" + p.image_small;    
    }
    return imgUrl;
}