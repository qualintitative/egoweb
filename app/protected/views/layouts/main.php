<!DOCTYPE html>
<html lang="en" ng-app="egowebApp">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=EDGE" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo CHtml::encode($this->pageTitle); ?></title>
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/www/css/bootstrap.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/www/css/flat-ui.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/www/css/main.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/summernote.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/summernote-bs3.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/font-awesome.min.css" />
		<?php Yii::app()->clientScript->registerCssFile(Yii::app()->clientScript->getCoreScriptUrl().'/jui/css/base/jquery-ui.css'); ?>
		<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/www/js/bootstrap.min.js'); ?>
		<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/summernote.js'); ?>
		<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/plugins/summernote-ext-fontstyle.js'); ?>
		<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/www/js/jquery.floatThead.js'); ?>
		<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/www/js/egoweb.js'); ?>
		<?php Yii::app()->clientScript->registerCoreScript('jquery'); ?>
		<?php Yii::app()->clientScript->registerCoreScript('jquery.ui'); ?>
	</head>
	<body>
        <nav class="navbar navbar-fixed-top" id="topbar">
				<?php if(!Yii::app()->user->isGuest): ?>
				<?php
				$condition = "id != 0";
				if(!Yii::app()->user->isSuperAdmin){
                    $criteria = array(
            			'condition'=>"interviewerId = " . Yii::app()->user->id,
                    );
                    $interviewers = Interviewer::model()->findAll($criteria);
                    $studies = array();
                    foreach($interviewers as $i){
                        $studies[] = $i->studyId;
                    }
					if($studies)
						$condition = "id IN (" . implode(",", $studies) . ")";
					else
						$condition = "id = -1";
				}

				$criteria = array(
					'condition'=>$condition . " AND active = 1",
					'order'=>'id DESC',
				);
				$studies = Study::model()->findAll($criteria);
				?>
				<ul class="nav navbar-nav navbar-left">
					<li class="dropdown">
						<a id="menu-button" href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
							<span class="fui-list"></span>
						</a>
						<ul class="dropdown-menu">
							<li><?=CHtml::link('Interviewing', $this->createUrl("/interview"))?>
								<ul>
									<?php foreach($studies as $data): ?>
									<li>
									<?php echo CHtml::link(CHtml::encode($data->name), array('/interview?studyId='.$data->id)); ?>
									</li>
									<?php endforeach; ?>
								</ul>
							</li>
							<?php if(Yii::app()->user->isAdmin): ?>
							<li><?=CHtml::link('Authoring', $this->createUrl("/authoring"))?>
								<ul>
									<?php foreach($studies as $data): ?>
									<li>
									<?php echo CHtml::link(CHtml::encode($data->name), array('/authoring/edit', 'id'=>$data->id)); ?>
									</li>
									<?php endforeach; ?>
								</ul>
							</li>
							<li><?=CHtml::link('Data Processing', $this->createUrl("/data"))?>
								<ul>
									<?php foreach($studies as $data): ?>
									<li>
									<?php echo CHtml::link(CHtml::encode($data->name), array('/data/', 'study'=>$data->id)); ?>
									</li>
									<?php endforeach; ?>
								</ul>
							</li>
							<li><?=CHtml::link('Alter Matching', $this->createUrl("/dyad"))?></li>
							<li><?=CHtml::link('Import & Export Studies', $this->createUrl("/importExport"))?></li>
							<?php endif; ?>
							<?php if(Yii::app()->user->isSuperAdmin): ?>
							<li><?=CHtml::link('User Admin', $this->createUrl("/admin/user"))?>
							<?php endif; ?>
							<li><?=CHtml::link('Mobile', $this->createUrl("/mobile"))?>
							<li><?=CHtml::link('Log Out', $this->createUrl("/site/logout"))?>
						</ul>
					</li>
				</ul>
				<?php else: ?>
				<ul class="nav navbar-nav navbar-left">
					<li class="dropdown">
						<a id="menu-button" href="#" class="dropdown-toggle" data-toggle="dropdown">
							<span class="fui-lock"></span>
						</a>
					</li>
				</ul>
				<?php endif; ?>
				<a class="titlelink" href="<?=$this->createUrl("/admin/")?>">EgoWeb 2.0</a><span class="title hidden-xs"><?php echo CHtml::encode($this->pageTitle); ?></span><?php if(!Yii::app()->user->isGuest): ?><span class="title hidden-xs" id="questionTitle"></span><?php endif; ?>

				<ul id="navbox" class="nav navbar-nav navbar-right">
					<li id="questionMenu" class="dropdown hidden">
						<a id="menu-button" href="#" class="dropdown-toggle" data-toggle="dropdown" target="#second">
							<span class="fui-gear"></span>
						</a>
                        <ul class="dropdown-menu" id="second"></ul>
					</li>

				</ul>
				<span class="interviewee"><?php if(Yii::app()->getController()->getId() == "interview" && isset($_GET['interviewId']) && !Yii::app()->user->isGuest): ?><?php echo (isset($_GET['interviewId']) && $_GET['interviewId']) ?  Interview::getEgoId($_GET['interviewId']) : ""; ?><?php endif; ?></span>
        </nav>
        <!--
			<div id="menubar">

				<div id="nav">
					<?php if(Yii::app()->getController()->getId() == "interviewing" && !Yii::app()->user->isGuest && !isset($_GET['studyId']) && preg_match('/\d+/', Yii::app()->getRequest()->getRequestUri())): ?>
					<a href="javascript:void(0)" onclick="$('#navigation').toggle()"><img src="/images/nav.png"></a>
					<?php endif; ?>
                    <div id="navigation">
                    	<div id="navbox">
                    		<ul>
                    		</ul>
                    	</div>
                    </div>
				</div>
				<?php if(Yii::app()->getController()->getId() == "interviewing" && isset($_GET['interviewId']) && !Yii::app()->user->isGuest): ?>
				<span class="interviewee"><?php echo (isset($_GET['interviewId']) && $_GET['interviewId']) ?  Interview::getEgoId($_GET['interviewId']) : ""; ?></span>
				<span class="intleft">Interviewing:</span>
				<?php endif; ?>
			</div>-->
			<div id="content" class="container">
            	<?php
            	if(Yii::app()->getController()->getId() == "authoring" && preg_match('/\d+/', Yii::app()->getRequest()->getRequestUri())){
            		if(isset($this->studyId)){
            			$this->menu=array(
            				array('label'=>'Study Settings', 'url'=>array('edit','id'=>$this->studyId), "active"=>Yii::app()->controller->action->id == 'edit'),
            				array('label'=>'Ego ID Questions', 'url'=>array('ego_id','id'=>$this->studyId), "active"=>Yii::app()->controller->action->id == 'ego_id'),
                            array('label'=>'Questions', 'url'=>array('questions','id'=>$this->studyId), "active"=>Yii::app()->controller->action->id == 'questions'),
            				array('label'=>'Expressions', 'url'=>array('expression','id'=>$this->studyId), "active"=>Yii::app()->controller->action->id == 'expression'),
							array('label'=>'Option Lists', 'url'=>array('optionlist','id'=>$this->studyId), "active"=>Yii::app()->controller->action->id == 'optionlist'),
							array('label'=>'Code Book', 'url'=>array('codebook','id'=>$this->studyId), "active"=>Yii::app()->controller->action->id == 'codebook'),
            			);

            		}
            	}
            	?>
				<?php $this->widget('zii.widgets.CMenu',array(
					'id'=>'mainNav',
					'items'=>$this->menu,
					'activeCssClass'=>'active',
					'htmlOptions'=>array('class'=>'nav nav-pills small')
				)); echo "\n";?>
				<?php echo $content; ?>
			</div>
			<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
					</div>
				</div>
			</div>
	</body>
</html>
