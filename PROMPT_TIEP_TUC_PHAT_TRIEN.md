# Prompt tiep tuc phat trien HoanKiemLAB

Tai lieu nay la ban tom tat de lan sau lap trinh vien/Codex co the tiep tuc code ma khong phai doc lai toan bo source va file chat log.

## 1. Tong quan du an

HoanKiemLAB la he thong quan ly lab nha khoa viet bang PHP thuan theo mo hinh MVC tu xay dung, chay trong XAMPP.

- Thu muc goc: `C:\xampp\htdocs\HoanKiemLAB`
- Entry point: `index.php`
- Routes: `routes/web.php`
- Core framework: `core/Router.php`, `core/Controller.php`, `core/Database.php`, `core/Auth.php`, `core/helpers.php`
- Config app: `config/app.php`
- Config DB: `config/database.php`
- Schema goc: `database/schema.sql`
- Public assets: `public/css/app.css`, `public/js/app.js`, `public/uploads`
- Base URL: `/HoanKiemLAB`
- Timezone app: `Asia/Ho_Chi_Minh`

Kien truc view:

- Controller goi `$this->view('admin.orders.index', [...])`
- Ten view duoc map thanh `views/admin/orders/index.php`
- Prefix `admin`, `customer`, `staff` se dung layout tuong ung trong `views/layouts`
- View `public.*`, `auth.*`, `errors.*` la standalone hoac layout rieng tuy controller/core xu ly

## 2. Cac portal hien co

### Admin portal

Route prefix: `/admin`, middleware `auth` + `role:admin`.

Chuc nang da co:

- Dashboard tong quan
- Quan ly don hang
- Quan ly khach hang
- Quan ly nhan vien
- Quan ly san xuat
- Quan ly vat lieu
- Quan ly giao hang
- Lich hen
- Doanh thu
- Thanh toan
- Bai viet
- Khuyen mai
- Chi nhanh
- Phan hoi
- Thong bao
- Cai dat
- Loai san pham
- Ho so admin

Controller chinh nam trong `controllers/Admin`.
View chinh nam trong `views/admin`.

### Customer portal

Route prefix: `/customer`, middleware `auth` + `role:customer`.

Chuc nang da co:

- Dashboard khach hang
- Danh sach don hang
- Chi tiet don hang
- Theo doi tien do san xuat/giao hang
- Gui phan hoi va danh gia
- Xac nhan nhan hang
- Yeu cau hoan tra
- Thong bao
- Ho so ca nhan

Controller chinh nam trong `controllers/Customer`.
View chinh nam trong `views/customer`.

### Staff portal

Route prefix: `/staff`, middleware `auth` + `role:staff`.

Chuc nang da co:

- Dashboard nhan vien
- Danh sach cong doan san xuat duoc giao
- Cap nhat cong doan: start, complete, rework
- Lich hen
- Xem thong tin khach hang
- Thong bao
- Ho so nhan vien

Controller chinh nam trong `controllers/Staff`.
View chinh nam trong `views/staff`.

### Public route

Da co route doi ngay hen khong can login:

- GET `/order/change-due-date/{token}`
- POST `/order/change-due-date/{token}`

Controller: `controllers/Public/DueDateController.php`
View: `views/public/due_date_change.php`

## 3. Cac view da hoan thien them

Trong qua trinh phat trien da tao day du cac view thieu cho admin:

- `views/admin/branches/index.php`, `create.php`, `edit.php`
- `views/admin/articles/index.php`, `create.php`, `edit.php`
- `views/admin/promotions/index.php`, `create.php`, `edit.php`
- `views/admin/revenue/index.php`, `by_customer.php`
- `views/admin/feedbacks/index.php`, `show.php`
- `views/admin/notifications/index.php`
- `views/admin/settings/index.php`
- `views/admin/product_types/index.php`, `create.php`, `edit.php`
- `views/admin/profile/show.php`
- `views/public/due_date_change.php`

Luu y: he thong hien co khoang 34 file controller/core va 62 file view da tung duoc lint PHP thanh cong trong lan kiem tra truoc.

## 4. Module giao hang da nang cap

Yeu cau gan nhat: khach hang va admin phai thay duoc don hang da di toi dau.

Da lam:

### Admin danh sach giao hang

File lien quan:

- `controllers/Admin/DeliveryController.php`
- `views/admin/deliveries/index.php`

Trang `/admin/deliveries` hien co:

- Loc theo trang thai giao hang
- Thong tin ma don, khach hang, phong kham
- Hang van chuyen, ma tracking
- Ngay gui, ngay hen tra
- Trang thai giao hang dang badge/visual

Loi da sua:

