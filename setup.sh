echo "Создаем образы сервисов..."
docker compose build

echo "Создаем .env на основе .env.example..."
docker compose run --rm --no-deps app cp .env.example .env

echo "Устанавливаем PHP зависимости..."
docker compose run --rm --no-deps app composer install

echo "Устанавливаем JS зависимости..."
docker compose run --rm --no-deps app npm install

echo "Генерируем ключ приложения..."
docker compose run --rm --no-deps app php artisan key:generate

echo "Создаем символическую ссылку с public/storage на storage/app/public..."
docker compose run --rm --no-deps app php artisan storage:link

echo "Готово! Сконфигурируйте пароли mysql в .env (DB_ROOT_PASSWORD, DB_PASSWORD) и запустите setup-db.sh"
