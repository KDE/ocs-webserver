import React, { useState, createContext } from 'react';
import TopProducts from './TopProducts';
import TopCreators from './TopCreators';
import Support from './Support';
import Supporters from './Supporters';
import Header from './Header';
import RecentPlinged from './RecentPlinged';

const AppSupportersContext = createContext();

const AppSupporters = () => {
  const [state, setState] = useState(window.data);
  const [section, setSection] = useState(window.data.section);
  const [products, setProducts] = useState(window.data.products);
  const [creators, setCreators] = useState(window.data.creators);
  const [productsCategory, setProductsCategory] = useState([]);
  const [creatorsCategory, setCreatorsCategory] = useState([]);
  const [sections, setSections] = useState(window.data.sections);
  const [supporters, setSupporters] = useState(window.data.supporters)

  const [details, setDetails] = useState(window.data.details);
  const [recentplings, setRecentplings] = useState([]);
  const [recentplingsLoaded, setRecentplingsLoaded] = useState(false);
  const [category, setCategory] = useState();
  const [showContent, setShowContent] = useState('overview');

  const loadData = async (section) => {
    const data = await fetch(`/supporters/top?id=${section.section_id}`);
    const items = await data.json();
    setProducts(items.products);
    setCreators(items.creators);
  }

  const loadrecentplings = async (section) => {
    const data = await fetch(`/supporters/recentplings?id=${section.section_id}`);
    const items = await data.json();
    setRecentplings(items.products);
    setRecentplingsLoaded(true);
  }

  const showDetail = sc => {
    setShowContent(sc);   
    if (sc == 'recentplings' && recentplingsLoaded===false) { loadrecentplings(section); }
  }

  const onClickCategory = async (category) => {
    const data = await fetch(`/supporters/topcat?cat_id=${category.project_category_id}`);
    const items = await data.json();
    setShowContent('overview-category-subcat');
    setProductsCategory(items.products);
    setCreatorsCategory(items.creators);
    setCategory(category);
  }

  // render
  let sectioncontainer, sectiondetail;
  //onClick={()=>handleClick(section)}
  if (sections) {
    const t = sections.map((s, index) => (
      <li key={s.section_id}
        className={(section && section.section_id == s.section_id) ? 'active' : ''}
      >
        <a href={"/supporters?id=" + s.section_id}>{s.name}</a>
      </li>
    ));
    sectioncontainer = <div className="pling-nav-tabs" style={{ 'display': 'flex' }}>
      <ul className="nav nav-tabs pling-section-tabs">{t}</ul>
    </div>
  }

  let categories;
  if (details && section) {
    categories = details.map((detail, index) => {
      if (detail.section_id == section.section_id)
        return <li key={index}><a onClick={() => onClickCategory(detail)}>{detail.title}</a></li>
    });
  }

  let detailContent;
  if (showContent == 'supporters') {
    detailContent = <Supporters baseUrlStore={state.baseurlStore} supporters={supporters} />
  } else if (showContent == 'recentplings') {
    detailContent = <RecentPlinged products={recentplings} baseUrlStore={state.baseurlStore}/>
  }else if (showContent == 'overview-category-subcat') {
    detailContent = <>
      <TopProducts products={productsCategory} baseUrlStore={state.baseurlStore}/>
      <TopCreators creators={creatorsCategory} baseUrlStore={state.baseurlStore}/>
    </>
  } else {
    // overview or category all on click
    detailContent = <>
      <TopProducts products={products} baseUrlStore={state.baseurlStore}/>
      <TopCreators creators={creators} baseUrlStore={state.baseurlStore}/>
    </>
  }

  sectiondetail = <div className="pling-section-detail">
    {section &&
      <div className="pling-section-detail-left">
        <h2 className={showContent == 'overview' ? 'focused' : ''}><a onClick={() => showDetail('overview')}>Plings</a></h2>
        <h2 className={showContent == 'supporters' ? 'focused' : ''}><a onClick={() => showDetail('supporters')}>Supporters</a></h2>
        <h2 className={showContent == 'overview-category' ? 'focused' : ''}><a onClick={() => showDetail('overview-category')}>Categories</a></h2>
        <ul className="pling-section-detail-ul">{categories}</ul>
        <h2 className={showContent == 'recentplings' ? 'focused' : ''}><a onClick={() => showDetail('recentplings')}>Recent Plings</a></h2>
      </div>
    }
    <div className="pling-section-detail-middle">
      {detailContent}
    </div>
    <div className="pling-section-detail-right">
      {section &&
        <>
          <div className="btnSupporter">Become a Supporter</div>
          <Support baseUrlStore={state.baseurlStore} section={section}
            supporters={supporters}
          />
        </>
      }
    </div>
  </div>


  return (
    <>
      <Header supporters={supporters} section={section} />
      {sectioncontainer}
      {sectiondetail}
    </>

  )
}

export default AppSupporters

