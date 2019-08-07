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
            <a href={props.baseUrlStore+"/backend/comments/"} >
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
        </ul>
  );
}

export default AdminlLinksContainer;
