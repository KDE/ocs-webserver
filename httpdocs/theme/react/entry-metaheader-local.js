import React from 'react';
import MetaHeader from './metaheader/MetaHeader';

ReactDOM.render(<MetaHeader config={window.config} hostname={window.location.hostname}/>, document.getElementById('metaheader'));
