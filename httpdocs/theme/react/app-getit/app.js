class GetIt extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      product:window.product,
      files:window.filesJson,
      xdgType:window.xdgTypeJson,
      env:appHelpers.getEnv(window.location.hostname)
    };
  }

  render(){

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

  componentDidMount() {

  }

  render(){

    const tableHeader = (
      <thead>
        <tr>
          <th>File</th>
          <th>Version</th>
          <th>Description</th>
          <th>Packagetype</th>
          <th>Architecture</th>
          <th>Downloads</th>
          <th>Date</th>
          <th>Filesize</th>
          <th>Action</th>
        </tr>
      </thead>
    );

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
          <table id="files-table">
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
    let downloadLinkUrlAttr;
    if (this.props.env === 'live') {
      downloadLinkUrlAttr = "https%3A%2F%dl.opendesktop.org%2Fapi%2F";
    } else {
      downloadLinkUrlAttr = "https%3A%2F%2Fcc.ppload.com%2Fapi%2F";
    }
    const baseUrl = window.location.host;
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
                       "files%2Fdownload%2Fid%2F"+f.id+
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

    let ocsInstallLinkDisplay;
    if (this.state.ocsInstallLink){
      ocsInstallLinkDisplay = (
        <span>
           &nbsp; - or - &nbsp;
           <a href={this.state.ocsInstallLink}>Install</a>
        </span>
      )
    }

    const date = new Date(f.created_timestamp); // Date 2011-05-09T06:08:45.178Z
    const year = date.getFullYear();
    const month = ("0" + (date.getMonth() + 1)).slice(-2);
    const day = ("0" + date.getDate()).slice(-2);
    const fDate = year + '-' + month + '-' + day;

    return (
      <tr>
        <td>
          <a href={this.state.downloadLink}>{f.title}</a>
        </td>
        <td>{f.version}</td>
        <td>{f.description}</td>
        <td>{f.packagename}</td>
        <td>{f.archname}</td>
        <td>{f.downloaded_count}</td>
        <td>{fDate}</td>
        <td>{appHelpers.getFileSize(f.size)}</td>
        <td>
          <a href={this.state.downloadLink}>Download</a>
          {ocsInstallLinkDisplay}
        </td>
      </tr>
    )
  }
}

ReactDOM.render(
    <GetIt />,
    document.getElementById('get-it-container')
);
