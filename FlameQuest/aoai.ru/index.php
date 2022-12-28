<?php

#Подключаем основные файлы
include('config.php');
include('dbConfig.php');
include('questions.php');

#Задаём id Админа
$admin_id = '623814802';

#############
#Обработка запроса телеги
#############



https://api.telegram.org/bot1378248920:AAH50owFZFANZvgsz-wfW8etXg8k1yweMBU/setwebhook?url=https://aoai.ru/



#Важные константы 
define('TOKEN', '1378248920:AAH50owFZFANZvgsz-wfW8etXg8k1yweMBU');


#Кнопкиииии



#############
#Подключаем БД
#############




#####Добавление пользователя  в БД#####
 if (isset($user_id)){ 
    if (isUserInDB($user_id)){
    echo "Вы уже в базе";
    }
    else {
      $sth = $connection->prepare("INSERT INTO users (id, name, data_json, chat_id, teamName) VALUES ('NULL', '$fname', '$data', '$user_id', 'Ы')");
      $sth->execute();
    }
}
#######Функции БД########

//  в файле dbConfig



############
#Механика#
############


/* 
===========================================================================
Основное взаимодействие с игрой осуществляется по id ВКонтакте человека, 
который принял при регистрации на себя роль капитана команды
===========================================================================
*/

// Отправка сообщения ОДНОМУ пользователю
function sendMessageToUser($message) {
    global $data;
    // $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
    $method = 'sendMessage';
    $send_data["method"] = 'sendMessage';
    $send_data['chat_id'] = $data['chat']['id'];
    $send_data["text"] = $message;
    sendTelegram($method, $send_data);
}

// Отправка картинки ОДНОМУ пользователю
function sendImageToUser($photoURL) {
    global $data;
    // $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
    $method = 'sendPhoto';
    $send_data["method"] = 'sendPhoto';
    $send_data['chat_id'] = $data['chat']['id'];
    $send_data["photo"] = $photoURL;
    sendTelegram($method, $send_data);
}



// Отправка сообщения ВСЕМ пользователям

function sendMessageToAll($message) {
    $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
    $result = $connection->query("SELECT chat_id FROM users");
    $dataFromRows = $result->fetchAll(PDO::FETCH_COLUMN);
    $send_data["method"] = 'sendMessage';
    $send_data["text"] = "[Трансляция]\n".$message;
    foreach ($dataFromRows as $id){
        $send_data['chat_id'] = $id;
        sendTelegram($method, $send_data);
    }
}
//sendMessageToAll('Привет');


#######Отправка заданий#################################################################

// Отправка сообщения всем участникам команды

function sendMessageToMembers($message) {
  global $data; //$send_data;
  // Обращаемся к БД один раз и работаем с массивом инфы о команде как со статикой
  $capitanID = $data['chat']['id'];
  $teamInfo = getTeamInfoById($capitanID);
//   print_r($teamInfo);
  $teamMembers = $teamInfo["members"];
  $teamMembers = explode(", ", $teamMembers);
  // send_data -- массив параметров для отправки сообщения членам команды
  //$send_data["user_ids"] = $teamMembers;
  $currentTask = $teamInfo["currentTask"];
  $teamName = $teamInfo["teamName"];
  $send_data["text"] = "[Сообщение команде \"$teamName\"]\n".$message;
  $send_data["method"] = 'sendMessage';
  $method = 'sendMessage';
//   print_r($teamMembers);
//   echo getType($teamMembers);
//Отправляем всем членам команды
  foreach ($teamMembers as $teamMember){
    $send_data['chat_id'] = $teamMember;
    sendTelegram($method, $send_data);
  }
//Отправляем капитану команды
  $send_data['chat_id'] = $capitanID;
  sendTelegram($method, $send_data);
}

// Отправка картинки всем участникам команды

