<?php
class ProfileController extends Controller {
    public function show(): void { $this->view('staff.profile', ['pageTitle' => 'Hồ sơ', 'user' => Auth::getInstance()->user()]); }
    public function update(): void {
        if (!verify_csrf()) { $this->redirect('/staff/profile'); return; }
        $auth = Auth::getInstance(); $user = $auth->user();
        $this->db->update('users', ['name' => $this->input('name', $user->name), 'email' => $this->input('email', '')], 'id=?', [$user->id]);
        $this->redirect('/staff/profile', ['success' => 'Đã cập nhật']);
    }
    public function changePassword(): void {
        if (!verify_csrf()) { $this->redirect('/staff/profile'); return; }
        $auth = Auth::getInstance(); $user = $auth->user();
        if (!password_verify($this->input('current_password', ''), $user->password)) { $this->redirect('/staff/profile', ['error' => 'Mật khẩu hiện tại không đúng']); return; }
        $newPass = $this->input('new_password', '');
        if (strlen($newPass) < 6) { $this->redirect('/staff/profile', ['error' => 'Mật khẩu tối thiểu 6 ký tự']); return; }
        $auth->updatePassword($user->id, $newPass);
        $this->redirect('/staff/profile', ['success' => 'Đã đổi mật khẩu']);
    }
}
