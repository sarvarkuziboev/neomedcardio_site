# Admin panelni sayt.uz'ga joylashtirish

Bu qo'llanma `admin/` (zayavkalarni ko'rish paneli) va `api/submit.php` (forma qabul qiluvchi skript) ni sayt.uz hostingiga birinchi marta o'rnatish uchun.

## 1. MySQL baza yaratish

ISPmanager panelida:
1. "Ma'lumotlar bazalari" (Databases) bo'limiga kiring.
2. Yangi MySQL baza yarating (masalan `neomedcardio`).
3. Bazaga ulanadigan foydalanuvchi yarating, kuchli parol qo'ying. Baza nomi, foydalanuvchi nomi, parol va host (odatda `localhost`) ni yozib oling.

## 2. Jadvallarni import qilish

1. ISPmanager'dagi phpMyAdmin'ni oching (yoki "Ma'lumotlar bazasi" yonidagi "Boshqarish" tugmasi).
2. Yaratilgan bazani tanlang → "Import" (Импорт) bo'limiga o'ting.
3. `sql/schema.sql` faylini tanlab yuklang (Go/Bajarish tugmasi).
4. `admins` va `submissions` jadvallari yaratilganini tekshiring.

## 3. Admin login/parol yaratish

Haqiqiy parolni git'ga yoki serverga oshkora yozmang — parolning shifrlangan (`hash`) ko'rinishini oldindan generatsiya qiling:

Kompyuteringizda PHP o'rnatilgan bo'lsa, terminalda:

```
php -r "echo password_hash('HAQIQIY_PAROL', PASSWORD_DEFAULT);"
```

Chiqadigan uzun matnni (masalan `$2y$10$...`) nusxalab oling, so'ng phpMyAdmin'da `admins` jadvaliga qo'lda qator qo'shing (Insert):

```
username: admin
password_hash: <yuqoridagi hash>
```

## 4. Fayllarni yuklash

`neomedcardio-site/` papkasidagi barcha fayl va papkalarni (jumladan yangi `admin/`, `api/`, `includes/`, `sql/`) ISPmanager File Manager yoki FTP/SFTP orqali hosting'dagi asosiy papkaga (`public_html` yoki shunga o'xshash) yuklang.

**Muhim:** `includes/config.php` fayli git'da yo'q (`.gitignore`'da) — uni serverga alohida yuklashingiz yoki to'g'ridan-to'g'ri serverda yaratishingiz kerak.

## 5. `includes/config.php` ni to'ldirish

Serverda `includes/config.example.php` nusxasini `includes/config.php` deb saqlang (yoki File Manager'da yangi fayl yarating) va haqiqiy qiymatlarni kiriting:

```php
<?php
return [
    'db_host'    => 'localhost',
    'db_name'    => 'neomedcardio',
    'db_user'    => '<sizning DB foydalanuvchingiz>',
    'db_pass'    => '<sizning DB parolingiz>',
    'db_charset' => 'utf8mb4',
];
```

## 6. HTTPS'ni tekshirish

ISPmanager'da sayt uchun bepul SSL (Let's Encrypt) yoqilganini tekshiring. Admin panel kirish sessiyasi HTTPS talab qiladi — HTTP orqali kirishda login ishlamasligi mumkin.

## 7. Sinov

1. `kontaktlar.html` sahifasidagi formani to'ldirib yuboring.
2. `sizsayt.uz/admin/login.php` ga kirib, 3-qadamda yaratgan login/parol bilan kiring.
3. Yuborilgan ariza jadvalda ko'rinishini tekshiring.
4. Telegram bot hali ham ochilishini tekshiring (u eskichasiga ishlashda davom etadi).

## 8. Qo'shimcha (ixtiyoriy)

`admin/` papkasini ISPmanager'ning HTTP-autentifikatsiya funksiyasi bilan qo'shimcha himoyalash mumkin — bu ikkinchi qatlam himoya bo'ladi, lekin shart emas.
