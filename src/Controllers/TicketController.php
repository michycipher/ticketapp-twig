<?php
// src/Controllers/TicketController.php

class TicketController
{
    private $twig;
    private $storage;
    private $auth;

    public function __construct($twig)
    {
        session_start(); // Ensure session is active
        $this->twig = $twig;
        $this->storage = new Storage(__DIR__ . '/../data');
        $this->auth = new AuthController($twig); // Reuse protect method
    }

    public function dashboard()
    {
        $this->auth->protect(); // Protect route

        $tickets = $this->storage->read('tickets.json');
        $total = count($tickets);
        $open = count(array_filter($tickets, fn($t) => $t['status'] === 'open'));
        $in_progress = count(array_filter($tickets, fn($t) => $t['status'] === 'in_progress'));
        $closed = count(array_filter($tickets, fn($t) => $t['status'] === 'closed'));

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        echo $this->twig->render('dashboard.twig', [
            'total' => $total,
            'open' => $open,
            'in_progress' => $in_progress,
            'closed' => $closed,
            'tickets' => $tickets,  // pass tickets to the view
            'isAuthenticated' => isset($_SESSION['ticketapp_token']),
            'user' => $_SESSION['user'] ?? null,
            'flash' => $flash
        ]);
    }

    public function list()
    {
        $this->auth->protect();

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $tickets = $this->storage->read('tickets.json');
        echo $this->twig->render('tickets/list.twig', [
            'tickets' => $tickets,
            'isAuthenticated' => isset($_SESSION['ticketapp_token']),
            'user' => $_SESSION['user'] ?? null,
            'flash' => $flash
        ]);
    }

    // public function create()
    // {
    //     $this->auth->protect();


    //     $errors = [];
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $tickets = $this->storage->read('tickets.json');
    //         $title = trim($_POST['title'] ?? '');
    //         $status = trim($_POST['status'] ?? '');
    //         $description = trim($_POST['description'] ?? '');

    //         $flash = $_SESSION['flash'] ?? null;
    //         unset($_SESSION['flash']);

    //         if (!$title) $errors['title'] = 'Title is required.';
    //         if (!in_array($status, ['open', 'in_progress', 'closed'])) $errors['status'] = 'Invalid status.';

    //         if (empty($errors)) {
    //             $id = time();
    //             $tickets[] = [
    //                 'id' => $id,
    //                 'title' => $title,
    //                 'status' => $status,
    //                 'description' => $description,
    //                 'created_at' => date('c')
    //             ];
    //             $this->storage->write('tickets.json', $tickets);
    //             header('Location: /tickets');
    //             exit;
    //         }
    //     }

    //     echo $this->twig->render('tickets/form.twig', [
    //         'action' => 'create',
    //         'errors' => $errors,
    //         'old' => $_POST ?? [],
    //         'isAuthenticated' => isset($_SESSION['ticketapp_token']),
    //         'user' => $_SESSION['user'] ?? null,
    //         'flash' => $flash
    //     ]);
    // }

