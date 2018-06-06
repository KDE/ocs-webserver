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

class FeaturedSlideshow extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    $('.shape').shape();
  }

  onFlipButtonClick(){
    $('.shape').shape('flip down');
  }

  componentWillReceiveProps(nextProps) {
    console.log(nextProps);
  }

  render(){
    return (
      <div id="featured-sideshow" className="hp-section">
        <a onClick={this.onFlipButtonClick}>flip shape</a>
        <div className="ui contaier">
          <div className="ui cube shape">
            <div className="sides">
              <div className="side">
                <div className="content">
                  <div className="center">
                    1
                  </div>
                </div>
              </div>
              <div className="side">
                <div className="content">
                  <div className="center">
                    2
                  </div>
                </div>
              </div>
              <div className="side">
                <div className="content">
                  <div className="center">
                    3
                  </div>
                </div>
              </div>
              <div className="side active">
                <div className="content">
                  <div className="center">
                    4
                  </div>
                </div>
              </div>
              <div className="side">
                <div className="content">
                  <div className="center">
                    5
                  </div>
                </div>
              </div>
              <div className="side">
                <div className="content">
                  <div className="center">
                    6
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    )
  }
}

const mapStateToFeaturedSlideshowProps = (state) => {
  const products = state.products;
  return {
    products
  }
}

const mapDispatchToFeaturedSlideshowProps = (dispatch) => {
  return {
    dispatch
  }
}

const FeaturedSlideshowWrapper = ReactRedux.connect(
  mapStateToFeaturedSlideshowProps,
  mapDispatchToFeaturedSlideshowProps
)(FeaturedSlideshow);
