import React from 'react';
import Drawer from '@material-ui/core/Drawer';
import AppBar from '@material-ui/core/AppBar';
import Button from '@material-ui/core/Button';

class DrawerOpenRight extends React.Component {

  constructor(props) {
    super(props);
    this.state = {open: false};
    this.handleToggle=this.handleToggle.bind(this);
  }

  handleToggle(){
    this.setState({open: !this.state.open});
  }

  render() {
    return (
      <div>
        <Button
          label="Toggle Drawer"
          onClick={this.handleToggle}
        />
        <Drawer width={200} openSecondary={true} open={this.state.open} >
          <AppBar title="AppBar" />
        </Drawer>
      </div>
    );
  }
}

export default DrawerOpenRight;
