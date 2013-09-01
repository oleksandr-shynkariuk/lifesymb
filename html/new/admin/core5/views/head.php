<html>
<head>
<?php require( dirname(__FILE__) . '/title.php' ); ?>
<title><?php echo ntsView::getTitle(); ?></title>

<?php require( dirname(__FILE__) . '/css.php' ); ?>

<link rel="stylesheet" type="text/css" href="<?php echo $NTS_CSS_URL; ?>" />
<link rel="stylesheet" type="text/css" href="http://www.lifesymb.com/new/admin/css/style.css" />
<script language="JavaScript" type="text/javascript" src="<?php echo ntsLink::makeLink('system/pull', '', array('what' => 'js', 'files' => 'jquery-1.7.2.min.js') ); ?>">
</script>

</head>
<body>