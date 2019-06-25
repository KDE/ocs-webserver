import React from 'react';

export function GenerateGalleryArray(product){
    let galleryArray = []
    if (window.galleryPicturesJson) window.galleryPicturesJson.forEach(function(gp,index){ galleryArray.push({url:gp,type:'image'}); });
    else galleryArray = [{url:product.image_small,type:'image'} ];
    if (product.embed_code !== null && product.embed_code.length > 0) galleryArray = [{url:product.embed_code,type:'embed'}, ... galleryArray ];
    if (window.filesJson) {
    window.filesJson.forEach(function(f,index){
        if (f.type.indexOf('video') > -1 || f.type.indexOf('audio') > -1 || f.type.indexOf('epub') > -1){
            
            let type;
            if (f.type.indexOf('video') > -1 || f.type.indexOf('audio') > -1 ) type = f.type.split('/')[0]
            else if (f.type.indexOf('epub') > -1 ) type = "book";
            
            let url_preview, url_thumb;
            if (f.url_thumb) url_thumb = f.url_thumb.replace(/%2F/g,'/').replace(/%3A/g,':');
            if (f.url_preview) url_preview = f.url_preview.replace(/%2F/g,'/').replace(/%3A/g,':');
            
            const gItem = {
                url:f.url.replace(/%2F/g,'/').replace(/%3A/g,':'),
                collection_id:f.collection_id,
                type:type,
                file_id:f.id,
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
    })
    }
    return galleryArray;
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