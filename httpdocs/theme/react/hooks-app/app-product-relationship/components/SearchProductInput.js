import React, { useState } from 'react';
import Autosuggest from 'react-autosuggest';

function renderSuggestion(suggestion) {
  return (
    <div className={'suggestionsContainer'}>
      <div>
        {<img style={{ width: '50px', height: '50px' }} src={suggestion.image_small} ></img>}
      </div>
      <div className="description">        
          <>
            <span>{suggestion.title}</span>
            <span style={{ fontSize: '11px', color: '#ccc',lineHeight:'15px' }}>{' by ' + suggestion.username}</span>
            <span style={{ fontSize: '11px', 'color': '#ccc',lineHeight:'15px' }}>{suggestion.cat_title}</span>            
          </>        
      </div>
    </div>
  );
}

const renderInputComponent = inputProps => (
  <div className="react-autosuggest__inputContainer" >
    <a onClick={inputProps.onSubmit}>
      <img className="react-autosuggest__icon" src={inputProps.baseUrlStore + "/theme/flatui/img/icon-search-input-2.png"} />
    </a>
    <input {...inputProps} />
  </div>
);

const SearchProductInput = (props) => {  
  const [searchText, setSearchText] = useState('');
  const [isShow, setIsShow] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [value, setValue] = useState('');
  const [suggestions, setSuggestions] = useState([]);
  const [selected, setSelected] = useState({});
  
  const [projectCategoryId, setProjectCategoryId] = useState(props.product.project_category_id);


  const loadSuggestions = value => {
    const inputLength = value.length;
    if (inputLength < 3) return;
    setIsLoading(true);
    let url = props.baseUrlStore + '/json/searchp/p/' + value+'/c/'+projectCategoryId;
    if (props.store) {
      url += '/s/' + props.store
    }
    fetch(url, {
      mode: 'cors',
      credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
      setSuggestions(data);
      setIsLoading(false);
    });

  }


  const onSearchFormSubmit = e => {
    e.preventDefault();
    
  }


  const getSuggestionValue = suggestion => {
    setSelected(suggestion);
    //setProject_id(suggestion.project_id);
    props.setProjectId(suggestion.project_id);
    return suggestion.title;
  }


  const onHandleChange = (event, { newValue, method }) => {
    setValue(newValue);
  }

  const shouldRenderSuggestions = value => {
    return value.trim().length > 2;
  }

  const onSuggestionsFetchRequested = ({ value }) => {
    loadSuggestions(value);
  }

  const onSuggestionsClearRequested = () => {
    setSuggestions([]);
  }

  const onSuggestionSelected = (event, { suggestion, suggestionValue, suggestionIndex, sectionIndex, method }) => {    
    setSelected(suggestion);
    //setProject_id(suggestion.project_id);
    props.setProjectId(suggestion.project_id);
  }

 
  const inputProps = {
    placeholder: "Search...",
    value,
    onChange: onHandleChange,
    onSubmit: onSearchFormSubmit,
    baseUrlStore: props.baseUrlStore
  };

  return (

        <div className="autosuggest">
           <div className="row">
             <div className="col-lg-12"><h6>ID of the Original on opendesktop:</h6> </div>
             <div className="col-lg-12">               
              <div style={{display:'flex'}}>
                  <div>
                  <input required name="project_id" id="project_id" value={props.project_id} style={{width:'100px',marginRight:'10px'}}></input>
                  </div>
                  <div>
                  <Autosuggest               
                      suggestions={suggestions}
                      onSuggestionsFetchRequested={onSuggestionsFetchRequested}
                      onSuggestionsClearRequested={onSuggestionsClearRequested}
                      shouldRenderSuggestions={shouldRenderSuggestions}
                      onSuggestionSelected={onSuggestionSelected}
                      getSuggestionValue={getSuggestionValue}
                      renderSuggestion={renderSuggestion}
                      inputProps={inputProps}
                      renderInputComponent={renderInputComponent}                            
                    />
                  </div>                
              </div>   
                        
              </div>
          </div>
          
          <div className="row">            
             <div className="col-lg-12">
              {selected && selected.project_id &&
              <div className='suggestionsContainer'>
                <div>
                  <img style={{ width: '50px', height: '50px' }} src={selected.image_small} ></img>
                </div>
                <div className="description">        
                    <>
                      <span>{selected.title}</span>
                      <span style={{ fontSize: '11px', color: '#ccc',lineHeight:'15px' }}>{' by ' + selected.username}</span>
                      <span style={{ fontSize: '11px', color: '#ccc',lineHeight:'15px' }}>{selected.cat_title}</span>            
                    </>        
                </div>
              </div>
              }
             </div>
          </div>
          
         
        
        </div>
     
  )
}
export default SearchProductInput

