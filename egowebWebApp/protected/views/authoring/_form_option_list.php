
            <?php echo CHtml::form(null, null, array('id'=>'newOptionForm')); ?>
				<table>
                    <tr>
                        <td align="right">Option Names for</td>
                        <td align="left" colspan="3" wicket:id="valuesTitle"><?php echo $answerList->listName; ?></td>
                    </tr>
                    <tr>
                        <td>Name</td><td>Value</td>
                    </tr>
                    <?php foreach($options as $key=>$value): ?>
                    <tr wicket:id="presetNamesList">
                        <td wicket:id="presetName"><?php echo $key ?></td>
                        <td wicket:id="presetValue"><?php echo $value ?></td>
                        <td><?php echo CHtml::link( 'delete',
                                                    'javascript:void(0)',
                                                    array('onclick'=>'js:$.get("'.$this->createUrl('/authoring/ajaxdelete?answerListId='.$answerList->id.'&key='.$key.'&value='.$value).'", function(data) {$("#formOptionList").html(data)});')
                                                    );
                                                    ?>
                        </td>
                        <td><?php echo CHtml::link( 'move up',
                                                    'javascript:void(0)',
                                                    array('onclick'=>'js:$.get("'.$this->createUrl('/authoring/ajaxmoveup?answerListId='.$answerList->id.'&key='.$key.'&value='.$value).'", function(data) {$("#formOptionList").html(data)});')
                                                    );
                            ?>
                        </td>
                        <td>
                        <?php echo CHtml::link( 'edit',
                                                'javascript:void(0)',
                                                array('onclick'=>'js:$.get("'.$this->createUrl('/authoring/ajaxload?form=_form_option_list_edit&answerListId='.$answerList->id.'&key='.$key.'&value='.$value).'", function(data) {$("#editOption").html(data)});')
                                                );
                        ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <input name="answerListId" type=hidden value=<?php echo $answerList->id ?>>
                        <td><input name="key" type="text" size="20" /></td>
                        <td><input name="value" type="text" size="5" /></td>
                        <td>
                        <?php
                            echo CHtml::button("Add", array(
                                "onclick"=>'js:$.get("/authoring/ajaxupdate", $("#newOptionForm").serialize(), function(data){$("#formOptionList").html(data);})'
                            ));
                        ?>

                        </td>
                        <td></td>
                    </tr>
				</table>
			</form>