function sendImageToMembers($imageURL) {
  global $data; //$send_data;
  // Обращаемся к БД один раз и работаем с массивом инфы о команде как со статикой
  $capitanID = $data['chat']['id'];
  $teamInfo = getTeamInfoById($capitanID);
  $teamMembers = $teamInfo["members"];
  $teamMembers = explode(", ", $teamMembers);
  $currentTask = $teamInfo["currentTask"];
  $teamName = $teamInfo["teamName"];
  $send_data["photo"] = "$imageURL";
  $send_data["method"] = 'sendPhoto';
  $method = 'sendPhoto';
//Отправляем всем членам команды
  foreach ($teamMembers as $teamMember){
    $send_data['chat_id'] = $teamMember;
    sendTelegram($method, $send_data);
  }
//Отправляем капитану команды
  $send_data['chat_id'] = $capitanID;
  sendTelegram($method, $send_data);
}

// Отправка карты
function sendLocationToMembers($latitude, $longitude) {
  global $data; //$send_data;
  // Обращаемся к БД один раз и работаем с массивом инфы о команде как со статикой
  $capitanID = $data['chat']['id'];
  $teamInfo = getTeamInfoById($capitanID);
  $teamMembers = $teamInfo["members"];
  $teamMembers = explode(", ", $teamMembers);
  $currentTask = $teamInfo["currentTask"];
  $teamName = $teamInfo["teamName"];
  $send_data["latitude"] = "$latitude";
  $send_data["longitude"] = "$longitude";
  $send_data["method"] = 'sendLocation';
  $method = 'sendLocation';
//Отправляем всем членам команды
  foreach ($teamMembers as $teamMember){
    $send_data['chat_id'] = $teamMember;
    sendTelegram($method, $send_data);
  }
//Отправляем капитану команды
  $send_data['chat_id'] = $capitanID;
  sendTelegram($method, $send_data);
}


// Отправка звукозаписи
function sendVoiceToMembers($voice) {
  global $data; //$send_data;
  // Обращаемся к БД один раз и работаем с массивом инфы о команде как со статикой
  $capitanID = $data['chat']['id'];
  $teamInfo = getTeamInfoById($capitanID);
  $teamMembers = $teamInfo["members"];
  $teamMembers = explode(", ", $teamMembers);
  $currentTask = $teamInfo["currentTask"];
  $teamName = $teamInfo["teamName"];
  $send_data["voice"] = "$voice";
  $send_data["method"] = 'sendVoice';
  $method = 'sendVoice';
//Отправляем всем членам команды
  foreach ($teamMembers as $teamMember){
    $send_data['chat_id'] = $teamMember;
    sendTelegram($method, $send_data);
  }
//Отправляем капитану команды
  $send_data['chat_id'] = $capitanID;
  sendTelegram($method, $send_data);
}





// Отправка ПРЕДзадания всем участникам команды
function sendPreTaskToMembers($taskID) {
  global $task, $user_id;
  // Получаем инфу о команде
  $currentTask = getTeamInfoById($user_id)["currentTask"];
  // Отправляем членам команды
    if (isset($task["task".$taskID]['text'])){
        sendMessageToMembers($task["task".$taskID]['preTaskText']);
        sendImageToMembers($task["task".$taskID]['preTaskImageURL']);
        sendLocationToMembers(($task["task".$taskID]['latitude']), ($task["task".$taskID]['longitude']));
    }
    else sendMessageToUser("Задания №$currentTask не существует!🙅");
}


// Отправка задания всем участникам команды
function sendTaskToMembers($taskID) {
  global $task, $user_id;
  // Получаем инфу о команде
  $currentTask = getTeamInfoById($user_id)["currentTask"];
  // Отправляем членам команды

    sendMessageToMembers($task["task".$taskID]['text']);
    sendImageToMembers($task["task".$taskID]['imageURL']);
    sendVoiceToMembers($task["task".$taskID]['voice']);


}



