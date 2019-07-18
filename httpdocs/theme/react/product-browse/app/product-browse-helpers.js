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
        aComparedValue = a.count_plings !== null ? a.count_plings : 0;
        bComparedValue = b.count_plings !== null ? b.count_plings : 0;
    }
    console.log(aComparedValue,bComparedValue);
    return aComparedValue - bComparedValue;
}