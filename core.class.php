<?php class Core{
	static public function getAnswer($string, $uid){
		if($msg = Core::_isCommand($string, $uid)){
			return $msg;
		}
		
		if($msg = Core::_tryMind($string)){
			return $msg;
		}
		return Core::_getRandomAnswer();
	}
		
	private static function _getRandomAnswer(){
		global $config;
		$filename = 'answers_'.$config['sex'].'.txt';
		if(file_exists($filename)){
			$answers = file($filename);
		}else{
			die("Can't open $filename");
		}
		$rand_key = rand(0, count($answers)-1);
		return $answers[$rand_key];
	}
	
	private static function _tryMind($string){
	global $mind;		
	foreach($mind as $preg=>$answer){
			if(preg_match("-$preg-ui", $string) > 0){
				return $answer;
			}
		}
		return false;
	}
	
	private static function _isCommand($string, $uid){
		$string = strtolower($string);
		if(preg_match('-!помощь-', $string) > 0){
			return Core::command_help();
		}
		
		if(preg_match('-!инфо-', $string) > 0){
			return Core::command_info();
		}
		
		if(preg_match('-поставь мне лайк под аватаркой-', $string) > 0){
			return Core::command_likeAvatar($uid);
		}
		
		if(preg_match('-поставь лайк под аватаркой пользователю-', $string) > 0){
			$uid = 0;
			$result = 1;
			preg_match_all('(0|1|2|3|4|5|6|7|8|9)', $string, $result);
			if(is_array($result)){
				foreach($result[0] as $num){
					$uid = $uid."$num";
				}
				$uid = (int)$uid;
			}
			return Core::command_likeAvatar($uid);
		}
		
		if(preg_match('-добавь меня в друзья-', $string) > 0){
			return Core::command_addFriend($uid);
		}
		
		if(preg_match('-добавь в друзья пользователя-', $string) > 0){
			$uid = 0;
			$result = 1;
			preg_match_all('(0|1|2|3|4|5|6|7|8|9)', $string, $result);
			if(is_array($result)){
				foreach($result[0] as $num){
					$uid = $uid."$num";
				}
				$uid = (int)$uid;
			}
			return Core::command_addFriend($uid);
		}
	}
	
	// Выполнение команд
	private static function command_help(){
		global $config;
		$name = $config['name'];
		return "
				СПИСОК КОМАНД:\r
				!помощь
				!инфо
				!поставь мне лайк под аватаркой
				!поставь лайк под аватаркой пользователю {vk_id}
				!добавь меня в друзья
				!добавь в друзья пользователя {vk_id}
				";
	}
	
	private static function command_info(){
		global $config;
		$name = $config['name'];
		return "Меня зовут $name и я бот. Рожденный быть бесполезным - это явно про меня. Сделали меня ночью 30 июня, потому что мой создатель в ту ночь не мог уснуть и хотел себя отвлечь чем-нибудь, чтобы ему не лезли разные мысли в голову. Зато вот я могу лайк кому-нибудь поставить и даже стараться поддерживать разговор. Попробуй меня, Йоу)
				";
	}
	
	private static function command_likeAvatar($uid){
		global $vk;
		$result = $vk->request('users.get', ['user_ids' => $uid, 'fields' => 'photo_id']);
		$first_name = $result['response'][0]['first_name'];
		$last_name = $result['response'][0]['last_name'];
		$photo_id = $result['response'][0]['photo_id'];
		$photo_id = preg_split('-_-', $photo_id);
		$photo_id = $photo_id[1];
		
		$result = $vk->request('likes.add', ['owner_id' => $uid, 'type' => 'photo', 'item_id' => $photo_id]);
		return "Хорошо :) Я поставил лайк пользователю $first_name $last_name";
	}
	
	private static function command_addFriend($uid){
		global $vk;
		$result = $vk->request('friends.add', ['user_id' => $uid]);
		$result = $vk->request('users.get', ['user_ids' => $uid, 'fields' => 'photo_id']);
		$first_name = $result['response'][0]['first_name'];
		$last_name = $result['response'][0]['last_name'];
		return "Ок. Я добавил в друзья $first_name $last_name";
	}
}