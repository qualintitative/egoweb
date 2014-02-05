<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title><?php echo CHtml::encode($this->pageTitle); ?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/themes/<?php echo Yii::app()->theme->name; ?>/css/normalize.min.css">
        <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/themes/<?php echo Yii::app()->theme->name; ?>/css/main.css">
		<!--[if IE 7]>
			<link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/themes/<?php echo Yii::app()->theme->name; ?>/css/ie7.css">
		<![endif]-->

        <script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/<?php echo Yii::app()->theme->name; ?>/js/vendor/modernizr-2.6.2.min.js"></script>
    </head>
    <body>
	<div class="container phn">
		<!--[if lt IE 7]>
		    <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
		<![endif]-->
		<nav class="navbar navbar-default mbn" role="navigation">
			<ul class="nav navbar-nav">
				<li class="dropdown">
					<a href="#" class="navbar-toggle mhn" data-toggle="dropdown">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>
					<ul class="dropdown-menu">
						<li>
							<a href="<?php echo  Yii::app()->createUrl('analysis'); ?>">Analysis</a>
						</li>
						<li>
							<a href="<?php echo  Yii::app()->createUrl('importExport'); ?>">Import / Export</a>
						</li>
						<li>
							<a href="<?php echo  Yii::app()->createUrl('interviewing'); ?>">Interviewing</a>
						</li>
						<li class="divider"></li>
						<li>
							<a href="<?php echo  Yii::app()->createUrl('admin/user'); ?>">User Admin</a>
						</li>
						<?php
						/*
						/**
						 * This is sample of how to implement a subsubmenu
						 * 
						<li class="dropdown-submenu">
							<a href="#" tabindex="-1" class="dropdown-toggle" data-toggle="dropdown">Something else here</a>
							<ul class="dropdown-menu">
								<li><a href="">test</a></li>
							</ul>
						</li>
						*/
						?>
					</ul>
				</li>
			</ul>
			<div class="brand">EgoWeb 2.0<span>Exploring social networks via interviews</span></div>
		</nav>
		<nav class="navbar navbar-inverse" role="navigation">
			&nbsp;
		</nav>	
		<?php echo $content; ?>
	</div>
        
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script>window.jQuery || document.write('<script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/<?php echo Yii::app()->theme->name; ?>/js/libs/jquery-1.10.1.min.js"><\/script>')</script>
	
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/<?php echo Yii::app()->theme->name; ?>/js/plugins.min.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/<?php echo Yii::app()->theme->name; ?>/js/main.min.js"></script>
	<script>
        if(typeof jp=='undefined'){
		var jp;
		jp = new <?php echo Yii::app()->getClassjs(); ?>();
		console.log('jp: Instatiating jp instance <?php echo Yii::app()->getClassjs(); ?>.');
		jp = new <?php echo Yii::app()->getClassjs(); ?>();
		try { jp.init(); } catch(e) { console.log(e); jp._init(); }
        } else console.log('jp: A jp instance has already been instantiated.');
        </script>
    </body>
</html>
