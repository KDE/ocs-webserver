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
    }
    return aComparedValue - bComparedValue;
}