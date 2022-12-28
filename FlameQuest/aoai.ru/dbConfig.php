<?php

include('questions.php');

const QUEST_ADMIN = '62381480№';

$connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');


#######Функции БД########



//Функция подсчета количества записей в таблице БД
 function countRows($table) {
    $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
    $sth = "SELECT COUNT(*) FROM $table";
    $res = $connection->query($sth);
    $count = $res->fetchColumn();

    return $count;
 }
 
 
//Функция проверки нахождения пользователя в БД
function isUserInDB ($user_id){
    $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
    $searchFlag = false;
    $rows = countRows('users'); 
    $dataFromRows = $connection->query('SELECT chat_id FROM users')->fetchAll(PDO::FETCH_COLUMN);
   
 
    for ($i=0; $i<$rows; $i++) {
        if ($dataFromRows[$i] == $user_id)
        $searchFlag = true;
    }
    
    return $searchFlag;
}

######################################

// Регистрация команды
function teamRegister($name, $capID) {
    $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');

    $res = $connection->prepare("INSERT INTO teams (ID, teamName, members, capitanID, score) VALUES ('NULL', '$name', '', '$capID', '0')");
    $res->execute();
    $result2 =  $connection->query("UPDATE users SET teamName = '$name'  WHERE chat_id = '$capID'");
    $result2->execute();
}

// Удаление команды
function teamDelete($name) {
    $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');

    $res = $connection->prepare("DELETE FROM teams WHERE teamName = '$name'");
    $res->execute();

}


//Проверка, существует ли команда с таким именем
function isTeamExist($teamName){
    $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
    
    $searchFlag = false;
    $rows = countRows('teams'); 
    $dataFromRows = $connection->query('SELECT teamName FROM teams')->fetchAll(PDO::FETCH_COLUMN);
   
 
    for ($i=0; $i<$rows; $i++) {
        if (($dataFromRows[$i] == $teamName) || ($dataFromRows[$i] == mb_strtolower(($teamName), 'utf-8')))
        $searchFlag = true;
    }
    
    return $searchFlag;
}



// Получить информацию о команде в виде ассоциативного массива по ID VK капитана
// В случае успеха вернёт ассоциативный массив с информацией о команде, в противном случае вернёт -1
function getTeamInfoById($id) {
  $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
  $result = $connection->query("SELECT * FROM teams WHERE capitanID = '$id'");
  $dataFromRows = $result->fetchAll(PDO::FETCH_ASSOC);
  if ($dataFromRows = array_shift($dataFromRows)){
      return $dataFromRows;
  }
  else return 0;
}

// Функция обновления счёта команды
function updateTeamScore($newScore, $id) {
  $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
  $result =  $connection->query("UPDATE teams SET score = $newScore WHERE capitanID = $id");

}

// Функция перебрасывания команды с капитаном $id на задание с номером $newTask
function updateTeamTask($newTask, $id) {
  $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
  $result = $connection->query("UPDATE teams SET currentTask = $newTask WHERE capitanID = $id");

}




// Функция добавления в команду участника (добавление ID человека в список members команд и teamName в список teamName участников)
function addMemberToTeam($id, $teamName) {
 $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
 $result =  $connection->query("UPDATE teams SET members = CONCAT(members, ', $id')  WHERE teamName = '$teamName'");
 $result2 =  $connection->query("UPDATE users SET teamName = '$teamName'  WHERE chat_id = '$id'");
}


// Получить информацию о команде в массиве
function getTeamInfo($user_id) {
    $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
    
    $teamNameArray = $connection->query("SELECT teamName FROM users WHERE chat_id = '$user_id'")->fetch();
    $teamName = $teamNameArray['teamName'];
    // echo $teamName;
    
    $stt = $connection->query("SELECT * FROM teams WHERE teamName = '$teamName'")->fetch(PDO::FETCH_ASSOC);
    // print_r($stt);
    
    return $stt;
}





// Получить информацию о членах команды в массиве
function getTeamMembersInfo($user_id) {
    $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
    
    $teamNameArray = $connection->query("SELECT teamName FROM users WHERE chat_id = '$user_id'")->fetch();
    $teamName = $teamNameArray['teamName'];
    
     $stt = $connection->query("SELECT * FROM teams WHERE teamName = '$teamName'")->fetch(PDO::FETCH_ASSOC);

    return $stt;
}



// Проверить, является ли текущий пользователь бота членом какой-либо команды
// (чтобы исключить возможность повторной регистрации в нескольких командах,
// становления капитаном в другой команде и т.п. Однако подобные функционал
// пока не реализован)
function isMember($id) {
  $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
  $searchFlag = false;
  $rows = countRows('teams');
  $dataFromRows = $connection->query('SELECT members FROM teams')->fetchAll(PDO::FETCH_NUM);

  for ($i=0; $i<$rows; $i++) {
        if (stripos($dataFromRows[$i][0], strval($id)) !== false)
        $searchFlag = true;
    }
    
    return $searchFlag;
}

// Проверить, является ли участник квеста капитаном команды
function isCaptain($id) {
  $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
  // Если игрок не состоит ни в какой команде как участник, то проверим, может быть, он капитан команды
  $searchFlag = false;
  if(!isMember($id)) {
    $rows = countRows('teams');
    $dataFromRows = $connection->query('SELECT capitanID FROM teams')->fetchAll(PDO::FETCH_NUM);
  
    for ($i=0; $i<$rows; $i++) {
      if ($id == $dataFromRows[$i][0]) {
        $searchFlag = true;
      }
    }
  }
  return $searchFlag;
}



// Является ли юзер квеста его админом
function isAdmin($id) {
  if ($id == QUEST_ADMIN) {
    return true;
  }
  else {
    return false;
  }
}





#Задания

//Получить текущее задание команды

function getCurrentTeamTaskID ($capitanID){
    $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
    $dataArray = $connection->query("SELECT currentTask FROM teams WHERE capitanID = '$capitanID'")->fetch();
    
    $taskID = $dataArray['currentTask'];
    
    return $taskID;
}

// Функция перевода статуса задания в завершенное командой
function completeFlagTask($taskID, $user_id) {
  
  $taskRow = 'flagTask'.$taskID;
  
  $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
  $result =  $connection->query("UPDATE teams SET $taskRow = '1' WHERE capitanID = '$user_id'");

}

//Функция проверки, не завершено ли задание уже
/*function isTaskComplete($taskID, $user_id){
    $taskRow = 'flagTask'.$taskID;
    
    $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
    $dataArray = $connection->query("SELECT currentTask FROM teams WHERE capitanID = '$capitanID'")->fetch();

    $taskFlag = $dataArray[$taskRow];
    
    if ( $taskFlag == '1') return true;
    else return false;
}*/


// Определение счёта команды по флажкам

function scoreByFlags($user_id){
    global $task;
    $count = 0;
    

    
    $connection = new PDO('mysql:host=localhost;dbname=u1292455_default;charset=utf8', 'u1292455_default', 'qw_hG6Rw');
    
    for ($i=1;$i<count($task);$i++) {
        $dataArray = $connection->query("SELECT * FROM teams WHERE capitanID = '$user_id'")->fetch();

        $taskFlag = $dataArray["flagTask$i"];
    
        if  ($taskFlag == '1') $count++;
    }
    return $count;
}


// "UPDATE users SET teamName = '$teamName'  WHERE chat_id = '$id'"
