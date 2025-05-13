# attendance-management-system
## 環境構築
### Dockerビルド
1. git clone git@github.com:saaya0401/attendance-management-system.git
1. docker-compose up -d --build

### Laravel環境構築
1. docker-compose exec php bash
1. composer install
1. cp .env.example .env
1. .envファイルの一部を以下のように編集
```
APP_TIMEZONE=Asia/Tokyo

APP_LOCALE=ja

DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_FROM_ADDRESS="attendance-management-system@example.com"
```

5. php artisan config:clear
1. php artisan key:generate
1. php artisan migrate
1. php artisan db:seed
1. php artisan config:cache

## 一般ユーザーのログイン用初期データ
- メールアドレス: saaya@example.com
- パスワード: saayakoba


## 管理者ユーザーのログイン用初期データ
- メールアドレス： admin@example.com
- パスワード： adminadmin


## テスト手順
1. テスト用データベースの作成
```
1. docker-compose exec mysql bash
2. mysql -u root -p
3. password入力を求められたらrootと入力する
4. CREATE DATABASE demo_test;
```
2. docker-compose exec php bash
1. cp .env .env.testing
1. .env.testingの一部を以下のように編集
```
APP_ENV=test
APP_KEY=     *空欄にしておいてください

DB_DATABASE=demo_test
DB_USERNAME=root
DB_PASSWORD=root
```
5. php artisan key:generate --env=testing
1. php artisan config:cache
1. php artisan config:clear
1. php artisan migrate --env=testing
1. php artisan test

*php artisan test でエラーになる場合は以下のように個別にテストしてください
```
1. php artisan test --filter RegisterTest
2. php artisan test --filter LoginTest
3. php artisan test --filter LoginAdminTest
4. php artisan test --filter AttendanceDateTest
5. php artisan test --filter AttendanceStatusTest
6. php artisan test --filter AttendanceClockInTest
7. php artisan test --filter AttendanceBreakTest
8. php artisan test --filter AttendanceClockOutTest
9. php artisan test --filter StaffIndexTest
10. php artisan test --filter DetailTest
11. php artisan test --filter StaffEditTest
12. php artisan test --filter AdminIndexTest
13. php artisan test --filter AdminDetailUpdateTest
14. php artisan test --filter AdminStaffListTest
15. php artisan test --filter AdminApproveTest
16. php artisan test --filter EmailVerifyTest
```

## 使用技術
- MySQL 8.0.26
- PHP 8.2-fpm
- Laravel 11
- MailHog（開発用メールサーバ）
- phpMyAdmin


## URL
- 環境開発
  1. 一般ユーザーのサイト: http://localhost/login
  1. 管理者ユーザーのサイト: http://localhost/admin/login

- phpMyAdmin: http://localhost:8080/
- MailHog: http://localhost:8025

## ER図
![image](attendance-management-system.drawio.png)
