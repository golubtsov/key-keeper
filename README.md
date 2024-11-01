
# Key-keeper

Создать ``.env`` с настрйками окружения

## Сборка

```
php key-keeper app:build
```

Не забыть перенести ``.env`` в папку ``./builds``

## Команды

```
passwords:delete        - Delete password
passwords:get_password  - Get password
passwords:list          - My passwords
passwords:new_password  - Add new password
passwords:upload        - Upload passwords
```

## Разработка

```
./vendor/bin/phpinsights
```

```
./vendor/bin/phpinsights --fix
```

```
vendor/bin/phpstan analyse
```