//Отправка заданий, статусов их выполнений членам команды
function sendRaitingToMembers($user_id){
    global $data; //$send_data;

    $capitanID = $data['chat']['id'];
    $teamInfo = getTeamInfoById($capitanID);

    $teamMembers = $teamInfo["members"];
    $teamMembers = explode(", ", $teamMembers);

    $currentTask = $teamInfo["currentTask"];
    $teamName = $teamInfo["teamName"];
    

        
    $send_data["text"] = getRaitingText();
    
   
   
    $send_data["method"] = 'sendMessage';
    $method = 'sendMessage';

    foreach ($teamMembers as $teamMember){
        $send_data['chat_id'] = $teamMember;
        sendTelegram($method, $send_data);
    }
    //Отправляем капитану команды
    $send_data['chat_id'] = $capitanID;
    sendTelegram($method, $send_data);
    
}



function sendTaskStatusToMembers($user_id){
    global $data; //$send_data;

    $capitanID = $data['chat']['id'];
    $teamInfo = getTeamInfoById($capitanID);

    $teamMembers = $teamInfo["members"];
    $teamMembers = explode(", ", $teamMembers);

    $currentTask = $teamInfo["currentTask"];
    $teamName = $teamInfo["teamName"];
    

        
    $send_data["text"] = getTaskStatusText();
    
   
   
    $send_data["method"] = 'sendMessage';
    $method = 'sendMessage';

    foreach ($teamMembers as $teamMember){
        $send_data['chat_id'] = $teamMember;
        sendTelegram($method, $send_data);
    }
    //Отправляем капитану команды
    $send_data['chat_id'] = $capitanID;
    sendTelegram($method, $send_data);
    
}





//Отправка заданий, статусов их выполнений одному пользователю
function sendRaitingToUser($user_id){

    global $data;
    // $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
    $method = 'sendMessage';
    $send_data["method"] = 'sendMessage';
    $send_data['chat_id'] = $data['chat']['id'];
    $send_data["text"] = getRaitingText();
    sendTelegram($method, $send_data);

}

function sendTaskStatusToUser($user_id){
    
    global $data;
    $method = 'sendMessage';
    $send_data["method"] = 'sendMessage';
    $send_data['chat_id'] = $data['chat']['id'];
    $send_data["text"] = getTaskStatusText();
    sendTelegram($method, $send_data);
}


//Получение абзаца с рейтингом команд
function getRaitingText(){
    $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
    $teamCapitanIDArray = $connection->query("SELECT capitanID FROM teams ORDER BY score DESC")->fetchAll(PDO::FETCH_COLUMN);
    
    $paragraf = '';
    
    ###Вычленияем первых трёх лидеров и печатаем им медальки
    $teamInfoFirst = getTeamInfoByID($teamCapitanIDArray[0]);
    $teamName = $teamInfoFirst['teamName'];
    $teamScore = $teamInfoFirst['score'];
    $paragraf = $paragraf. "🔥 Команда \"$teamName\". Счёт: $teamScore  \n";
    
    $teamInfoSecond = getTeamInfoByID($teamCapitanIDArray[1]);
    $teamName = $teamInfoSecond['teamName'];
    $teamScore = $teamInfoSecond['score'];
    $paragraf = $paragraf. "🔥 Команда \"$teamName\". Счёт: $teamScore  \n";
    
    $teamInfoThird = getTeamInfoByID($teamCapitanIDArray[2]);
    $teamName = $teamInfoThird['teamName'];
    $teamScore = $teamInfoThird['score'];
    $paragraf = $paragraf. "🔥 Команда \"$teamName\". Счёт: $teamScore  \n";
    ###Вычленияем первых трёх лидеров и печатаем им медальки
   
   
    $teamCapitanIDArrayAllOthers = array_slice($teamCapitanIDArray, 3);
    
     foreach ($teamCapitanIDArrayAllOthers as $teamCapitanID){
        $teamInfoArr = getTeamInfoByID($teamCapitanID);
    
        $teamName = $teamInfoArr['teamName'];
        $teamScore = $teamInfoArr['score'];
        
        $paragraf = $paragraf. "      Команда \"$teamName\". Счёт: $teamScore \n";
     }
    
    return $paragraf;
    
}


