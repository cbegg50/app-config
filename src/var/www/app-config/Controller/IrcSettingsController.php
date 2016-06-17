<?php
class IrcSettingsController extends AppController {
	public $helpers = array('Html', 'Session');
	public $components = array('Flash', 'RequestHandler', 'Session');

	public function edit($id = null) {
		$irc_settings = $this->IrcSetting->findById($id);

		if (!$irc_settings) {
			throw new NotFoundException(__('Invalid setting'));
		}

		$this->set('irc_settings', $irc_settings);
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->IrcSetting->save($this->request->data)) {
				$latest_irc_settings = $this->getIrcSettings();

				if ($this->renderIrcdConfig($latest_irc_settings) == 0) {
					$this->Flash->success(__('Your settings have been saved and the server will be restarted.'));
				}
			} else {
				$this->Flash->error(__('Unable to update your settings, please review any validation errors.'));
			}
		}

		if (!$this->request->data) {
			$this->request->data = $irc_settings;
		}
	}

}

?>
