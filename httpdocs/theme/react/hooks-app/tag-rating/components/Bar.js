import React from 'react'

const Bar = (props) => {

    const mystyle = {marginRight: '3px'
                ,width:'120px'
                ,display:'inline-block'
                ,padding:'3px'
                ,border: '1px solid #ccc'
                ,textAlign: 'center'};
    return (
        <>
            <span className="taglabel" 
                style={mystyle}>
                        {props.title}
            </span>  
        </>
    )
}

export default Bar; 