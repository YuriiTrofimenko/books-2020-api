<?php
// Сущность "Запрос на получение книги"
class Request {
  // уникальный id - будет генерироваться БД при вставке строки
  protected $id;
  // временная метка добавления запроса
  protected $createdAt;
  // идентификатор книги, на которую подан запрос
  protected $bookId;
  // Email пользователя Google
  protected $userEmail;
  // Конструктор
  function __construct(
    $userEmail
    , $bookId
    , $id = 0
    , $createdAt = ''
    ) {
    $this->id = $id;
    $this->createdAt = $createdAt;
    $this->bookId = $bookId;
    $this->userEmail = $userEmail;
  }
  // добавление запроса на книгу
  function create () {
    try {
      $toEmail;
      $bookInfo;
      // При помощи id книги определяем,
      // какая книга была запрошена и на какой Email отправлять письмо
      require_once('Book.php');
      // Получаем контекст для работы с БД
      $pdo = getDbContext();
      // Готовим запрос к БД для получения данных книги по ее id
      $ps = $pdo->prepare("SELECT * FROM `Books` WHERE `id` = :id");
      //Пытаемся выполнить запрос на получение данных
      $resultCode = $ps->execute(["id"=>$this->bookId]);
      // Если такая книга найдена
      if ($resultCode && ($row = $ps->fetch())) {
        // 1. Вставка строки о запросе на получение книги в БД
        // Получаем контекст для работы с БД
        $pdo = getDbContext();
        // Готовим sql-запрос добавления строки в таблицу "Запросы"
        $ps = $pdo->prepare("INSERT INTO `Requests` (`userEmail`, `bookId`) VALUES (:userEmail, :bookId)");
        // Превращаем объект в массив
        $ar = get_object_vars($this);
        // Удаляем из него первые два элемента - id и created_at, потому что их создаст СУБД
        array_shift($ar);
        array_shift($ar);
        // Выполняем запрос к БД для добавления записи
        $ps->execute($ar);
        // Добавление id, сгенерированного СУБД, в текущий объект сущности "Запрос"
        $this->id = $pdo->lastInsertId();
        $requestEntityArray = get_object_vars($this);
        // 2. попытка уведомить пользователя книги о запросе на ее получение
        // от другого пользователя
        // Email текущего пользователя книги, на который нужно отправить письмо
        $to = $row['userEmail'];
        $subject = "Запрос на книгу {$row['title']}";
        // В письме сообщаем Email пользователя, который запросил книгу
        $message = "Пользователь системы 'CommonLib' ({$this->userEmail}) просит Вас предоставить ему книгу {$row['author']}. {$row['title']}";
        /* $headers = 'From: yurii@localhost' . "\r\n" .
          'Reply-To: yurii@localhost' . "\r\n" .
          'Content-type: text/plain; charset=UTF-8' . "\r\n" .
          'X-Mailer: PHP/' . phpversion(); */
        $headers = "From: $this->userEmail" . "\r\n" .
          "Reply-To: $to" . "\r\n" .
          'Content-type: text/plain; charset=UTF-8' . "\r\n" .
          'X-Mailer: PHP/' . phpversion();
        try {
          $requestEntityArray['isOwnerNotified'] = mail($to, $subject, $message, $headers);
        } catch (\Throwable $th) {
          //throw $th;
        }
        return $requestEntityArray;
      } else {
        throw new Exception('Книга не найдена');
      }
    } catch (PDOException $e) {
      /*// Если произошла ошибка - возвращаем ее текст
      $err = $e->getMessage();
      if (substr($err, 0, strrpos($err, ":")) == 'SQLSTATE[23000]:Integrity constraint violation') {
        return 1062;
      } else {
        return $e->getMessage();
      }*/
      throw new Exception($e->getMessage());
    }
  }
  /* // Редактирование строки о книге по ее идентификатору
  function edit() {
    try {
      // Удаляем старую версию строки из БД
      Request::delete($this->id);
      // Вставляем новую версию строки в БД
      $this->create();
    } catch (PDOException $e) {
      $err = $e->getMessage();
      if (substr($err, 0, strrpos($err, ":")) == 'SQLSTATE[23000]:Integrity constraint violation') {
        return 1062;
      } else {
        return $e->getMessage();
      }
    }
  }*/
  /*// Удаление строки книге из БД по идентификатору
  function delete ($id) {
    try {
      // Получаем контекст для работы с БД
      $pdo = getDbContext();
      // Готовим sql-запрос удаления строки из таблицы "Книги"
      $pdo->exec("DELETE FROM `Requests` WHERE `id` = $id");
    } catch (PDOException $e) {
      $err = $e->getMessage();
      if (substr($err, 0, strrpos($err, ":")) == 'SQLSTATE[23000]:Integrity constraint violation') {
        return 1062;
      } else {
        return $e->getMessage();
      }
    }
  }*/
  // Получение списка всех запросов к данному владельцу книг из БД
  static function filter($args) {
    // Переменная для подготовленного запроса
    $ps = null;
    // Переменная для результата запроса
    $requests = null;
    try {
        // Получаем контекст для работы с БД
        $pdo = getDbContext();
        // Массив для условий запроса
        $whereClouse = [];
        // Сбор условий запроса в массив
        if (isset($args['lastId'])) {
          $whereClouse[] = "`r`.`id` < '{$args['lastId']}'";
        }
        if (isset($args['userId'])) {
          $whereClouse[] = "`b`.`userId` = '{$args['userId']}'";
        }
        $whereClouseString = 'WHERE ';
        $expressionCount = 0;
        foreach ($whereClouse as $expression) {
          if ($expressionCount++ == 0) {
            $whereClouseString .=  $expression;
          } else {
            $whereClouseString .= ' AND ' . $expression;
          }
        }
        // Готовим sql-запрос чтения всех строк данных из таблицы "Запросы"
        // с подключением связанных таблиц: "Книги",
        // сортируем по идентификаторам,
        // пытаемся получить только десять значений из строк, идентификаторы которых меньше заданного
        $ps = $pdo->prepare("SELECT `r`.`id`, `r`.`createdAt`, `r`.`bookId`, `r`.`userEmail`, IF(((`b`.`volumeOrIssue` IS NULL) OR (`b`.`volumeOrIssue` = '')), `b`.`title`, CONCAT(`b`.`title`, ' (', `b`.`volumeOrIssue`, ')')) AS 'bookName' FROM `Requests` AS `r` INNER JOIN `Books` AS `b` ON (`r`.`bookId` = `b`.`id`) {$whereClouseString} ORDER BY `r`.`createdAt` DESC LIMIT 10");
        // echo "SELECT `b`.`id`, `b`.`updatedAt`, `b`.`userId`, `b`.`title`, `b`.`author`, `b`.`genre`, `b`.`description`, `co`.`name` AS 'country', `ci`.`name` AS 'city', `ty`.`name` AS 'type', `b`.`image`, `b`.`active` FROM `Requests` AS `b` INNER JOIN `Country` AS `co` ON (`b`.`countryId` = `co`.`id`) INNER JOIN `City` AS `ci` ON (`b`.`cityId` = `ci`.`id`) INNER JOIN `Type` AS `ty` ON (`b`.`typeId` = `ty`.`id`) {$whereClouseString} ORDER BY `b`.`id` DESC LIMIT 3";
        // Выполняем
        $ps->execute();
        //Сохраняем полученные данные в ассоциативный массив
        $requests = $ps->fetchAll();
        return $requests;
    } catch (PDOException $e) {
        echo $e->getMessage();
        return false;
    }
  }
  static function getCount ($userId) {
    try {
      // Получаем контекст для работы с БД
      $pdo = getDbContext();
      // Готовим sql-запрос удаления строки из таблицы "Requests"
      $ps = $pdo->prepare("SELECT COUNT(*) AS `totalCount` FROM `Requests` AS `r` INNER JOIN `Books` AS `b` ON (`r`.`bookId` = `b`.`id`) WHERE `b`.`userId` = '$userId'");
      // Выполняем
      $ps->execute();
      //Сохраняем полученные данные в ассоциативный массив
      $count = $ps->fetch();
      return $count;
    } catch (PDOException $e) {
      $err = $e->getMessage();
      if (substr($err, 0, strrpos($err, ":")) == 'SQLSTATE[23000]:Integrity constraint violation') {
        return 1062;
      } else {
        return $e->getMessage();
      }
    }
  }
}