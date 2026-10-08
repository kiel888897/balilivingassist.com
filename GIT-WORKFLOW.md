# Git Workflow: Lokal ↔ Hosting

## Struktur Environment

| Environment | Lokasi                                         | File .env            |
| ----------- | ---------------------------------------------- | -------------------- |
| **Lokal**   | `C:\xampp\htdocs\bla-prod`                     | `.env` (development) |
| **Hosting** | `/home/kusc1568/.../dev.balilivingassist.com/` | `.env` (production)  |

## File yang TIDAK masuk Git (di-ignore)

```
.env              # Konfigurasi environment (beda tiap server)
.env.production   # Template .env production
/vendor           # Dependencies (install via composer)
/node_modules     # Dependencies frontend
/storage/*.key    # Key Laravel
*.zip, *.log      # File temporary
```

## Workflow Harian

### 1. Kerja di Lokal → Push ke GitHub

```bash
cd C:\xampp\htdocs\bla-prod

# Cek status
git status

# Add & commit
git add .
git commit -m "Deskripsi perubahan"

# Push ke GitHub
git push origin main
```

### 2. Deploy ke Hosting (Git Version Control cPanel)

Karena hosting punya **Git Version Control**, deploy lebih mudah:

#### Setup Awal (Sekali Saja)

1. **cPanel → Git™ Version Control**
2. **Create** → Clone dari GitHub:
    - Clone URL: `https://github.com/kiel888897/balilivingassist.com.git`
    - Repository Path: `/home/kusc1568/public_html/dev.balilivingassist.com`
    - Branch: `main`
3. **Konfigurasi deployment** (jika ada):
    - Document Root: `/home/kusc1568/public_html/dev.balilivingassist.com/public`
4. **Upload `vendor/` manual** via File Manager (karena Git tidak include vendor)

#### Deploy Harian

1. **Lokal:** `git push origin main`
2. **cPanel → Git Version Control → Manage → Pull or Deploy**
3. **Jalankan via browser:**
    - `https://dev.balilivingassist.com/fix-permissions.php` (jika permission berubah)
    - `https://dev.balilivingassist.com/setup-cache-7f3a9b2e` (untuk config:cache)

#### Alternatif: Pull Otomatis via Webhook

Beberapa cPanel support **Webhook URL**:

1. Di GitHub repo → Settings → Webhooks → Add webhook
2. URL: `https://dev.balilivingassist.com/git-webhook.php` (perlu dibuat)
3. Content type: `application/json`
4. Secret: (optional)

Contoh `public/git-webhook.php`:

```php
<?php
// HAPUS setelah setup selesai
$secret = 'rahasia-webhook-anda'; // Ganti dengan secret kuat

// Verifikasi webhook GitHub
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload = file_get_contents('php://input');
$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($hash, $signature)) {
    http_response_code(403);
    die('Forbidden');
}

// Jalankan git pull
$output = shell_exec('cd /home/kusc1568/public_html/dev.balilivingassist.com && git pull origin main 2>&1');
echo "<pre>$output</pre>";

// Jalankan composer install jika perlu
// $output = shell_exec('cd /home/kusc1568/public_html/dev.balilivingassist.com && composer install --no-dev 2>&1');

echo "Deploy selesai";
```

## Setup Awal Git (Jika belum)

```bash
cd C:\xampp\htdocs\bla-prod

# Inisialisasi Git
git init

# Add semua file
git add .

# Commit pertama
git commit -m "Initial commit: Laravel 8 + vendor production"

# Buat repo di GitHub, lalu:
git remote add origin https://github.com/username/bla-prod.git
git branch -M main
git push -u origin main
```

## File Penting untuk Git

### `.env.example` (Masuk Git)

Template `.env` untuk development. Isi dengan nilai default lokal.

### `.env.production` (Di-ignore Git)

Template `.env` production. **Jangan masukkan ke Git** karena mengandung:

- `APP_KEY` production
- Database password
- URL production

Simpan `.env.production` di tempat aman (password manager, notes terenkripsi).

### `composer.lock` (Masuk Git)

File ini memastikan semua environment install versi package yang sama.

## Checklist Sebelum Push

- [ ] `.env` lokal tidak ter-commit (harus di `.gitignore`)
- [ ] `.env.production` tidak ter-commit
- [ ] `vendor/` tidak ter-commit
- [ ] File test/debug sudah dihapus
- [ ] `composer.lock` ter-update

## Troubleshooting

### "Permission denied" setelah extract di hosting

Jalankan `fix-permissions.php` via browser, atau set manual via cPanel File Manager:

- Folder: 755
- File: 644
- `storage/`, `bootstrap/cache/`: 775 (atau 777 jika perlu)

### Config tidak berubah setelah update `.env`

Jalankan `config:clear` dan `config:cache` ulang via route sementara.

### Composer dependencies tidak terinstall

**Cara 1:** Upload ulang folder `vendor/` dari lokal via File Manager

**Cara 2:** Buat file `public/composer-install.php`:

```php
<?php
// HAPUS setelah selesai
set_time_limit(300);
$output = shell_exec('cd /home/kusc1568/public_html/dev.balilivingassist.com && /usr/local/bin/ea-php80 /usr/local/bin/composer install --no-dev --optimize-autoloader 2>&1');
echo "<pre>$output</pre>";
```

Akses `https://dev.balilivingassist.com/composer-install.php`, lalu hapus file tersebut.

### Git pull tidak bekerja di cPanel

Pastikan:

- Repository path benar
- Branch `main` ada di GitHub
- Tidak ada konflik file (cek `git status` di cPanel jika ada terminal)

## Workflow Ringkas (Dengan Git cPanel)

```
┌─────────────┐     git push      ┌─────────────┐     Pull via cPanel     ┌─────────────┐
│   Lokal     │ ────────────────→ │   GitHub    │ ─────────────────────→ │   Hosting   │
│  (XAMPP)    │                   │  (Repo)     │                        │  (cPanel)   │
└─────────────┘                   └─────────────┘                        └─────────────┘
      ↑                                                                  │
      └──────────────────── Test & Debug ←───────────────────────────────┘
```

### Langkah Harian:

1. **Coding** di lokal → test di `http://localhost/bla-prod`
2. **Commit & push:** `git add . && git commit -m "..." && git push`
3. **cPanel → Git → Pull/Deploy**
4. **Browser:** `https://dev.balilivingassist.com/setup-cache-7f3a9b2e` (clear & cache config)

## Tips

1. **Selalu test di lokal dulu** sebelum push
2. **Backup database** sebelum deploy besar
3. **Gunakan branch** untuk fitur besar: `git checkout -b fitur-baru`
4. **Tag release** untuk versi stabil: `git tag v1.0.0`
5. **Vendor di-upload manual sekali saja** — setelah itu hanya perlu update jika `composer.lock` berubah
