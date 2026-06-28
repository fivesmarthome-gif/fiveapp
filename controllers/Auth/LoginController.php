<?php
/**
 * Login Controller
 * HoanKiem LAB
 */

class LoginController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::getInstance()->check()) {
            $this->redirect(Auth::getInstance()->redirectPath());
        }
        $this->view('auth.login');
    }

    public function login(): void
    {
        if (!verify_csrf()) {
            $this->redirect('/login', ['error' => 'Yêu cầu không hợp lệ']);
            return;
        }

        $phone    = trim($this->input('phone', ''));
        $password = $this->input('password', '');
        $code     = trim($this->input('activation_code', ''));
        $remember = (bool)$this->input('remember', true);

        $auth = Auth::getInstance();

        // Login by activation code
        if ($code) {
            if ($auth->attemptWithCode($phone, $code)) {
                $this->redirect($auth->redirectPath());
                return;
            }
            $this->redirect('/login', [
                'error' => 'Số điện thoại hoặc mã kích hoạt không đúng',
                'old'   => ['phone' => $phone],
            ]);
            return;
        }

        // Login by password
        if ($auth->attempt($phone, $password, $remember)) {
            $this->redirect($auth->redirectPath());
            return;
        }

        $this->redirect('/login', [
            'error' => 'Số điện thoại hoặc mật khẩu không đúng',
            'old'   => ['phone' => $phone],
        ]);
    }

    public function logout(): void
    {
        Auth::getInstance()->logout();
        $this->redirect('/login', ['success' => 'Đã đăng xuất thành công']);
    }
}
