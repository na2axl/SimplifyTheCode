<?php
/* Smarty version 3.1.30, created on 2016-12-11 11:46:45
  from "D:\Centers Technologies\IAI Social Network\app\views\templates\errors\exception.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_584d2e955f5c28_57219364',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '2f5171530920123c36b5608ffea261fe2d5ad20d' => 
    array (
      0 => 'D:\\Centers Technologies\\IAI Social Network\\app\\views\\templates\\errors\\exception.tpl',
      1 => 1450702197,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_584d2e955f5c28_57219364 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Uncaught Exception</title>
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
            <h1>An uncaught Exception was encountered</h1>
            <p><b><u>Type:</u></b> <?php echo $_smarty_tpl->tpl_vars['type']->value;?>
<br>
            <b><u>Message:</u></b> <?php echo $_smarty_tpl->tpl_vars['mess']->value;?>
<br>
            <b><u>Filename:</u></b> <?php echo $_smarty_tpl->tpl_vars['file']->value;?>
<br>
            <b><u>Line Number:</u></b> <?php echo $_smarty_tpl->tpl_vars['line']->value;?>
</p>
            <p><b><u>Backtrace:</u></b>
            	<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['excp']->value->getTrace(), 'error');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['error']->value) {
?>
            		<?php if ((isset($_smarty_tpl->tpl_vars['error']->value['file']) && strpos($_smarty_tpl->tpl_vars['error']->value['file'],realpath(BASEPATH)) !== 0)) {?>
                    <p style="margin-left:30px">
                    <u>File:</u> <?php echo $_smarty_tpl->tpl_vars['error']->value['file'];?>
<br>
                    <u>Line:</u> <?php echo $_smarty_tpl->tpl_vars['error']->value['line'];?>
<br>
                    <u>Function:</u> <?php echo $_smarty_tpl->tpl_vars['error']->value['function'];?>

                    </p>
                    <?php }?>
            	<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

            </p>
        </div>
    </body>
</html><?php }
}
