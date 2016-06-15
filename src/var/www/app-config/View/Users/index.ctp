<!-- File: /app/View/Users/index.ctp -->

<script>
    $( document ).on("click", ".open-mapModal", function () {
  // set lat-lon labels on the modal dialog
  $("#longitude").text($(this).data('lon'));
  $("#latitude").text($(this).data('lat'));


  if ((typeof(Microsoft) !== 'undefined') && (typeof(Microsoft.Maps) !== 'undefined')) {
            var latVal = $(this).data('lat');
            var lonVal = Microsoft.Maps.Location.normalizeLongitude($(this).data('lon'));
            var center_loc = new Microsoft.Maps.Location(latVal, lonVal);
            var pin = new Microsoft.Maps.Pushpin(center_loc, {draggable:false});
            var map = new Microsoft.Maps.Map(document.getElementById("mapDiv"), {credentials: "<?php echo ((null != $maps_api_key) ? $maps_api_key : '');?>"});
            map.setView({center:center_loc, zoom:15});
            map.entities.push(pin);
  }

    });
</script>

<div class="page-header">
  <h1>Users Status&nbsp;
      <small><?php echo $node_name;?>
</small></h1>
</div>

<div class="row">
  <div class="col-md-8">
      <h3>Users</h3>
      <?php
if ($users != NULL && sizeof($users) > 0) {
        ?>
      <table class="table table-striped table-bordered">
        <tr>
          <th>Name</th>
          <th>Actions</th>
        </tr>
        <?php
foreach ($users as $user) {
                ?>
        <tr>
          <td><?php echo $user['User']['username'];?></td>
          <td>
                <?php
if ($user_id != NULL) {
	if (($user_id == 1) || ($user_id == $user['User']['id']))
	echo $this->Html->link('', array(
			'controller' => 'users',
                        'action' => 'edit',
                        $user['User']['id'],
        	),
                array('class' => 'glyphicon glyphicon-edit'));
		echo '&emsp;';
	if  ($user_id == 1) {
		if  ($user['User']['id'] == 1) {
			echo $this->Html->link('', array(
				'controller' => 'users',
	                        'action' => 'add',
	                	),
	                        array('class' => 'glyphicon glyphicon-plus'));
		} else {
			echo $this->Html->link('', array(
				'controller' => 'users',
	                        'action' => 'delete',
	                        $user['User']['id'],
	                ),
	                        array('class' => 'glyphicon glyphicon-trash'));
		}
	}
}
                ?>
          </td>
        </tr>
        <?php
}
        ?>
      </table>
      <?php
} else {
        ?>
      <div class="alert alert-info">
           No users have been defined.
      </div>
      <?php }
?>
  </div>
</div>

<div class="row">
  <div class="col-md-8">
    <h6>App Config Version:&nbsp;<?php echo Configure::read('App.version');?></h6>
  </div>
</div>

<!-- Modal -->
<div id="mapModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabelMap" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="modalLabelMap">Node Location Map</h3>
      </div>
      <div class="modal-body">
        <div id='mapDiv' style="position:relative; width:500px; height:350px;"></div>
        <h5>Latitude:&nbsp;<em id="latitude"></em>&nbsp;&nbsp;Longitude:&nbsp;<em id="longitude"></em></h5>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Close</button>
      </div>
    </div>
  </div>
</div>