function getTaskStatusText(){
    global $task;
    global $user_id;
    
    $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');

    $paragraf = '';
    
    $capitan_id = getTeamInfo($user_id)['capitanID'];
    
    for ($i=1;$i<count($task);$i++) {
        $dataArray = $connection->query("SELECT * FROM teams WHERE capitanID = '$capitan_id'")->fetch();

        $taskFlag = $dataArray["flagTask$i"];
    
        if  ($taskFlag == '1'){
            $paragraf = $paragraf."✅Задание $i Выполнено  \n";
        }
        else {
            $paragraf = $paragraf."❌Задание $i Не выполнено  \n";
        }
        
    }
    
    
    
    
    
    return $paragraf;
    
}

##############################################################################

// Получить уровень доступа к квесту 3 - админ, 2 - капитан, 1 - участник команды, 0 - незарегистрированный ни в качестве капитана ни в качестве участника команды, ноубади, ноунейм
function getAccessLayer($id) {
  if (isAdmin($id)) {
    return 3;
  }
  else if (isCaptain($id)) {
    return 2;
  }
  else if (isMember($id)) {
    return 1;
  }
  else {
    return 0;
  }
}



// Слои доступа к игре
/*
0 -- стандартное состояние, по умолчанию доступное всем
доступные команды:
привет
команда ИмяКоманды -- защититься от повторных и пустых регистраций
участники
справка
1 -- игроки команды
2 -- капитаны команд, утвержденные администратором квеста
играть
добавить
подсказка
очки
игроки
3 -- администратор квеста
Запретить командам входить в игру после запуска игры
*/


# Принимаем запрос
$data = json_decode(file_get_contents('php://input'), TRUE);
#Обрабатываем ручной ввод или нажатие на кнопку
$data = $data['callback_query'] ? $data['callback_query'] : $data['message'];




//Получаем информацию о пользователе
//Получаем id Пользователя
$user_id = $data['from']['id'];
    
//Записываем остальные данные о пользователе
$fname = $data['chat']['first_name']; // выделяем имя собеседника
$lname = $data['chat']['last_name'];  // выделяем фамилию собеседника
$uname = $data['chat']['username'];   // выделяем ник собеседника

//Записываем сообщение пользователя
$messageNoLower = ($data['text'] ? $data['text'] : $data['data']);
$message = mb_strtolower(($data['text'] ? $data['text'] : $data['data']), 'utf-8');
    
//Записываем инфу о команде пользователя, если он капитан
if (isset(getTeamInfoById($user_id)["capitanID"])) {
  $teamInfo = getTeamInfoById($user_id);
  $currtask = $teamInfo["currentTask"];
}


// ================================ //
// ОСНОВНЫЕ НАСТРОЙКИ ДИНАМИКИ ИГРЫ //##########################################################################################
// ================================ //

###############################
// Admin panel
##############################

if (getAccessLayer($user_id)==3) {

    if ($message == 'кто я') {
        sendMessageToUser( "Ты Админ, поздравляю!" );
    }
    else if ((mb_substr($messageNoLower,0,15)=="удалить команду") || (mb_substr($messageNoLower,0,15)=="Удалить команду")) {
        $teamname = mb_substr($messageNoLower, 16, strlen($messageNoLower));
        if ($teamname != "") {   
            if (isTeamExist($teamname)){
                teamDelete($teamname);
                sendMessageToUser("Команда \"$teamname\" успешно удалена.");
            }
            else{
                sendMessageToUser("Команда с таким именем не существует!");
            }
        }
        else {
            sendMessageToUser("Имя команды не может быть пустой строкой! Повторите ввод!");
        }
    }
    else if (mb_substr($message,0,14)=="отправить всем") {
        $messageToAll = mb_substr($message, 15, strlen($message));
        if ($messageToAll != "") {
            sendMessageToAll($messageToAll);
        }
    
        else {
            sendMessageToUser("Сообщение не может быть пустой строкой! Повторите ввод!");
        }
    }
    else sendMessageToUser("Ваше сообщение не является ни правильным ответом на задание, ни ключом к какому-либо заданию :с ");
    
}

###############################
// Capitan panel
##############################


