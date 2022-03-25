<?php

use Phalcon\Mvc\Controller;
use Phalcon\Http\Request;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Http\Response;

class AuthController extends Controller
{
    public function indexAction()
    {
        $log = new \App\Components\Loggerclass();
        $sanitizer = new \App\Components\Myescaper();
        // if session is not set
        if (!$this->session->get("user")) {
            // if cookies are set then login through cookies and set session
            if ($this->cookies->get('remember-me')->getValue()) {
                // check in the database
                $user = new Users();
                $dbData = ($user::findFirst([
                    "conditions" => "email = :email:",
                    "bind" => [
                        "email" => $this->cookies->get('remember-me')->getValue(),
                    ]
                ]));
                if ($dbData) {
                    $this->session->set("user", $this->cookies->get('remember-me')->getValue());
                }
            }
        }
        if ($this->session->get("user")) {
            header("location:/index");
        }
        $validation = new Validation();
        $request = new Request();
        // if got post
        if ($request->ispost()) {
            // adding validation for email
            $validation->add(
                'email',
                new Email()
            );
            // adding validation for password
            $validation->add(
                'password',
                new PresenceOf(
                    [
                        'length' => 5,
                        'message' => 'The password is required',
                    ]
                )
            );
            // checking if password is less then 5
            $validation->add(
                'password',
                new StringLength(
                    [
                        'min' => 5,
                        'message' => 'password must be longer',
                    ]
                )
            );
            // fire validation 😻
            $messages = $validation->validate($request->getPost());
            // if validation has errors
            if (count($messages)) {
                $errors = [];
                // if there are errors
                foreach ($messages as $message) {
                    $d = json_decode(json_encode($message, false));
                    $errors[$d->field] = $d->message;
                    $log->LoginLog("error", $d->message);
                }
                // send errors into the view
                $this->view->errors = $errors;
            } else {
                // now check for uer login details
                $user = new Users();
                $formData = $this->request->getPost();
                $dbData = ($user::findFirst([
                    "conditions" => "email = :email: AND password = :password:",
                    "bind" => [
                        "email" => $sanitizer->sanitize($formData["email"]),
                        "password" => $sanitizer->sanitize($formData["password"]),
                    ]
                ]));
                // checking for email and password 🔏
                if ($dbData) {
                    echo "creds ok";
                    // setting the session 🔗
                    $this->session->set('user', $dbData->email);
                    if (isset($formData["remember"])) {
                        // setting the cookies 🍪
                        $this->cookies->set(
                            'remember-me',
                            $dbData->email,
                            time() + 15 * 86400
                        );
                        $this->cookies->send();
                        $log->LoginLog("info", "remember me is on, setting cookies");
                    } else {
                        echo "remember me off";
                    }
                    $log->LoginLog("info", $dbData->email . " logged in");
                    header("location:/index");
                } else {
                    $log->LoginLog("error", "username or password is incorrect");
                    header("location:/error");
                }
            }
        }
    }
    public function registerAction()
    {
        $log = new \App\Components\Loggerclass();
        $sanitizer = new \App\Components\Myescaper();
        $validation = new Validation();
        if ($this->request->isPost()) {
            // adding validation for password
            $validation->add(
                'name',
                new PresenceOf(
                    [
                        'message' => 'The name is required',
                    ]
                )
            );
            $validation->add(
                'email',
                new Email()
            );
            // adding validation for password
            $validation->add(
                'password',
                new PresenceOf(
                    [
                        'length' => 5,
                        'message' => 'The password is required',
                    ]
                )
            );
            // checking if name is less then 5
            $validation->add(
                'name',
                new StringLength(
                    [
                        'min' => 5,
                        'message' => 'name must be longer',
                    ]
                )
            );
            // checking if password is less then 5
            $validation->add(
                'password',
                new StringLength(
                    [
                        'min' => 5,
                        'message' => 'password must be longer',
                    ]
                )
            );
            // fire validation 😻
            $messages = $validation->validate($this->request->getPost());
            if (count($messages)) {
                $errors = [];
                // if there are errors
                foreach ($messages as $message) {
                    $d = json_decode(json_encode($message, false));
                    $errors[$d->field] = $d->message;
                    $log->RegisterLog("error", $d->message);
                }
                // send errors into the view
                $this->view->errors = $errors;
            } else {
                print_r($this->request->getPost());
                $users = new Users();
                $d = $users::findFirst(
                    [
                        'conditions' => 'email = :email:',
                        'bind' => [
                            'email' => $this->request->getPost()["email"],
                        ]
                    ]
                );
                if ($d) {
                    $this->view->message = "This email already exists";
                } else {
                    $users->assign(
                        [
                            'name' => $sanitizer->sanitize($this->request->getPost()['name']),
                            'email' => $sanitizer->sanitize($this->request->getPost()['email']),
                            'password' => $sanitizer->sanitize($this->request->getPost()['password'])
                        ]
                    );
                    if ($users->save()) {
                        $this->view->message = "Successfully registered";
                        $log->RegisterLog("error", "Successfully registered");
                        header("location:/index");
                    } else {
                        $this->view->message = "Error registering";
                    }
                }
            }
        }
        // die();
    }
    public function logoutAction()
    {
        // remove session 😎
        $this->session->destroy();
        // remove cookies 🍪
        $this->cookies->get("remember-me")->delete();
        header("location:/auth");
    }
}
