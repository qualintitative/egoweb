<?php /* @var $this Controller */ ?>
<?php $this->beginContent('//layouts/main'); ?>
	<?php
	if(Yii::app()->getController()->getId() == "authoring" && preg_match('/\d+/', Yii::app()->getRequest()->getRequestUri())){
		if(isset($this->studyId)){
			$this->menu=array(
				array('label'=>'Study Settings', 'url'=>array('edit','id'=>$this->studyId)),
				array('label'=>'Ego ID Questions', 'url'=>array('ego_id','id'=>$this->studyId)),
				array('label'=>'Ego Questions', 'url'=>array('ego','id'=>$this->studyId)),
				array('label'=>'Alter Questions', 'url'=>array('alter','id'=>$this->studyId)),
				array('label'=>'Alter Pair Questions', 'url'=>array('alterpair','id'=>$this->studyId)),
				array('label'=>'Network Questions', 'url'=>array('network','id'=>$this->studyId)),
				array('label'=>'Expressions', 'url'=>array('expression','id'=>$this->studyId)),
				array('label'=>'Option Lists', 'url'=>array('optionlist','id'=>$this->studyId)),
			);

		}
	}
	?>
	<?php echo $content; ?>

<?php $this->endContent(); ?>