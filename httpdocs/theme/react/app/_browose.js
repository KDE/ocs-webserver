class HomePageTemplateTwo extends React.Component {
  render(){
    return (
      <div id="hompage-version-two">
        <FeaturedSlideshowWrapper/>
        <div id="top-products" className="hp-section">
          top 4 products with pic and info
        </div>
        <div id="other-products" className="hp-section">
          another top 6 products with pic and info
        </div>
        <div id="latest-products" className="hp-section">
          3 columns with 3 products each
        </div>
      </div>
    )
  }
}
