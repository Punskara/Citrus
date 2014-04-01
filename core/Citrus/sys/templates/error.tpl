<style type="text/css">
    .citrus-error {
        font-family: Arial, verdana, sans-serif;
        background:         #f5f5f5;
        box-shadow: 0 0 5px rgba(0,0,0,.2) inset;
        border-radius:      4px;
        margin:             10px auto;
        /*position: absolute;*/
        width:              80%;
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
    .citrus-error-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,.7);
        z-index: 9999;
    }
</style>
<div class="citrus-error-overlay">
    <div class="citrus-error">
        <p>Ouch ! There's something Citrus didn't like…</p>
        {citrus_error}
    </div>
</div>