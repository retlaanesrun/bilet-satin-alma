# 🚌 Bilet Satın Alma Platformu  
**PHP 8 + SQLite + Docker Compose** ile geliştirilen tam fonksiyonel otobüs bileti satış platformu.

> 🎯 Bu proje, *Admin / Firma Admin / Kullanıcı (Yolcu)* rollerine sahip bir bilet satış sisteminin temel işlevlerini gerçekleştirmek üzere geliştirilmiştir.  
> Kodlar tamamen PHP üzerinde, Docker ile konteynerize edilmiştir.

## 🚀 Özellikler

| Özellik | Açıklama |
|----------|-----------|
| 👤 **Roller** | Admin, Firma Admin, User (Yolcu) |
| 🧭 **Sefer Arama** | Kalkış/Varış noktasına göre filtreleme |
| 🪑 **Koltuk Seçimi** | Dolu koltuklar devre dışı, boş koltuk seçilebilir |
| 💳 **Bilet Satın Alma** | Kullanıcının kredisi düşülerek, kupon indirimi uygulanarak bilet oluşturulur |
| 💰 **Kupon Desteği** | Kupon kodu ile yüzde bazlı indirim (örn: `INDIRIM10`) |
| ⏱️ **İptal Kuralı** | Kalkışa **1 saatten az** kaldıysa iptal edilemez |
| 🔁 **Bilet İptali** | Kullanıcı ve Firma Admin bilet iptali yapabilir, ücret krediye iade edilir |
| 🧾 **PDF Bilet** | PHP ile dinamik olarak oluşturulmuş, indirilebilir PDF bilet |
| 🏢 **Firma Admin Paneli** | Sefer ekleme/silme, satış listesi ve iptal işlemleri |
| 🧰 **Admin Paneli** | Firma, Firma Admin ve Kupon yönetimi |
| 🐳 **Docker Compose** | `nginx` + `php-fpm` + `sqlite` (tek komutla çalışır) |

## ⚙️ Kurulum ve Çalıştırma

### Gereksinimler
- [Docker](https://www.docker.com/)  
- [Docker Compose](https://docs.docker.com/compose/)

### Kurulum Adımları
```bash
cd bilet-satin-alma
mkdir -p data
docker compose up --build
```
Tarayıcı: **http://localhost:8080**

## 🔑 Örnek Giriş Bilgileri

| Rol | E-posta | Şifre | Açıklama |
|-----|----------|--------|-----------|
| 🧙‍♂️ Admin | admin@example.com | admin123 | Firma ve kupon yönetimi |
| 🏢 Firma Admin | firma@example.com | firm123 | Kendi firmasına ait sefer CRUD + satış/iptal |
| 👤 Kullanıcı | user@example.com | user123 | Sefer arama, bilet satın alma, iptal, PDF indir |

## 💼 Rollere Göre Yetkiler

| İşlem | Admin | Firma Admin | Kullanıcı |
|-------|:------:|:------------:|:----------:|
| Sefer Ekle/Sil | ✅ | ✅ (kendi firması) | ❌ |
| Firma Ekle/Sil | ✅ | ❌ | ❌ |
| Firma Admin Atama | ✅ | ❌ | ❌ |
| Kupon Yönetimi | ✅ | ❌ | ❌ |
| Bilet Satın Alma | ❌ | ❌ | ✅ |
| Bilet İptal Etme | ❌ | ✅ | ✅ |
| Bilet PDF Görüntüleme | ❌ | ❌ | ✅ |

> ⏰ Bilet iptali yalnızca kalkıştan **en az 1 saat önce** yapılabilir.

## 🧾 PDF Bilet Özellikleri
- PHP ile dinamik olarak oluşturulur.
- Yolcu, sefer, koltuk, tutar, durum bilgileri içerir.
- `ticket_pdf.php` üzerinden görüntülenir.
- Gerektiğinde FPDF ile UTF-8 uyumlu hale getirilebilir.

## 🐳 Docker Servisleri
**docker-compose.yml**
```yaml
services:
  php:
    build: .
    volumes:
      - .:/var/www/html
      - appdata:/var/www/html/data
    expose:
      - "9000"

  nginx:
    image: nginx:1.27
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
      - ./nginx.conf:/etc/nginx/nginx.conf:ro
    depends_on:
      - php

volumes:
  appdata:
```

**Dockerfile**
```dockerfile
FROM php:8.2-fpm
RUN apt-get update && apt-get install -y libsqlite3-dev && docker-php-ext-install pdo pdo_sqlite
WORKDIR /var/www/html
COPY . /var/www/html
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html/data
USER www-data
CMD ["php-fpm"]
```

## 📂 Klasör Yapısı
```
bilet-satin-alma/
├── public/
│   ├── index.php
│   ├── trip_details.php
│   ├── buy_ticket.php
│   ├── my_tickets.php
│   ├── cancel_ticket.php
│   ├── ticket_pdf.php
│   ├── admin/
│   └── company/
│       ├── trips.php
│       ├── sales.php
│       └── ticket_cancel.php
├── src/
│   ├── db.php
│   ├── auth.php
│   ├── helpers.php
│   ├── pdf_basic.php
├── schema.sql
├── Dockerfile
├── docker-compose.yml
├── nginx.conf
└── README.md
```

## 📦 Teslim & Dağıtım

```bash
git init
git add .
git commit -m "Bilet Satın Alma Platformu MVP"
git branch -M main
git remote add origin https://github.com/<kullanici-adin>/bilet-satin-alma.git
git push -u origin main
```

## 👏 Katkı ve Lisans
Bu proje eğitim amaçlıdır.  
**Lisans:** MIT

> “Yolculuk her zaman bir biletle başlar.”
