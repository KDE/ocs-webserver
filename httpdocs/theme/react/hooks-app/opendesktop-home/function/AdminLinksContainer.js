import React from 'react';
function AdminlLinksContainer(props)
{
  return (
        <ul id="admin-links-container">
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

          <li id="new-products-link-item">
            <a href={props.baseUrlStore+"/newproducts/"} >
              <span>Most New Products</span>
            </a>
          </li>

          <li id="product-files-link-item">
            <a href={props.baseUrlStore+"/spam/product"} >
              <span>New Products with min.10 files</span>
            </a>
          </li>

          <li id="same-paypal-link-item">
            <a href={props.baseUrlStore+"/samepaypal"} >
              <span>Same paypal check</span>
            </a>
          </li>

          <li id="unpublished-link-item">
            <a href={props.baseUrlStore+"/spam/unpublishedproduct"} >
              <span>List of unpublished products</span>
            </a>
          </li>
          <li id="list-newproduct-link-item">
            <a href={props.baseUrlStore+"/spam/newproduct"} >
              <span>List of new products (published or unpublished) less than 2 months </span>
            </a>
          </li>

          <li id="backend-link-item">
            <a href={props.baseUrlStore+"/backend"} >
              <span>Backend</span>
            </a>
          </li>

          <li id="stati-link-item">
            <a href={props.baseUrlStore+"/statistics"} >
              <span>Statistics</span>
            </a>
          </li>

          <li id="piwik-link-item">
            <a href={"https://piwik.opendesktop.org"} >
              <span>Piwik</span>
            </a>
          </li>

          <li id="Nagios-link-item">
            <a href={"http://nagios.opendesktop.org/nagios"} >
              <span>Nagios</span>
            </a>
          </li>
        </ul>
  );
}

export default AdminlLinksContainer;
