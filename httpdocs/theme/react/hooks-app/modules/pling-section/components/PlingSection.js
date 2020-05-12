import React, { useState } from 'react';
import TopProducts from './TopProducts';
import TopCreators from './TopCreators';
import Support from './Support';
import Supporters from './Supporters';
import Header from './Header';

const PlingSection = () => {
  const [state, setState] = useState(window.data);
  const [products, setProducts] = useState(window.data.products);
  const [creators, setCreators] = useState(window.data.creators);
  const [sections, setSections] = useState(window.data.sections);
  const [supporters, setSupporters] = useState(window.data.supporters)
  const [section, setSection] = useState(window.data.section);
  const [details, setDetails] = useState(window.data.details);
  const [category, setCategory] = useState();
  const [showContent, setShowContent] = useState('overview');
  const [loading, setLoading] = useState(false);

  const loadData = section =>{
    let url = '/section/top?id='+section.section_id;
    fetch(url)
    .then(response => response.json())
    .then(data => {
      setLoading(false);
      setProducts(data.products);
      setCreators(data.creators);      
     });
  }

  const showDetail = sc =>{
      setShowContent(sc);      
      if(showContent=='overview'){ loadData(section);}
  }

  const onClickCategory = category=>{
     let url = '/section/topcat?cat_id='+category.project_category_id;
     fetch(url)
     .then(response => response.json())
     .then(data => {
      setLoading(false);  
      setShowContent('overview');       
      setProducts(data.products);
      setCreators(data.creators);    
      setCategory(category);      
    });        
  }


  // render
  let sectioncontainer, sectiondetail;
  //onClick={()=>handleClick(section)}
  if (sections){
      const t = sections.map((s,index) => (
        <li key={s.section_id}
          className={(section && section.section_id==s.section_id)?'active':''}
          >
           <a href={"/section?id="+s.section_id}>{s.name}</a>
        </li>
      ));
     sectioncontainer =  <div className="pling-nav-tabs">
                        <ul className="nav nav-tabs pling-section-tabs">{t}</ul>
                        </div>
    }

    let categories;
    if (details && section){
      categories = details.map((detail,index) => {
            if(detail.section_id==section.section_id)
            return <li key={index}><a onClick={() => onClickCategory(detail)}>{detail.title}</a></li>
      });
    }

    let detailContent;
    if(showContent=='supporters')
    {
      detailContent = <Supporters baseUrlStore={state.baseurlStore} supporters = {supporters}/>
    }else {
      detailContent = (<React.Fragment>
                  <TopProducts isAdmin={state.isAdmin} baseUrlStore={state.baseurlStore}
                        products={products} section={section} category={category}/>

                  <TopCreators isAdmin={state.isAdmin} creators={creators} section={section} category={category}
                      baseUrlStore={state.baseurlStore}
                      />
                </React.Fragment>
                )
    }

    sectiondetail = <div className="pling-section-detail">
                    { section &&
                      <div className="pling-section-detail-left">
                        <h2 className={showContent=='overview'?'focused':''}><a onClick={()=>showDetail('overview')}>Overview</a></h2>
                        <h2 className={showContent=='supporters'?'focused':''}><a onClick={()=>showDetail('supporters')}>Supporters</a></h2>
                        <h2 className={showContent=='categories'?'focused':''}><a onClick={()=>showDetail('overview')}>Categories</a></h2>
                        <ul className="pling-section-detail-ul">{categories}</ul>
                      </div>
                    }
                    <div className="pling-section-detail-middle">
                      {detailContent}
                    </div>
                    <div className="pling-section-detail-right">
                      <div className="btnSupporter">Become a Supporter</div>
                        { section &&
                      <Support baseUrlStore={state.baseurlStore} section={section}
                              supporters = {supporters}
                        />
                      }
                    </div>
                  </div>


  return (
    <>
       <Header section={section} isAdmin={state.isAdmin}
               supporters = {supporters}
         />
       {sectioncontainer}
       {sectiondetail}
      </>
  )
}

export default PlingSection

