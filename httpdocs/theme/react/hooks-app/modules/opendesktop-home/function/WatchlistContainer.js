import React from 'react';
function WatchlistContainer(props)
{
  return (
        <ul id="admin-links-container">
          <li id="product-moderation-link-item">
            <a href={props.baseUrlStore+"/watchlist-productmoderation"} >
              <span>Product moderation</span>
            </a>
          </li>
          <li id="spam-link-item">
            <a href={props.baseUrlStore+"/spam/"} >
              <span>Spam</span>
            </a>
          </li>
          <li id="spam-comment-link-item">
            <a href={props.baseUrlStore+"/spam/comments/"} >
              <span>Spam comments</span>
            </a>
          </li>

          <li id="clones-link-item">
            <a href={props.baseUrlStore+"/clones/"} >
              <span>Clones</span>
            </a>
          </li>
          <li id="clones-link-item">
            <a href={props.baseUrlStore+"/mods/"} >
              <span>Mods</span>
            </a>
          </li>

          <li id="new-products-link-item">
            <a href={props.baseUrlStore+"/watchlist-newproducts"} >
              <span>Most New Products</span>
            </a>
          </li>

          <li id="product-files-link-item">
            <a href={props.baseUrlStore+"/watchlist-products-10-files"} >
              <span>New Products with min.10 files</span>
            </a>
          </li>

          <li id="same-paypal-link-item">
            <a href={props.baseUrlStore+"/watchlist-samepaypal"} >
              <span>Same paypal check</span>
            </a>
          </li>

          <li id="same-paypal-link-item">
            <a href={props.baseUrlStore+"/watchlist-md5sum-duplicated"} >
              <span>File uploaded md5sum duplicated</span>
            </a>
          </li>

          <li id="unpublished-link-item">
            <a href={props.baseUrlStore+"/watchlist-unpublishedproduct"} >
              <span>List of unpublished products</span>
            </a>
          </li>
          <li id="list-newproduct-link-item">
            <a href={props.baseUrlStore+"/watchlist-newproduct-2-month"} >
              <span>List of new products (published or unpublished) less than 2 months </span>
            </a>
          </li>

          <li id="list-newproduct-link-item">
            <a href={props.baseUrlStore+"/misuse"} >
              <span>List of Misuse-Reports</span>
            </a>
          </li>

          <li id="list-deprecated-link-item">
            <a href={props.baseUrlStore+"/watchlist-products-deprecated"} >
              <span>List of Deprecated Products</span>
            </a>
          </li>
      
        </ul>
  );
}

export default WatchlistContainer;
