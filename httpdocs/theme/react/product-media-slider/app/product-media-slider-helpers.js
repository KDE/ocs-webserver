export function GenerateGalleryArray(product){
    let galleryArray = [];
    let noGallery = false, noLogo = false;
    if (window.galleryPicturesJson){
        window.galleryPicturesJson.forEach(function(gp,index){ galleryArray.push({url:gp,type:'image'}); });
        noGallery = true;
    }
    else {
        galleryArray = [{url:product.image_small,type:'image'} ];
        if (!product.image_small) noLogo = true;
    }
    if (product.embed_code !== null && product.embed_code.length > 0) galleryArray = [{url:product.embed_code,type:'embed'}, ... galleryArray ];
    if (window.filesJson) {
        window.filesJson.forEach(function(f,index){
            if (f.active === "1"){
                console.log(f.type);
                let addFileToGallery = false;
                if (f.type.indexOf('video') > -1 || 
                    f.type.indexOf('audio') > -1 || 
                    f.type.indexOf('ogg') > -1 ||
                    f.type.indexOf('epub') > -1){
                    addFileToGallery = true;
                }

                if (f.type.indexOf("image") > -1 && noGallery === true && noLogo === true) addFileToGallery = true;

                if (addFileToGallery === true){
                    let type;
                    if (f.type.indexOf('video') > -1 || f.type.indexOf('audio') > -1 || f.type.indexOf('ogg') > -1 ) type = f.type.split('/')[0]
                    else if (f.type.indexOf('epub') > -1 ) type = "book";
                    else if (f.type.indexOf('image') > -1) type = "image";
                    // else if (f.name.indexOf('.cbr') > -1 || f.name.indexOf('.cbz') > -1) type = "comics";
                    
                    let url_preview, url_thumb;
                    if (f.url_thumb) url_thumb = f.url_thumb.replace(/%2F/g,'/').replace(/%3A/g,':');
                    if (f.url_preview) url_preview = f.url_preview.replace(/%2F/g,'/').replace(/%3A/g,':');
                    
                    const gItem = {
                        url:f.url.replace(/%2F/g,'/').replace(/%3A/g,':'),
                        collection_id:f.collection_id,
                        type:type,
                        file_type:SplitByLastDot(f.title),
                        file_id:f.id,
                        collection_id:f.collection_id,
                        title:f.title,
                        url_thumb:url_thumb,
                        url_preview:url_preview
                    }
                    
                    if (f.type.indexOf('audio') > -1){
                        gItem.name = gItem.title;
                        gItem.cover = window.galleryPicturesJson[0];
                        gItem.musicSrc = gItem.url;
                        gItem.lyric = '';
                    }
                    
                    galleryArray = [gItem, ... galleryArray] 
                }    
            }
        })
    }
    return galleryArray;
}

function SplitByLastDot(string){
    var period = string.lastIndexOf('.');
    var fileExtension = string.substring(period + 1);
    return fileExtension;
}

export function CheckForMultipleAudioFiles(galleryArray){
    let hasMultipleAudioFiles = false;
    let audioFilesCounter = 0;
    galleryArray.forEach(function(gi,index){
        if (gi.type === "audio") audioFilesCounter += 1;
    })
    if (audioFilesCounter > 1) hasMultipleAudioFiles = true;
    return hasMultipleAudioFiles;
}

export function GroupAudioFilesInGallery(galleryArray){
    let newGalleryArray = [
        {type:"audio",playlist:true,items:[]}
    ]
    galleryArray.forEach(function(gi,index){
        if (gi.type === "audio"){
            newGalleryArray[0].items.push(gi);
        } else {
            newGalleryArray.push(gi);
        }
    });
    return newGalleryArray;
}

export function generatePagesArray(pages,displayType){
    let pagesArray;
    if (displayType === "single") pagesArray = pages;
    else if (displayType === "double"){
        pagesArray = chunkArray(pages,2);
    }
    return pagesArray;
}

function chunkArray(myArray, chunk_size){
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