if (getAccessLayer($user_id)==2) {
    
    
    
    $currentTaskID = getCurrentTeamTaskID($user_id);
    
    
    if ($message == 'справка') {
        sendMessageToUser( $sysmsg["rules"] );
    }
    
    else if ($message == 'кто я') {
        $teamName = getTeamInfo($user_id)['teamName'];
        sendMessageToUser( "Ты Капитан команды \"$teamName\", поздравляю!" );
    }
    
    else if ($message == 'начать') {
        $teamName = getTeamInfo($user_id)['teamName'];
        
        sendMessageToUser("Главное меню квеста\nКоманда 🚩$teamName 🚩");
        
        sendTaskStatusToUser($user_id);
        
        sendImageToUser("https://aoai.ru/images/Map2.PNG");

        sendRaitingToUser($user_id);
    }
    else if ($message == 'счёт' || $message == 'счет') {
        $teamName = getTeamInfo($user_id)['teamName'];
        sendMessageToUser("Счёт команды \"$teamName\": ". scoreByFlags($user_id));
    }
    else if ($message == 'игроки') {
        
        $teamName = getTeamInfo($user_id)['teamName'];
        sendMessageToUser("Игроки команды \"$teamName\":");
        
        $membersString = getTeamMembersInfo($user_id)['members'];
        $membersArray = explode(", ", $membersString);
        
        
        foreach ($membersArray as $member){
            $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
            $dataFromRows = $connection->query("SELECT name FROM users WHERE chat_id = '$member'")->fetch();
            $name = $dataFromRows['name'];
            
            sendMessageToUser("$name "); 
         }
         
        $teamCapitanID = getTeamInfo($user_id)['capitanID'];
        $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
        $dataFromRows = $connection->query("SELECT name FROM users WHERE chat_id = '$user_id'")->fetch();
        $name = $dataFromRows['name'];

        sendMessageToUser("Капитан команды:");
        sendMessageToUser($name); 
    }
    
    

    
    
    ######ЗАДАНИЯ######
    
    else if ($message == 'текущее задание'){

        sendPreTaskToMembers(getCurrentTeamTaskID($user_id));
        
    }
   
    
    else if ($message == 'меню'){

        $teamName = getTeamInfo($user_id)['teamName'];
        
        sendMessageToUser("Главное меню квеста\nКоманда 🚩$teamName 🚩");
        
        sendTaskStatusToUser($user_id);
        
        sendImageToUser("https://aoai.ru/images/Map2.PNG");

        sendRaitingToUser($user_id);


    }
    
    
    else if (mb_substr($message,0,7)=="задание") {
        $taskID = mb_substr($message, 8, strlen($message));
        if ($taskID != "") {
            updateTeamTask($taskID, $user_id);
            sendPreTaskToMembers(getCurrentTeamTaskID($user_id));
        }
    
        else {
            sendMessageToUser("Номер задания не может быть пустой строкой! Повторите ввод!");
        }
    }
    
    /*else if ($message == 'счёт' || $message == 'счет'){

        updateTeamTask('0', $user_id);
        sendTaskToMembers(getCurrentTeamTaskID($user_id));
        $i=1;

    }*/
    
   //Будем давать доступ к заданию по ключевому слову, т.е. если оно известно -- то можно показать задание, т.о. нужно только один раз узнать слово, а потом можно будет в любое время вернуться к заданию 

    else if (isThereRightKey()) {
        for ($i = 0; $i<count($task); $i++){
            if ($message == $task["task" . $i]['preTaskAnswer']){
                updateTeamTask($i, $user_id);
                sendTaskToMembers($i);
            }
        }
    }
        
    else if (isThereRightAnswer()) {
        for ($i = 0; $i<count($task); $i++){
            if ($message == $task["task" . $i]['taskAnswer']){
                completeFlagTask($i, $user_id);
                updateTeamScore(scoreByFlags($user_id), $user_id);
                sendMessageToMembers("Поздравляю! Вы правильно ответили на задание $i");
                
                
                $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
                $teamInfoArr = getTeamInfoByID($user_id);
                $teamName = $teamInfoArr['teamName'];
                $teamScore = $teamInfoArr['score'];
                sendMessageToAll("Команда \"$teamName\" справилась с заданием $i! Теперь её счёт: $teamScore!");
                
                if ($teamScore == (count($task)-1)){
                    sendMessageToMembers("Поздравляю, \"$teamName\"! Вы успешно выполнили все задания квеста! Вы просто супер!");
                    sendMessageToAll("Команда \"$teamName\" ответила на все задания и завершила выполнение квеста!");
                }
            }
        }    
    }
    else sendMessageToUser("Ваше сообщение не является ни правильным ответом на задание, ни ключом к какому-либо заданию :с ");

}

    

