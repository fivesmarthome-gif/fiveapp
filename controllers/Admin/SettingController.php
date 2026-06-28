<?php
/**
 * Admin Setting Controller
 * HoanKiem LAB
 */

class SettingController extends Controller
{
    public function index(): void
    {
        $settingsRows = $this->db->fetchAll("SELECT * FROM settings ORDER BY setting_group, setting_key");

        // Format to key-value array
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row->setting_key] = $row->setting_value;
        }

        $this->view('admin.settings.index', [
            'pageTitle' => 'Cài đặt hệ thống',
            'breadcrumbs' => ['Dashboard' => url('/admin/dashboard'), 'Cài đặt' => ''],
            'settings' => $settings
        ]);
    }

    public function update(): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/settings'); return; }

        $inputs = $this->allInput();

        $this->db->beginTransaction();
        try {
            foreach ($inputs as $key => $value) {
                // Ignore CSRF field
                if ($key === '_csrf') continue;

                // Check if setting exists
                $exists = $this->db->count('settings', 'setting_key = ?', [$key]);

                if ($exists) {
                    $this->db->update('settings', ['setting_value' => $value], 'setting_key = ?', [$key]);
                } else {
                    $this->db->insert('settings', [
                        'setting_key' => $key,
                        'setting_value' => $value,
                        'setting_group' => 'general'
                    ]);
                }
            }

            $this->db->commit();
            $this->redirect('/admin/settings', ['success' => 'Đã cập nhật cài đặt thành công']);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->redirect('/admin/settings', ['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }
}
