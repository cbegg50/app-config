
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

        protected function render_email_config($email_setting) {
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
                exec('sudo postmap /etc/postfix/helo_access');
                exec('sudo service postfix restart');
        }

        protected function render_ircd_config($email_setting) {
                // Render /etc/postfix/main.cf
                $ircd_conf = file_get_contents(WWW_ROOT . "/files/ircd-hybrid/ircd.conf.template");
		$ircd_server = $email_setting['EmailSetting']['hostname'] . '.' . $email_setting['EmailSetting']['domain'];
		$ircd_desc = "Dummy IRC Server Description";
                $ircd_conf_output = str_replace(array('{ircd_server}', '{short_desc}'),
                                                array($ircd_server, $ircd_desc),
                                                $ircd_conf);

                file_put_contents('/etc/ircd-hybrid/ircd.conf', $ircd_conf_output);
                exec('sudo service ircd restart');
        }


}
?>