    public function create()
    {
        $this->auth->protect();

        $errors = [];
        $ticket = null; // initialize ticket
        $tickets = $this->storage->read('tickets.json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $status = trim($_POST['status'] ?? '');
            $description = trim($_POST['description'] ?? '');

            // Validation
            if (!$title) $errors['title'] = 'Title is required.';
            if (!in_array($status, ['open', 'in_progress', 'closed'])) $errors['status'] = 'Invalid status.';

            if (empty($errors)) {
                $ticket = [
                    'id' => time(),
                    'title' => $title,
                    'status' => $status,
                    'description' => $description,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $tickets[] = $ticket;
                $this->storage->write('tickets.json', $tickets);
            }
        }

        echo $this->twig->render('tickets/form.twig', [
            'action' => 'create',
            'errors' => $errors,
            'old' => $_POST ?? [],
            'ticket' => $ticket, // pass newly created ticket
            'tickets' => $tickets, // pass all tickets for listing
            'isAuthenticated' => isset($_SESSION['ticketapp_token']),
            'user' => $_SESSION['user'] ?? null
        ]);
    }


    // public function edit(int $id)
    // {
    //     $this->auth->protect();

    //     $flash = $_SESSION['flash'] ?? null;
    //     unset($_SESSION['flash']);


    //     $tickets = $this->storage->read('tickets.json');
    //     $errors = [];
    //     $foundIndex = null;

    //     foreach ($tickets as $i => $t) {
    //         if ((int)$t['id'] === $id) {
    //             $foundIndex = $i;
    //             break;
    //         }
    //     }

    //     if ($foundIndex === null) {
    //         echo $this->twig->render('layout.twig', ['content' => '<h2>Ticket not found</h2>']);
    //         return;
    //     }

    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $title = trim($_POST['title'] ?? '');
    //         $status = trim($_POST['status'] ?? '');
    //         $description = trim($_POST['description'] ?? '');

    //         if (!$title) $errors['title'] = 'Title is required.';
    //         if (!in_array($status, ['open', 'in_progress', 'closed'])) $errors['status'] = 'Invalid status.';

    //         if (empty($errors)) {
    //             $tickets[$foundIndex]['title'] = $title;
    //             $tickets[$foundIndex]['status'] = $status;
    //             $tickets[$foundIndex]['description'] = $description;
    //             $this->storage->write('tickets.json', $tickets);
    //             header('Location: /tickets');
    //             exit;
    //         }
    //     }

    //     echo $this->twig->render('tickets/form.twig', [
    //         'action' => 'edit',
    //         'errors' => $errors,
    //         'old' => $tickets[$foundIndex],
    //         'ticket_id' => $id,
    //         'isAuthenticated' => isset($_SESSION['ticketapp_token']),
    //         'user' => $_SESSION['user'] ?? null,
    //         'flash' => $flash
    //     ]);
    // }

    public function edit($id)
{
    $this->auth->protect();
    $tickets = $this->storage->read('tickets.json');
    $errors = [];

    $ticket = array_filter($tickets, fn($t) => $t['id'] == $id);
    $ticket = reset($ticket);

    if (!$ticket) {
        $_SESSION['flash'] = 'Ticket not found.';
        header('Location: /tickets/create');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$title) $errors['title'] = 'Title is required.';
        if (!in_array($status, ['open', 'in_progress', 'closed'])) $errors['status'] = 'Invalid status.';

        if (empty($errors)) {
            foreach ($tickets as &$t) {
                if ($t['id'] == $id) {
                    $t['title'] = $title;
                    $t['status'] = $status;
                    $t['description'] = $description;
                    break;
                }
            }
            $this->storage->write('tickets.json', $tickets);
            $_SESSION['flash'] = 'Ticket updated successfully.';
            header('Location: /tickets/create');
            exit;
        }
    }

    echo $this->twig->render('tickets/form.twig', [
        'action' => 'edit',
        'errors' => $errors,
        'old' => $_POST ?: $ticket,
        'ticket' => $ticket,
        'tickets' => $tickets,
        'isAuthenticated' => isset($_SESSION['ticketapp_token']),
        'user' => $_SESSION['user'] ?? null
    ]);
}

    // public function delete(int $id)
    // {
    //     $this->auth->protect();

    //     $flash = $_SESSION['flash'] ?? null;
    //     unset($_SESSION['flash']);


    //     $tickets = $this->storage->read('tickets.json');
    //     $foundIndex = null;

    //     foreach ($tickets as $i => $t) {
    //         if ((int)$t['id'] === $id) {
    //             $foundIndex = $i;
    //             break;
    //         }
    //     }

    //     if ($foundIndex === null) {
    //         header('Location: /tickets');
    //         exit;
    //     }

    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         array_splice($tickets, $foundIndex, 1);
    //         $this->storage->write('tickets.json', $tickets);
    //         header('Location: /tickets');
    //         exit;
    //     }

    //     echo $this->twig->render('layout.twig', [
    //         'content' => '
    //         <main class="max-w-5xl mx-auto p-6">
    //             <h2>Confirm delete</h2>
    //             <form method="post" action="/tickets/delete/' . $id . '">
    //                 <p>Are you sure you want to delete this ticket?</p>
    //                 <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Yes, delete</button>
    //                 <a href="/tickets" class="ml-4 px-4 py-2 border rounded">Cancel</a>
    //             </form>
    //         </main>',
    //         'isAuthenticated' => isset($_SESSION['ticketapp_token']),
    //         'user' => $_SESSION['user'] ?? null,
    //         'flash' => $flash
    //     ]);
    // }

    public function delete($id)
{
    $this->auth->protect();
    $tickets = $this->storage->read('tickets.json');

    $filtered = array_filter($tickets, fn($t) => $t['id'] != $id);
    $this->storage->write('tickets.json', array_values($filtered));

    $_SESSION['flash'] = 'Ticket deleted successfully.';
    header('Location: /tickets/create');
    exit;
}

}


