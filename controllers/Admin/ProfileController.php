<?php
/**
 * Admin Profile Controller
 * HoanKiem LAB
 */

class ProfileController extends Controller
{
    public function show(): void
    {
        $this->view('admin.profile.show', [
            'pageTitle' => 'Thông tin cá nhân',
            'breadcrumbs' => ['Dashboard' => url('/admin/dashboard'), 'Hồ sơ' => ''],
            'user' => Auth::getInstance()->user()
        ]);
    }

    public function update(): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/profile', ['error' => 'CSRF error']); return; }

        $auth = Auth::getInstance();
        $user = $auth->user();

        $this->db->update('users', [
            'name' => $this->input('name', $user->name),
            'email' => $this->input('email', ''),
            'address' => $this->input('address', ''),
            'birthday' => $this->input('birthday') ?: null,
            'gender' => $this->input('gender', 'other'),
        ], 'id = ?', [$user->id]);

        $this->redirect('/admin/profile', ['success' => 'Cập nhật hồ sơ thành công']);
    }

    public function changePassword(): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/profile', ['error' => 'CSRF error']); return; }

        $auth = Auth::getInstance();
        $user = $auth->user();

        $currentPass = $this->input('current_password', '');
        $newPass = $this->input('new_password', '');
        $confirmPass = $this->input('new_password_confirmation', '');

        if (!password_verify($currentPass, $user->password)) {
            $this->redirect('/admin/profile', ['error' => 'Mật khẩu hiện tại không chính xác']);
            return;
        }

        if (strlen($newPass) < 6) {
            $this->redirect('/admin/profile', ['error' => 'Mật khẩu mới phải từ 6 ký tự trở lên']);
            return;
        }

        if ($newPass !== $confirmPass) {
            $this->redirect('/admin/profile', ['error' => 'Mật khẩu mới và mật khẩu xác nhận không khớp']);
            return;
        }

        $auth->updatePassword($user->id, $newPass);
        $this->redirect('/admin/profile', ['success' => 'Thay đổi mật khẩu thành công']);
    }
}
