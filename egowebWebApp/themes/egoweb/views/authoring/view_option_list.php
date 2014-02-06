	<div>
			<legend>Options Lists for Selection Questions</legend>
			<table style="width:240px">
			<tr><td>
				<form action="/authoring/ajaxupdate" method=post>
				<table>
				<tr><td colspan="2"><b>Option Lists</b></td></tr>
	
				<?php foreach ($model as $list): ?>
				<tr wicket:id="presetTitlesList">
				<td><?php echo 
				CHtml::ajaxLink(
					$list->listName,
					$this->createUrl("/authoring/ajaxload", array("answerListId"=>$list->id, "_"=>"'.uniqid().'", "form"=>"_form_option_list")),
					array('update'=>'#formOptionList')
				);
					
				
				?></td>
				<td><a href="/authoring/ajaxdelete?AnswerList[id]=<?php echo $list->id; ?>">delete</a></td>
				</tr>
				<?php endforeach; ?>
				<tr>
				<td>
					<input name="AnswerList[studyId]" type=hidden value=<?php echo $studyId ?>>
					<input name="AnswerList[listName]" type="text" size="20" 	/></td>
				<td><input type="submit" value="Add" /></td><td></td>
				</tr>
				</table>
				</form>
			</td>
			<td>
				<div id = "formOptionList" >

				</div>
			</td>
			<td>
				<div id="editOption">

			   </div>
			</td>
			</tr>
			</table>
</div>