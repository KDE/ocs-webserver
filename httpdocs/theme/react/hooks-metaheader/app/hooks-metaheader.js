import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import {isMobile} from 'react-device-detect';

function MetaHeader(){

    const [ isReady, setIsReady ] = useState(false);

    React.useEffect(() => {
        setIsReady(true);
    },[]);

    React.useEffect(() => {
        console.log('will trigger on "isReady" change');
    },[isReady])

    return (
        <div id="metaheader">
            hooks
        </div>
    )
}

const rootElement = document.getElementById("hooks-metaheader");
ReactDOM.render(<MetaHeader />, rootElement);