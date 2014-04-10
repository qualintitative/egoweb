<?php /* @var $this Controller */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo CHtml::encode($this->pageTitle); ?></title>
		<!--[if lt IE 8]>
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
		<![endif]-->
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/flat-ui.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
		<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/indexof.js"></script>
		<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/bootstrap.min.js');
		Yii::app()->clientScript->registerCssFile(Yii::app()->clientScript->getCoreScriptUrl().'/jui/css/base/jquery-ui.css');
		Yii::app()->clientScript->registerCoreScript('jquery');
		Yii::app()->clientScript->registerCoreScript('jquery.ui'); ?>
	</head>
	<body>
		<div id="wrapper">
			<nav class="navbar">
			<div class="collapse navbar-collapse" id="topbar">
				<?php if(!Yii::app()->user->isGuest): ?>
				<?php
				$condition = "id != 0";
				if(!Yii::app()->user->isSuperAdmin){
					$studies = q("SELECT studyId FROM interviewers WHERE interviewerId = " . Yii::app()->user->id)->queryColumn();
					if($studies)
						$condition = "id IN (" . implode(",", $studies) . ")";
					else
						$condition = "id = -1";
				}

				$criteria = array(
					'condition'=>$condition,
					'order'=>'id DESC',
				);
				$studies = Study::model()->findAll($criteria);
				?>
				<ul class="nav navbar-nav navbar-left">
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							<span class="fui-list"></span>
						</a>
						<ul class="dropdown-menu">
							<li><a href="/interviewing">Interviewing</a>
								<ul>
									<?php foreach($studies as $data): ?>
									<li>
									<?php echo CHtml::link(CHtml::encode($data->name), array('/interviewing?studyId='.$data->id)); ?>
									</li>
									<?php endforeach; ?>
								</ul>
							</li>
							<?php if(Yii::app()->user->isSuperAdmin): ?>
							<li><a href="/authoring">Authoring</a>
								<ul>
									<?php foreach($studies as $data): ?>
									<li>
									<?php echo CHtml::link(CHtml::encode($data->name), array('/authoring/edit', 'id'=>$data->id)); ?>
									</li>
									<?php endforeach; ?>
								</ul>
							</li>
							<li><a href="/analysis">Analysis</a>
								<ul>
									<?php foreach($studies as $data): ?>
									<li>
									<?php echo CHtml::link(CHtml::encode($data->name), array('/analysis/', 'study'=>$data->id)); ?>
									</li>
									<?php endforeach; ?>
								</ul>
							</li>
							<li><a href="/importExport">Import / Export</a></li>
							<li><a href="/admin/user">User Admin</a>
							<?php endif; ?>
							<li><a href="/mobile">Mobile</a>
							<li><a href="/site/logout">Log Out</a>
						</ul>
					</li>
				</ul>
				<?php else: ?>
				<ul class="nav navbar-nav navbar-left">
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							<span class="fui-lock"></span>
						</a>
					</li>
				</ul>
				<?php endif; ?>
				<a class="titlelink" href="/admin">EgoWeb 2.0</a><span class="title">Exploring social networks via interviews</span>
			</div>
			</nav>
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
					<?php if(Yii::app()->getController()->getId() == "interviewing" && !Yii::app()->user->isGuest && !isset($_GET['studyId']) && preg_match('/\d+/', Yii::app()->getRequest()->getRequestUri())): ?>
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

