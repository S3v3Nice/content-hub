echo "Создаем таблицы БД и выполняем начальное заполнение данных..."
docker compose run --rm app php artisan migrate:fresh --seed

docker compose down
echo "Готово!"