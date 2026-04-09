<?php

return [

    'accepted' => 'Необходимо принять :attribute.',
    'confirmed' => 'Подтверждение :attribute не совпадает.',
    'current_password' => 'Неверный пароль.',
    'email' => 'Поле :attribute должно содержать корректный email.',
    'exists' => 'Выбранное значение :attribute недопустимо.',
    'image' => 'Поле :attribute должно быть изображением.',
    'max' => [
        'array' => 'В поле :attribute не должно быть больше :max элементов.',
        'file' => 'Размер :attribute не должен превышать :max КБ.',
        'numeric' => 'Значение :attribute не должно быть больше :max.',
        'string' => 'Текст :attribute не должен быть длиннее :max символов.',
    ],
    'min' => [
        'string' => 'Текст :attribute должен содержать не менее :min символов.',
    ],
    'password' => [
        'letters' => 'Поле :attribute должно содержать хотя бы одну букву.',
        'mixed' => 'Поле :attribute должно содержать строчные и прописные буквы.',
        'numbers' => 'Поле :attribute должно содержать хотя бы одну цифру.',
        'symbols' => 'Поле :attribute должно содержать хотя бы один символ.',
        'uncompromised' => 'Этот :attribute был скомпрометирован. Выберите другой.',
    ],
    'required' => 'Поле :attribute обязательно.',
    'string' => 'Поле :attribute должно быть строкой.',
    'unique' => 'Такое значение поля :attribute уже занято.',

    'attributes' => [
        'name' => 'имя',
        'email' => 'email',
        'password' => 'пароль',
        'content' => 'текст',
        'body' => 'текст',
        'image' => 'изображение',
        'avatar' => 'аватар',
        'bio' => 'о себе',
        'post_id' => 'публикация',
    ],

];
