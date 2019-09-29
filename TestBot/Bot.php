<?php
	if (!isset($_REQUEST)) {
		return;
	}
	// Строка, которую должен вернуть сервер (См. Callback API->Настройки сервера)
	$confirmationToken = 'b1357503';
	// Ключ доступа сообщества (длинная строчка которую получили нажав "создать ключ")
	$token = '27cf5a29ce0895eed31d9dc76c6cc08357ac0df223bc87dafa47181071183ff58189e5d4c81235fbfbc82';
	// Секретный ключ. (Задаем в Callback API->Настройки сервера)
	$secretKey = 'a1b2c4d3';
	
	// Получаем и декодируем уведомление
	$data = json_decode(file_get_contents('php://input'));
	// проверяем secretKey
	if (strcmp($data->secret, $secretKey) !== 0 && strcmp($data->type, 'confirmation') !== 0) {return;}
	
		
	// Проверяем, что находится в поле "type"
	switch ($data->type) {
		// Запрос для подтверждения адреса сервера (посылает ВК)
		case 'confirmation':
			echo $confirmationToken; // отправляем строку для подтверждения адреса
			break;
		// Если это уведомление о новом сообщении...
		case 'message_new':
		// получаем id автора сообщения
			$userId = $data->object->user_id;
		// через users.get получаем данные об авторе
			$userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&v=5.0")	);
		// Вытаскиваем имя отправителя
			$user_name = $userInfo->response[0]->first_name;
		// Через messages.send используя токен сообщества отправляем ответ
			$request_params = array(
				'message' => "{$user_name}, Ваше сообщение получено!
				В ближайшее время админ группы на него ответит.",
				'user_id' => $userId,
				'access_token' => $token,
				'v' => '5.0'
			);
			$get_params = http_build_query($request_params);
			file_get_contents('https://api.vk.com/method/messages.send?'. $get_params);
			echo('ok'); // Возвращаем "ok" серверу Callback API
			break;
	}
?>