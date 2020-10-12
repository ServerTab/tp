<?php

return [
    'permissions' => [
        'websocket_ *' => 'Дозволяє доступ до веб-сокета для цього сервера.',
        'control_console' => 'Дозволяє користувачеві надсилати дані на консоль сервера.',
        'control_start' => 'Дозволяє користувачеві запускати екземпляр сервера.',
        'control_stop' => 'Дозволяє користувачеві зупинити екземпляр сервера.',
        'control_restart' => 'Дозволяє користувачеві перезапустити екземпляр сервера.',
        'control_kill' => 'Дозволяє користувачеві вбити екземпляр сервера.',
        'user_create' => 'Дозволяє користувачеві створювати нові облікові записи користувача для сервера.',
        'user_read' => 'Дозволяє користувачеві переглядати користувачів, пов'язаних з цим сервером.',
        'user_update' => 'Дозволяє користувачеві змінювати інших користувачів, пов'язаних з цим сервером.',
        'user_delete' => 'Дозволяє користувачеві видаляти інших користувачів, пов'язаних з цим сервером.',
        'file_create' => 'Дозволяє користувачеві дозволити створювати нові файли та каталоги.',
        'file_read' => 'Дозволяє користувачеві переглядати файли та папки, пов'язані з цим екземпляром сервера, а також переглядати їх вміст.',
        'file_update' => 'Дозволяє користувачеві оновлювати файли та папки, пов'язані з сервером.',
        'file_delete' => 'Дозволяє користувачеві видаляти файли та каталоги.',
        'file_archive' => 'Дозволяє користувачеві створювати архіви файлів та розпаковувати існуючі архіви.',
        'file_sftp' => 'Дозволяє користувачеві виконувати вищезазначені дії з файлом за допомогою клієнта SFTP.',
        'allocation_read' => 'Дозволяє доступ до серверних сторінок управління розподілом.',
        'allocation_update' => 'Дозволяє користувачеві вносити зміни до розподілу сервера.',
        'database_create' => 'Дозволяє користувачеві дозволити створювати нову базу даних для сервера.',
        'database_read' => 'Дозволяє користувачеві переглядати бази даних сервера.',
        'database_update' => 'Дозволяє користувачеві вносити зміни до бази даних. Якщо користувач також не має дозволу "Переглянути пароль", він також не зможе змінити пароль. ',
        'database_delete' => 'Дозволяє користувачеві дозволити видаляти екземпляр бази даних.',
        'database_view_password' => 'Дозволяє користувачеві переглядати пароль бази даних у системі.',
        'schedule_create' => 'Дозволяє користувачеві створювати новий графік для сервера.',
        'schedule_read' => 'Дозволяє користувачеві дозволити перегляд розкладів для сервера.',
        'schedule_update' => 'Дозволяє користувачеві дозволити вносити зміни до існуючого розкладу сервера.',
        'schedule_delete' => 'Дозволяє користувачеві видалити розклад для сервера.',
    ],
];
