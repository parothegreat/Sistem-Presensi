<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Check if current user has one of the allowed roles.
     * Usage in routes: ['filter' => 'role:admin'] or ['filter' => 'role:admin,guru']
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // $arguments may be array or comma-separated string depending on route registration
        $allowed = [];
        if (is_array($arguments)) {
            $allowed = $arguments;
        } elseif (is_string($arguments) && strlen($arguments) > 0) {
            $allowed = explode(',', $arguments);
        }

        $role = $session->get('role');
        if (! empty($allowed) && ! in_array($role, $allowed)) {
            // Logged in but not allowed
            return service('response')->setStatusCode(403)->setBody('Forbidden');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
