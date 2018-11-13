all:
	sudo chown www-data core/storage/time_travel.json
	touch rpc/storage/logs/laravel.log
	sudo chown -R www-data rpc/storage/logs
	sudo chmod g+w rpc/storage/logs/laravel.log
	sudo chown -R www-data rpc/storage/framework
	mkdir -p rpc/bootstrap/cache
	sudo chown -R www-data rpc/bootstrap/cache
	cp rpc/.env.example rpc/.env
	composer install
