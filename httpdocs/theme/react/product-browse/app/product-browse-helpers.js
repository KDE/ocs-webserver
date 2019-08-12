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

export function getNumberOfItemsPerRow(browseListType,isMobile,containerWidth){
    let itemsPerRow;
    if (isMobile) itemsPerRow = 2;
    else {
        if (browseListType === "music") itemsPerRow = getAdujustItemsPerRow(6,containerWidth,180) 
        else if (browseListType === "phone-pictures") itemsPerRow = getAdujustItemsPerRow(5,containerWidth,210)
        else itemsPerRow = getAdujustItemsPerRow(3,containerWidth,300)
    }
    itemsPerRow;
    return itemsPerRow;
}

function getAdujustItemsPerRow(itemsPerRow,containerWidth,minWidth){
    if (containerWidth / itemsPerRow > minWidth) return itemsPerRow;
    else {
        itemsPerRow = itemsPerRow - 1;
        if (containerWidth / itemsPerRow > minWidth) return itemsPerRow;
        else {
            itemsPerRow = itemsPerRow - 1;
            if (containerWidth / itemsPerRow > minWidth) return itemsPerRow;
            else {
                itemsPerRow = itemsPerRow - 1;
                if (containerWidth / itemsPerRow > minWidth) return itemsPerRow;
                else {
                    itemsPerRow = itemsPerRow - 1;
                    if (containerWidth / itemsPerRow > minWidth) return itemsPerRow;
                    else {
                        itemsPerRow = itemsPerRow - 1;
                        if (containerWidth / itemsPerRow > minWidth) return itemsPerRow;        
                    }        
                }
            }
        }
    }
}

export function chunkArray(myArray, chunk_size){
    var index = 0;
    var arrayLength = myArray.length;
    var tempArray = [];
    
    for (index = 0; index < arrayLength; index += chunk_size) {
        const myChunk = myArray.slice(index, index+chunk_size);
        // Do something if you want with the group
        tempArray.push(myChunk);
    }
    return tempArray;
} 

export function getImageHeight(browseListType,itemWidth){
    
    let itemHeightDivider, imgHeight;

    if (browseListType === "music"){
        itemHeightDivider = 1;
        imgHeight = (itemWidth - 14) / itemHeightDivider;
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