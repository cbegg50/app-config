
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
				'action' => 'index')
			)
		);

	protected function loadEmailAttributes() {
		$this->loadModel('EmailSetting');
		$settings = $this->EmailSetting->findById(1);
		$this->set('user_id', AuthComponent::user('id'));
		$this->set('hostname', $settings['EmailSetting']['hostname']);
		$this->set('domain', $settings['EmailSetting']['domain']);
	}

	protected function getEmailSettings() {
		$this->loadModel('EmailSetting');
		return $this->EmailSetting->findById(1);
	}

	protected function getIrcSettings() {
		$this->loadModel('IrcSetting');
		return $this->IrcSetting->findById(1);
	}

	protected function getUsers() {
		$this->loadModel('User');
		return $this->User->find('all');
	}

        protected function renderEmailConfig($email_setting) {
                // Render /etc/postfix/main.cf
                $postfix_conf = file_get_contents(WWW_ROOT . "/files/main.cf.template");
                $postfix_conf_output = str_replace(array('{myhostname}', '{mydomain}'),
                                                array($email_setting['EmailSetting']['hostname'],
                                                        $email_setting['EmailSetting']['domain']),
                                                $postfix_conf);

                file_put_contents('/etc/postfix/main.cf', $postfix_conf_output);
                // Render /etc/postfix/helo_access
                $postfix_conf = file_get_contents(WWW_ROOT . "/files/helo_access.template");
                $postfix_conf_output = str_replace(array('{myhostname}', '{mydomain}'),
                                                array($email_setting['EmailSetting']['hostname'],
                                                        $email_setting['EmailSetting']['domain']),
                                                $postfix_conf);

                file_put_contents('/etc/postfix/helo_access', $postfix_conf_output);
		$output = '';
		$result = 0;
                exec('sudo postmap /etc/postfix/helo_access 2>&1', $output, $result);
		if ($result != 0) {
			$this->Flash->error(__('postmap failed:' . implode(':', $output)));
                }
		exec('sudo service postfix restart 2>&1', $output ,$result);
		if ($result != 0) {
			$this->Flash->error(__('Failed to restart postfix:' . implode(':', $output)));
		}
		return $result;
        }

        protected function renderIrcdConfig($settings) {
                // Render /etc/postfix/main.cf
                $ircd_conf = file_get_contents(WWW_ROOT . "/files/ircd-hybrid/ircd.conf.template");
                $ircd_conf_output = str_replace(array('{ircd_server}', '{short_desc}', '{net_name}', '{net_desc}'),
                                                array($settings['IrcSetting']['ircd_server'],
							$settings['IrcSetting']['short_desc'],
							$settings['IrcSetting']['net_name'],
							$settings['IrcSetting']['net_desc']),
                                                $ircd_conf);

                file_put_contents('/etc/ircd-hybrid/ircd.conf', $ircd_conf_output);
		$output = '';
		$result = -1;
                exec('sudo service ircd-hybrid restart 2>&1', $output, $result);
		if ($result != 0) {
			$this->Flash->error(__('Failed to restart ircd:' . implode(':', $output)));
		}
		return $result;
        }


}
?>
