import React from 'react';
class DomainsMenuGroup extends React.Component {
  constructor(props){
    super(props);
    this.state = {};
    this.filterDomainsByMenuGroup = this.filterDomainsByMenuGroup.bind(this);
  }

  filterDomainsByMenuGroup(domain){
    if (domain.menugroup === this.props.menuGroup){
      return domain;
    }
  }

  render(){
      const domainsDisplay = this.props.domains.filter(this.filterDomainsByMenuGroup).map((domain,index) => {
        let domainPrefix = "";
        if (domain.menuhref.indexOf('https://') === -1 && domain.menuhref.indexOf('http://') === -1){
          domainPrefix += "http://";
        }
        return (
          <li key={index}>
            <a href={domainPrefix + domain.menuhref}>{domain.name}</a>
          </li>
        );
      });

    return (
      <li>
        <a className="groupname"><b>{this.props.menuGroup=='Other'?'Pling':this.props.menuGroup}</b></a>
        <ul className="domains-sub-menu">
          {domainsDisplay}
        </ul>
      </li>
    )
  }
}

export default DomainsMenuGroup;
