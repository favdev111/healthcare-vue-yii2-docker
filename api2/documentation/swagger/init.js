var swaggerDataString = localStorage.getItem('swaggerData');
var swaggerData = {};

if (swaggerDataString) {
  swaggerData = JSON.parse(swaggerDataString);
}

function saveSwaggerData() {
  localStorage.setItem('swaggerData', JSON.stringify(swaggerData));
}

function onChange(event, key) {
  var value = event.target.value;
  if (value) {
    swaggerData[key] = value;
  } else if (swaggerData[key]) {
    delete swaggerData[key];
  }
  saveSwaggerData();
}

window.onload = function () {
  var mockFetch = window.fetch;
  window.fetch = function() {
    return mockFetch.apply(this, arguments).then(function (response) {
      if(
        response.url.endsWith('/accounts/signin')
        && response.status === 200
        && response.type !== 'cors'
      ) {
        response.json().then(function (data) {
          swaggerData.accessToken = data.data.accessToken;
          setAccessToken(swaggerData.accessToken);
          saveSwaggerData();
        });
      }

      return response;
    });
  };

  function findReactElement(node) {
    for (var key in node) {
      if (key.startsWith("__reactInternalInstance$")) {
        return node[key]._currentElement._owner._instance;
      }
    }
    return null;
  }

  // Build a system
  var ui = SwaggerUIBundle({
    url: "openapi.yaml",
    dom_id: '#swagger-ui',
    deepLinking: true,
    apisSorter: "alpha",
    onComplete: function() {
      // Add blocks X-Device-Token, X-Platform
      var op = document.querySelector('#swagger-ui .swagger-ui .scheme-container');
      op.insertAdjacentHTML(
        'beforebegin',
        `<div class="scheme-container">
                <section class="wrapper block col-12">
                  <div style="margin-bottom: 20px;">
                    <label>Device token (X-Device-Token)<span style="color: red;">&nbsp;*</span>:</label>
                    <section><input id="deviceToken" type="text" onchange="onChange(event, \\'deviceToken\\')" /></section>
                    <label>Unique token of the current device (or browser). Used to manage separate tokens for each device.</label>
                  </div>

                  <div>
                    <label>Platform (X-Platform)<span style="color: red;">&nbsp;*</span>:</label>
                    <section><select id="platform" onchange="return onChange(event, \\'platform\\')"><option>ios</option><option>android</option><option>web</option><option>windows</option><option>osx</option></select></section>
                    <label>Platform is used to determine which part of the application is connected to the API. Required for internal processes.</label>
                  </div>
                </section>
              </div>`
      );

      if (swaggerData.deviceToken) {
        document.getElementById('deviceToken').value = swaggerData.deviceToken;
      }

      if (swaggerData.platform) {
        document.getElementById('platform').value = swaggerData.deviceToken;
      }

      if (swaggerData.accessToken) {
        setAccessToken(swaggerData.accessToken);
      }
    },
    requestInterceptor: function(req) {
      var deviceToken = document.getElementById('deviceToken');
      var platform = document.getElementById('platform');

      if (deviceToken && deviceToken.value) {
        req.headers['X-Device-Token'] = deviceToken.value;
      } else {
        deviceToken && deviceToken.scrollIntoView(true);
      }

      if (platform && platform.value) {
        req.headers['X-Platform'] = platform.value;
      } else {
        platform && platform.scrollIntoView(true);
      }

      return req;
    },
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
    securityDefinitions: {
      "Bearer": {
        "type": "apiKey",
        "name": "Authorization",
        "in": "header"
      }
    },
    layout: "StandaloneLayout"
  });

  function authorizeRequest(email, password) {
    var _headers = Object.assign({
      "Accept": "application/json, text/plain, */*",
      "Access-Control-Allow-Origin": "*",
      "Content-Type": "application/json"
    });

    fetch(
      '/api/v1/accounts/signin',
      {
        method: 'post',
        headers: _headers,
        mode: 'cors',
        body: JSON.stringify({
          email: email,
          password: password
        })
      }
    )
      .then(function (response) {
        response.json().then(function (data) {
          setAccessToken(data.token);
        });
      })
      .catch(function () {});
  }

  function setAccessToken(token) {
    ui.authActions.authorize({
      Bearer: {
        name: "Bearer",
        schema: {
          type: "apiKey",
          in: "header",
          name: "Authorization"
        },
        value: 'Bearer ' + token
      }
    });
  }

  function getQueryVariables() {
    var query = window.location.search.substring(1);
    var vars = query.split('&');
    var params = {};
    for (var i = 0; i < vars.length; i++) {
      var pair = vars[i].split('=');
      params[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
    }

    return params;
  }

  var queryParams = getQueryVariables();
  if (queryParams.login && queryParams.password) {
    authorizeRequest(queryParams.login, queryParams.password);
  }

  window.ui = ui
}
