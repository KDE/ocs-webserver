import React, { useState } from 'react';
import Autosuggest from 'react-autosuggest';

function getSuggestionValue(suggestion) {
  return suggestion.title;
}

function renderSuggestion(suggestion) {
  return (
    <div className="suggestionsContainer">     
      <div>
        <img src={suggestion.image_small} style={{width:'50px',height:'50px'}}></img>      
      </div> 
      <div className="description">
        <span>{suggestion.title}</span>        
        <span className="small">{' by '+suggestion.username}</span>
      </div>            
    </div>    
  );
}

const renderInputComponent = inputProps => (
  <div className="react-autosuggest__inputContainer">
    <a onClick={inputProps.onSubmit}>
      <img className="react-autosuggest__icon" src={inputProps.baseUrlStore+"/theme/flatui/img/icon-search-input-2.png"} />
    </a>
    <input {...inputProps} />
  </div>
);

const SearchForm = (props) => {
  const [searchText, setSearchText] = useState('');
  const [isShow, setIsShow] = useState(false);
  const [products, setProducts] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  const [value, setValue] = useState('');
  const [suggestions, setSuggestions] = useState([]);
  const [selected, setSelected] = useState();
  
  // const onSearchTextChange = e => {
  //   setSearchText(e.target.value);
  //   console.log('setonSearchTextChange...');
  //   if (e.target.value && e.target.value.length >=3) {      
  //     setIsloading(true);
  //     setIsShow(true);            
  //     let url = props.baseUrlStore+'/membersetting/searchproducts/p/'+searchText;
  //     console.log(url);
  //     fetch(url,{
  //       mode: 'cors',
  //       credentials: 'include'
  //       })
  //     .then(response => response.json())
  //     .then(data => {          
  //       setProducts(data);        
  //       setIsloading(false);
  //     });
  //   } else {
  //     setIsShow(false);
  //   }

  // }
  const onSearchFormSubmit = e => {
    e.preventDefault();
    window.location.href = props.searchBaseUrl + value;
  }


  const loadSuggestions = value=> {
    const inputLength = value.length;
    if(inputLength<3) return;
    setIsLoading(true);      
     let url = props.baseUrlStore+'/membersetting/searchproducts/p/'+value;
     fetch(url,{
                mode: 'cors',
                credentials: 'include'
                })
      .then(response => response.json())
      .then(data => {       
          setSuggestions(data);
          setIsLoading(false);                  
      });
  }

  const onHandleChange = (event, { newValue, method })=>{
    setValue(newValue);    
  }

  const shouldRenderSuggestions = value =>{    
    return value.trim().length > 2;
  }

  const onSuggestionsFetchRequested=({ value })=>{
    loadSuggestions(value);
  }

  const onSuggestionsClearRequested=()=>{
    setSuggestions([]);    
  }

  // getUserInfo(member_id){
  //   let url = `${this.props.baseUrl}/membersetting/userinfo?member_id=${member_id}`;
  //    fetch(url,{
  //               mode: 'cors',
  //               credentials: 'include'
  //               })
  //     .then(response => response.json())
  //     .then(data => {
  //       this.setState({userinfo:data},function(){
  //             //  this.getUserOdComments(member_id);
  //          });
  //     });
  // }

  const onSuggestionSelected = (event, { suggestion, suggestionValue, suggestionIndex, sectionIndex, method })=>{
    // this.getUserInfo(suggestion.member_id);    
    setSelected(suggestion);
    window.location.href = props.baseUrlStore + '/p/'+suggestion.member_id;
  }


  const inputProps = {
    placeholder: "Type to search min.3 chars",
    value,
    onChange: onHandleChange,
    onSubmit:onSearchFormSubmit,
    baseUrlStore: props.baseUrlStore
  };

  return (
    <div id="site-header-search-form" className={isShow ? 'open' : ''}>
      <form id="search-form" onSubmit={onSearchFormSubmit}>
      <div className="autosuggest">
          <Autosuggest
          suggestions={suggestions}
          onSuggestionsFetchRequested={onSuggestionsFetchRequested}
          onSuggestionsClearRequested={onSuggestionsClearRequested}
          shouldRenderSuggestions={shouldRenderSuggestions}
          onSuggestionSelected ={onSuggestionSelected}
          getSuggestionValue={getSuggestionValue}
          renderSuggestion={renderSuggestion}
          inputProps={inputProps}
          renderInputComponent={renderInputComponent}
          
          />

          <div className="react-autosuggest_status">
            {status}
          </div>
      </div>   
      </form>
    </div>
  )
}
export default SearchForm

