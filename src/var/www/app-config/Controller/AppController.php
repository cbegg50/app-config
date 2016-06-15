
<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

class AppController extends Controller {

	public $components = array(
		'Session',
		'Auth' => array('loginRedirect' => array('controller' => 'status',
			'action' => 'index'),
			'logoutRedirect' => array('controller' => 'status',
				'action' => 'index'),
			'authorize' => array('Controller')
			)
		);

	public function isAuthorized($user) {
		// admin can access every action
		if (isset($user['role']) && $user['role'] === 'admin') {
			return true;
		}
		// Default deny
		return false;
	}

	protected function load_email_attributes() {
		$this->loadModel('EmailSetting');
		$settings = $this->EmailSetting->findById(1);
		$this->set('user_id', AuthComponent::user('id'));
		$this->set('hostname', $settings['EmailSetting']['hostname']);
		$this->set('domain', $settings['EmailSetting']['domain']);
	}

	protected function get_email_settings() {
		$this->loadModel('EmailSetting');
		return $this->EmailSetting->findById(1);
	}

	protected function get_users() {
		$this->loadModel('User');
		return $this->User->find('all');
	}
}
?>
