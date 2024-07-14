<!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }} API</title>
    <link rel="stylesheet" type="text/css" href="/swagger/swagger-ui.css" >
    <link rel="icon" type="image/png" href="/swagger/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="/swagger/favicon-16x16.png" sizes="16x16" />
    <style>
      html
      {
        box-sizing: border-box;
        overflow: -moz-scrollbars-vertical;
        overflow-y: scroll;
      }

      *,
      *:before,
      *:after
      {
        box-sizing: inherit;
      }

      body
      {
        margin:0;
        background: #fafafa;
        font-size:14px;
      }

      input[type=text] {    
        margin: 0 0 0 0;
        height: calc(1.9rem);
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
        font-weight: 400;
        line-height: 1.5;
        border-radius: 0rem;
      }
    </style>
  </head>

  <body>
    <div id="swagger-ui"></div>

    <script src="/swagger/swagger-ui-bundle.js"> </script>
    <script src="/swagger/swagger-ui-standalone-preset.js"> </script>
    <script>
    window.onload = function() {
      // Begin Swagger UI call region
      const ui = SwaggerUIBundle({
        url: "/swagger/api{{$div? ucfirst($div):''}}.json",
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [
          SwaggerUIBundle.presets.apis,
          SwaggerUIStandalonePreset
        ],
        plugins: [
          SwaggerUIBundle.plugins.DownloadUrl
        ],
        layout: "BaseLayout"		// StandaloneLayout
      })
      // End Swagger UI call region

      window.ui = ui
    }
  </script>
  </body>
</html>
