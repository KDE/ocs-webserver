export function getNumberOfItemsPerRow(browseListType,isMobile,containerWidth){
    let itemsPerRow;
    if (isMobile) itemsPerRow = 2;
    else {
        if (browseListType === "music") itemsPerRow = getAdujustItemsPerRow(7,containerWidth,160 + 14)
        else if (browseListType === "comics") itemsPerRow = getAdujustItemsPerRow(7,containerWidth,160 + 14)
        else if (browseListType === "phone-pictures") itemsPerRow = getAdujustItemsPerRow(5,containerWidth,210)
        else if (browseListType === "favorites" ) itemsPerRow = getAdujustItemsPerRow(1,containerWidth,containerWidth - 1);
        else itemsPerRow = getAdujustItemsPerRow(3,containerWidth,250 + 14)
    }
    console.log(itemsPerRow);
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

export function getItemWidth(browseListType,containerWidth,itemsInRow){
    let itemWidth;
    if (browseListType === "music") itemWidth = 160 + 14;
    else if (browseListType === "comics") itemWidth = 160 + 14;
    else itemWidth = containerWidth / itemsInRow;
    return itemWidth;
}

export function getImageHeight(browseListType,itemWidth){
    console.log(browseListType);
    let itemHeightDivider, imgHeight;

    itemWidth = itemWidth - 14;

    if (browseListType === "music"){
       imgHeight = 155;
    } 
    else if (browseListType === "phone-pictures"){
        itemHeightDivider = .5;
        imgHeight = itemWidth / itemHeightDivider;
    } 
    else if (browseListType === "comics"){
        imgHeight = 220;
    }
    else if (browseListType === "favorites"){
        imgHeight = 167;
    }
    else {
        itemHeightDivider = 1.85;
        imgHeight = itemWidth / itemHeightDivider;
    }
    console.log(imgHeight);
    return imgHeight;
}

export function getImageUrl(p,itemWidth,imgHeight){
    let imgUrl = "";
    if (p.image_small && p.image_small.indexOf('https://') > -1 || p.image_small && p.image_small.indexOf('http://') > -1 ) imgUrl = p.image_small;
    else {
        imgUrl = json_server_images;
        imgUrl += "/cache/" + Math.ceil(itemWidth * 2) + "x" + Math.ceil(imgHeight * 2) + "/img/" + p.image_small;    
        console.log(imgUrl);
    }
    return imgUrl;
}