- View dung `$d->due_date`, nen query `DeliveryController@index()` da duoc cap nhat lay them `o.due_date`, `o.adjusted_due_date`

### Admin chi tiet van don

File lien quan:

- `controllers/Admin/DeliveryController.php`
- `views/admin/deliveries/show.php`

Trang `/admin/deliveries/{id}` hien co:

- Visual progress tracker cho giao hang
- Form cap nhat trang thai giao hang
- Chon nhan vien giao noi bo
- Nhap hang van chuyen ngoai va ma tracking
- Ghi chu giao hang
- Upload anh xac nhan `proof_photo`
- Hien thong tin khach hang/dia chi
- Timeline lich su trang thai tu `order_status_logs`
- Khi update se dong bo `deliveries.status` sang `orders.delivery_status`
- Khi update se ghi log va tao thong bao cho khach hang

### Customer chi tiet don hang

File lien quan:

- `controllers/Customer/OrderController.php`
- `views/customer/orders/show.php`

Trang `/customer/orders/{id}` hien co:

- Hanh trinh don hang 5 buoc:
  `Dat hang -> Dang san xuat -> San sang giao -> Dang giao hang -> Da nhan hang`
- Lay thong tin delivery moi nhat bang `SELECT * FROM deliveries WHERE order_id=? ORDER BY id DESC LIMIT 1`
- Hien hang van chuyen, ma van don, anh xac nhan neu co
- Khach co the xac nhan nhan hang khi trang thai `delivered`
- Khach co the yeu cau hoan tra
- Khach co the gui feedback kem rating 1-5 va upload nhieu anh

## 5. Luu y DB quan trong

Schema goc trong `database/schema.sql` co the chua khop hoan toan voi DB dang chay vi da co mot so ALTER truc tiep trong qua trinh phat trien.

Can nho cac thay doi DB da ap dung:

### deliveries.status

Schema va app config hien tai dung:

```sql
ENUM('waiting_pickup', 'shipping', 'delivered', 'pending_return', 'returned')
```

DB dang chay da tung duoc sua bang lenh tuong duong:

```sql
ALTER TABLE deliveries
MODIFY COLUMN status ENUM('waiting_pickup','shipping','delivered','pending_return','returned')
DEFAULT 'waiting_pickup';
```

`database/schema.sql` da duoc dong bo lai. Voi DB cu, dung migration `database/migrations/2026_06_28_delivery_feedback_updates.sql`.

### order_feedbacks

Da them cac cot:

```sql
ALTER TABLE order_feedbacks
ADD COLUMN rating TINYINT DEFAULT 5 AFTER content,
ADD COLUMN images TEXT NULL AFTER rating;
```

Code `Customer/OrderController@feedback` dang insert `rating` va `images`. Neu dung DB moi tu schema goc ma chua ALTER, se loi SQL vi thieu cot.

### delivery sample data

Da tung them sample delivery cho order id 3:

- Courier: `Viettel Post`
- Tracking: `VT20260628001`
- Status: `shipping`
- Order code lien quan: `DH-20260624-001`

## 6. Tai khoan demo

Trong qua trinh test HTTP, cac tai khoan demo dung duoc:

- Admin: `0901234567` / `password`
- Staff: `0911111111` / `password`
- Customer: `0922222221` / `password`

Luu y: `database/schema.sql` comment password mau la `admin123`, `staff123`, `customer123`, nhung trong DB dang chay da test thanh cong bang `password`.

## 7. Cac bang DB chinh

Bang nghiep vu chinh:

- `users`
- `branches`
- `product_types`
- `orders`
- `order_items`
- `order_attachments`
- `production_steps`
- `order_status_logs`
- `order_feedbacks`
- `feedback_images`
- `deliveries`
- `appointments`
- `suppliers`
- `materials`
- `material_transactions`
- `articles`
- `promotions`
- `notifications`
- `payments`
- `settings`
- `activity_logs`

Luon kiem tra schema thuc te bang MySQL truoc khi code tiep cac phan lien quan DB, vi schema file co the chua duoc sync voi DB dang chay.

## 8. Quy tac status hien tai

### Production statuses

Trong `config/app.php`:

- `pending`
- `confirmed`
- `in_production`
- `qc_passed`
- `ready`

### Delivery statuses

Trong `config/app.php`:

- `none`
- `waiting_pickup`
- `shipping`
- `delivered`
- `pending_return`
- `returned`

Luu y: da sua flow customer confirm nhan hang de giu `orders.delivery_status = 'delivered'` va chi set `orders.overall_status = 'completed'`. Filter "Hoan thanh" o trang customer orders dung `overall_status = 'completed'`.

### Overall statuses

