export function SortByCurrentFilter(a,b){
    const aDate = typeof a.changed_at !== undefined ? a.changed_at : a.created_at
    const aCreatedAt = new Date(aDate);
    // const aTimeStamp = aCreatedAt.getTime();
    const bDate = typeof b.changed_at !== undefined ? b.changed_at : b.created_at
    const bCreatedAt = new Date(bDate);
    // const bTimeStamp = bCreatedAt.getTime();
    return aCreatedAt - bCreatedAt;
}