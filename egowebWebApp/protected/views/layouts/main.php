<?php /* @var $this Controller */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php Yii::app()->clientScript->registerCoreScript('jquery'); ?>
<?php Yii::app()->clientScript->registerCoreScript('jquery.ui'); ?>

<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo CHtml::encode($this->pageTitle); ?></title>
		<!-- blueprint CSS framework -->
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
		<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
		<!--[if lt IE 8]>
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
		<![endif]-->
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/flat-ui.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
		<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/indexof.js"></script>
	</head>
	<body>
		<div id="wrapper">
			<div id="topbar">
				<?php if(!Yii::app()->user->isGuest): ?>
				<ul class="adminMenu">
					<li><a href="javascript:void(0)"><img src="/images/menu.png" style="float:right"></a>
						<ul>
							<li><a href="/authoring">Authoring</a>
							    <ul>
							    <li>
							    	<div>
									<?php $studies = Study::model()->findAll(); ?>
									<?php foreach($studies as $data): ?>
									<?php echo CHtml::link(CHtml::encode($data->name), array('/authoring/edit', 'id'=>$data->id))."<br>"; ?>
									<?php endforeach; ?>
									</div>
									</li>
									</ul>
								</li>
								<li><a href="/analysis">Analysis</a>
									<ul>
										<li>
											<div>
												<?php $studies = Study::model()->findAll(); ?>
												<?php foreach($studies as $data): ?>
												<?php echo CHtml::link(CHtml::encode($data->name), array('/analysis/', 'study'=>$data->id))."<br>"; ?>
												<?php endforeach; ?>
											</div>
										</li>
									</ul>
								</li>
								<li><a href="/importExport">Import / Export</a></li>
								<li><a href="/interviewing">Interviewing</a>
									<ul>
										<li>
											<div>
												<?php $studies = Study::model()->findAll(); ?>
												<?php foreach($studies as $data): ?>
												<?php echo CHtml::link(CHtml::encode($data->name), array('/interviewing?studyId='.$data->id))."<br>"; ?>
												<?php endforeach; ?>
											</div>
										</li>
									</ul>
								</li>
							</ul>
						</li>
					</ul>
				<?php endif; ?>
				<span class="title">EgoWeb 2.0 | Exploring social networks via interviews</span>
				<?php if(!Yii::app()->user->isGuest): ?>
				<a href="/admin"><img id="home_button" src="/images/home_button.png" style="float:right;"/></a>
				<?php else: ?>
				<img id="home_button" src="/images/home_button.png" style="float:right;" />
				<?php endif; ?>
			</div>
			<div id="menubar">
				<!-- navigation start -->
				<?php $this->widget('zii.widgets.CMenu',array(
					'id'=>'mainNav',
					'items'=>$this->menu,
					'activateItems'=>false,
					'htmlOptions'=>array('class'=>'authoring')
				)); echo "\n";?>
				<!-- navigation end -->
				<div id="nav">
					<?php if(Yii::app()->getController()->getId() == "interviewing" && !Yii::app()->user->isGuest && preg_match('/\d+/', Yii::app()->getRequest()->getRequestUri())): ?>
					<a href="javascript:void(0)" onclick="$('#navigation').toggle()"><img src="/images/nav.png"></a>
					<?php endif; ?>
				</div>
				<?php if(Yii::app()->getController()->getId() == "interviewing" && isset($_GET['interviewId'])): ?>
				<span class="interviewee"><?php echo (isset($_GET['interviewId']) && $_GET['interviewId']) ?  Interview::getEgoId($_GET['interviewId']) : ""; ?></span>
				<span class="intleft">Interviewing:</span>
				<?php endif; ?>
			</div>
			<div id="content">
					<?php echo $content; ?>
			</div>
		</div>
	</body>
</html>

