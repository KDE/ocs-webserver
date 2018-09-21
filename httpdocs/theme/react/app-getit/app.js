class GetIt extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      product:window.product,
      files:window.filesJson,
      xdgType:window.xdgTypeJson,
      env:'test'
    };
  }

  render(){
    console.log(this.state);
    return (
      <div id="get-it">
        <button
            data-toggle="modal"
            data-target="#get-it-modal-window"
            style={{"width":"100%"}}
            id="project_btn_getit" className="btn dropdown-toggle active btn-primary  "
            type="button">
            Get it
          </button>
          <div className="modal fade" id="get-it-modal-window" tabIndex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div id="get-it-modal" className="modal-dialog" role="document">

              <GetItFilesList
                files={this.state.files}
                product={this.state.product}
                env={this.state.env}
                xdgType={this.state.xdgType}
              />
            </div>
          </div>
      </div>
    )
  }
}

class GetItFilesList extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      activeTab:'active'
    };
    this.toggleActiveTab = this.toggleActiveTab.bind(this);
  }

  toggleActiveTab(tab){
    this.setState({activeTab:tab});
  }

  render(){

    const activeFiles = this.props.files.filter(file => file.active == "1").map((f,index) => (
      <GetItFilesListItem
        product={this.props.product}
        xdgType={this.props.xdgType}
        env={this.props.env}
        key={index}
        file={f}
      />
    ));
    const archivedFiles = this.props.files.filter(file => file.active == "0").map((f,index) => (
      <GetItFilesListItem
        product={this.props.product}
        xdgType={this.props.xdgType}
        env={this.props.env}
        key={index}
        file={f}
      />
    ));
    const summeryRow = productHelpers.getFilesSummary(this.props.files);
    const summeryRowDisplay = (
      <tr>
        <td>{summeryRow.total} files ({summeryRow.archived} archived)</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>{summeryRow.downloads}</td>
        <td></td>
        <td>{appHelpers.getFileSize(summeryRow.fileSize)}</td>
        <td></td>
        <td></td>
      </tr>
    );
    const tableHeader = (
      <thead>
        <tr>
          <th className="mdl-data-table__cell--non-numericm">File</th>
          <th className="mdl-data-table__cell--non-numericm">Version</th>
          <th className="mdl-data-table__cell--non-numericm">Description</th>
          <th className="mdl-data-table__cell--non-numericm">Packagetype</th>
          <th className="mdl-data-table__cell--non-numericm">Architecture</th>
          <th className="mdl-data-table__cell--non-numericm">Downloads</th>
          <th className="mdl-data-table__cell--non-numericm">Date</th>
          <th className="mdl-data-table__cell--non-numericm">Filesize</th>
          <th className="mdl-data-table__cell--non-numericm">DL</th>
          <th className="mdl-data-table__cell--non-numericm">OCS-Install</th>
        </tr>
      </thead>
    );

    let tableFilesDisplay;
    if (this.state.activeTab === "active"){
      tableFilesDisplay = (
        <tbody>
          {activeFiles}
          {summeryRowDisplay}
        </tbody>
      )
    } else if (this.state.activeTab === "archived"){
      tableFilesDisplay = (
        <tbody>
          {archivedFiles}
          {summeryRowDisplay}
        </tbody>
      )
    }

    return (
      <div id="files-tabs-container">
        <button type="button" className="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>

        <div className="files-tabs-header">
          <h2>Thanks for your support!</h2>
        </div>
        <div className="tabs-menu">
          <ul className="nav nav-tabs" role="tablist">
             <li role="presentation" className={this.state.activeTab === "active" ? "active" : ""}>
               <a  onClick={() => this.toggleActiveTab('active')}>Files ({summeryRow.total})</a>
             </li>
             <li role="presentation" className={this.state.activeTab === "archived" ? "active pull-right" : "pull-right"}>
               <a  onClick={() => this.toggleActiveTab('archived')}>Archive ({summeryRow.archived})</a>
             </li>
           </ul>
        </div>
        <div id="files-tab" className="product-tab">
          <table id="active-files-table" className="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
            {tableHeader}
            {tableFilesDisplay}
          </table>
        </div>
      </div>
    );
  }
}

class GetItFilesListItem extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {downloadLink:""};
  }

  componentDidMount() {
    let baseUrl, downloadLinkUrlAttr;
    if (this.props.env === 'live') {
      baseUrl = 'opendesktop.org';
      downloadLinkUrlAttr = "https%3A%2F%dl.opendesktop.org%2Fapi%2F";
    } else {
      baseUrl = 'pling.cc';
      downloadLinkUrlAttr = "https%3A%2F%2Fcc.ppload.com%2Fapi%2F";
    }

    const f = this.props.file;
    const timestamp =  Math.floor((new Date().getTime() / 1000)+3600)
    const fileDownloadHash = appHelpers.generateFileDownloadHash(f,this.props.env);
    const downloadLink = "https://"+baseUrl+
                       "/p/"+this.props.product.project_id+
                       "/startdownload?file_id="+f.id+
                       "&file_name="+f.title+
                       "&file_type="+f.type+
                       "&file_size="+f.size+
                       "&url="+downloadLinkUrlAttr+
                       "files%2Fdownloadfile%2Fid%2F"+f.id+
                       "%2Fs%2F"+fileDownloadHash+
                       "%2Ft%2F"+timestamp+
                       "%2Fu%2F"+this.props.product.member_id+
                       "%2F"+f.title;

    const ocsInstallLink = productHelpers.generateOcsInstallLink(f,this.props.xdgType,downloadLink);
    this.setState({
      downloadLink:downloadLink,
      ocsInstallLink:ocsInstallLink
    });
  }

  render(){
    const f = this.props.file;
    let title;
    if (f.title.length > 30){
      title = f.title.substring(0,30) + "...";
    } else {
      title = f.title;
    }

    let ocsInstallLinkDisplay;
    if (this.state.ocsInstallLink){
      ocsInstallLinkDisplay = (
        <a className="btn btn-native download-button" href={this.state.ocsInstallLink}>Install</a>
      )
    }

    return (
      <tr>
        <td className="mdl-data-table__cell--non-numericm">
          <a href={this.state.downloadLink}>{title}</a>
        </td>
        <td>{f.version}</td>
        <td className="mdl-data-table__cell--non-numericm">{f.description}</td>
        <td className="mdl-data-table__cell--non-numericm">{f.packagename}</td>
        <td  className="mdl-data-table__cell--non-numericm">{f.archname}</td>
        <td>{f.downloaded_count}</td>
        <td className="mdl-data-table__cell--non-numericm">{appHelpers.getTimeAgo(f.created_timestamp)}</td>
        <td className="mdl-data-table__cell--non-numericm">{appHelpers.getFileSize(f.size)}</td>
        <td>
          <a href={this.state.downloadLink} className="btn btn-native download-button">
            <img src="/images/system/download.svg" alt="download" style={{width:"20px",height:"20px"}}/>
          </a>
        </td>
        <td>{ocsInstallLinkDisplay}</td>
      </tr>
    )
  }
}

ReactDOM.render(
    <GetIt />,
    document.getElementById('get-it-container')
);
