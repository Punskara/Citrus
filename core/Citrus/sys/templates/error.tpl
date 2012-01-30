<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Ouch !</title>
    <style type="text/css">
        body {
        	background: 	    #fff;
        	font-family: 		Verdana, Arial, Helvetica,sans-serif;
        	font-size: 			9pt;
        	margin:				0;
        }
        .page {
            margin:             0 auto;
            width:              90%;
        }
        .message {
            background:         #ddd;
            font-size:          12pt;
            padding:            10px;
            -moz-border-radius: 8px;
            -webkit-border-radius: 8px
        }
        li {
            margin:             5px 0;
        }
        li i {
            background:         #ddd;
        }
    </style>
</head>
<body class="error">
    <div class="page">
        <p>Ouch ! There's something Citrus didn't like…</p>
        {citrus_error}
    </div>
</body>
</html>