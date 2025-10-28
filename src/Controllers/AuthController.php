<?php


class AuthController
{
    private $twig;
    private $storage;

    public function __construct($twig)
    {
        session_start(); // Ensure session is always started
        $this->twig = $twig;
        $this->storage = new Storage(__DIR__ . '/../data');
    }

    private function setServerToken($token)
    {
        setcookie('ticketapp_token', $token, time() + 3600, "/");
    }

    public function register()
    {
        // session_start();
        // if (isset($_SESSION['ticketapp_token'])) {
        //     header('Location: /dashboard');
        //     exit;
        // }

        $errors = [
            'name' => '',
            'email' => '',
            'password' => '',
            'general' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $users = $this->storage->read('users.json');
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Validate Name
            if (!$name) {
                $errors['name'] = 'Name is required.';
            }

            // Validate Email
            if (!$email) {
                $errors['email'] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Enter a valid email.';
            } elseif (array_filter($users, fn($u) => $u['email'] === $email)) {
                $errors['email'] = 'Email already registered.';
            }

            // Validate Password
            if (!$password) {
                $errors['password'] = 'Password is required.';
            } elseif (strlen($password) < 6) {
                $errors['password'] = 'Password must be at least 6 characters.';
            }

            // If no errors, register user
            if (empty($errors['name']) && empty($errors['email']) && empty($errors['password'])) {
                $id = time();
                $users[] = [
                    'id' => $id,
                    'name' => $name,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT)
                ];
                $this->storage->write('users.json', $users);

                // Auto-login
                $token = bin2hex(random_bytes(16));
                $_SESSION['ticketapp_token'] = $token;
                $_SESSION['user'] = ['id' => $id, 'name' => $name, 'email' => $email];
                $this->setServerToken($token);

                $_SESSION['flash'] = 'Account created successfully. Welcome, ' . $name . '!';

                header('Location: /dashboard');
                exit;
            }
        }

        echo $this->twig->render('auth/register.twig', [
            'errors' => $errors,
            'old' => $_POST ?? [],
            'isAuthenticated' => false,
            'user' => null
        ]);
    }

    public function login()
    {
        // session_start();
        // if (isset($_SESSION['ticketapp_token'])) {
        //     header('Location: /dashboard');
        //     exit;
        // }

        $errors = [
            'email' => '',
            'password' => '',
            'general' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $users = $this->storage->read('users.json');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Validate Email
            if (!$email) {
                $errors['email'] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Enter a valid email.';
            }

            // Validate Password
            if (!$password) {
                $errors['password'] = 'Password is required.';
            } elseif (strlen($password) < 6) {
                $errors['password'] = 'Password must be at least 6 characters.';
            }

            // If no validation errors
            if (empty($errors['email']) && empty($errors['password'])) {
                $found = null;
                foreach ($users as $u) {
                    if ($u['email'] === $email && password_verify($password, $u['password'])) {
                        $found = $u;
                        break;
                    }
                }

                if (!$found) {
                    $errors['general'] = 'Invalid credentials.';
                } else {
                    // Successful login
                    $token = bin2hex(random_bytes(16));
                    $_SESSION['ticketapp_token'] = $token;
                    $_SESSION['user'] = $found;
                    $this->setServerToken($token);

                    // Set flash message here
                    $_SESSION['flash'] = 'Welcome back, ' . $found['name'] . '!';

                    header('Location: /dashboard');
                    exit;
                }
            }
        }

        // Always render the form once, after processing
        // echo $this->twig->render('auth/login.twig', [
        //     'errors' => $errors,
        //     'old' => $_POST ?? [],
        //     'isAuthenticated' => false,
        //     'user' => null
        // ]);

        echo $this->twig->render('auth/login.twig', [
            'errors' => $errors,
            'old' => $_POST ?? []
        ]);
    }

    public function logout()
    {
        setcookie('ticketapp_token', '', time() - 3600, "/");
        unset($_SESSION['ticketapp_token']);
        unset($_SESSION['user']);
        header('Location: /');
        exit;
    }

    public function protect()
    {
        $cookie = $_COOKIE['ticketapp_token'] ?? null;
        if (!$cookie || empty($_SESSION['ticketapp_token']) || $_SESSION['ticketapp_token'] !== $cookie) {
            header('Location: /auth/login');
            exit;
        }
    }
}
