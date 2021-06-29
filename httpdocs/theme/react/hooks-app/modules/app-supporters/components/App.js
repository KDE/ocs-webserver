import React from 'react'
import {BrowserRouter as Router,Switch,Route} from 'react-router-dom';
import Header from './Header';
const App = () => {
    return (
        <Router>
            <Switch>
                <Route path="/supporters" component={Header} /> 
                
            </Switch>
        </Router>
    )
}

export default App
