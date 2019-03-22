import React from 'react';
import Autosuggest from 'react-autosuggest';
import UserAutoCompleteTabs from './UserAutoCompleteTabs';
function getSuggestionValue(suggestion) {
  return suggestion.username;
}

function renderSuggestion(suggestion) {
  return (
    <span>{suggestion.username}</span>
  );
}

class UserAutoCompleteInput extends React.Component {
  constructor() {
    super();

    this.state = {
      value: '',
      suggestions: [],
      showTabs:false,
      userSelected:[],
      isLoading: false
    };

    this.onChange = this.onChange.bind(this);
    this.loadSuggestions = this.loadSuggestions.bind(this);
    this.onSuggestionsFetchRequested = this.onSuggestionsFetchRequested.bind(this);
    this.onSuggestionsClearRequested = this.onSuggestionsClearRequested.bind(this);
    this.shouldRenderSuggestions = this.shouldRenderSuggestions.bind(this);
    this.onSuggestionSelected = this.onSuggestionSelected.bind(this);
  }

  loadSuggestions(value) {
    const inputLength = value.length;
    if(inputLength<3) return;
      this.setState({
        isLoading: true
      });
     let url = this.props.baseUrl+'/membersetting/searchmember?username='+value;
     fetch(url,{
                mode: 'cors',
                credentials: 'include'
                })
      .then(response => response.json())
      .then(data => {
        if(data)
        {
          this.setState({suggestions:data,isLoading:false});
        }
      });
  }

  onChange(event, { newValue, method }){
    this.setState({
      value: newValue,
      showTabs: false,
    });
  }

  shouldRenderSuggestions(value) {
    return value.trim().length > 2;
  }

  onSuggestionsFetchRequested({ value }){
    this.loadSuggestions(value);
  }

  onSuggestionsClearRequested(){
    this.setState({
      suggestions: []
    });
  }

  onSuggestionSelected(event, { suggestion, suggestionValue, suggestionIndex, sectionIndex, method })
  {
    this.setState({
      showTabs: true,
      userSelected:suggestion
    });
  }

  render() {
    const { value, suggestions, isLoading } = this.state;
    const inputProps = {
      placeholder: "Type to search member min.3 chars",
      value,
      onChange: this.onChange

    };
    const status = (isLoading ? 'Loading...' : '');

    let contentTabs;
    if(this.state.showTabs && this.state.userSelected)
    {
      contentTabs = (<UserAutoCompleteTabs
                      user={this.state.userSelected}
                      baseUrl={this.props.baseUrl}
                      />
                    )
    }

    return (
      <div>
      <div className="autosuggest">

          <Autosuggest
          suggestions={suggestions}
          onSuggestionsFetchRequested={this.onSuggestionsFetchRequested}
          onSuggestionsClearRequested={this.onSuggestionsClearRequested}
          shouldRenderSuggestions={this.shouldRenderSuggestions}
          onSuggestionSelected ={this.onSuggestionSelected}
          getSuggestionValue={getSuggestionValue}
          renderSuggestion={renderSuggestion}
          inputProps={inputProps} />

          <div className="react-autosuggest_status">
            {status}
          </div>
      </div>
      <div >
        {contentTabs}
      </div>
      </div>
    );
  }
}

export default UserAutoCompleteInput;
