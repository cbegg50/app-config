<!-- File: /app/View/IrcSettings/index.ctp -->
<div class="page-header">
  <h1>IRC Settings</h1>
</div>

<?php
echo $this->Form->create('IrcSetting', array(
  'inputDefaults' => array(
    'div' => 'form-group',
    'label' => array('class' => 'col col-md-3 control-label'),
    'class' => 'form-control'),
	'url' => array('controller' => 'irc_settings', 'action' => 'edit')));
echo $this->Form->input('id', array(
	'type' => 'hidden',
));

?>
<span class="pull-right">
<?php echo $this->Form->submit(__('Save'), array('name' => 'submit', 'div' => false, 'class' => 'btn btn-primary'));?>
</span>
<p></p>

<?php
echo $this->Form->input('ircd_server', array('label' => __('ircd Server'), 'type' => 'text', 'readonly' => 'readonly'));
echo $this->Form->input('short_desc', array('label' => __('Short Description')));
echo $this->Form->input('net_name', array('label' => __('Net Name')));
echo $this->Form->input('net_desc', array('label' => __('Net Description')));
?>
  </div>
<?php
echo $this->Form->end();
?>
