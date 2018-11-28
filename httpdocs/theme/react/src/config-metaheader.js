import '@babel/polyfill';
import React from 'react';
import ReactDOM from 'react-dom';

// Still need jQuery?
import $ from 'jquery';

// Use this object for config data instead of window.domains,
// window.baseUrl, window.etc... so don't set variables in global scope.
// Please see initConfig()
let config = {};

async function initConfig(target) {
  // API https://www.opendesktop.org/home/metamenujs should send
  // JSON data with CORS.
  // Please see config-dummy.php.

  // Also this API call sends cookie of www.opendesktop.org/cc
  // by fetch() with option "credentials: 'include'", so
  // www.opendesktop.org/cc possible detect user session.
  // Can we consider if include user information into JSON data of
  // API response instead of cookie set each external site?

  let url = '';

  if (location.hostname.endsWith('opendesktop.org')) {
    url = `https://www.opendesktop.org/home/metamenubundlejs?target=${target}`;
  }
  else if (location.hostname.endsWith('opendesktop.cc')) {
    url = `https://www.opendesktop.cc/home/metamenubundlejs?target=${target}`;
  }
  else if (location.hostname.endsWith('localhost')) {
    url = `http://localhost:${location.port}/config-dummy.php`;
  }else if (location.hostname.endsWith('pling.local')) {
    url = `http://pling.local/home/metamenubundlejs?target=${target}`;
  }

  try {
    const response = await fetch(url, {
      mode: 'cors',
      credentials: 'include'
    });
    if (!response.ok) {
      throw new Error('Network response error');
    }
    config = await response.json();
    return true;
  }
  catch (error) {
    console.error(error);
    return false;
  }
}
