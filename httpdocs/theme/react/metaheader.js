class MetaHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    return React.createElement(
      "ul",
      { className: "nav nav-pills meta-nav-top right" },
      React.createElement(
        "li",
        null,
        React.createElement(
          "a",
          { href: "/community" },
          " Community "
        ),
        " "
      ),
      React.createElement(
        "li",
        null,
        React.createElement(
          "a",
          { href: "<?=$url_forum?>", target: "_blank" },
          " Forum"
        ),
        " "
      ),
      React.createElement(
        "li",
        null,
        React.createElement(
          "a",
          { href: "<?=$url_blog?>", target: "_blank" },
          " Blog"
        ),
        " "
      ),
      React.createElement(
        "li",
        null,
        React.createElement(
          "a",
          { id: "plingList", href: "/plings", className: "popuppanel" },
          " What are Plings? "
        )
      ),
      React.createElement(
        "li",
        null,
        React.createElement(
          "a",
          { id: "ocsapiContent", href: "/partials/ocsapicontent.phtml", className: "popuppanel" },
          " API "
        ),
        " "
      ),
      React.createElement(
        "li",
        null,
        React.createElement(
          "a",
          { id: "aboutContent", href: "/partials/about.phtml", className: "popuppanel" },
          "About "
        )
      ),
      React.createElement(
        "li",
        { id: "user-context-dropdown-container", className: "metaheader-menu-item" },
        React.createElement(
          "div",
          { id: "user-dropdown" },
          React.createElement(
            "button",
            { className: "btn btn-default dropdown-toggle", type: "button", id: "dropdownMenu2", "data-toggle": "dropdown", "aria-haspopup": "true", "aria-expanded": "true" },
            React.createElement("span", { className: "glyphicon glyphicon-th" })
          ),
          React.createElement(
            "ul",
            { className: "dropdown-menu three-column dropdown-menu-right", "aria-labelledby": "dropdownMenu2" },
            React.createElement(
              "li",
              { id: "opendesktop-link-item" },
              React.createElement(
                "a",
                { href: "http://www.opendesktop.org" },
                React.createElement("div", { className: "icon" }),
                React.createElement(
                  "span",
                  null,
                  "OpenDesktop"
                )
              )
            ),
            React.createElement(
              "li",
              { id: "discourse-link-item" },
              React.createElement(
                "a",
                { href: "http://discourse.opendesktop.org/" },
                React.createElement("div", { className: "icon" }),
                React.createElement(
                  "span",
                  null,
                  "Discourse"
                )
              )
            ),
            React.createElement(
              "li",
              { id: "gitlab-link-item" },
              React.createElement(
                "a",
                { href: "https://about.gitlab.com/" },
                React.createElement("div", { className: "icon" }),
                React.createElement(
                  "span",
                  null,
                  "Gitlab"
                )
              )
            ),
            React.createElement(
              "li",
              { id: "opencode-link-item" },
              React.createElement(
                "a",
                { href: "https://www.opencode.net/" },
                React.createElement("div", { className: "icon" }),
                React.createElement(
                  "span",
                  null,
                  "OpenCode"
                )
              )
            )
          )
        )
      ),
      React.createElement(
        "li",
        { id: "user-dropdown-container", className: "metaheader-menu-item" },
        React.createElement(
          "div",
          { id: "user-dropdown" },
          React.createElement(
            "button",
            { className: "btn btn-default dropdown-toggle", type: "button", id: "dropdownMenu1", "data-toggle": "dropdown", "aria-haspopup": "true", "aria-expanded": "true" },
            React.createElement("img", { src: "<?=$profile_image_url?>" })
          ),
          React.createElement(
            "ul",
            { className: "dropdown-menu dropdown-menu-right", "aria-labelledby": "dropdownMenu1" },
            React.createElement(
              "li",
              { id: "user-info-menu-item" },
              React.createElement(
                "div",
                { id: "user-info-section" },
                React.createElement(
                  "div",
                  { className: "user-avatar" },
                  React.createElement(
                    "div",
                    { className: "no-avatar-user-letter" },
                    React.createElement("img", { src: "<?=$profile_image_url?>" }),
                    React.createElement(
                      "a",
                      { className: "change-profile-pic" },
                      "Change"
                    )
                  )
                ),
                React.createElement(
                  "div",
                  { className: "user-details" },
                  React.createElement(
                    "ul",
                    null,
                    React.createElement(
                      "li",
                      null,
                      React.createElement(
                        "b",
                        null,
                        "username"
                      )
                    ),
                    React.createElement(
                      "li",
                      null,
                      "$loginMember->mail"
                    ),
                    React.createElement("li", null),
                    React.createElement(
                      "li",
                      null,
                      React.createElement(
                        "a",
                        null,
                        "Profile"
                      ),
                      " - ",
                      React.createElement(
                        "a",
                        null,
                        "Privacy"
                      )
                    ),
                    React.createElement(
                      "li",
                      null,
                      React.createElement(
                        "button",
                        { className: "btn btn-default btn-blue" },
                        "Account"
                      )
                    )
                  )
                )
              )
            ),
            React.createElement("li", { id: "main-seperator", role: "separator", className: "divider" }),
            React.createElement(
              "li",
              { className: "buttons" },
              React.createElement(
                "button",
                { className: "btn btn-default btn-metaheader" },
                "Add Account"
              ),
              React.createElement(
                "button",
                { className: "btn btn-default pull-right btn-metaheader" },
                "Sign Up"
              )
            )
          )
        )
      ),
      React.createElement(
        "li",
        { id: "user-signin-button", className: "metaheader-menu-item" },
        React.createElement(
          "button",
          { className: "btn btn-default btn-blue" },
          React.createElement(
            "a",
            { href: "/login" },
            "Login"
          )
        )
      )
    );
  }
}

ReactDOM.render(React.createElement(MetaHeader, null), document.getElementById('metaheader-menu'));
