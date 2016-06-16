<?php
class StatusController extends AppController {
        public $components = array('Flash', 'RequestHandler');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index', 'edit');
	}


	public function index() {
		$this->load_email_attributes();
		$this->set('users', $this->get_users());
		// Check hostname and domain
                $current_hostname = file_get_contents("/etc/hostname");
                $this->set('current_hostname', $current_hostname);
                $dnsmasq = file_get_contents("/etc/dnsmasq.d/hsmm-pi.conf");
                $current_domain = strstr($dnsmasq, "domain=");  // includes rest of file
                $current_domain = strtok($current_domain, "=\n");
                $current_domain = strtok("\n");
                $this->set('current_domain', $current_domain);
		$email_setting = $this->get_email_settings();

		// Have they changed?
		if ((0 != strcmp($current_hostname, $email_setting['EmailSetting']['hostname']))
		 || (0 != strcmp($current_domain, $email_setting['EmailSetting']['domain'])))  {
			$this->Flash->success(__('Difference(s) found, reloading Postfix.'));
	                if ($this->EmailSetting->save(array(
	'id' => 1,
        'hostname' => $current_hostname,
        'domain' => $current_domain))) {
				$this->render_email_config($this->get_email_settings());
				exec('sudo /usr/sbin/service postfix reload');
				exec('sudo /usr/sbin/service dovecot reload');
				$this->redirect(array('action' => 'index'));
			}
		}
	}

	function startsWith($haystack, $needle) {
		return !strncmp($haystack, $needle, strlen($needle));
	}
}

?>
