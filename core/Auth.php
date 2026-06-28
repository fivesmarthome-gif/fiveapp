<?php
/**
 * Auth Class - Authentication & Session Management
 * HoanKiem LAB
 */

class Auth
{
    private static ?Auth $instance = null;
    private ?object $user = null;
    private Database $db;

    private function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadUser();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load user from session
     */
    private function loadUser(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->user = $this->db->fetch(
                "SELECT * FROM users WHERE id = ? AND is_active = 1",
                [$_SESSION['user_id']]
            );

            if (!$this->user) {
                $this->logout();
            }
        }
    }

    /**
     * Attempt login with phone and password
     */
    public function attempt(string $phone, string $password, bool $remember = true): bool
    {
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE phone = ? AND is_active = 1",
            [$phone]
        );

        if (!$user) return false;

        if (!password_verify($password, $user->password)) return false;

        // Login successful
        $this->user = $user;
        $_SESSION['user_id'] = $user->id;

        // Update last login
        $this->db->update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [$user->id]);

        // Remember token
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->db->update('users', ['remember_token' => $token], 'id = ?', [$user->id]);
            setcookie('remember_token', $token, time() + 86400 * 30, '/');
        }

        return true;
    }

    /**
     * Attempt login with activation code
     */
    public function attemptWithCode(string $phone, string $code): bool
    {
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE phone = ? AND activation_code = ? AND is_active = 1",
            [$phone, $code]
        );

        if (!$user) return false;

        $this->user = $user;
        $_SESSION['user_id'] = $user->id;

        // Clear activation code after use
        $this->db->update('users', [
            'activation_code' => null,
            'last_login_at'   => date('Y-m-d H:i:s'),
        ], 'id = ?', [$user->id]);

        return true;
    }

    /**
     * Register new user
     */
    public function register(array $data): ?int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');

        return $this->db->insert('users', $data);
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        if ($this->user) {
            $this->db->update('users', ['remember_token' => null], 'id = ?', [$this->user->id]);
        }

        $this->user = null;
        unset($_SESSION['user_id']);
        setcookie('remember_token', '', time() - 3600, '/');

        session_destroy();
    }

    /**
     * Check if user is logged in
     */
    public function check(): bool
    {
        return $this->user !== null;
    }

    /**
     * Get current user
     */
    public function user(): ?object
    {
        return $this->user;
    }

    /**
     * Get current user ID
     */
    public function id(): ?int
    {
        return $this->user ? $this->user->id : null;
    }

    /**
     * Check if user has role
     */
    public function hasRole(string $role): bool
    {
        return $this->user && $this->user->role === $role;
    }

    /**
     * Check if admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if staff
     */
    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    /**
     * Check if shipper
     */
    public function isShipper(): bool
    {
        return $this->hasRole('shipper');
    }

    /**
     * Check if customer
     */
    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    /**
     * Get redirect path based on role
     */
    public function redirectPath(): string
    {
        if (!$this->user) return '/login';

        return match ($this->user->role) {
            'admin'    => '/admin/dashboard',
            'staff'    => '/staff/dashboard',
            'shipper'  => '/shipper/dashboard',
            'customer' => '/customer/dashboard',
            default    => '/login',
        };
    }

    /**
     * Update user password
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        return $this->db->update('users', [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ], 'id = ?', [$userId]) > 0;
    }

    /**
     * Try to login with remember token cookie
     */
    public function tryRememberLogin(): void
    {
        if ($this->check()) return;

        $token = $_COOKIE['remember_token'] ?? null;
        if (!$token) return;

        $user = $this->db->fetch(
            "SELECT * FROM users WHERE remember_token = ? AND is_active = 1",
            [$token]
        );

        if ($user) {
            $this->user = $user;
            $_SESSION['user_id'] = $user->id;
        }
    }
}
