<?php
/* Smarty version 3.1.30, created on 2016-12-12 23:23:40
  from "D:\Centers Technologies\IAI Social Network\app\views\templates\index.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_584f236c1cd896_25365309',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '9ccec86287c74e140471b94db8b4e8aed8c236b2' => 
    array (
      0 => 'D:\\Centers Technologies\\IAI Social Network\\app\\views\\templates\\index.tpl',
      1 => 1481581416,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_584f236c1cd896_25365309 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?php echo $_smarty_tpl->tpl_vars['app_name']->value;?>
</title>
        <style type="text/css">

            ::selection { background-color: #E13300; color: white; }
            ::-moz-selection { background-color: #E13300; color: white; }

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
            <h1><?php echo smarty_modifier_translate('welcome');?>
</h1>
            <p style="font-style: italic; text-align: center; font-size: 0.75em;"><?php echo smarty_modifier_translate('welcome_info',array($_smarty_tpl->tpl_vars['elapsed_time']->value,$_smarty_tpl->tpl_vars['memory_usage']->value));?>
</p>
            <p><a href="<?php echo $_smarty_tpl->tpl_vars['app_base_url']->value;?>
lang/fr/<?php echo $_smarty_tpl->tpl_vars['this_page_route']->value;?>
" title="Francais">Francais</a></p>
            <p><a href="<?php echo $_smarty_tpl->tpl_vars['app_base_url']->value;?>
lang/en/<?php echo $_smarty_tpl->tpl_vars['this_page_route']->value;?>
" title="English">English</a></p>
        </div>
    </body>
</html>
<?php }
}
