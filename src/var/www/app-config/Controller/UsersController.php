<?php
// app/Controller/UsersController.php
class UsersController extends AppController {
	public $components = array('Flash', 'RequestHandler', 'Session');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('edit', 'logout');
	}

	public function login() {
		if ($this->request->is('post')) {

			if ($this->Auth->login()) {
				$this->Flash->success(__('Login successful, carry on'));
				$this->redirect($this->Auth->redirectUrl());
			} else {
				$this->Flash->error(__('Invalid username or password, try again'));
			}
		} else {
			$this->Flash->info(__('You must login to access this part of the site'));
		}
	}

	public function logout() {
		$this->redirect($this->Auth->logout());
	}

        public function index() {
                $this->redirect(array('controller' => 'status',
					'action' => 'index'));
        }

	public function edit($id = null) {
                $user = $this->User->findById($id);

                if (!$user) {
                        throw new NotFoundException(__('Invalid user'));
                }
		$this->set('username', $user['User']['username']);
		$this->set('id', $user['User']['id']);
		if ($this->request->is('post')) {
			$user = $this->User->findById($id);
			if ($user == null) {
				$this->Flash->error(__('Unable to find user account  ' . strval($id) . ', this should never happen'));
			} else if (strcmp($user['User']['password'], Security::hash($this->request->data['User']['current_password'], null, true)) != 0) {
				$this->Flash->error(__('The current password was incorrect'));
			} else if (strcmp($this->request->data['User']['password'], $this->request->data['User']['password_confirmation']) != 0) {
				$this->Flash->error(__('New passwords did not match'));
			} else {
				$user['User']['password'] = $this->request->data['User']['password'];

				if ($this->User->save($user)) {
					$this->Flash->success(__('Password changed successfully.'));
					$this->index();
				} else {
					$this->Flash->error(__('Unable to update your settings, please review any validation errors.'));
				}
			}
		}
	}

	public function delete($id = null) {
                $this->User->id = $id;
                if (!$this->User->exists()) {
                        throw new NotFoundException(__('Invalid user key'), 'default', array('class' => 'alert alert-danger'));
                }

                if ($this->User->delete()) {
                } else {
                        $this->Flash->error(__('Unable to delete user.'));
		}
	}

	public function add() {
                if ($this->request->is('post')) {
			// Encrypt default password
			$this->request->data['User']['current_password'] =
				Security::hash('changeme', null, true);

                        $this->User->create();
                        if ($this->User->save($this->request->data)) {
//                                $new_user = $this->get_newest_user();
//                                $this->system_add_user($new_user);

                                $this->Flash->reboot(__('The user has been added, and will take effect on the next reboot.'));
                                $this->redirect(array('controller' => 'status',
							'action' => 'index'));
                        } else {
                                $this->Flash->error(__('Unable to add new user.'));
                        }
                }
	}
}
?>
