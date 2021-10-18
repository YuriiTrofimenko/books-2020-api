<?php
// Сущность "Страна"
class Country {
  // уникальный id - будет генерироваться БД при вставке строки
  protected $id;
  // название страны
  protected $name;
  // Конструктор
  function __construct(
    $name
    , $id = 0
    ) {
    $this->id = $id;
    $this->name = $name;
  }
  // вставка строки о стране в БД
  function create () {
    try {
      // Получаем контекст для работы с БД
      $pdo = getDbContext();
      // Превращаем объект в массив
      $ar = get_object_vars($this);
      // Удаляем из него первый элемент - id потому что его создаст СУБД
      array_shift($ar);
      // Если в БД еще нет страны с таким названием -
      // сначала добавляем ее, иначе - сразу возвращаем данные о ней
      $ps = $pdo->prepare("SELECT * FROM `Country` WHERE `name` = :name");
      //Пытаемся выполнить запрос на получение данных
      $resultCode = $ps->execute($ar);
      if ($resultCode && ($row = $ps->fetch())) {
        $this->id = $row['id'];
      } else {
        // Готовим sql-запрос добавления строки в таблицу "Страна"
        $ps = $pdo->prepare("INSERT INTO `Country` (`name`) VALUES (:name)");
        // Выполняем запрос к БД для добавления записи
        $ps->execute($ar);
        $this->id = $pdo->lastInsertId();
      }
      return get_object_vars($this);
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }
  // Редактирование строки о стране по ее идентификатору
  function edit() {
    try {
      // Удаляем старую версию строки из БД
      Country::delete($this->id);
      // Вставляем новую версию строки в БД
      $this->create();
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }
  // Удаление строки из БД по идентификатору
  function delete ($id) {
    try {
      // Получаем контекст для работы с БД
      $pdo = getDbContext();
      // Готовим sql-запрос удаления строки из таблицы  "Страна"
      $pdo->exec("DELETE FROM `Country` WHERE `id` = $id");
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }
  // Получение списка всех стран из БД
  static function getAll () {
    // Переменная для подготовленного запроса
    $ps = null;
    // Переменная для результата запроса
    $countries = null;
    try {
        // Получаем контекст для работы с БД
        $pdo = getDbContext();
        // пытаемся получить все записи и странах
        $ps = $pdo->prepare("SELECT * FROM `Country`");
        // Выполняем
        $ps->execute();
        //Сохраняем полученные данные в ассоциативный массив
        $countries = $ps->fetchAll();
        return $countries;
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }
  // Получение списка стран из БД
  static function filter($args) {
    // Переменная для подготовленного запроса
    $ps = null;
    // Переменная для результата запроса
    $countries = null;
    try {
        // Получаем контекст для работы с БД
        $pdo = getDbContext();
        $countries = null;
        if($args['startsWith'] !== ''){
          // пытаемся получить все записи и странах
          $ps = $pdo->prepare("SELECT * FROM `Country` WHERE `name` LIKE '{$args['startsWith']}%'");
          // Выполняем
          $ps->execute();
          //Сохраняем полученные данные в ассоциативный массив
          $countries = $ps->fetchAll();
        } else {
          $countries = [];
        }
        return $countries;
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }
}