<?php
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="col-12 row">
    <div class="col-6 p-3">
        <input onkeyup="regexSearch($(this).val())" placeholder="Filter Ego ID" />
    </div>
    <div class="col-4 p-3">
        <input type="checkbox" onchange="noAlterSearch($(this).is(':checked'))" /> With Alters Only
    </div>
</div>
<table id="interview-table"> 
    <thead>
        <th class="col-1"><input type="checkbox" onclick="$('input.export[type=checkbox]').prop('checked', $(this).prop('checked'))" data-toggle="tooltip" data-placement="top" title="Select All"></th>
        <th class="col-4">Ego ID</th>
        <th class="col-3">Status</th>
        <th class="col-2">Alters</th>
        <th class="col-2">&nbsp;</th>
    </thead>
    <tbody>
        <?php
            foreach ($interviews as $interview) {
                if ($interview->completed == -1) {
                    $completed = "<span style='color:#0B0'>COMPLETED</span>";
                } else {
                    $completed = "INCOMPLETE";
                }
                echo "<tr>";
                echo "<td>".Html::checkbox('export[]', false, array('class'=>"export",'value'=>$interview->id, "id"=>"export-$interview->id")) . "</td>";
                echo "<td><label class='form-label' for='export-$interview->id'>" .  $egoIds[$interview->id] . "</label></td>";
                echo "<td>".$completed."</td>";
                echo "<td>".$alters[$interview->id]."</td>";
                echo "<td>".Html::button('Review', array("class"=>"btn btn-xs btn-info",'onclick'=>"document.location='".Url::to('/interview/'.$study->id.'/'.$interview->id . "#/page/0'")))."</td>";
                echo "</tr>";
            }
            ?>
    </tbody>
    </table>
    <script>
    function regexSearch(val){
        jQuery('#interview-table').DataTable()
                    .columns(1)
                    .search(val,true,false)
                    .draw();
    }
    function noAlterSearch(checked){
        if(checked){
        jQuery('#interview-table').DataTable()
                    .columns(3)
                    .search('^((?!(0)).)*$',true,false)
                    .draw();
        }else{
            jQuery('#interview-table').DataTable()
                    .columns(3)
                    .search('',true,false)
                    .draw();
        }
    }
    
    table = $('#interview-table').DataTable( {
        //paging: false,
        info: false,
        lengthMenu: [10, 50, 100, 250, 500],
        order: [[1, 'asc']],
        rowReorder: true,
        pageLength: 500,
        pagingType: 'simple_numbers',
        language: {
    paginate: {
      next: '>', // or '→'
      previous: '<' // or '←' 
    }
  },
        columnDefs: [
            { orderable: false, targets:[0,-1] }
        ]
    });
    $("#interview-table_filter").hide()

        </script>
