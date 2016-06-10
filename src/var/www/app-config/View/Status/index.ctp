<!-- File: /app/View/Status/index.ctp -->

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
  <h1>App Status&nbsp;
      <small><?php echo $node_name;?>
</small></h1>
</div>

<div class="row">
  <div class="col-md-8">
    <div class="well">
      <h3>Apps</h3>

      <table class="table table-striped table-bordered">
  <tr>
    <th>Email Host (db)</th>
    <th>Email Domain (db)</th>
    <th>Email Host (now)</th>
    <th>Email Domain (now)</th>
  </tr>
  <tr>
    <td><?php echo $hostname ; ?>
    </td>
    <td><?php echo $domain ; ?>
    </td>
    <td><?php echo $current_hostname ; ?>
    </td>
    <td><?php echo $current_domain ; ?>
    </td>
  </tr>
      </table>
<!--      <div class="alert alert-danger">
  <strong>Warning!</strong>.  There are no mesh links in range.  It's a bit quiet around here.
      </div>
-->
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
