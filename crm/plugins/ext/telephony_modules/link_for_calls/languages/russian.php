<?php 

define('TEXT_MODULE_LINK_FOR_CALLS_TITLE','Ссылка для звонков');
define('TEXT_MODULE_LINK_FOR_CALLS_LINK_PREFIX','URL Префикс');
define('TEXT_MODULE_LINK_FOR_CALLS_LINK_PREFIX_INFO','<p>Этот модуль преобразует номер телефона в url. Введите префикс URL-адреса, например: sip:, callto:, tel:</p>
		В качестве префикса можно ввести собственный url, например:<br>
		<i>http://localhost/cgi-bin/app.com?Number=[phone]&Account=@[13]</i><br>
		[phone] - номер телефона из поля Телефон.<br>
		[13] - поле ввода из сущности пользователи, заменяется на значение текущего пользователя.');
