import React from 'react';
import ReactDOM from 'react-dom';
import MetaHeader from './metaheader/MetaHeader';
import '@webcomponents/custom-elements';
async function initConfig(target, redirect)
{
         let url = `https://www.opendesktop.org/home/metamenubundlejs?target=${target}&url=${redirect}`;
         if (window.location.hostname.endsWith('cc')) {
            url = `https://www.opendesktop.cc/home/metamenubundlejs?target=${target}&url=${redirect}`;
          }


          try {
            const response = await fetch(url, {
              mode: 'cors',
              credentials: 'include'
            });
            if (!response.ok) {
              throw new Error('Network response error');
            }
            let config = await response.json();
            config.isAdmin = config.json_isAdmin;
            config.showModal = false;
            config.isExternal = true;
            config.modalUrl = '';
            return config;
          }
          catch (error) {
            console.error(error);
            return false;
          }


}

// async function f() {
//   let config = await initConfig('opendesktop',window.location.href); // wait till the promise resolves (*)
//   ReactDOM.render(<MetaHeader config={config} hostname={window.location.hostname}/>, document.getElementById('metaheader'))
// }
//
// f();


customElements.define('opendesktop-metaheader', class extends HTMLElement {
  constructor() {
    super();
    this.buildComponent();
  }

  async buildComponent() {

    const stylesheetElement = document.createElement('link');
    stylesheetElement.rel = 'stylesheet';
    stylesheetElement.href = 'https://www.opendesktop.org/theme/react/assets/css/metaheader.css';

    if (window.location.hostname.endsWith('cc')) {
      stylesheetElement.href = 'https://www.opendesktop.cc/theme/react/assets/css/metaheader.css';
    }/*else if(location.hostname.endsWith('localhost'))
    {
      //stylesheetElement.href = 'https://www.opendesktop.cc/theme/react/assets/css/metaheader.css';
    }*/
    // else if (location.hostname.endsWith('localhost')) {
    //   stylesheetElement.href = 'https://www.opendesktop.cc/theme/react/assets/css/metaheader.css';
    // }else if (location.hostname.endsWith('local')) {
    //     stylesheetElement.href = 'https://www.opendesktop.cc/theme/react/assets/css/metaheader.css';
    // }
    // else{
    //    stylesheetElement.href = 'https://www.opendesktop.org/theme/react/assets/css/metaheader.css';
    // }
    this.appendChild(stylesheetElement);

    const metaheaderElement = document.createElement('div');
    metaheaderElement.id = 'metaheader';
    let config = await initConfig(this.getAttribute('config-target'),window.location.href); // wait till the promise resolves (*)


    ReactDOM.render(<MetaHeader config={config} hostname={window.location.hostname}/>, metaheaderElement);


    // Component must be capsule within Shadow DOM, and don't hack
    // context/scope of external sites.
    /*
    this.attachShadow({mode: 'open'});
    this.shadowRoot.appendChild(stylesheetElement);
    this.shadowRoot.appendChild(metaheaderElement);
    */

    // However, make this as Light DOM for now, because current
    // implementation is not real component design yet.
    // Need solve event handling, scoped CSS.
    this.appendChild(metaheaderElement);
  }
});
