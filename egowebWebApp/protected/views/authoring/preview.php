	<?php if ($question->subjectType != "EGO_ID"): ?>
	<div class="questionText">
		<?php echo $question->prompt; ?>
	</div>
	<br style="clear:both">
	<div class="question">
	<?php endif; ?>
	<?php $phrase = ""; ?>
	<?php if ($question->answerType == "MULTIPLE_SELECTION"): ?>
		<?php
		$phrase = "Please select ";
		if($question->minCheckableBoxes != "" && $question->maxCheckableBoxes != "" && $question->minCheckableBoxes == $question->maxCheckableBoxes)
			$phrase .= $question->maxCheckableBoxes;
		else if($question->minCheckableBoxes != "" && $question->maxCheckableBoxes != "" && $question->minCheckableBoxes != $question->maxCheckableBoxes)
			$phrase .= $question->minCheckableBoxes . " to " . $question->maxCheckableBoxes;
		else if ($question->minCheckableBoxes == "" && $question->maxCheckableBoxes != "")
			$phrase .= " up to " . $question->maxCheckableBoxes ;
		else if ($question->minCheckableBoxes != "" && $question->maxCheckableBoxes == "")
			$phrase .= " at least " . $question->minCheckableBoxes ;

		if($question->maxCheckableBoxes == 1)
			$phrase .= " response";
		else
			$phrase .= " responses";
		if($question->askingStyleList && !$question->withListRange)
			$phrase .= " for each row";
		?>
		<?php endif; ?>
	<?php if ($question->answerType == "NUMERICAL"): ?>
	<?php
		$min = ""; $max = ""; $numberErrors = 0;
		if($question->minLimitType == "NLT_LITERAL"){
			$min = $question->minLiteral;
		}else if($question->minLimitType == "NLT_PREVQUES"){
			$min = Answer::model()->findByAttributes(array('interviewId'=>$interviewId,'questionId'=>$question->minPrevQues));
			if($min)
				$min = $min->value;
			else
				$min = "";
		}
		if($question->maxLimitType == "NLT_LITERAL"){
			$max = $question->maxLiteral;
		}else if($question->maxLimitType == "NLT_PREVQUES"){
			$max = Answer::model()->findByAttributes(array('interviewId'=>$interviewId,'questionId'=>$question->maxPrevQues));
			if($max)
				$max = $max->value;
			else
				$max = "";
		}
		if($min != "")
			$numberErrors++;
		if($max != "")
			$numberErrors = $numberErrors + 2;

		if($numberErrors == 3)
			$phrase = "Please enter a number from " . $min . " to " . $max .".";
		else if ($numberErrors == 2)
			$phrase = "Please enter a number (" . $max . " or lower).";
		else if ($numberErrors == 1)
			$phrase = "Please enter a number (" . $min . " or higher).";
		if($question->askingStyleList && !$question->withListRange)
			$phrase .= " for each row";
	?>
	<?php endif; ?>

	<?php if ($question->subjectType == "EGO_ID"): ?>
		<div class="orangeText"><?php echo $question->prompt; ?></div>
		<br clear=all>
	<?php endif; ?>

	<?php if($phrase != ""): ?>
		<div class="orangeText"><?php echo $phrase; ?></div>
		<br clear=all>
	<?php endif; ?>
