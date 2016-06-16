<?php
class LocationSettingsController extends AppController {
	public $helpers = array('Html', 'Session');
	public $components = array('Flash', 'RequestHandler', 'Session');

	public function edit($id = null) {
		$irc_setting = $this->IrcSetting->findById($id);

		if (!$irc_setting) {
			throw new NotFoundException(__('Invalid setting'));
		}

		$this->set('ircd_server', $irc_setting['IrcSetting']['ircd_server']);
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->IrcSetting->save($this->request->data)) {
				$latest_irc_setting = $this->get_irc_setting();

				$this->render_ircd_config($latest_irc_setting());
				$this->Flash->reboot(__('Your settings have been saved and will take effect on the next reboot.'));
			} else {
				$this->Flash->error(__('Unable to update your settings, please review any validation errors.'));
			}
		}

		if (!$this->request->data) {
			$this->request->data = $irc_setting;
		}
	}

}

?>
