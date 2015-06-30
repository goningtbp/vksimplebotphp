<?php
	header ('Content-type: text/html; charset=utf-8');
	session_start();
	include_once("config.php");
	include_once("vk.class.php");
	include_once("core.class.php");
	include_once("mind.inc.php");
	
	$vk = new vk($config['vk_token']);
	
	// Делаем нас онлайн
	$vk->request('account.setOnline');
	
	// Отвечаем на поступившие сообщения //
    $dialogs = $vk->request('messages.getDialogs', array(
        'count' => '5',
    ));
    $dialogs = $dialogs['response'];
        
    foreach($dialogs as $dialog){    
	   if($dialog['out'] == 1){
		    //если на диалог мы уже ответили, то просто пропускаем
	    }elseif(isset($dialog['uid'])){
		        $vk->request('messages.markAsRead', array(
                    'peer_id' => $dialog['uid'],
                ));
                sleep(1);
                $typing = $vk->request('messages.setActivity', array(
                    'type' => 'typing',
                    'user_id' => $dialog['uid'],
                ));
                sleep(1);
                
                $msgToSend = Core::getAnswer($dialog['body'], $dialog['uid']);
                $result = $vk->request('messages.send', array(
                    'message' => $msgToSend,
                    'uid' => $dialog['uid'],
                ));
                
                print_r($result);
                
	    }
    }
    
    // Случайный репост
    if(time() > $_SESSION['last_repost'] + 3600 * $config['repost_time']){
	     $wall = $vk->request('newsfeed.get', array(
	        'count' => '5',
	        'return_banned' => '0',
	    ));
	    
	    if(!isset($_SESSION['objects'])){
	    	$_SESSION['objects'] = array();
	    }
		$n = rand (0, 4);
		$object_name = 'wall'.$wall['response']['items'][$n]['source_id'].'_'.$wall['response']['items'][$n]['post_id'];
		if(!in_array($object_name, $_SESSION['objects'])){
	    $repost = $vk->request('wall.repost', array(
	        'object' => $object_name,
			));
	    
	    $_SESSION['objects'][] = $object_name;
	    $_SESSION['last_repost'] = time();
	    
	    }
    }

    
?>
<meta http-equiv="Refresh" content="30" />