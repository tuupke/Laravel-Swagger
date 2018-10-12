<!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swagger UI</title>
    <link rel="stylesheet" type="text/css" href="/vendor/swagger/swagger-ui.css" >
    <link rel="icon" type="image/png" href="/vendor/swagger/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="/vendor/swagger/favicon-16x16.png" sizes="16x16" />
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
        }
    </style>
</head>

<body>
<div id="swagger-ui"></div>

<script src="/vendor/swagger/swagger-ui-bundle.js"> </script>
<script src="/vendor/swagger/swagger-ui-standalone-preset.js"> </script>
<script>
    window.onload = function() {

        // Build a system
        const ui = SwaggerUIBundle({
            url: '{{ $api_docs or "http://petstore.swagger.io/v2/swagger.json"}}',
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],
            docExpansion: "none",
            layout: "StandaloneLayout",
            validatorUrl: null,
            displayRequestDuration: true
        })

        window.ui = ui
    }
</script>
</body>
</html>
