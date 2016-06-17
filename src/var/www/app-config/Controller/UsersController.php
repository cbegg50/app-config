<?php
// app/Controller/UsersController.php
class UsersController extends AppController {
	public $components = array('Flash', 'RequestHandler', 'Session');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('logout', 'edit');
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
                $this->loadEmailAttributes();
 		$this->set('users', $this->getUsers());
        }

	public function edit($id = null) {
                $user = $this->User->findById($id);

                if (!$user) {
                        throw new NotFoundException(__('Invalid user'));
                }
		$username = $user['User']['username'];
		$this->set('username', $username);
		$this->set('id', $user['User']['id']);
                $this->set('logged_id', AuthComponent::user('id'));
 		if ($this->request->is('post')) {
			if ($user == null) {
				$this->Flash->error(__('Unable to find user account  ' . $id . ', this should never happen'));
			} else if (( $user['User']['id'] == AuthComponent::user('id')) &&
				   (strcmp($user['User']['password'], Security::hash($this->request->data['User']['current_password'], null, true)) != 0)) {
				$this->Flash->error(__('The current password was incorrect'));
			} else if (strcmp($this->request->data['User']['password'], $this->request->data['User']['password_confirmation']) != 0) {
				$this->Flash->error(__('New passwords did not match'));
			} else {
				$password = $this->request->data['User']['password'];
				$user['User']['password'] = $password;

				if ($this->User->save($user)) {
					// Change system password for the user
					$this->systemUpdatePassword($username, $password);
					$this->redirect(array('action' => 'index'));
				} else {
					$this->Flash->error(__('Unable to update your password, please review any validation errors.'));
				}
			}
		}
	}

	public function delete($id = null) {
                $this->User->id = $id;
                if (!$this->User->exists()) {
                        throw new NotFoundException(__('Invalid user key'), 'default', array('class' => 'alert alert-danger'));
                }
                $user = $this->User->findById($id);
		$username = $user['User']['username'];
                if ($this->User->delete()) {
			$this->systemDelUser($username);
			$this->redirect(array('action' => 'index'));
                } else {
                        $this->Flash->error(__('Unable to delete user ' . $username . '.'));
		}
	}

	public function add() {
                if ($this->request->is('post')) {
			$username = $this->request->data['User']['username'];
			// Check for duplicate username
			if ($this->User->findByUsername($username)) {
				$this->Flash->error(__('User ' . $username . ' already exists.'));
				$this->redirect(array('action' => 'index'));
			} else {
				// Encrypt default password
				$this->request->data['User']['current_password'] =
					Security::hash('changeme', null, true);
				$this->request->data['User']['role'] = 'author';
	                        $this->User->create();
	                        if ($this->User->save($this->request->data)) {
					// System call to add user
					$this->systemAddUser($username);
					$this->redirect(array('action' => 'index'));
	                        } else {
	                                $this->Flash->error(__('Unable to add new user.'));
	                        }
			}
                }
	}

	private function systemAddUser($username) {
		exec("bash <<'END'
sudo adduser " . $username . "
changeme
changeme






END
",$output, $result);
		if ($result == 0) {
                        $this->Flash->success(__('User ' . $username . ' successfully added.'));
		} else {
                       	$this->Flash->error(__('Error: ' . implode(':', $output) . ' ' . $result ));
		}
	}

	private function systemDelUser($username) {
		exec('sudo deluser ' . $username . ' 2>&1', $output, $result);
		if ($result == 0) {
                        $this->Flash->success(__('User ' . $username . ' successfully deleted.'));
		} else {
                       	$this->Flash->error(__('Error: ' . implode(':', $output) . ' ' . $result ));
		}
	}

	private function systemUpdatePassword($username, $password) {
		exec("bash <<'END'
sudo passwd " . $username . "
" . $password . "
" . $password . "
END
",$output, $result);
		if ($result == 0) {
	                $this->Flash->success(__('Password for user ' . $username . ' successfully updated.'));
		} else {
	               	$this->Flash->error(__('Error: ' . implode(':', $output) . ' ' . $result ));
		}
	}
}
?>
