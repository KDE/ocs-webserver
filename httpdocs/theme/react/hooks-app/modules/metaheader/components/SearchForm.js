import React, { useState ,useContext} from 'react';
import Autosuggest from 'react-autosuggest';
import {MetaheaderContext} from '../contexts/MetaheaderContext';

function renderSuggestion(suggestion) {
  return (
    <div className={suggestion.type + ' suggestionsContainer'}>
      <div>
        {suggestion.type == 'user' ? <img style={{ width: '50px', height: '50px', borderRadius: '999px' }} src={suggestion.image_small} ></img> : <img style={{ width: '50px', height: '50px' }} src={suggestion.image_small} ></img>}
      </div>

      <div className="description">
        {suggestion.type == 'project' ? (
          <>
            <span>{suggestion.title}</span>
            <span style={{ 'font-size': '11px', 'color': '#ccc','line-height':'15px' }}>{' by ' + suggestion.username}</span>
            <span style={{ 'font-size': '11px', 'color': '#ccc','line-height':'15px' }}>{suggestion.cat_title}</span>
            
          </>
        ) : (
            <span>{suggestion.username}</span>
          )}
      </div>
    </div>
  );
}

const renderInputComponent = inputProps => (
  <div className="react-autosuggest__inputContainer">
    <a onClick={inputProps.onSubmit}>
      <img className="react-autosuggest__icon" src={inputProps.baseUrlStore + "/theme/flatui/img/icon-search-input-2.png"} />
    </a>
    <input {...inputProps} />
  </div>
);

const SearchForm = () => {
  const {state} = useContext(MetaheaderContext);
  const [searchText, setSearchText] = useState('');
  const [isShow, setIsShow] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [value, setValue] = useState('');
  const [suggestions, setSuggestions] = useState([]);
  const [selected, setSelected] = useState();


  const loadSuggestions = value => {
    const inputLength = value.length;
    if (inputLength < 3) return;
    setIsLoading(true);
    let url = state.baseUrlStore + '/json/search/p/' + value;
    if (state.store) {
      url += '/s/' + state.store
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
    if (!selected) {
      window.location.href = state.searchbaseurl + value;
    } else {
      if (selected.type == 'project') {
      
        window.location.href = state.baseUrlStore + '/p/' + selected.project_id;
      } else {

        window.location.href = state.baseUrlStore + '/u/' + selected.username;
      }
    }
  }


  const getSuggestionValue = suggestion => {
    setSelected(suggestion);
    if (suggestion.type == 'project') {
      return suggestion.title;
    } else {
      return suggestion.username;
    }

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
    if (suggestion.type == 'project') {
      window.location.href = state.baseUrlStore + '/p/' + suggestion.project_id;
    } else {
      window.location.href = state.baseUrlStore + '/u/' + suggestion.username;
    }
  }

  const renderSectionTitle = section => {
    return (
      <strong>{section.title}</strong>
    );
  }

  const getSectionSuggestions = section => {
    return section.values;
  }

  const inputProps = {
    placeholder: "",
    value,
    onChange: onHandleChange,
    onSubmit: onSearchFormSubmit,
    baseUrlStore: state.baseUrlStore
  };

  return (
    <div id="site-header-search-form" className={isShow ? 'open' : ''}>
      <form id="search-form" onSubmit={onSearchFormSubmit}>
        <div className="autosuggest">
          <Autosuggest
            multiSection={true}
            suggestions={suggestions}
            onSuggestionsFetchRequested={onSuggestionsFetchRequested}
            onSuggestionsClearRequested={onSuggestionsClearRequested}
            shouldRenderSuggestions={shouldRenderSuggestions}
            onSuggestionSelected={onSuggestionSelected}
            getSuggestionValue={getSuggestionValue}
            renderSuggestion={renderSuggestion}
            inputProps={inputProps}
            renderInputComponent={renderInputComponent}
            renderSectionTitle={renderSectionTitle}
            getSectionSuggestions={getSectionSuggestions}

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

