# Neo Med Cardio — sayt demosi

## Qanday ishga tushirish (VS Code)

1. Papkani (`neomedcardio-site`) VS Code'da oching.
2. "Live Server" kengaytmasi o'rnatilgan bo'lsin (Extensions → "Live Server" qidiring, o'rnating).
3. `index.html` faylini oching → pastki o'ng burchakdagi **"Go Live"** tugmasini bosing.
4. Brauzerda avtomatik ochiladi (odatda `http://127.0.0.1:5500`).

**Muhim:** faylni to'g'ridan-to'g'ri (double-click) ochmang — `fetch()` orqali header/footer yuklanadi, bu faqat http server orqali ishlaydi (Live Server buni avtomatik hal qiladi).

## Fayl tuzilmasi

```
neomedcardio-site/
├── index.html          → bosh sahifa
├── xizmatlar.html       → xizmatlar va narxlar
├── shifokorlar.html     → barcha shifokorlar
├── kontaktlar.html      → kontaktlar, forma, xarita
├── css/style.css        → barcha dizayn (ranglar, shriftlar shu yerda)
├── js/main.js           → animatsiyalar, menyu, filtrlar
├── js/i18n.js           → UZ/RU tarjimalar — YANGI MATN shu yerga qo'shiladi
├── images/               → logo va shifokor rasmlari
└── partials/             → header va footer (barcha sahifalarda umumiy)
```

## Nimalarni albatta almashtirish/tekshirish kerak

- [ ] **Narxlar** — `xizmatlar.html` ichidagi narxlar taxminiy. Har birini klinika bilan aniqlashtiring.
- [ ] **Telegram bot havolasi** — `https://t.me/neomedcardioclinicbot` hali header/footer va "Telegram orqali yozilish" tugmalarida ishlatiladi. Kontaktlar formasi endi botga yo'naltirmaydi — ariza to'g'ridan-to'g'ri admin panelga (`admin/`) saqlanadi, qarang `DEPLOY.md`.
- [ ] **Xarita** — `kontaktlar.html` ichidagi Yandex xarita koordinatalari (`ll=69.2537,41.2907`) — klinikaning aniq joylashuviga moslab sozlang (Yandex/Google Maps'dan "share/embed" havolasini oling).
- [ ] **Pochta manzili** — footer'dagi `info@neomedcardio.uz` — agar haqiqiy pochta bo'lsa, shuni qo'ying.
- [ ] **Shifokorlar** — hozir Instagram/Telegramdan olingan 7 ta rasm ishlatilgan. Qolgan shifokorlar (LOR, pediatr, endokrinolog va h.k.) rasmi kelsa, xuddi shu namunada `shifokorlar.html` ga qo'shiladi.

## Matn/tarjima qo'shish yoki o'zgartirish

Barcha matnlar `js/i18n.js` faylida **kalit: qiymat** tarzida saqlanadi (masalan `"hero.title.1"`). HTML ichida esa faqat `data-i18n="hero.title.1"` deb yozilgan. Matnni o'zgartirish uchun **faqat `i18n.js`** faylini tahrirlang — HTML'ga tegmang.

## Texnologiyalar

Hech qanday build-tool (npm, webpack va h.k.) kerak emas — 100% toza HTML/CSS/JS. Shuning uchun Live Server bilan to'g'ridan-to'g'ri ishlaydi va istalgan hostingga (shu jumladan oddiy shared hosting) yuklab qo'yish mumkin.

Animatsiyalar: `IntersectionObserver` (scroll bilan paydo bo'lish), CSS `stroke-dashoffset` (ECG chizig'i), CSS keyframes (yurak urishi, suzuvchi kartochka).
