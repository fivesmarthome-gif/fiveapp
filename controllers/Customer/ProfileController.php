<?php
/**
 * Customer Profile Controller
 * HoanKiem LAB
 */

class ProfileController extends Controller
{
    public function show(): void
    {
        $auth = Auth::getInstance();
        $this->view('customer.profile', [
            'pageTitle' => 'Hồ sơ của tôi',
            'backUrl'   => url('/customer/dashboard'),
            'user'      => $auth->user(),
        ]);
    }

    public function update(): void
    {
        if (!verify_csrf()) { $this->redirect('/customer/profile', ['error' => 'CSRF error']); return; }

        $auth = Auth::getInstance();
        $user = $auth->user();

        $this->db->update('users', [
            'name'         => $this->input('name', $user->name),
            'email'        => $this->input('email', ''),
            'address'      => $this->input('address', ''),
            'clinic_name'  => $this->input('clinic_name', ''),
            'dentist_name' => $this->input('dentist_name', ''),
            'gender'       => $this->input('gender', ''),
            'birthday'     => $this->input('birthday') ?: null,
            'notify_promotion' => $this->input('notify_promotion', 0) ? 1 : 0,
        ], 'id=?', [$user->id]);

        $this->redirect('/customer/profile', ['success' => 'Đã cập nhật thông tin']);
    }

    public function changePassword(): void
    {
        if (!verify_csrf()) { $this->redirect('/customer/profile', ['error' => 'CSRF error']); return; }

        $auth = Auth::getInstance();
        $user = $auth->user();

        $currentPass = $this->input('current_password', '');
        $newPass     = $this->input('new_password', '');
        $confirmPass = $this->input('new_password_confirmation', '');

        if (!password_verify($currentPass, $user->password)) {
            $this->redirect('/customer/profile', ['error' => 'Mật khẩu hiện tại không đúng']);
            return;
        }

        if (strlen($newPass) < 6) {
            $this->redirect('/customer/profile', ['error' => 'Mật khẩu mới tối thiểu 6 ký tự']);
            return;
        }

        if ($newPass !== $confirmPass) {
            $this->redirect('/customer/profile', ['error' => 'Mật khẩu xác nhận không khớp']);
            return;
        }

        $auth->updatePassword($user->id, $newPass);
        $this->redirect('/customer/profile', ['success' => 'Đã đổi mật khẩu thành công']);
    }

    public function uploadAvatar(): void
    {
        if (!verify_csrf()) { $this->redirect('/customer/profile'); return; }

        $auth = Auth::getInstance();
        $user = $auth->user();

        $path = $this->uploadFile('avatar', 'avatars');
        if ($path) {
            $this->db->update('users', ['avatar' => $path], 'id=?', [$user->id]);
            $this->redirect('/customer/profile', ['success' => 'Đã cập nhật ảnh đại diện']);
        } else {
            $this->redirect('/customer/profile', ['error' => 'Không thể upload ảnh']);
        }
    }
}
