<?php
// Сущность "Город"
class City {
  // уникальный id - будет генерироваться БД при вставке строки
  protected $id;
  // название города
  protected $name;
  // идентификатор страны, в которй находится данный город
  protected $countryId;
  // Конструктор
  function __construct(
    $name
    , $countryId
    , $id = 0
    ) {
    $this->id = $id;
    $this->name = $name;
    $this->countryId = $countryId;
  }
  // вставка строки о городе в БД
  function create () {
    try {
      // Получаем контекст для работы с БД
      $pdo = getDbContext();
      // Превращаем объект в массив
      $ar = get_object_vars($this);
      // Удаляем из него первый элемент - id потому что его создаст СУБД
      array_shift($ar);
      // Если в БД еще нет города с таким названием -
      // сначала добавляем его, иначе - сразу возвращаем данные о нем
      $ps = $pdo->prepare("SELECT * FROM `City` WHERE `name` = :name AND `countryId` = :countryId");
      //Пытаемся выполнить запрос на получение данных
      $resultCode = $ps->execute($ar);
      if ($resultCode && ($row = $ps->fetch())) {
        $this->id = $row['id'];
      } else {
        // Готовим sql-запрос добавления строки в таблицу "Город"
        $ps = $pdo->prepare("INSERT INTO `City` (`name`, `countryId`) VALUES (:name, :countryId)");
        // Выполняем запрос к БД для добавления записи
        $ps->execute($ar);
        // Устанавливаем в поле id текущего объекта модели
        // последний id, сгененрированный БД
        $this->id = $pdo->lastInsertId();
      }
      return get_object_vars($this);
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }
  // Редактирование строки о городе по ее идентификатору
  function edit() {
    try {
      // Удаляем старую версию строки из БД
      City::delete($this->id);
      // Вставляем новую версию строки в БД
      $this->create();
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }
  // Удаление строки из БД по идентификатору
  function delete ($id) {
    try {
      //Получаем контекст для работы с БД
      $pdo = getDbContext();
      // Готовим sql-запрос удаления строки из таблицы "Город"
      $pdo->exec("DELETE FROM `City` WHERE `id` = $id");
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }
  // Получение списка всех городов по идентификатору страны из БД
  static function filter ($args) {
    // Переменная для подготовленного запроса
    $ps = null;
    // Переменная для результата запроса
    $cities = null;
    try {
        // Получаем контекст для работы с БД
        $pdo = getDbContext();
        // Пытаемся получить значения из строк, идентификаторы стран в которых равны заданному
        $ps = $pdo->prepare("SELECT * FROM `City` WHERE `countryId` = '{$args['countryId']}' AND `name` LIKE '{$args['startsWith']}%'");
        // Выполняем
        $ps->execute();
        //Сохраняем полученные данные в ассоциативный массив
        $cities = $ps->fetchAll();
        return $cities;
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }
}