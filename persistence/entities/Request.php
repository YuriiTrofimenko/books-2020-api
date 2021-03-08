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
        $to = $row['userEmail'];
        $subject = "Запрос на книгу {$row['title']}";
        // В письме сообщаем Email пользователя, который запросил книгу
        $message = "Пользователь системы 'CommonLib' ({$this->userEmail}) просит Вас предоставить ему книгу {$row['author']}. {$row['title']}";
        /* $headers = 'From: yurii@localhost' . "\r\n" .
          'Reply-To: yurii@localhost' . "\r\n" .
          'Content-type: text/plain; charset=UTF-8' . "\r\n" .
          'X-Mailer: PHP/' . phpversion(); */
        $headers = "From: {$this->userEmail}" . "\r\n" .
          "Reply-To: {$row['userEmail']}" . "\r\n" .
          'Content-type: text/plain; charset=UTF-8' . "\r\n" .
          'X-Mailer: PHP/' . phpversion();
        return mail($to, $subject, $message, $headers);
      } else {
        throw new Exception('Book not found');
      }
      
      /*// Получаем контекст для работы с БД
      $pdo = getDbContext();
      // Готовим sql-запрос добавления строки в таблицу "Книги"
      $ps = $pdo->prepare("INSERT INTO `Requests` (`userId`, `title`, `author`, `genre`, `description`, `countryId`, `cityId`, `typeId`, `image`, `active`) VALUES (:userId, :title, :author, :genre, :description, :countryId, :cityId, :typeId, :image, :active)");
      // Превращаем объект в массив
      $ar = get_object_vars($this);
      // Удаляем из него первые два элемента - id и created_at, потому что их создаст СУБД
      array_shift($ar);
      array_shift($ar);
      // Выполняем запрос к БД для добавления записи
      $ps->execute($ar);
      //
      $this->id = $pdo->lastInsertId();
      return get_object_vars($this);*/
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
  // Получение списка всех книг из БД
  /*static function filter($args) {
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
          $whereClouse[] = "`b`.`id` < '{$args['lastId']}'";
        }
        if (isset($args['userId'])) {
          $whereClouse[] = "`b`.`userId` = '{$args['userId']}'";
        }
        if (isset($args['active'])) {
          $whereClouse[] = "`b`.`active` = '{$args['active']}'";
        }
        if (isset($args['search']) && $args['search']) {
          $whereClouse[] = "((locate('{$args['search']}', `b`.`title`) > 0) OR (locate('{$args['search']}', `b`.`author`) > 0))";
        }
        if (isset($args['country']) && $args['country']->id) {
          $whereClouse[] = "`b`.`countryId` = '{$args['country']->id}'";
        }
        if (isset($args['city']) && $args['city']->id) {
          $whereClouse[] = "`b`.`cityId` = '{$args['city']->id}'";
        }
        if (isset($args['typeId'])) {
          $whereClouse[] = "`b`.`typeId` = '{$args['typeId']}'";
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
        // Готовим sql-запрос чтения всех строк данных из таблицы "Книги"
        // с подключением связанных таблиц "Страна", "Город", "Тип",
        // сортируем по идентификаторам,
        // пытаемся получить только три значения из строк, идентификаторы которых меньше заданного
        $ps = $pdo->prepare("SELECT `b`.`id`, `b`.`updatedAt`, `b`.`userId`, `b`.`title`, `b`.`author`, `b`.`genre`, `b`.`description`, `co`.`name` AS 'country', `ci`.`name` AS 'city', `ty`.`name` AS 'type', `b`.`image`, `b`.`active` FROM `Requests` AS `b` INNER JOIN `Country` AS `co` ON (`b`.`countryId` = `co`.`id`) INNER JOIN `City` AS `ci` ON (`b`.`cityId` = `ci`.`id`) INNER JOIN `Type` AS `ty` ON (`b`.`typeId` = `ty`.`id`) {$whereClouseString} ORDER BY `b`.`id` DESC LIMIT 3");
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
  }*/
}