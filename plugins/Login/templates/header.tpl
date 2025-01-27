<!DOCTYPE html>
<!--[if lt IE 9 ]><html class="old-ie"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html><!--<![endif]-->
<head>
    <title>{if !$isCustomLogo}Piwik &rsaquo; {/if}{'Login_LogIn'|translate}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="plugins/CoreHome/templates/images/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="plugins/Login/templates/login.css" />
    <meta name="description" content="{'General_OpenSourceWebAnalytics'|translate|escape}" />
    <!--[if lt IE 9]>
    <script src="libs/html5shiv/html5shiv.js"></script>
    <![endif]-->
    <script type="text/javascript" src="libs/jquery/jquery.js"></script>
    <script type="text/javascript" src="libs/jquery/jquery.placeholder.js"></script>
    {if isset($forceSslLogin) && $forceSslLogin}
        {literal}
            <script type="text/javascript">
                if(window.location.protocol !== 'https:') {
                    var url = window.location.toString();
                    url = url.replace(/^http:/, 'https:');
                    window.location.replace(url);
                }
            </script>
        {/literal}
    {/if}
    {literal}
        <script type="text/javascript">
            $(function() {
                $('#form_login').focus();
                $('input').placeholder();
            });
        </script>
    {/literal}
    <script type="text/javascript" src="plugins/Login/templates/login.js"></script>
    {if 'General_LayoutDirection'|translate =='rtl'}
        <link rel="stylesheet" type="text/css" href="themes/default/rtl.css" />
    {/if}
    {include file="CoreHome/templates/iframe_buster_header.tpl"}
</head>
<body class="login">
{include file="CoreHome/templates/iframe_buster_body.tpl"}
<div id="logo">
    <img src='/themes/logo.png' title="{$linkTitle}" alt="Intranet Statistics" width="240" style='margin-right: 20px' />
</div>