//Проверка, есть ли в сообщении правильный ответ к заданию
function isThereRightAnswer(){
    global $task;
    global $message;
    $flag = false;
    
    for ($i = 0; $i<count($task); $i++){
           if ($message == $task["task" . $i]['taskAnswer']){
                // updateTeamTask($i, $user_id);
                // completeFlagTask($i, $user_id);
                // sendMessageToMembers("Задание $i успешно выполнено");
                 $flag = true;
            }
    }
    return $flag;
}
//Проверка, есть ли в сообщении правильный ключ к заданию
function isThereRightKey(){
    global $task;
    global $message;
    $flag = false;
    
    for ($i = 0; $i<count($task); $i++){
           if ($message == $task["task" . $i]['preTaskAnswer']){
                // updateTeamTask($i, $user_id);
                // completeFlagTask($i, $user_id);
                // sendMessageToMembers("Задание $i успешно выполнено");
                 $flag = true;
            }
    }
    return $flag;
}

//Проверка, есть ли в сообщении правильный ответ либо ключ к заданию
function isThereRightAnswerOrKey(){
    global $task;
    global $message;
    $flag = false;
    // sendMessageToMembers("туть работает");
    // return true;
    for ($i = 0; $i<count($task); $i++){
            if ($message == $task["task" . $i]['preTaskAnswer']){
                //  updateTeamTask($i, $user_id);
                //  sendTaskToMembers($i);
                $flag = true;
            }
    }
    for ($i = 0; $i<count($task); $i++){
           if ($message == $task["task" . $i]['taskAnswer']){
                // updateTeamTask($i, $user_id);
                // completeFlagTask($i, $user_id);
                // sendMessageToMembers("Задание $i успешно выполнено");
                 $flag = true;
            }
    }
    return $flag;
}











###############################
// Member panel
##############################

if (getAccessLayer($user_id)==1) {
  // ПОКАЗАТЬ СЧЁТ
  if ($message == 'счёт' || $message == 'счет') {
    $capitanID = getTeamInfo($user_id)['capitanID'];
    $teamName = getTeamInfo($user_id)['teamName'];
    sendMessageToUser("Счёт команды \"$teamName\": ". scoreByFlags($capitanID));
  }
  
  else if ($message == 'кто я') {
        $teamName = getTeamInfo($user_id)['teamName'];
        sendMessageToUser( "Ты Игрок команды \"$teamName\", поздравляю!" );
    }


  else if ($message == 'справка') {
    sendMessageToUser( $sysmsg["rules"] );
  }
  
  else if ($message == 'игроки') {
        
       $teamName = getTeamInfo($user_id)['teamName'];
        sendMessageToUser("Игроки команды \"$teamName\":");
        
        $membersString = getTeamMembersInfo($user_id)['members'];
        $membersArray = explode(", ", $membersString);
        
        
        foreach ($membersArray as $member){
            $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
            $dataFromRows = $connection->query("SELECT name FROM users WHERE chat_id = '$member'")->fetch();
            $name = $dataFromRows['name'];
            
            sendMessageToUser("$name "); 
         }
        
        //узнаем капитана команды 
        $teamCapitanID = getTeamInfo($user_id)['capitanID'];
        $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
        $dataFromRows = $connection->query("SELECT name FROM users WHERE chat_id = '$teamCapitanID'")->fetch();
        $name = $dataFromRows['name'];

        sendMessageToUser("Капитан команды:");
        sendMessageToUser($name); 
    }
    
    else if ($message == 'меню'){

        $teamName = getTeamInfo($user_id)['teamName'];
        
        sendMessageToUser("Главное меню квеста\nКоманда 🚩$teamName 🚩");
        
        sendTaskStatusToUser($user_id);
        
        sendImageToUser("https://aoai.ru/images/Map.PNG");

        sendRaitingToUser($user_id);


    }
  

  else {
    sendMessageToUser("Неверный уровень доступа, команда или логика взаимодействия. Воспользуйтесь командой \"справка\". (Бот тебя не понял :с )");
  }

}





