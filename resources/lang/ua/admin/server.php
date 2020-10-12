<?php
/**
 * Pterodactyl - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

return [
    'exceptions' => [
        'no_new_default_allocation' => 'Ви намагаєтесь видалити розподіл за замовчуванням для цього сервера, але резервного розподілу для використання немає.',
        'Marked_as_Failed' => 'Цей сервер позначено як невдалий під час попередньої інсталяції. Поточний статус не можна перемикати в цьому стані. ',
        'bad_variable' => 'Сталася помилка перевірки із змінною: name.',
        'daemon_exception' => 'Під час спроби зв’язку з демоном стався виняток, що призвів до коду відповіді коду HTTP /:. Цей виняток було зареєстровано. ',
        'default_allocation_not_found' => 'Запитаний розподіл за замовчуванням не був знайдений у розподілах цього сервера.',
    ],
    'alerts' => [
        'startup_changed' => 'Конфігурацію запуску для цього сервера оновлено. Якщо гніздо або яйце цього сервера було змінено, зараз відбудеться перевстановлення. ',
        'server_deleted' => 'Сервер успішно видалено з системи.',
        'server_created' => 'Сервер було успішно створено на панелі. Будь ласка, дайте демону кілька хвилин, щоб повністю встановити цей сервер. ',
        'build_updated' => 'Деталі збірки для цього сервера були оновлені. Для деяких змін може знадобитися перезапуск, щоб набути чинності. ',
        'suspension_toggled' => 'Статус призупинення сервера змінено на: status.',
        'rebuild_on_boot' => 'Цей сервер позначено як такий, що вимагає відновлення контейнера Docker. Це станеться при наступному запуску сервера. ',
        'install_toggled' => 'Стан інсталяції для цього сервера змінено.',
        'server_reinstalled' => 'Цей сервер в черзі на переінсталяцію, починаючи зараз.',
        'details_updated' => 'Деталі сервера були успішно оновлені.',
        'docker_image_updated' => 'Успішно змінено образ Docker за замовчуванням для використання на цьому сервері. Для застосування цієї зміни потрібно перезавантажити. ',
        'node_required' => 'Ви повинні мати принаймні один вузол, налаштований, перш ніж ви зможете додати сервер на цю панель.',
        'transfer_nodes_required' => 'Перед передачею серверів у вас повинні бути налаштовані принаймні два вузли.',
        'transfer_started' => 'Передача сервера розпочата.',
        'transfer_not_viable' => 'Вибраний вузол не є життєздатним для цього перенесення.',
    ],
];

