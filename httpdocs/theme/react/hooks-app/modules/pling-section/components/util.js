
export function filterDuplicated(el,idx,array) {
    for (let i = 0; i < array.length; i++) {
      if (array[i].member_id == el.member_id)
      {
         if(idx==i){
           return el;
         }else
         {
           break;
         }
      }
    }
}

