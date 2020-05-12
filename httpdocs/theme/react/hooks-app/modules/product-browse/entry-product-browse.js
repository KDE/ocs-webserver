import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import ProductBrowse from './app/product-browse.js';

const rootElement = document.getElementById("product-browse-container");
ReactDOM.render(<ProductBrowse />, rootElement);