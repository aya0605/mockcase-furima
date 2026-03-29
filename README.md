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
```mermaid
erDiagram
    users ||--o{ profiles : ""
    users ||--o{ items : ""
    users ||--o{ sold_items : ""
    users ||--o{ likes : ""
    users ||--o{ comments : ""
    
    items ||--o{ category_items : ""
    categories ||--o{ category_items : ""
    items ||--o{ sold_items : ""
    items ||--o{ likes : ""
    items ||--o{ comments : ""
    condition ||--o{ items : ""

    users {
        id
        name
        email
        password
    }

    profiles {
        id
        user_id
        img_url
        postcode
        address
    }

    items {
        id
        user_id
        condition_id
        name
        price
        description
    }

    sold_items {
        id
        user_id
        item_id
        sending_postcode
        sending_address
    }

    categories {
        id
        category
    }

    condition {
        id
        condition
    }

    likes {
        id
        user_id
        item_id
    }

    comments {
        id
        user_id
        item_id
        comment
    }
```

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


