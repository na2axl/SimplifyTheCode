<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Error</title>
    <style type="text/css">
        ::selection {
            background-color: #E13300;
            color: white;
        }

        ::-moz-selection {
            background-color: #E13300;
            color: white;
        }

        body,
        textarea,
        select,
        input {
            font-family: Segoe UI, Droid Sans, DejaVu Sans, sans-serif;
            font-size: 12pt;
            color: #555;
            line-height: 1.5em;
            font-weight: 300;
        }

        body {
            padding: 2.5em;
            background-color: #333;
        }

        a {
            cursor: pointer;
            color: #06c;
            text-decoration: none;
        }

        h1 {
            color: #444;
            background-color: #fff;
            border-bottom: 1px solid #333;
            font-size: 19px;
            font-weight: normal;
            margin: 0 0 14px 0;
            padding: 14px 15px 10px 15px;
        }

        code {
            font-family: Consolas, Monaco, Courier New, Courier, monospace;
            font-size: 12px;
            background-color: #f9f9f9;
            border: 1px solid #333;
            color: #002166;
            display: block;
            margin: 14px 0 14px 0;
            padding: 12px 10px 12px 10px;
        }

        #container {
            margin: 10px;
            border: 1px solid #333;
            box-shadow: 0 0 8px #333;
            background-color: #fff;
        }

        p {
            margin: 12px 15px 12px 15px;
        }

        p.mess {
            margin-left: 30px;
        }
    </style>
</head>

<body>
    <div id="container">
        <h1>A PHP Error was encountered</h1>
        <p><u><b>Severity:</u></b> {$severity}</p>
        <p><u><b>Message:</u></b> {$message}</p>
        <p><u><b>Filename:</u></b> {$filepath}</p>
        <p><u><b>Line Number:</u></b> {$line}</p>
        {if ($backtrace === TRUE)}
            <p><u><b>Backtrace:</u></b></p>
            {foreach debug_backtrace() as $error}
                {if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0)}
                    <p style="margin-left:30px">
                        <u>File:</u> {$error['file']}<br>
                        <u>Line:</u> {$error['line']}<br>
                        <u>Function:</u> {$error['function']}
                    </p>
                {/if}
            {/foreach}
        {/if}
    </div>
</body>

</html>