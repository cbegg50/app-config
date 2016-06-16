<?php
class LocationSetting extends AppModel {

	public $validate = array(
		'ircd_server' => array('required' => array('rule' => array('notBlank'), 'message' => 'The server domain name.')),
		'short_desc' => array('required' =>  array('rule' => array('notBlank'), 'message' => 'A short description of the server')),
		'net_name' => array('required' =>  array('rule' => array('notBlank'), 'message' => 'A short description of the server')),
		'net_desc' => array('required' =>  array('rule' => array('notBlank'), 'message' => 'A short description of the server')),
	);
}
?>
