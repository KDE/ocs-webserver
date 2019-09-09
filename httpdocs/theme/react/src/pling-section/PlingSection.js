import React, { Component } from 'react';
import TopProducts from './TopProducts';
import TopCreators from './TopCreators';
import Support from './Support';
import Supporters from './Supporters';
import Header from './function/Header';
class PlingSection extends Component {
  constructor(props){
  	super(props);
  	this.state ={...window.data,showContent:'overview'};
    //this.handleClick = this.handleClick.bind(this);
    this.loadData = this.loadData.bind(this);
    this.onClickCategory = this.onClickCategory.bind(this);
    this.showDetail = this.showDetail.bind(this);
  }


  // handleClick(section) {
  //   this.setState(state => ({
  //     isToggleOn: !state.isToggleOn,
  //     section:section,
  //     loading:true,
  //     category:''
  //   }));
  //   this.loadData(section);
  // }

  loadData(section){
    let url = '/section/top?section_id='+section.section_id;
    fetch(url)
    .then(response => response.json())
    .then(data => {
       this.setState(prevState => ({loading:false, products:data.products, creators:data.creators}))
     });
  }

  showDetail(showContent){
      this.setState(state => ({
        showContent: showContent
      }));
      if(showContent=='overview'){ this.loadData(this.state.section);}

      console.log("showDetail:"+showContent);

  }
  onClickCategory(category)
  {
     let url = '/section/topcat?cat_id='+category.project_category_id;
     fetch(url)
     .then(response => response.json())
     .then(data => {
        this.setState(prevState => ({loading:false,showContent: 'overview',products:data.products, creators:data.creators,category:category}))
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
         if(this.state.showContent =='categories') // ignore section show all categories
         {
           return <li><a onClick={() => this.onClickCategory(detail)}>{detail.title}</a></li>
         }else
         {
            if(detail.section_id==this.state.section.section_id)
            return <li><a onClick={() => this.onClickCategory(detail)}>{detail.title}</a></li>
          }
      });
    }

    let detailContent;
    if(this.state.showContent=='supporters')
    {
      detailContent = <Supporters baseUrlStore={this.state.baseurlStore} supporters = {this.state.supporters}/>
    }else {
      detailContent = (<React.Fragment>
                  <TopProducts baseUrlStore={this.state.baseurlStore}
                        products={this.state.products} section={this.state.section} category={this.state.category}/>

                  <TopCreators creators={this.state.creators} section={this.state.section} category={this.state.category}
                      baseUrlStore={this.state.baseurlStore}
                      />
                </React.Fragment>
                )
    }

    sectiondetail = <div className="pling-section-detail">
                    { this.state.section &&
                      <div className="pling-section-detail-left">
                        <h2 className={this.state.showContent=='overview'?'focused':''}><a onClick={()=>this.showDetail('overview')}>Overview</a></h2>
                        <h2 className={this.state.showContent=='supporters'?'focused':''}><a onClick={()=>this.showDetail('supporters')}>Supporters</a></h2>
                        <h2 className={this.state.showContent=='categories'?'focused':''}><a onClick={()=>this.showDetail('categories')}>Categories</a></h2>
                        <ul className="pling-section-detail-ul">{s}</ul>
                      </div>
                    }
                    <div className="pling-section-detail-middle">
                      {detailContent}
                    </div>
                    <div className="pling-section-detail-right">
                      <div className="btnSupporter">Become a Supporter</div>
                        { this.state.section &&
                      <Support baseUrlStore={this.state.baseurlStore} section={this.state.section}
                              supporters = {this.state.supporters}
                        />
                      }
                    </div>
                  </div>

    return (
      <React.Fragment>
       <Header section={this.state.section} amount={this.state.probably_payout_amount}
              goal = {this.state.probably_payout_goal}
         />
       {sectioncontainer}
       {sectiondetail}
      </React.Fragment>
    );
  }
}
export default PlingSection;
