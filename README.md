1. Парсинг отчёта и расчет баланса
Парсер реализован в StatementController::parseStatement().
Логика:
Ищет таблицу, определяет столбец profit (без учёта регистра).
Проходит по всем строкам, где в столбце profit содержится число.
Баланс считается как сумма изменений (profit), начиная с нуля.
Баланс не может быть отрицательным (есть проверка: если баланс < 0, то баланс = 0).
Тип операции не учитывается — только числовой profit.

2. Возможность загрузки любого файла и построения нового графика
Форма загрузки реализована во view views/statement/index.php через стандартный виджет Yii2.
Валидация: только HTML-файлы, проверка структуры, обработка ошибок.
После загрузки строится новый график по новым данным.

3. График баланса
Используется Chart.js — современная, интерактивная JS-библиотека.
График строится по массиву балансов (ось X — номер сделки, ось Y — баланс).
Интерактивность: масштабирование, tooltips, адаптивность.

4. Устойчивость к негативным тестам
Обработка ошибок:
Нет таблицы — выводится сообщение.
Нет столбца profit — сообщение.
Нет строк с числовым profit — сообщение.
Невалидный файл — сообщение.
Валидация на сервере и клиенте.

5. Валидность верстки
Форма загрузки: Bootstrap, стандартные виджеты Yii2, корректная разметка.
Страница графика: чистый HTML5, корректная интеграция Chart.js, кнопка возврата.
Нет лишних или устаревших HTML-элементов.

6. Соответствие интерфейса задаче
Интерфейс минималистичный, интуитивный: загрузка → график → повторная загрузка.
Нет лишних шагов, всё по задаче.
Возможность повторной загрузки любого файла.

7. Дополнительные плюсы реализации
Код легко расширяем (можно добавить drag&drop, предпросмотр, экспорт и т.д.).
Безопасность: файлы не сохраняются на сервере, только временно обрабатываются.
Сообщения об ошибках информативны.

Вывод
График строится корректно и интерактивно.
Парсинг устойчив к ошибкам и невалидным данным.
Интерфейс чистый, валидный и удобный.
Решение легко масштабируется и поддерживается.
