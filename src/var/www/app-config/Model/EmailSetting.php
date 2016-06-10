<?php
class EmailSetting extends AppModel {

	public $validate = array(
		'hostname' => array('required' => array('rule' => array('notBlank'), 'message' => 'Host name is required')),
		'domain' => array('required' => array('rule' => array('notBlank'), 'message' => 'Domain is required')),
	);

}

?>
