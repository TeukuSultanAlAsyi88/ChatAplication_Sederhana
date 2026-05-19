# Panduan Upload GitHub

1. Extract file ZIP.
2. Buka terminal di folder project.
3. Jalankan perintah berikut:

```bash
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/username/chattatan-pro-plus.git
git push -u origin main
```

Pastikan file `.env` tidak berisi data rahasia jika repository public.
