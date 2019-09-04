import React, { Component } from 'react';
import TopProducts from './TopProducts';
import TopCreators from './TopCreators';
import Support from './Support';
class PlingSection extends Component {
  constructor(props){
  	super(props);
  	this.state ={...window.data};
    this.handleClick = this.handleClick.bind(this);
    this.loadData = this.loadData.bind(this);
    this.onClickCategory = this.onClickCategory.bind(this);
  }

  componentDidMount() {

  }


  handleClick(section) {
    this.setState(state => ({
      isToggleOn: !state.isToggleOn,
      section:section,
      loading:true,
      category:''
    }));
    this.loadData(section);
  }

  loadData(section){
    let url = '/section/top?section_id='+section.section_id;
    fetch(url)
    .then(response => response.json())
    .then(data => {
       this.setState(prevState => ({loading:false, products:data.products, creators:data.creators}))
     });
  }

  onClickCategory(category)
  {

     let url = '/section/topcat?cat_id='+category.project_category_id;
     fetch(url)
     .then(response => response.json())
     .then(data => {
        this.setState(prevState => ({loading:false, products:data.products, creators:data.creators,category:category}))
      });
  }

  render() {

    let sectioncontainer, sectiondetail;
    //onClick={()=>this.handleClick(section)}
    if (this.state.sections){
      const s = this.state.sections.map((section,index) => (
        <li key={section.section_id}
          className={(this.state.section && section.section_id==this.state.section.section_id)?'active':''}
          >
           <a href={"/section?id="+section.section_id}>{section.name}</a>
        </li>
      ));
     sectioncontainer =  <div className="pling-nav-tabs">
                        <ul className="nav nav-tabs pling-section-tabs">{s}</ul>
                        </div>
    }

    let s;
    if (this.state.details && this.state.section){
       s = this.state.details.map((detail,index) => {
          if(detail.section_id==this.state.section.section_id)
          return <li><a onClick={() => this.onClickCategory(detail)}>{detail.title}</a></li>
      });
    }

    sectiondetail = <div className="pling-section-detail">
                    { this.state.section &&
                      <div className="pling-section-detail-left">
                        <h2>Categories</h2>
                        <ul className="pling-section-detail-ul">{s}</ul>
                      </div>
                    }
                    <div className="pling-section-detail-middle">
                      <TopProducts baseUrlStore={this.state.baseurlStore}
                        products={this.state.products} section={this.state.section} category={this.state.category}/>

                      <TopCreators creators={this.state.creators} section={this.state.section} category={this.state.category}
                          baseUrlStore={this.state.baseurlStore}
                          />
                    </div>
                    <div className="pling-section-detail-right">
                      <a href={this.state.baseurlStore+'/support'} className="btnSupporter">Become a Supporter</a>
                      <Support baseUrlStore={this.state.baseurlStore} section={this.state.section}/>
                    </div>
                  </div>

    return (
      <React.Fragment>
       <h1>Section Detail </h1>
       {sectioncontainer}
       {sectiondetail}
      </React.Fragment>
    );
  }
}
export default PlingSection;