###############################
// Nobodys panel
##############################
if (getAccessLayer($user_id)==0){
    
    if (isset($user_id)){ 
    if (isUserInDB($user_id)){
    echo "Вы уже в базе";
    }
    else {
      $sth = $connection->prepare("INSERT INTO users (id, name, data_json, chat_id, teamName) VALUES ('NULL', '$fname', '$data', '$user_id', 'Ы')");
      $sth->execute();
      
      $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
    
      $userNameArray = $connection->query("SELECT name FROM users WHERE chat_id = '$user_id'")->fetch();
      $userName = $userNameArray['name'];
      sendMessageToAll("Пользователь $userName присоединился к Боту, добро пожаловать!");
    }
}
    
    if ($message == 'привет'){
        sendMessageToUser("$fname! Рады тебя приветствовать на квесте Пламени! Скорее вводи \"справка\", читай правила, регистрируй команду и вступай в игру!");
    }
    else if ($message == 'кто я') {
        $teamName = getTeamInfo($user_id)['teamName'];
        sendMessageToUser( "Ты пока ещё не состоишь в команде, скорее пиши \"создать НазваниеКоманды\", чтобы создать команду, или \"вступить НазваниеКоманды\", чтобы вступить в существующую команду!" );
    }
    else if ($message == 'справка'){
        sendMessageToUser( $sysmsg["rules"] );
    }
    else if ((mb_substr($messageNoLower,0,7)=="создать") || (mb_substr($messageNoLower,0,7)=="Создать")) {
        $teamname = mb_substr($messageNoLower, 8, strlen($messageNoLower));
        if ($teamname != "") {   //Можно создать команды с одинаковым именем!
            if (! isTeamExist($teamname)){
                // Сообщение пользователю
                sendMessageToUser("Регистрация прошла успешно! Приветствуем $teamname в квесте! Пиши \"игроки\" для просмотра игроков команды, а также пиши \"начать\", чтобы приступить к выполнению заданий!");
                // Транслировать событие регистрации в игре
                // sendMessageToAll("[Трансляция]\nКоманда $teamname теперь в квесте!"); РАСКОМЕНТИТЬ!
                // Регистрация команды в базе данных
                teamRegister($teamname, $user_id);
                sendMessageToAll("Команда \"$teamname\" вступила в игру!");
            }
            else{
                sendMessageToUser("Команда с таким именем уже существует, если хочешь вступить в неё, пиши \"вступить названиекоманды\"  ");
            }
        }
        else {
            sendMessageToUser("Имя команды не может быть пустой строкой! Повторите ввод!");
        }
    }
    else if (mb_substr($message,0,8)=="вступить" || mb_substr($message,0,8)=="Вступить") {
        $teamname = mb_substr($messageNoLower, 9, strlen($messageNoLower));
        if ($teamname != "") {   //Можно создать команды с одинаковым именем!
           if (isTeamExist($teamname)){ // Сообщение пользователю
             sendMessageToUser("Регистрация прошла успешно! Приветствуем $fname в команде $teamname! Пиши \"игроки\" для просмотра номеров. И жди, пока Капитан команды начнёт первое задание");
                // Транслировать событие регистрации в игре
                // sendMessageToAll("[Трансляция]\nКоманда $teamname теперь в квесте!"); РАСКОМЕНТИТЬ!
                // Регистрация команды в базе данных
                addMemberToTeam($user_id, $teamname);
           }
           else{
                sendMessageToUser("Команда с таким именем ещё не существует, если хочешь создать её, пиши \"создать НазваниеКоманды\"  ");
            }
        }
        else {
            sendMessageToUser("Имя команды не может быть пустой строкой! Повторите ввод!");
        }
    }
    else{
        sendMessageToUser("Неверный уровень доступа, команда или логика взаимодействия. Воспользуйтесь командой \"справка\". (Бот тебя не понял :с )");
    }
}





