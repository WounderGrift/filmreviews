Создание модуля:
php artisan module:make (Название модуля)
добавить в композер "Modules\\(Название модуля)\\":"Modules/(Название модуля)/app/"
и выполнить composer dump-autoload

Создание компонента в модуле:
php artisan module:make-component (Название компонента) (Название модуля в нижнем регистре)
