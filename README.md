# あみぷろ (Amipro)

あみぷろは、FuelPHP と Knockout.js を用いており、Docker でローカル環境を構築可能です。

---

## 必要環境

- Docker & Docker Compose
- Git
- ブラウザ（Chrome / Firefox 推奨）

---

## Docker でのセットアップ

1. リポジトリをクローン:

```bash
git clone https://github.com/username/amipro.git
cd amipro
```

2. Docker コンテナをビルド・起動し、初期マイグレーションを実行:

```bash
docker compose up -d --build
docker compose exec app php oil refine migrate
docker compose exec app php oil refine session
```

- その次のコマンドで、createを入力してください。

---

## データベース

- MySQL を使用
- FuelPHP の `db.php` で接続確認可能
- データベースの内容を確認したい場合:

```bash
docker compose exec db mysql -u amipro_user -p amipro_db
```

- パスワードはamipro_passとなります。

---

## ローカルでのアクセス

Docker 起動後、ブラウザでアクセス:
```http://localhost:8080```

- ポートはdocker-compose.ymlファイルから変更できます。

---