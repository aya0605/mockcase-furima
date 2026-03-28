# フリマアプリ

## 環境構築

### Dockerビルド
1.  git clone [https://github.com/aya0605/mockcase-furima.git](https://github.com/aya0605/mockcase-furima.git)
2.  cd coachtech/laravel/mockcase-furima
3.  DockerDesktopアプリを立ち上げる
4.  docker-compose up -d --build
    ```yaml
    mysql:
        platform: linux/x86_64(この文追加)
        image: mysql:8.0.26
        environment:
    ```

### Laravel環境構築
1.  docker-compose exec php bash
2.  composer install
3. 「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.envファイルを作成
4.  .envに以下の環境変数を設定（データベース・メール設定)
    ```dotenv
    DB_CONNECTION=mysql  
    DB_HOST=mysql  
    DB_PORT=3306  
    DB_DATABASE=laravel_db  
    DB_USERNAME=laravel_user  
    DB_PASSWORD=laravel_pass  
    ```
    ```
    MAIL_MAILER=smtp
    MAIL_HOST=mailhog
    MAIL_PORT=1025
    MAIL_USERNAME=null
    MAIL_PASSWORD=null
    MAIL_ENCRYPTION=null
    MAIL_FROM_ADDRESS="admin@example.com"
    ```


5.  アプリケーションキーの作成
    ```bash
    php artisan key:generate
    ```

6.  マイグレーションの実行
    ```bash
    php artisan migrate
    ```

7.  シーディングの実行
    ```bash
    php artisan db:seed
    ```

8.  シンボリックリンク作成
    ```bash
    php artisan storage:link
    ```

## 使用技術(実行環境)

* PHP8.3.0
* Laravel8.83.27
* MySQL8.0.26

### ER図
erDiagram
    Users ||--o{ Items : "出品(seller_id)"
    Users ||--o{ Addresses : "所有"
    Users ||--o{ Purchases : "購入(user_id)"
    Users ||--o{ Messages : "送信(user_id)"
    Users ||--o{ Ratings : "評価(from/to_user_id)"
    Users ||--o{ Likes : "いいね"
    Users ||--o{ Comments : "投稿"

    Items ||--o{ Item_Category : "中間"
    Categories ||--o{ Item_Category : "中間"
    Items ||--|| Purchases : "1つの取引"
    Items ||--o{ Messages : "チャット履歴"
    Items ||--o{ Likes : "被いいね"
    Items ||--o{ Comments : "商品への質問"
    
    Conditions ||--o{ Items : "状態定義"

    Users {
        bigint id PK
        string name
        string email
        string profile_image_path
    }

    Items {
        bigint id PK
        bigint seller_id FK "Users.id"
        bigint condition_id FK "Conditions.id"
        string name
        integer price
        boolean is_sold
    }

    Purchases {
        bigint id PK
        bigint user_id FK "Users.id (購入者)"
        bigint item_id FK "Items.id"
        bigint shipping_address_id FK "Addresses.id"
        timestamp seller_last_read_at "既読管理"
        timestamp buyer_last_read_at "既読管理"
    }

    Messages {
        bigint id PK
        bigint item_id FK "Items.id"
        bigint user_id FK "Users.id (送信者)"
        text content
        string image_url
    }

    Ratings {
        bigint id PK
        bigint item_id FK "Items.id"
        bigint from_user_id FK "Users.id"
        bigint to_user_id FK "Users.id"
        integer rating "1-5のスコア"
    }

    Addresses {
        bigint id PK
        bigint user_id FK "Users.id"
        string postal_code
        string address
        boolean is_default
    }

    Conditions {
        bigint id PK
        string condition "新品/中古など"
    }

## テストアカウント
name: 一般ユーザ  
email: general1@gmail.com  
password: password  
-------------------------
name: 一般ユーザ  
email: general2@gmail.com  
password: password  
-------------------------
name: テストユーザ  
email: test@example.com  
password: password  
-------------------------

## URL
* **開発環境**： (http://localhost/)
* **phpMyAdmin**:  (http://localhost:8080/)
* **Mailhog(メール確認)**: http://localhost:8025/


