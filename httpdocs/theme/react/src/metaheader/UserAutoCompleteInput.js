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

const renderInputComponent = inputProps => (
  <div className="react-autosuggest__inputContainer">
    <img className="react-autosuggest__icon" src="https://cdn4.iconfinder.com/data/icons/small-n-flat/24/user-alt-128.png" />
    <input {...inputProps} />
  </div>
);

class UserAutoCompleteInput extends React.Component {
  constructor() {
    super();

    this.state = {
      value: '',
      suggestions: [],
      showTabs:false,
      userSelected:[],
      isLoading: false,
      userinfo:[],
      odComments:[]
    };

    this.onChange = this.onChange.bind(this);
    this.loadSuggestions = this.loadSuggestions.bind(this);
    this.onSuggestionsFetchRequested = this.onSuggestionsFetchRequested.bind(this);
    this.onSuggestionsClearRequested = this.onSuggestionsClearRequested.bind(this);
    this.shouldRenderSuggestions = this.shouldRenderSuggestions.bind(this);
    this.onSuggestionSelected = this.onSuggestionSelected.bind(this);

    this.getUserInfo = this.getUserInfo.bind(this);
    this.getUserOdComments = this.getUserOdComments.bind(this);
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
    this.setState({value:newValue});
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

  getUserInfo(member_id){
    let url = `${this.props.baseUrl}/membersetting/userinfo?member_id=${member_id}`;
     fetch(url,{
                mode: 'cors',
                credentials: 'include'
                })
      .then(response => response.json())
      .then(data => {
        this.setState({userinfo:data},function(){
              this.getUserOdComments(member_id);
           });
      });
  }

  getUserOdComments(member_id){
      let url = `${this.props.baseUrl}/membersetting/memberjson?member_id=${member_id}`;
       fetch(url,{
                  mode: 'cors',
                  credentials: 'include'
                  })
        .then(response => response.json())
        .then(data => {
          this.setState({odComments:data.commentsOpendeskop,showTabs: true},function(){
               //this.getUserForumComments();
             });
        });
  }


  onSuggestionSelected(event, { suggestion, suggestionValue, suggestionIndex, sectionIndex, method })
  {
    this.getUserInfo(suggestion.member_id);
    this.setState({
      userSelected:suggestion
    });
  }

  render() {
    const { value, suggestions, isLoading } = this.state;
    const inputProps = {
      placeholder: "Type to search min.3 chars",
      value,
      onChange: this.onChange

    };
    const status = (isLoading ? 'Loading...' : '');

    let contentTabs;
    if(this.state.showTabs)
    {
      contentTabs = <UserAutoCompleteTabs
                      user={this.state.userSelected}
                      userinfo={this.state.userinfo}
                      baseUrl={this.props.baseUrl}
                      odComments={this.state.odComments}
                      />
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
          inputProps={inputProps}
          renderInputComponent={renderInputComponent}
          
          />

          <div className="react-autosuggest_status">
            {status}
          </div>
      </div>
      <div>
        {contentTabs}
        </div>
      </div>
    );
  }
}

export default UserAutoCompleteInput;
