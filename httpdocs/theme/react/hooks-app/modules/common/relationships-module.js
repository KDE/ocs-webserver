import React from 'react';
import PuiCardsGrid from './pui-cards-grid';

function RelationshipsModule(props){
    
    const relationships = props.relationships;
    let relationshipsDisplay;    
    if (props.type === "product"){
        let acestorsDisplay, childrenDisplay, parentsDisplay, siblingsDisplay;
        if (relationships.related_ancesters) acestorsDisplay = <Relationships {...props} type={"Ancestors"} items={relationships.related_ancesters}/>
        if (relationships.related_children) childrenDisplay = <Relationships {...props} type={"Children"} items={relationships.related_children}/>
        if (relationships.related_parents) parentsDisplay = <Relationships  {...props} type={"Parents"} items={relationships.related_parents}/>
        if (relationships.related_siblings) siblingsDisplay = <Relationships  {...props} type={"Siblings"} items={relationships.related_siblings}/>
        relationshipsDisplay = (
            <React.Fragment>
                {acestorsDisplay}
                {childrenDisplay}
                {parentsDisplay}
                {siblingsDisplay}
            </React.Fragment>
        )
    } else if (props.type === "user-profile"){
        relationshipsDisplay = props.relationships.map((rl,index) => (
            <Relationships key={index} {...props} type="category" showPlings={true} items={rl.items} category={rl}/>
        ))
    }

    return (
        <div className="col-lg-12">
            {relationshipsDisplay}
        </div>
    )
}

export function Relationships(props){    

    let titleDisplay = <h2></h2>;
    if (props.showCatTitle !== false){
        if (props.type !== "category") titleDisplay = <h2>{titleDisplay}</h2>
        else titleDisplay = <h2>{props.category.title ? props.category.title : props.category.cat_title}</h2>
    }
    return (
        <React.Fragment>
            {titleDisplay}
            <PuiCardsGrid gridType="compact" products={props.items} showUser={props.showUser}/>
        </React.Fragment>
    )
}

export default RelationshipsModule;