/*
  for ($i = 0; $i<count($task); $i++){
            if ($message == $task["task" . $i]['preTaskAnswer']){
                //  updateTeamTask($i, $user_id);
                //  sendTaskToMembers($i);
                return true;
                sendMessageToMembers(count($task));
            }
            else if ($message == $task["task" . $i]['taskAnswer']){
                // updateTeamTask($i, $user_id);
                // completeFlagTask($i, $user_id);
                // sendMessageToMembers("Задание $i успешно выполнено");
                 return true;
            }
            else  return false;*/



/*
$send_data['chat_id'] = $data['chat']['id'];
$send_data['text'] = $currtask;                 //Проверка
sendTelegram('sendMessage', $send_data);
*/




#############
#Обрабатываем сообщение
#############

/*switch ($message)
{
	case 'я в базе':
		$method = 'sendMessage';
		
		if (isUserInDB($user_id)){
		
	    	$send_data = [
	        'text' => 'Есть'
	 	    ];
	    
		}
		else{
		    
		    $send_data = [
	        'text' => 'Нет'
	 	    ];
		}
		
		break;
	
	case 'я':
		$method = 'sendMessage';
		$send_data = [
	    'text' => 'ID: ' . $user_id . "\nFirst name: " . $fname . "\nLast name: " . $lname . "\nNick name: " . $uname
	    ];
		break;
	
	case 'id':
		$method = 'sendMessage';
		
		if ($user_id == $admin_id) 
		{
		    $send_data = [
		    'text' => $user_id
		    ];
		}
		else 
		{
		    $send_data = [
		    'text' => 'Ты не админ'
		    ];
		}
		
		break;
	
	case 'музыка':
		$method = 'sendVoice';
		$send_data = [
			'voice' => 'https://aoai.ru/voice/voice.mp3'   
		];
		break;
	
	case 'фото':
		$method = 'sendPhoto';
		$send_data = [
			'photo' => 'https://aoai.ru/images/iscra.png'   
		];
		break;
		
	case 'карта':
		$method = 'sendLocation';
		$send_data = [
			'latitude' => '59.98346750051541',   
			'longitude' => '30.256473477546585'
 
		];
		break;

	case 'кнопки':
		$method = 'sendMessage';
		$send_data = [
			'text' => 'Вот мои кнопки',
			'reply_markup' => [
				'resize_keyboard' => true,
				'keyboard' => [
					[
						['text' => 'Ы'],
						['text' => '1'],
					],
					[
						['text' => 'ыфыв'],
						['text' => 'фыв'],
					]
				]
			]
		];
		break;

	default:
		$send_data = [
			'method' => 'sendMessage',
			'text' => 'Не понимаю о чем ',
			'user_ids' => ""
		];
}
*/

#############
#Отправка ответного сообщения от бота
#############
#Добавляем данные пользователя
// $send_data['chat_id'] = $data['chat']['id'];

// print_r($send_data);


// $res = sendTelegram($method, $send_data);
// print_r($res);
function sendTelegram($method, $data, $headers = [])
{
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => 'https://api.telegram.org/bot' . TOKEN . '/' . $method,
		CURLOPT_POSTFIELDS => json_encode($data),
		CURLOPT_HTTPHEADER => array_merge(array("Content-Type: application/json"), $headers)
	]);

	$result = curl_exec($curl);
	curl_close($curl);
	return (json_decode($result, 1) ? json_decode($result, 1) : $result);
}