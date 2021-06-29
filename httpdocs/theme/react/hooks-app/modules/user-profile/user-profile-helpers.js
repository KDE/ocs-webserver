export const RenderUserProductsArray = (products) => {
    let sortedArray;
    if (products){
        let productGroupsObject = {}
        products.forEach(function(product,index){
            if (!productGroupsObject[product.project_category_id]){
                productGroupsObject[product.project_category_id] = {
                    title:product.catTitle ? product.catTitle : product.cat_title,
                    id:product.project_category_id,
                    items:[product]
                }
            } else productGroupsObject[product.project_category_id].items.push(product);
        })
        let productGroupsArray = [];
        for (var i in productGroupsObject){ productGroupsArray.push(productGroupsObject[i]); }
        sortedArray = productGroupsArray;
    }
    return sortedArray;
}

function sortArrayByCatTitle(a, b) {
    const titleAttr = a.title ? "title" : "cat_title";
    let textA, textB;
    if (a[titleAttr]) textA = a[titleAttr].toUpperCase();
    if (b[titleAttr]) textB = a[titleAttr].toUpperCase();
    return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;
}

export const GenerateFetchProductsRequest = (username,type,loaded,pageNum) => {
    
    let ajaxRequest = {
        url:'/u/'+username+"/" + ( type !== null ? type : "products") + "?page="+pageNum
    }

    if (loaded === true || loaded === false && pageNum > 1){
        ajaxRequest.data = {projectpage:pageNum};
        ajaxRequest.type = "POST";
    }

    return ajaxRequest;
}

export const GenerateProductArraysByCategory = (oldProducts, newProducts, groupResults) => {
    let mergedOldProducts = [];
    oldProducts.forEach(function(pa,index){
        mergedOldProducts = [ ...mergedOldProducts, ...pa ];
    });
    let mergedArray = [...mergedOldProducts, ...newProducts];
    if (groupResults === true) mergedArray = [ ...newProducts];
    const arrayContainer = {};
    let arrayNumberCounter = 0;
    let previousCatTitle = null;
    mergedArray.forEach(function(p,index){
        if (groupResults === true){
            if (!arrayContainer[p.cat_id]) arrayContainer[p.cat_id] = [];
            arrayContainer[p.cat_id].push(p);
        } else {
            if (previousCatTitle !== p.cat_title && previousCatTitle !== p.catTitle){
                previousCatTitle = p.catTitle ? p.catTitle : p.cat_title;
                arrayNumberCounter += 1;
                arrayContainer[arrayNumberCounter] = [];
            }
            arrayContainer[arrayNumberCounter].push(p);
        }
    })
    let newArray = [];
    if (groupResults === true) newArray = [[...mergedOldProducts]];
    for (var i in arrayContainer){
      newArray.push(arrayContainer[i]);
    }
    return newArray;
}