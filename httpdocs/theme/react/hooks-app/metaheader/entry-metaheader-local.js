import React from 'react';
import ReactDOM from 'react-dom';
import MetaHeader from './components/MetaHeader';

ReactDOM.render(<MetaHeader config={window.config} hostname={window.location.hostname}/>, document.getElementById('metaheader'));
