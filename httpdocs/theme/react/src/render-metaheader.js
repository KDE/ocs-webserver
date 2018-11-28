
customElements.define('opendesktop-metaheader', class extends HTMLElement {
  constructor() {
    super();
    this.buildComponent();
  }

  async buildComponent() {
    await initConfig(this.getAttribute('config-target'));

    const metaheaderElement = document.createElement('div');
    metaheaderElement.id = 'metaheader';
    ReactDOM.render(React.createElement(MetaHeader, null), metaheaderElement);

    const stylesheetElement = document.createElement('link');
    stylesheetElement.rel = 'stylesheet';
    if (location.hostname.endsWith('opendesktop.org')) {
      stylesheetElement.href = 'https://www.opendesktop.org/theme/react/assets/css/metaheader.css';
    }
    else if (location.hostname.endsWith('opendesktop.cc')) {
      stylesheetElement.href = 'https://www.opendesktop.cc/theme/react/assets/css/metaheader.css';
    }
    else if (location.hostname.endsWith('localhost')) {
      stylesheetElement.href = 'https://www.opendesktop.cc/theme/react/assets/css/metaheader.css';
    }else{
       stylesheetElement.href = 'https://www.opendesktop.org/theme/react/assets/css/metaheader.css';
    }

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
    this.appendChild(stylesheetElement);
    this.appendChild(metaheaderElement);
  }
});
