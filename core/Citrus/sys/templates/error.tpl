<style type="text/css">
    .citrus-error {
        font-family: Arial, verdana, sans-serif;
        background:         #f5f5f5;
        box-shadow: 0 0 5px rgba(0,0,0,.2) inset;
        border-radius:      4px;
        margin:             10px auto;
        /*width:              800px;*/
        padding: 20px;
    }
    .citrus-error pre {
        color: red;
    }
    .citrus-error .message {
        background:         #fff;
        font-size:          12pt;
        padding:            10px;
        border-radius:      8px;
    }
    .citrus-error li {
        margin:             5px 0;
    }
    .citrus-error li i {
        background:         #ddd;
    }
</style>
<div class="citrus-error">
    <p>Ouch ! There's something Citrus didn't like…</p>
    {citrus_error}
</div>