<?php
Yii::import('zii.widgets.grid.CButtonColumn');
class EWebCButtonColumn extends CButtonColumn  {
    /**
     * @var string the template that is used to render the content in each data cell.
     * These default tokens are recognized: {view}, {update} and {delete}. If the {@link buttons} property
     * defines additional buttons, their IDs are also recognized here. For example, if a button named 'preview'
     * is declared in {@link buttons}, we can use the token '{preview}' here to specify where to display the button.
     */
    public $template='{update} {delete}';
    /**
     * Renders a link button.
     * @param string $id the ID of the button
     * @param array $button the button configuration which may contain 'label', 'url', 'imageUrl' and 'options' elements.
     * See {@link buttons} for more details.
     * @param integer $row the row number (zero-based)
     * @param mixed $data the data object associated with the row
     */
    protected function renderButton($id,$button,$row,$data) {
        if (isset($button['visible']) && !$this->evaluateExpression($button['visible'],array('row'=>$row,'data'=>$data)))
            return;
        $label=isset($button['label']) ? $button['label'] : $id;
        $url=isset($button['url']) ? $this->evaluateExpression($button['url'],array('data'=>$data,'row'=>$row)) : '#';
        $options=isset($button['options']) ? $button['options'] : array();
        if(!isset($options['title']))
            $options['title']=$label;
        switch ($id) {
            case 'delete':
                echo CHtml::link('<span class="fui-cross"></span>',$url,$options);
        }
    }
}