Trong schema:

- `new`
- `processing`
- `completed`
- `cancelled`

## 9. Cac lenh kiem tra nhanh

Dung PHP cua XAMPP:

```powershell
& "C:\xampp\php\php.exe" -v
```

Kiem tra syntax mot file:

```powershell
& "C:\xampp\php\php.exe" -l "C:\xampp\htdocs\HoanKiemLAB\controllers\Admin\DeliveryController.php"
```

Kiem tra DB:

```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root hoankiemlab -e "SHOW TABLES;"
```

Kiem tra enum giao hang:

```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root hoankiemlab -e "SHOW COLUMNS FROM deliveries LIKE 'status';"
```

Kiem tra trang login:

```powershell
Invoke-WebRequest -Uri "http://localhost/HoanKiemLAB/login" -UseBasicParsing -TimeoutSec 10
```

Neu can test trang co login, hay lay CSRF token tu form login roi POST bang `WebRequestSession`.

## 10. Viec nen lam tiep

Uu tien gan:

1. Dong bo `database/schema.sql` voi DB thuc te:
   - Da sua enum `deliveries.status`
   - Da them `rating`, `images` cho `order_feedbacks`
   - Nen tao file migration SQL rieng neu can deploy sang may khac

2. Kiem tra toan bo flow giao hang:
   - Admin tao/cap nhat delivery
   - Khach xem timeline
   - Khach confirm nhan hang
   - Khach yeu cau hoan tra
   - Admin duyet hoan tra
   - Notification va log co dong bo dung khong

3. Khi deploy sang DB cu:
   - Chay `database/migrations/2026_06_28_delivery_feedback_updates.sql`
   - Migration nay map enum cu `pending/shipped/return_pending` sang enum moi truoc khi siết schema

4. Quet loi encoding:
   - Mot so noi hien thi trong terminal bi mojibake do encoding console, nhung file co the van render dung trong browser.
   - Khi sua text tieng Viet, giu UTF-8.

5. Chay lint toan bo PHP sau moi thay doi.

## 11. Cach lay source tu GitHub de lam tiep

Repo GitHub da luu source:

- `https://github.com/fivesmarthome-gif/fiveapp.git`
- Nhanh chinh: `main`
- Thu muc local khuyen nghi khi dung XAMPP: `C:\xampp\htdocs\HoanKiemLAB`

Neu may moi chua co source, clone ve dung thu muc XAMPP:

```powershell
cd C:\xampp\htdocs
git clone https://github.com/fivesmarthome-gif/fiveapp.git HoanKiemLAB
cd C:\xampp\htdocs\HoanKiemLAB
```

Neu da co source local roi, cap nhat ban moi nhat:

```powershell
cd C:\xampp\htdocs\HoanKiemLAB
git pull origin main
```

Sau khi clone/pull:

1. Mo XAMPP va start Apache + MySQL.
2. Tao database `hoankiemlab` neu chua co.
3. Import `database/schema.sql` cho DB moi.
4. Neu la DB cu, xem them cac migration trong `database/migrations/`, dac biet:
   - `2026_06_28_delivery_feedback_updates.sql`
   - `2026_06_28_shipper_location.sql`
5. Kiem tra config DB trong `config/database.php` co dung may local khong.
6. Truy cap app tai `http://localhost/HoanKiemLAB/login`.
7. Doc file nay truoc khi code tiep, khong can doc lai toan bo `HoanKiemLAB Project Development.md` tru khi can lich su cu.

Neu sua code, hay commit va push lai GitHub:

```powershell
git status
git add .
git commit -m "Mo ta ngan gon thay doi"
git push origin main
```

## 12. Prompt ngan cho lan sau

Neu muon bat dau nhanh, co the dua noi dung sau cho Codex:

```text
Ban dang lam tiep du an HoanKiemLAB. Repo GitHub: https://github.com/fivesmarthome-gif/fiveapp.git, branch main. Neu chua co source, clone vao C:\xampp\htdocs\HoanKiemLAB; neu da co source thi git pull origin main. Day la app PHP thuan MVC tu xay dung, route trong routes/web.php, core trong core, controller chia Admin/Customer/Staff/Public/Shipper, view trong views theo dot notation. Hay doc PROMPT_TIEP_TUC_PHAT_TRIEN.md truoc, sau do uu tien dong bo schema DB voi code giao hang/feedback/shipper, kiem tra flow delivery end-to-end va sua cac lech enum/status. Sau moi cum sua code, update PROMPT_TIEP_TUC_PHAT_TRIEN.md, commit va push lai GitHub. Khong doc lai HoanKiemLAB Project Development.md tru khi can lich su chat cu.
```
