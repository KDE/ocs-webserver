import React, { useState } from 'react';

const SearchForum = (props) => {
    const [searchText, setSearchText] = useState('');

    const onSearchTextChange = e => {
        setSearchText(e.target.value);
    }
    const onSearchFormSubmit = e => {
        e.preventDefault();
        window.location.href = props.searchBaseUrl + searchText;
    }


    return (
        <div id="site-header-search-form">
            <form id="search-form" onSubmit={onSearchFormSubmit}>
                <input onChange={onSearchTextChange} value={searchText} type="text" name="projectSearchText" />
                <a onClick={onSearchFormSubmit}></a>
            </form>
        </div>
    )
}
export default SearchForum
