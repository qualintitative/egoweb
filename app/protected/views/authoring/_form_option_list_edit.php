<?php echo CHtml::form(null, null, array('id'=>'editOptionForm')); ?>

	<input type=hidden name = "answerListId"  value=<?php echo $answerListId; ?> />
	<input type=hidden name = "oldKey"  value="<?php echo $key; ?>" />
	<input type=hidden name = "oldValue"  value="<?php echo $value; ?>" />
	<table >
		<tr><td colspan="2">Change Option Name</td></tr>
		<tr>
			<td align="right">Name: </td>
			<td >
			<input name="key" type="text" size="20" value=<?php echo $key; ?> />
			</td>
			</tr><tr>
			<td align="right">Value: </td>
			<td>
			<input name = "value" type="text" size="5"  value=<?php echo $value; ?> />
			</td>
		</tr>
		<tr>
			<td>
			<?php
				echo CHtml::button("Save", array(
					"onclick"=>'js:$.get("/authoring/ajaxupdate", $("#editOptionForm").serialize(), function(data){$("#formOptionList").html(data);$("#editOptionForm").html("")})'
				));
			?>
			</td>
		</tr>
	</table>
</form>
